<?php

namespace Kesify\MicroserviceSkeleton\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kesify\MicroserviceSkeleton\Traits\UUID;

class OrganizationUser extends Model
{
    use HasFactory, UUID;

    protected $table = "organization_users";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'organization_id',
        'active',
    ];

    protected $appends = ['name','logo_light','logo_dark','database'];


    public function organization(){
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function organizationDatabase(){
        return $this->belongsTo(OrganizationDatabase::class, 'organization_id', 'organization_id');
    }


    public function getNameAttribute()
    {
        return $this->organization()->first()->name; // Assuming the relationship is defined properly in the user() method.
    }

    public function getLogoDarkAttribute()
    {
        return $this->organization()->first()->logo_dark; // Assuming the relationship is defined properly in the user() method.
    }

    public function getLogoLightAttribute()
    {
        return $this->organization()->first()->logo_light; // Assuming the relationship is defined properly in the user() method.
    }

    public function getDatabaseAttribute()
    {
        return $this->organizationDatabase()->first()->db_name; // Assuming the relationship is defined properly in the user() method.
    }
}
