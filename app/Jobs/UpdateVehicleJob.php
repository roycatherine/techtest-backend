<?php

namespace App\Jobs;

use App\Models\Vehicle;
use App\Repositories\VehicleRepositoryInterface;
use Illuminate\Foundation\Queue\Queueable;

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
     */
    public function handle(
        VehicleRepositoryInterface $vehicleRepository
    ): Vehicle {
        $vehicleRepository->update($this->vehicle, [
            'price' => $this->price,
            'type' => $this->type,
            'sold_for' => $this->soldFor
        ]);
        $this->vehicle->fees()->delete();
        $this->vehicle->fees()->createMany($this->fees);

        return $this->vehicle;
    }
}
