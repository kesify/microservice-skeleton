<?php

namespace Kesify\MicroserviceSkeleton\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redis;
use Kesify\MicroserviceSkeleton\Support\AccessToken;
use Kesify\MicroserviceSkeleton\Traits\APIResponse;

class ResolveOrganization
{
    use APIResponse;
    public function handle(Request $request, Closure $next)
    {
        $auth  = $request->header('Authorization');
        $token = AccessToken::fromRequest($auth);
        if (!$token) {
            return $this->apiResponse(['success'=>false,'message'=>'Missing token'], 401);
        }

        $hash = AccessToken::hash($token);
        $key  = "at:{$hash}:org";

        $json = Redis::get($key);
        if (!$json) {
            return $this->apiResponse(['success'=>false,'message'=>'Organization not selected'], 428);
        }
        $ctx = json_decode($json, true) ?: [];

        if (empty($ctx['organization_id'])) {
            return $this->apiResponse(['success'=>false,'message'=>'Organization missing'], 428);
        }

        $request->attributes->add([
            'organization'    => $ctx,
            'organization_id' => $ctx['organization_id'],
            'organization_user_id' => $ctx['user_id'] ?? null,
        ]);

        App::instance('organization', $ctx);

        return $next($request);
    }
}

