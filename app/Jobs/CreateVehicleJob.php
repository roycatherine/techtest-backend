<?php

namespace App\Jobs;

use App\Models\Vehicle;
use App\Repositories\FeeRepositoryInterface;
use App\Repositories\VehicleRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class CreateVehicleJob
{
    use Queueable;

    private float $price;
    private string $type;
    private float $soldFor;
    private array $fees;

    /**
     * Create a new job instance.
     */
    public function __construct(
        float $price,
        string $type,
        float $soldFor,
        array $fees
    ) {
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
            $vehicle = $vehicleRepository->create([
                'price' => $this->price,
                'type' => $this->type,
                'sold_for' => $this->soldFor
            ]);

            $feeRepository->create(array_map(function ($fee) use ($vehicle) {
                $fee['vehicle_id'] = $vehicle->id;
                $fee['created_at'] = Carbon::now();
                return $fee;
            }, $this->fees));

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("The vehicle could not be created.", 500, $e);
        }

        return $vehicle;
    }
}
