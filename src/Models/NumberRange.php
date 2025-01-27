<?php

namespace Kesify\MicroserviceSkeleton\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumberRange extends Model
{
    use HasFactory;

    protected $connection = 'organization';

    protected $fillable = [
        'name',
        'number',
        'offset',
        'prefix',
        'suffix',
    ];

    protected $appends = ['formatted_prefix', 'formatted_suffix'];

    public function increase(): int
    {
        $this->number += 1;
        $this->save();

        return $this->number;
    }

    public function decrease(): int
    {
        $this->number -= 1;
        $this->save();

        return $this->number;
    }

    public function getFormattedPrefixAttribute(): string
    {
        $placeholders = [
            '{YYYY}' => date('Y'), // Current year
            '{MM}'   => date('m'), // Current month (two digits)
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $this->prefix);
    }

    public function getFormattedSuffixAttribute(): string
    {
        $placeholders = [
            '{YYYY}' => date('Y'), // Current year
            '{MM}'   => date('m'), // Current month (two digits)
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $this->suffix);
    }
}
