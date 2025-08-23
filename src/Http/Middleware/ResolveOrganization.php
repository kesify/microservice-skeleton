<?php

namespace Kesify\MicroserviceSkeleton\Http\Middleware;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redis;
use Kesify\MicroserviceSkeleton\Traits\APIResponse;
use Kesify\MicroserviceSkeleton\Support\AccessToken;
use Kesify\MicroserviceSkeleton\Services\OrganizationService; // <— neu

class ResolveOrganization
{
    use APIResponse;

    /**
     * @throws BindingResolutionException
     */
    public function handle(Request $request, Closure $next)
    {
        $headersCfg = config('gateway.headers', []);

        $orgId  = $request->attributes->get('ctx_org_id');
        $userId = $request->attributes->get('ctx_user_id');

        if (!$orgId) {
            $orgId  = $request->header($headersCfg['org_id']  ?? 'X-Org-Id', '');
            $userId = $userId ?: $request->header($headersCfg['user_id'] ?? 'X-User-Id', '');
        }

        $orgSvc  = OrganizationService();

        if ($orgId) {
            $organization = $orgSvc->getOrganization($orgId);

            if (!$organization || empty($organization->database)) {
                return $this->apiResponse([
                    'success'    => false,
                    'message'    => 'Organization database not configured',
                    'error_code' => 'ORG-DB-0',
                ], 503);
            }

            try {
                $orgSvc->setOrganizationDatabase($organization->database);
            } catch (\Throwable $e) {
                report($e);
                return $this->apiResponse([
                    'success'    => false,
                    'message'    => 'Failed to switch organization database',
                    'error_code' => 'ORG-DB-1',
                ], 500);
            }

            $ctx = [
                'organization_id' => $orgId,
                'organization_name' => $organization->name,
                'user_id'         => $userId ?: null,
                'database'        => $organization->database,
            ];

            $request->attributes->add([
                'organization'         => $ctx,
                'organization_id'      => $ctx['organization_id'],
                'organization_name'      => $ctx['organization_name'],
                'organization_user_id' => $ctx['user_id'],
                'organization_database'=> $ctx['database'],
            ]);

            setPermissionsTeamId($ctx['organization_id']);
            App::instance('organization', $organization);

            return $next($request);
        }

        $authHeader = $request->header('Authorization');
        if ($authHeader) {
            $token = AccessToken::fromRequest($authHeader);
            if ($token) {
                $hash = AccessToken::hash($token);
                $key  = "at:{$hash}:org";
                if ($json = Redis::get($key)) {
                    $ctx = json_decode($json, true) ?: [];
                    if (!empty($ctx['organization_id'])) {
                        $organization = $orgSvc->getOrganization($ctx['organization_id']);

                        if (!$organization || empty($organization->database)) {
                            return $this->apiResponse([
                                'success'=>false,'message'=>'Organization database not configured','error_code'=>'ORG-DB-0'
                            ], 503);
                        }

                        try {
                            $orgSvc->setOrganizationDatabase($organization->database);
                        } catch (\Throwable $e) {
                            report($e);
                            return $this->apiResponse([
                                'success'=>false,'message'=>'Failed to switch organization database','error_code'=>'ORG-DB-1'
                            ], 500);
                        }

                        $ctx['database'] = $organization->database;

                        $request->attributes->add([
                            'organization'          => $ctx,
                            'organization_id'       => $ctx['organization_id'],
                            'organization_user_id'  => $ctx['user_id'] ?? null,
                            'organization_database' => $ctx['database'],
                        ]);

                        setPermissionsTeamId($ctx['organization_id']);
                        App::instance('organization', $organization);

                        return $next($request);
                    }
                }
            }
        }

        return $next($request);
    }
}
