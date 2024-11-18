<?php
namespace App\Repositories;

interface FeeRepositoryInterface
{
    public function create(array $data): bool;
    public function deleteByVehicleId(int $vehicleId): bool;
}
