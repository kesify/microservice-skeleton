<?php

namespace Kesify\MicroserviceSkeleton\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kesify\MicroserviceSkeleton\Support\GatewaySigner;
use Kesify\MicroserviceSkeleton\Traits\APIResponse;

class VerifyGatewaySignature
{
    use APIResponse;

    public function handle(Request $request, Closure $next)
    {
        $headers    = config('gateway.headers', []);
        $secret     = (string) config('gateway.hmac_secret', '');
        $skew       = (int) config('gateway.skew', 60);

        if ($secret === '') {
            return $this->apiResponse([
                'success'    => false,
                'message'    => 'Gateway secret is missing',
                'error_code' => 'GWSIG-08',
            ], 500);
        }

        $userId  = $request->header($headers['user_id']   ?? 'X-User-Id', '');
        $orgId   = $request->header($headers['org_id']    ?? 'X-Org-Id', '');
        $scopes  = $request->header($headers['scopes']    ?? 'X-Scopes', '');
        $tokenId = $request->header($headers['token_id']  ?? 'X-Token-Id', '');
        $ts      = $request->header($headers['timestamp'] ?? 'X-Timestamp', '');
        $sig     = $request->header($headers['signature'] ?? 'X-Signature', '');

        $bodyShaHeader = $request->header('X-Body-SHA256');

        if ($sig === '') {
            return $this->apiResponse([
                'success'=>false, 'message'=>'Signature is missing', 'error_code'=>'GWSIG-01'
            ], 400);
        }
        if ($ts === '') {
            return $this->apiResponse([
                'success'=>false, 'message'=>'Timestamp is missing', 'error_code'=>'GWSIG-02'
            ], 400);
        }
        if (!ctype_digit((string) $ts)) {
            return $this->apiResponse([
                'success'=>false, 'message'=>'Timestamp format is invalid', 'error_code'=>'GWSIG-02A'
            ], 400);
        }
        if (abs(time() - (int) $ts) > $skew) {
            return $this->apiResponse([
                'success'=>false, 'message'=>'Signature has expired', 'error_code'=>'GWSIG-03'
            ], 401);
        }

        if ($bodyShaHeader !== null) {
            $calc = hash('sha256', (string) $request->getContent());
            if (!hash_equals($calc, $bodyShaHeader)) {
                return $this->apiResponse([
                    'success'=>false, 'message'=>'Body hash mismatch', 'error_code'=>'GWSIG-07'
                ], 400);
            }
        }

        $parts = [
            'user_id'   => $userId ?? null,
            'org_id'    => $orgId ?? null,
            'scopes'    => $scopes ?? null,
            'token_id'  => $tokenId ?? null,
            'timestamp' => $ts,
            'method'    => $request->getMethod(),
            'path'      => $request->path(),
        ];
        if ($bodyShaHeader !== null) {
            $parts['body_sha256'] = $bodyShaHeader;
        }

        $payload  = GatewaySigner::canonical($parts);
        $expected = GatewaySigner::sign($payload, $secret);

        if (!hash_equals($expected, $sig)) {
            $response = [
                'success'    => false,
                'message'    => 'Signature is invalid',
                'error_code' => 'GWSIG-04',
            ];
            if (config('app.debug')) {
                $response['debug'] = [
                    'method'  => $request->getMethod(),
                    'path'    => $request->path(),
                    'payload' => $payload,
                ];
            }
            return $this->apiResponse($response, 403);
        }

        $request->attributes->add([
            'ctx_user_id'   => $userId,
            'ctx_org_id'    => $orgId,
            'ctx_scopes'    => $scopes,
            'ctx_token_id'  => $tokenId,
        ]);

        return $next($request);
    }
}
