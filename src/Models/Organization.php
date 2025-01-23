<?php

namespace Kesify\MicroserviceSkeleton\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        return Cache::remember(
            'is_user_in_organization:' . $this->id . ':' . $user->id,
            Carbon::now()->addMinutes(10),
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

    public function database(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(OrganizationDatabase::class, 'id', 'organization_id');
    }

    public function getAddressesAttribute()
    {
        $addresses = $this->hasMany(OrganizationAddress::class, 'organization_id', 'id')->get()->makeHidden(['organization_id']);
        return $addresses;
    }

    public function addresses(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(OrganizationAddress::class, 'id', 'organization_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(OrganizationUser::class, 'id', 'organization_id');
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrganizationUser::class, 'organization_id', 'id');
    }

    public function fileStorageDark(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FileStorage::class, 'logo_dark_id');
    }

    public function fileStorageLight(): \Illuminate\Database\Eloquent\Relations\BelongsTo
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
