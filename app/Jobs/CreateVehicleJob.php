<?php

namespace App\Jobs;

use App\Models\Vehicle;
use App\Repositories\VehicleRepositoryInterface;
use Illuminate\Foundation\Queue\Queueable;

class CreateVehicleJob
{
    use Queueable;

    private int $price;
    private string $type;
    private int $soldFor;
    private array $fees;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $price,
        string $type,
        int $soldFor,
        array $fees
    ) {
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
        $vehicle = $vehicleRepository->create([
            'price' => $this->price,
            'type' => $this->type,
            'sold_for' => $this->soldFor
        ]);
        $vehicle->fees()->createMany($this->fees);

        return $vehicle;
    }
}
