<?php

namespace App\Jobs;

use App\Models\Vehicle;
use App\Repositories\FeeRepositoryInterface;
use App\Repositories\VehicleRepositoryInterface;
use Exception;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

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
     * @throws Exception
     */
    public function handle(
        VehicleRepositoryInterface $vehicleRepository,
        FeeRepositoryInterface $feeRepository
    ): Vehicle {

        // Use transaction to revert all changes if any of the queries fail.
        DB::beginTransaction();

        try {
            $feeRepository->deleteByVehicleId($this->vehicle->id);
            $vehicleRepository->delete($this->vehicle);

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("The vehicle could not be deleted.", 500, $e);
        }

        return $this->vehicle;
    }
}
