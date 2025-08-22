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

        // 1) HMAC-Kontext bevorzugen (kommt aus VerifyGatewaySignature)
        $orgId  = $request->attributes->get('ctx_org_id');
        $userId = $request->attributes->get('ctx_user_id');

        if (!$orgId) {
            // Fallback auf Header (falls Attributes nicht gesetzt)
            $orgId  = $request->header($headersCfg['org_id']  ?? 'X-Org-Id', '');
            $userId = $userId ?: $request->header($headersCfg['user_id'] ?? 'X-User-Id', '');
        }

        if ($orgId) {
            // 2) Metadaten zur Org laden (insb. DB-Name) und DB-Connection umschalten
            $orgSvc  = app(OrganizationService::class);
            $orgMeta = $orgSvc->resolveOrganizationMeta($orgId);   // ['id'=>..., 'database'=>..., ...] oder null

            if (!$orgMeta || empty($orgMeta['database'])) {
                return $this->apiResponse([
                    'success'    => false,
                    'message'    => 'Organization database not configured',
                    'error_code' => 'ORG-DB-0',
                ], 503);
            }

            try {
                // Standard: Connection "tenant" aus config/database.php
                $orgSvc->setOrganizationDatabase($orgMeta['database'], 'tenant');
            } catch (\Throwable $e) {
                report($e);
                return $this->apiResponse([
                    'success'    => false,
                    'message'    => 'Failed to switch organization database',
                    'error_code' => 'ORG-DB-1',
                ], 500);
            }

            // 3) Kontext für Controller/Services bereitstellen
            $ctx = [
                'organization_id' => $orgId,
                'user_id'         => $userId ?: null,
                'database'        => $orgMeta['database'],
            ];

            $request->attributes->add([
                'organization'         => $ctx,
                'organization_id'      => $ctx['organization_id'],
                'organization_user_id' => $ctx['user_id'],
                'organization_database'=> $ctx['database'],
                'db_connection'        => 'tenant',
            ]);
            App::instance('organization', $ctx);

            return $next($request);
        }

        // 4) Fallback: Gateway-Modus mit Bearer-Token → Redis-Mapping
        $authHeader = $request->header('Authorization');
        if ($authHeader) {
            $token = AccessToken::fromRequest($authHeader);
            if ($token) {
                $hash = AccessToken::hash($token);
                $key  = "at:{$hash}:org";
                if ($json = Redis::get($key)) {
                    $ctx = json_decode($json, true) ?: [];
                    if (!empty($ctx['organization_id'])) {
                        $orgMeta = OrganizationService()->resolveOrganizationMeta($ctx['organization_id']);

                        if (!$orgMeta || empty($orgMeta['database'])) {
                            return $this->apiResponse([
                                'success'=>false,'message'=>'Organization database not configured','error_code'=>'ORG-DB-0'
                            ], 503);
                        }

                        try {
                            OrganizationService()->setOrganizationDatabase($orgMeta['database']);
                        } catch (\Throwable $e) {
                            report($e);
                            return $this->apiResponse([
                                'success'=>false,'message'=>'Failed to switch organization database','error_code'=>'ORG-DB-1'
                            ], 500);
                        }

                        $ctx['database'] = $orgMeta['database'];

                        $request->attributes->add([
                            'organization'          => $ctx,
                            'organization_id'       => $ctx['organization_id'],
                            'organization_user_id'  => $ctx['user_id'] ?? null,
                            'organization_database' => $ctx['database'],
                            'db_connection'         => 'tenant',
                        ]);
                        App::instance('organization', $ctx);

                        return $next($request);
                    }
                }
            }
        }

        return $next($request);
    }
}
