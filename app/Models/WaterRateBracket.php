<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterRateBracket extends Model
{
    protected $fillable = [
        'min_cubic',
        'max_cubic',
        'rate_per_cubic',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rate_per_cubic' => 'decimal:2',
        ];
    }

    /**
     * Calculate the charge for a given consumption using all brackets.
     *
     * @param  int  $consumption  Total cubic meters consumed
     * @return float Total computed charge (before minimum bill check)
     */
    public static function calculateCharge(int $consumption): float
    {
        $brackets = self::orderBy('sort_order')->get();
        $totalCharge = 0;
        $remainingCubic = $consumption;

        foreach ($brackets as $bracket) {
            if ($remainingCubic <= 0) {
                break;
            }

            $bracketStart = $bracket->min_cubic;
            $bracketEnd = $bracket->max_cubic ?? PHP_INT_MAX;

            // How many cubics fall into this bracket?
            if ($consumption > $bracketStart) {
                $cubicsInBracket = min($consumption, $bracketEnd) - $bracketStart;
                $cubicsInBracket = max(0, $cubicsInBracket);

                if ($cubicsInBracket > 0) {
                    $totalCharge += $cubicsInBracket * $bracket->rate_per_cubic;
                }
            }
        }

        return $totalCharge;
    }
}
