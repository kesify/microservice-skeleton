<?php

namespace Kesify\MicroserviceSkeleton\Services;

use Illuminate\Support\Facades\DB;
use Kesify\MicroserviceSkeleton\Models\NumberRange;

class NumberRangesService
{
    /**
     * @param string $name
     * @return string|null
     */
    public function getNextNumber(string $name): ?string
    {
        return DB::transaction(function () use ($name) {
            $numberRange = NumberRange::where('name', $name)->lockForUpdate()->first();

            if ($numberRange) {
                $nextNumber = ($numberRange->formatted_prefix ?? '') .
                    ($numberRange->number + $numberRange->offset) .
                    ($numberRange->formatted_suffix ?? '');

                $numberRange->increment('number');

                return $nextNumber;
            }

            return null;
        });
    }
}

