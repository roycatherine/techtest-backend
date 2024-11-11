<?php
namespace App\Repositories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

class VehicleRepository implements VehicleRepositoryInterface
{
    public function all(): Collection
    {
        return Vehicle::all();
    }

    public function find(int $id): ?Vehicle
    {
        return Vehicle::findOrFail($id);
    }

    public function create(array $data): Vehicle
    {
        return Vehicle::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $vehicle = $this->find($id);
        if (!$vehicle) {
            return false;
        }
        return $vehicle->update($data);
    }

    public function delete(int $id): bool
    {
        $vehicle = $this->find($id);
        if (!$vehicle) {
            return false;
        }
        return $vehicle->delete();
    }
}
