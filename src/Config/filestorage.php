<?php

return [
    'type'=>[
        'default'=>[
            'name'=>'default',
            'connection'=>'organization',
            'disk'=>'s3-private',
            'path'=>'organizations/{{organization_id}}//default/{{generated_filename}}.{{extension}}',
            'maxSize'=>10000000, //1mb
            'markAsInactive' => false,
            'deleteRestOnSameConfiguration'=>false,
            //'afterUpload' => [\App\Http\Controllers\Controller::class, 'handleUpload'],
        ],
        'module-image'=>[
            'name'=>'default',
            'connection'=>'main',
            'disk'=>'s3-public',
            'path'=>'module/{{module_id}}/{{generated_filename}}.{{extension}}',
            'maxSize'=>10000000, //1mb
            'markAsInactive' => false,
            'deleteRestOnSameConfiguration'=>false,
            //'afterUpload' => [\App\Http\Controllers\Controller::class, 'handleUpload'],
        ],
        'module-screenshots'=>[
            'name'=>'default',
            'connection'=>'main',
            'disk'=>'s3-public',
            'path'=>'module/{{module_id}}/screenshots/{{generated_filename}}.{{extension}}',
            'maxSize'=>10000000, //1mb
            'markAsInactive' => false,
            'deleteRestOnSameConfiguration'=>false,
            //'afterUpload' => [\App\Http\Controllers\Controller::class, 'handleUpload'],
        ]
    ]
];
