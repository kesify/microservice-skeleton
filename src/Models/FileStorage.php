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
            return Storage::disk($disk)->url($path);
        }else
            return null;
    }
}
