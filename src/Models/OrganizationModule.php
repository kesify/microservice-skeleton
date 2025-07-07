<?php

namespace Kesify\MicroserviceSkeleton\Models;

use Kesify\MicroserviceSkeleton\Enums\OrganizationModuleStatus;
use Kesify\MicroserviceSkeleton\Models\Module;
use Kesify\MicroserviceSkeleton\Models\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kesify\MicroserviceSkeleton\Traits\UUID;

class OrganizationModule extends Model
{
    use HasFactory, UUID;
    protected $connection = 'main';
    protected $table = "organization_modules";

    protected $fillable = [
        'organization_id',
        'module_id',
        'stripe_price_id',
        'status',
        'activated_at',
        'deactivated_at',
    ];

    protected $dates = [
        'activated_at',
        'deactivated_at',
    ];

    protected $casts = [
        'status' => OrganizationModuleStatus::class
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
