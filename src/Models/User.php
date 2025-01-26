<?php

namespace Kesify\MicroserviceSkeleton\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Kesify\MicroserviceSkeleton\Traits\UUID;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Model
{
    use HasApiTokens,HasRoles, HasFactory, Notifiable, UUID, Authenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'title',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'status',
        'active',
        'gender',
        'avatar_id',
        'email_verified_at',
        'deleted',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean',
        'deleted' => 'boolean',
    ];

    protected $appends = ['avatar'];

    public function getAvatarAttribute()
    {
        $file = $this->hasOne(FileStorage::class, 'id', 'avatar_id')->where('active',true)->first() ?? null;
        $url = $file ?$file->url:null;

        return $url ?? null;
    }
}
