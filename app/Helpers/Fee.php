<?php

namespace App\Helpers;

use App\Models\Vehicle;
use http\Exception\InvalidArgumentException;

class Fee
{
    /**
     * @throws InvalidArgumentException
     */
    public static function calculateBuyersFee(int $price, string $vehicleType): float
    {
        $fee = (10/100) * $price; // 10% of vehicle price

        return match ($vehicleType) {
            Vehicle::TYPE_COMMON => $fee < 10 ? 10 : min($fee, 50), // min 10$ & max 50$
            Vehicle::TYPE_LUXURY => $fee < 25 ? 25 : min($fee, 200), // min 25$ & max 200$
            default => throw new InvalidArgumentException($vehicleType . 'is not a valid vehicle type.'),
        };
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function calculateSellersFee(int $price, string $vehicleType): float
    {
        return match ($vehicleType) {
            Vehicle::TYPE_COMMON => (2/100) * $price, // 2% of vehicle price
            Vehicle::TYPE_LUXURY => (4/100) * $price, // 4% of vehicle price
            default => throw new InvalidArgumentException($vehicleType . 'is not a valid vehicle type.'),
        };
    }

    public static function calculateAssociationFee(int $price): int
    {
        return match (true) {
            $price <= 500 => 5,
            $price <= 1000 => 10,
            $price <= 3000 => 15,
            $price > 3000 => 20,
            default => 0,
        };
    }
}
