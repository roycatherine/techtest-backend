<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Vehicle
 *
 * @property int $id
 * @property int $price
 * @property string $type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Vehicle extends Model
{
    use SoftDeletes;

    const TYPE_COMMON = 'common';
    const TYPE_LUXURY = 'luxury';

    const TYPES = [
        self::TYPE_COMMON,
        self::TYPE_LUXURY
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'price',
        'type',
        'sold_for'
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
        ];
    }
}
