<?php

namespace Kesify\MicroserviceSkeleton\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redis;
use Kesify\MicroserviceSkeleton\Traits\APIResponse;
use Kesify\MicroserviceSkeleton\Support\AccessToken;

class ResolveOrganization
{
    use APIResponse;

    public function handle(Request $request, Closure $next)
    {
        $headersCfg = config('gateway.headers', []);

        $orgId  = $request->attributes->get('ctx_org_id');
        $userId = $request->attributes->get('ctx_user_id');

        if (!$orgId) {
            $orgId  = $request->header($headersCfg['org_id']  ?? 'X-Org-Id', '');
            $userId = $userId ?: $request->header($headersCfg['user_id'] ?? 'X-User-Id', '');
        }

        if ($orgId) {
            $ctx = [
                'organization_id'     => $orgId,
                'user_id'             => $userId ?: null,
            ];

            // expose for controllers/services
            $request->attributes->add([
                'organization'         => $ctx,
                'organization_id'      => $ctx['organization_id'],
                'organization_user_id' => $ctx['user_id'],
            ]);
            App::instance('organization', $ctx);

            return $next($request);
        }

        // 2) Fallback: Gateway mode with Bearer token -> read org mapping from Redis
        $authHeader = $request->header('Authorization');
        if ($authHeader) {
            $token = AccessToken::fromRequest($authHeader);
            if ($token) {
                $hash = AccessToken::hash($token);
                $key  = "at:{$hash}:org";
                if ($json = Redis::get($key)) {
                    $ctx = json_decode($json, true) ?: [];
                    if (!empty($ctx['organization_id'])) {
                        $request->attributes->add([
                            'organization'         => $ctx,
                            'organization_id'      => $ctx['organization_id'],
                            'organization_user_id' => $ctx['user_id'] ?? null,
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
