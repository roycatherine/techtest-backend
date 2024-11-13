<?php

namespace App\Models;

use Carbon\Carbon;
use http\Exception\InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Fee
 *
 * @property int $id
 * @property int $price
 * @property string $type
 * @property Vehicle $vehicle
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Fee extends Model
{
    use SoftDeletes;

    const TYPE_BUYER = 'buyer'; // Basic buyer's fee
    const TYPE_SELLER = 'seller'; // Special seller's fee
    const TYPE_ASSOCIATION = 'association'; // Added costs for the association
    const TYPE_STORAGE = 'storage'; // Fixed storage fee

    const TYPES = [
        self::TYPE_BUYER,
        self::TYPE_SELLER,
        self::TYPE_ASSOCIATION,
        self::TYPE_STORAGE
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'type',
        'amount'
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
        ];
    }

    /**
     * Get the vehicle that owns the fee.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
