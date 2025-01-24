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
        ]
    ]
];
