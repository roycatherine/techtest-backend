<?php

namespace App\Jobs;

use App\Models\Vehicle;
use App\Repositories\FeeRepositoryInterface;
use App\Repositories\VehicleRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class UpdateVehicleJob
{
    use Queueable;

    private Vehicle $vehicle;
    private int $price;
    private string $type;
    private int $soldFor;
    private array $fees;

    /**
     * Create a new job instance.
     */
    public function __construct(
        Vehicle $vehicle,
        int $price,
        string $type,
        int $soldFor,
        array $fees
    ) {
        $this->vehicle = $vehicle;
        $this->price = $price;
        $this->type = $type;
        $this->soldFor = $soldFor;
        $this->fees = $fees;
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
            $vehicleRepository->update($this->vehicle, [
                'price' => $this->price,
                'type' => $this->type,
                'sold_for' => $this->soldFor
            ]);

            $vehicleId = $this->vehicle->id;

            // Delete all fees and create them again, easier than updating each one.
            $feeRepository->deleteByVehicleId($vehicleId);
            $feeRepository->create(array_map(function ($fee) use ($vehicleId) {
                $fee['vehicle_id'] = $vehicleId;
                $fee['created_at'] = Carbon::now();
                return $fee;
            }, $this->fees));

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("The vehicle could not be updated.", 500, $e);
        }

        return $this->vehicle;
    }
}
