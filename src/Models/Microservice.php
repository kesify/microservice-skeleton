<?php

namespace Kesify\MicroserviceSkeleton\Models;

use Illuminate\Database\Eloquent\Model;
use Kesify\MicroserviceSkeleton\Traits\UUID;

class Microservice extends Model
{
    use UUID;

    protected $connection = 'main';

    protected $table = 'microservices';

    protected $fillable = [
        'id',
        'name',
        'host',
        'ssl',
        'port',
        'key',
        'secret',
        'active',
        'online',
    ];

    protected $casts = [
        'ssl' => 'boolean',
        'active' => 'boolean',
        'online' => 'boolean',
    ];
}
