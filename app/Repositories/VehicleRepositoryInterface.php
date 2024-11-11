<?php
namespace App\Repositories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

interface VehicleRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Vehicle;
    public function create(array $data): Vehicle;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
