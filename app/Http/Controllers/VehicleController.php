<?php

namespace App\Http\Controllers;

use App\Helpers\Fee as FeeHelper;
use App\Http\Requests\CalculateFeesRequest;
use App\Http\Requests\VehiclePostRequest;
use App\Jobs\CreateVehicleJob;
use App\Jobs\DeleteVehicleJob;
use App\Jobs\UpdateVehicleJob;
use App\Models\Fee;
use App\Repositories\VehicleRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VehicleController
{
    private VehicleRepositoryInterface $vehicleRepository;

    public function __construct(VehicleRepositoryInterface $vehicleRepository)
    {
        $this->vehicleRepository = $vehicleRepository;
    }
    public function index(): JsonResponse
    {
        $vehicles = $this->vehicleRepository->all();

        return response()->json([
            'vehicles' => $vehicles
        ]);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function show($id): JsonResponse
    {
        $vehicle = $this->vehicleRepository->find($id);

        return response()->json($vehicle);
    }

    public function store(VehiclePostRequest $request): JsonResponse
    {
        $vehicle = CreateVehicleJob::dispatchSync(
            $request->get('price'),
            $request->get('type'),
            $request->get('soldFor'),
            $request->get('fees')
        );

        return response()->json($vehicle);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function update(VehiclePostRequest $request, $vehicleId): void
    {
        $vehicle = $this->vehicleRepository->find($vehicleId);

        UpdateVehicleJob::dispatchSync(
            $vehicle,
            $request->get('price'),
            $request->get('type'),
            $request->get('soldFor'),
            $request->get('fees')
        );
    }

    /**
     * @throws NotFoundHttpException
     */
    public function delete(Request $request, $vehicleId): void
    {
        $vehicle = $this->vehicleRepository->find($vehicleId);
        DeleteVehicleJob::dispatchSync($vehicle);
    }

    public function getFeesAndTotalByPriceAndType(CalculateFeesRequest $request): array
    {
        $price = $request->get('price');
        $vehicleType = $request->get('vehicleType');

        $fees = [
            Fee::TYPE_BUYER => FeeHelper::calculateBuyersFee($request->get('price'), $vehicleType),
            Fee::TYPE_SELLER => FeeHelper::calculateSellersFee($price, $vehicleType),
            Fee::TYPE_ASSOCIATION => FeeHelper::calculateAssociationFee($price),
            Fee::TYPE_STORAGE => 100,
        ];
        $fees['total'] = $price + array_sum($fees);

        return $fees;
    }
}
