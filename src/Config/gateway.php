<?php

return [
    'hmac_secret' => env('GATEWAY_HMAC_SECRET'),
    'headers' => [
        'user_id'    => 'X-User-Id',
        'org_id'     => 'X-Org-Id',
        'scopes'     => 'X-Scopes',
        'token_id'   => 'X-Token-Id',
        'timestamp'  => 'X-Timestamp',
        'signature'  => 'X-Signature',
    ],
    'skew' => 60,
];
