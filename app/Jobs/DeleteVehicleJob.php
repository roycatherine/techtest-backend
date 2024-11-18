<?php

namespace App\Jobs;

use App\Models\Vehicle;
use App\Repositories\VehicleRepositoryInterface;
use Illuminate\Foundation\Queue\Queueable;

class DeleteVehicleJob
{
    use Queueable;

    private Vehicle $vehicle;

    /**
     * Create a new job instance.
     */
    public function __construct(
        Vehicle $vehicle
    ) {
        $this->vehicle = $vehicle;
    }

    /**
     * Execute the job.
     */
    public function handle(
        VehicleRepositoryInterface $vehicleRepository
    ): Vehicle {
        $this->vehicle->fees()->delete();
        $vehicleRepository->delete($this->vehicle);

        return $this->vehicle;
    }
}
