<?php
namespace App\Repositories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class VehicleRepository implements VehicleRepositoryInterface
{
    public function all(): Collection
    {
        return Cache::remember('vehicles', 300, function () {
            return Vehicle::all();
        });
    }

    public function find(int $id): ?Vehicle
    {
        return Vehicle::findOrFail($id);
    }

    public function create(array $data): Vehicle
    {
        Cache::forget('vehicles');
        return Vehicle::create($data);
    }

    public function update(Vehicle $vehicle, array $data): bool
    {
        Cache::forget('vehicles');
        return $vehicle->update($data);
    }

    public function delete(Vehicle $vehicle): bool
    {
        Cache::forget('vehicles');
        return $vehicle->delete();
    }
}
