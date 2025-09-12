<?php

return [
    'type'=>[
        'default'=>[
            'name'=>'default',
            'connection'=>'organization',
            'disk'=>'s3',
            'path'=>'organizations/{{organization_id}}/protected/default/{{generated_filename}}.{{extension}}',
            'maxSize'=>10000000, //1mb
            'markAsInactive' => false,
            'deleteRestOnSameConfiguration'=>false,
            //'afterUpload' => [\App\Http\Controllers\Controller::class, 'handleUpload'],
        ],
        'module-image'=>[
            'name'=>'module-image',
            'connection'=>'main',
            'disk'=>'s3',
            'path'=>'module/{{module_id}}/{{generated_filename}}.{{extension}}',
            'maxSize'=>10000000, //1mb
            'markAsInactive' => false,
            'deleteRestOnSameConfiguration'=>false,
        ],
        'module-screenshots'=>[
            'name'=>'module-screenshots',
            'connection'=>'main',
            'disk'=>'s3',
            'path'=>'module/{{module_id}}/screenshots/{{generated_filename}}.{{extension}}',
            'maxSize'=>10000000, //1mb
            'markAsInactive' => false,
            'deleteRestOnSameConfiguration'=>false,
        ]
    ]
];
