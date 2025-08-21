<?php

namespace Kesify\MicroserviceSkeleton\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kesify\MicroserviceSkeleton\Support\GatewaySigner;
use Laravel\Passport\Exceptions\AuthenticationException;

class VerifyGatewaySignature
{
    public function handle(Request $request, Closure $next)
    {
        $headers = config('gateway.headers');
        $secret  = config('gateway.hmac_secret');
        $skew    = (int) config('gateway.skew', 60);

        $userId   = $request->header($headers['user_id'], '');
        $orgId    = $request->header($headers['org_id'], '');
        $scopes   = $request->header($headers['scopes'], '');
        $tokenId  = $request->header($headers['token_id'], '');
        $ts       = $request->header($headers['timestamp'], '');
        $sig      = $request->header($headers['signature'], '');

        if (!$sig || !$ts || abs(time() - (int)$ts) > $skew) {
            throw new AuthenticationException('Forbidden');
        }

        $payload = GatewaySigner::canonical([
            'user_id'   => $userId,
            'org_id'    => $orgId,
            'scopes'    => $scopes,
            'token_id'  => $tokenId,
            'timestamp' => $ts,
            'method'    => $request->getMethod(),
            'path'      => $request->path(),
        ]);

        $expected = GatewaySigner::sign($payload, $secret);
        if (!hash_equals($expected, $sig)) {
            throw new AuthenticationException('Forbidden');
        }

        // Kontext für Controller "bereitstellen"
        $request->attributes->add([
            'ctx_user_id' => $userId,
            'ctx_org_id'  => $orgId,
            'ctx_scopes'  => $scopes,
            'ctx_token_id'=> $tokenId,
        ]);

        return $next($request);
    }
}
