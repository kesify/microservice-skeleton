<?php

return [
    'type' => [
        'default' => [
            'name' => 'default',
            'connection' => 'organization',
            'disk' => 's3',
            'path' => 'protected/organizations/{{organization_id}}/default/{{generated_filename}}.{{extension}}',
            'maxSize' => 10000000, // 10 MB
            'markAsInactive' => false,
            'deleteRestOnSameConfiguration' => false,
        ],
        'module-image' => [
            'name' => 'module-image',
            'connection' => 'main',
            'disk' => 's3',
            'path' => 'public/module/{{module_id}}/{{generated_filename}}.{{extension}}',
            'maxSize' => 10000000, // 10 MB
            'markAsInactive' => false,
            'deleteRestOnSameConfiguration' => false,
        ],
        'module-screenshots' => [
            'name' => 'module-screenshots',
            'connection' => 'main',
            'disk' => 's3',
            'path' => 'public/module/{{module_id}}/screenshots/{{generated_filename}}.{{extension}}',
            'maxSize' => 10000000, // 10 MB
            'markAsInactive' => false,
            'deleteRestOnSameConfiguration' => false,
        ],
    ]
];
