<?php

namespace Kesify\MicroserviceSkeleton\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kesify\MicroserviceSkeleton\Enums\ModuleStatus;
use Kesify\MicroserviceSkeleton\Traits\UUID;

class Module extends Model
{
    use HasFactory, UUID;

    protected $connection = 'main';
    protected $table = "modules";

    protected $fillable = [
        'key',
        'label',
        'description',
        'stripe_product_id',
        'metadata',
        'status',
        'image_url',
    ];

    protected $casts = [
        'metadata' => 'array',
        'status' => ModuleStatus::class,
    ];

    public function organizationModules()
    {
        return $this->hasMany(OrganizationModule::class);
    }
}
