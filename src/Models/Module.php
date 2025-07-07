<?php

namespace Kesify\MicroserviceSkeleton\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'image_dark_id',
        'image_light_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'status' => ModuleStatus::class,
    ];

    public function organizationModules()
    {
        return $this->hasMany(OrganizationModule::class);
    }

    public function imageDark(): BelongsTo
    {
        $instance = new FileStorage();
        $instance->setConnection('main');
        return $this->belongsTo(get_class($instance), 'image_dark_id');
    }

    public function imageLight(): BelongsTo
    {
        $instance = new FileStorage();
        $instance->setConnection('main');
        return $this->belongsTo(get_class($instance), 'image_light_id');
    }
}
