<?php
namespace App\Repositories;

use App\Models\Fee;

class FeeRepository implements FeeRepositoryInterface
{
    public function create(array $data): bool
    {
        return Fee::insert($data);
    }

    public function deleteByVehicleId(int $vehicleId): bool
    {
        return Fee::where('vehicle_id', $vehicleId)->delete();
    }
}
