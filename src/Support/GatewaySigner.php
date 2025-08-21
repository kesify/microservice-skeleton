<?php

namespace Kesify\MicroserviceSkeleton\Support;

class GatewaySigner
{
    public static function canonical(array $parts): string
    {
        // stabil sortieren: key=value\n
        ksort($parts);
        return implode("\n", array_map(fn($k)=>$k.'='.$parts[$k], array_keys($parts)));
    }

    public static function sign(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }
}
