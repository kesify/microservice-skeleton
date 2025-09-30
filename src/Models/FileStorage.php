<?php

namespace Kesify\MicroserviceSkeleton\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Kesify\MicroserviceSkeleton\Traits\UUID;

class FileStorage extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'user_id',
        'filename',
        'generated_filename',
        'extension',
        'size',
        'active',
        'deleted',
        'disk',
        'path',
        'configuration',
    ];

    protected $casts = [
      'size' => 'integer',
      'active' => 'boolean',
      'deleted' => 'boolean'
    ];

    protected $appends = ['url'];

    public function getUrlAttribute($path = null, $disk = null): ?string
    {
        $path = $path ?? $this->path;
        $disk = $disk ?? $this->disk;
        if($path && $disk){
            if($this->isPublicKey($path)){
                $relativePath = preg_replace('#^public/#', '', ltrim($path, '/'));
                return Storage::disk($disk)->url($relativePath);
            }else
                return Storage::disk($disk)->temporaryUrl($path,now()->addMinutes(10));
        }else
            return null;
    }

    protected function isPublicKey(string $key): bool
    {
        return str_starts_with($key, 'public/');
    }

    protected function isProtectedKey(string $key): bool
    {
        return str_starts_with($key, 'protected/');
    }
}
