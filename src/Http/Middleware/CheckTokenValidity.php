<?php

namespace Kesify\MicroserviceSkeleton\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Passport\Token;
use League\OAuth2\Server\Exception\OAuthServerException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class CheckTokenValidity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Prüfen, ob der Authorization-Header vorhanden ist
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader || !preg_match('/^Bearer\s(\S+)$/', $authorizationHeader, $matches)) {
            return Response::json(['error' => 'Token not provided or invalid format'], 403);
        }

        $token = $matches[1];

        try {
            // Token abrufen und validieren
            $tokenModel = Token::where('id', $token)->first();

            if (!$tokenModel || $tokenModel->revoked || $tokenModel->expires_at->isPast()) {
                return Response::json(['error' => 'Token is invalid or expired'], 403);
            }

            // Benutzer authentifizieren
            Auth::setUser($tokenModel->user);

        } catch (OAuthServerException $e) {
            return Response::json(['error' => 'OAuth error: ' . $e->getMessage()], 403);
        } catch (\Exception $e) {
            return Response::json(['error' => 'Internal server error'], 500);
        }

        return $next($request);
    }
}
