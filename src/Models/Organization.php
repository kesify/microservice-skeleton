<?php

namespace Kesify\MicroserviceSkeleton\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Kesify\MicroserviceSkeleton\Traits\UUID;

class Organization extends Model
{
    use HasFactory, UUID;

    // Zentrale/Directory-DB
    protected $connection = 'main';

    protected $fillable = [
        'name','firstname','lastname','email','phonenumber','fax','vat',
        'locale','language','active','public','deleted','logo_light_id','logo_dark_id',
    ];

    protected $hidden  = ['is_user_in_organization'];
    protected $appends = ['database','addresses','is_user_in_organization'];

    /* ---------- Relationen (fix) ---------- */

    // <-- NEU: klar benannte Relation, damit sie nicht mit dem Accessor 'database' kollidiert
    public function databaseRecord(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(OrganizationDatabase::class, 'organization_id', 'id');
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrganizationUser::class, 'organization_id', 'id');
    }

    public function addresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrganizationAddress::class, 'organization_id', 'id');
    }

    public function fileStorageDark(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FileStorage::class, 'logo_dark_id');
    }

    public function fileStorageLight(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FileStorage::class, 'logo_light_id');
    }

    /* ---------- Accessors ---------- */

    public function getIsUserInOrganizationAttribute()
    {
        $user = Auth::user();
        if (!$user) return false;

        return Cache::remember(
            'is_user_in_organization:' . $this->id . ':' . $user->id,
            Carbon::now()->addMinutes(10),
            fn () => OrganizationUser::where([
                'organization_id' => $this->id,
                'user_id'         => $user->id,
                'active'          => 1,
            ])->exists()
        );
    }

    public function getDatabaseAttribute(): ?string
    {
        if ($this->relationLoaded('databaseRecord')) {
            return $this->getRelation('databaseRecord')?->db_name;
        }
        return $this->databaseRecord()->value('db_name');
    }

    public function getAddressesAttribute()
    {
        return $this->addresses()->get()->makeHidden(['organization_id']);
    }

    public function getLogoDarkAttribute()
    {
        return $this->fileStorageDark ? $this->fileStorageDark->url : null;
    }

    public function getLogoLightAttribute()
    {
        return $this->fileStorageLight ? $this->fileStorageLight->url : null;
    }
}
