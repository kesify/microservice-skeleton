<?php

namespace Kesify\MicroserviceSkeleton\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kesify\MicroserviceSkeleton\Models\FileStorage;
use Kesify\MicroserviceSkeleton\Traits\UUID;
use Kesify\MicroserviceSkeleton\Models\OrganizationAddress;
use Kesify\MicroserviceSkeleton\Models\OrganizationUser;

class Organization extends Model
{
    use HasFactory, UUID;
    protected $connection = 'main';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'firstname',
        'lastname',
        'email',
        'phonenumber',
        'fax',
        'vat',
        'locale',
        'language',
        'active',
        'public',
        'deleted',
        'logo_light_id',
        'logo_dark_id',
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'is_user_in_organization'
    ];

    protected $appends = ['database','addresses', 'is_user_in_organization'];

    public function getIsUserInOrganizationAttribute()
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return cache()->remember(
            'is_user_in_organization:' . $this->id . ':' . $user->id,
            now()->addMinutes(10),
            function () use ($user) {
                return OrganizationUser::where([
                    'organization_id' => $this->id,
                    'user_id' => $user->id,
                    'active' => 1
                ])->exists();
            }
        );
    }
    public function getDatabaseAttribute()
    {
        $db = $this->hasOne(OrganizationDatabase::class, 'organization_id', 'id')->get()->first();
        return $db?->db_name;
    }

    public function database(){
        return $this->belongsTo(OrganizationDatabase::class, 'id', 'organization_id');
    }

    public function getAddressesAttribute()
    {
        $addresses = $this->hasMany(OrganizationAddress::class, 'organization_id', 'id')->get()->makeHidden(['organization_id']);
        return $addresses;
    }

    public function addresses(){
        return $this->belongsTo(OrganizationAddress::class, 'id', 'organization_id');
    }

    public function user()
    {
        return $this->belongsTo(OrganizationUser::class, 'id', 'organization_id');
    }

    public function users()
    {
        return $this->hasMany(OrganizationUser::class, 'organization_id', 'id');
    }

    public function fileStorageDark()
    {
        return $this->belongsTo(FileStorage::class, 'logo_dark_id');
    }

    public function fileStorageLight()
    {
        return $this->belongsTo(FileStorage::class, 'logo_light_id');
    }

    public function getLogoDarkAttribute()
    {
        // Nutzt die Beziehung, um auf die URL zuzugreifen, wenn vorhanden
        return $this->fileStorageDark ? $this->fileStorageDark->url : null;
    }

    public function getLogoLightAttribute()
    {
        return $this->fileStorageLight ? $this->fileStorageLight->url : null;
    }

}
