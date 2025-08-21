<?php

namespace Kesify\MicroserviceSkeleton\Support;

class AccessToken
{
    public static function fromRequest(?string $authorizationHeader): ?string
    {
        if (!$authorizationHeader || stripos($authorizationHeader, 'Bearer ') !== 0) {
            return null;
        }
        return trim(substr($authorizationHeader, 7));
    }

    public static function hash(string $token): string
    {
        // Kompakter Hash für Redis-Key
        return hash('sha256', $token);
    }
}
