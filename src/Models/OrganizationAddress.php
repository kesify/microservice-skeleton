<?php

namespace Kesify\MicroserviceSkeleton\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationAddress extends Model
{
    use HasFactory, UUID;

    protected $table = "organization_address";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id',
        'street',
        'house_number',
        'zip',
        'city',
        'state',
        'country',
        'is_standard',
        'deleted',
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
