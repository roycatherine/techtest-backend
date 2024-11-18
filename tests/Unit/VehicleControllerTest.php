<?php

namespace Tests\Unit;

use App\Jobs\CreateVehicleJob;
use App\Jobs\DeleteVehicleJob;
use App\Jobs\UpdateVehicleJob;
use App\Models\Vehicle;
use App\Repositories\FeeRepositoryInterface;
use App\Repositories\VehicleRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Mockery as m;

/**
 * @property Application $app
 */
class VehicleControllerTest extends TestCase
{
    public function testIndexWorksCorrectly(): void
    {
        $response = $this->get('api/');

        $response->assertStatus(200);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"Bid Calculation Tool - API"}',
            $response->baseResponse->getContent()
        );
    }

    public function testNotFoundWithoutApi(): void
    {
        $response = $this->get('/');
        $response->assertStatus(404);
    }

    public function testGetFeesWorksCorrectly(): void
    {
        $response = $this->get('api/calculate-fees?price=398&vehicleType=common');
        $response->assertStatus(200);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"buyer":39.800000000000004,"seller":7.96,"association":5,"storage":100,"total":550.76}',
            $response->baseResponse->getContent()
        );

        $response = $this->get('api/calculate-fees?price=1800&vehicleType=luxury');
        $response->assertStatus(200);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"buyer":180,"seller":72,"association":15,"storage":100,"total":2167}',
            $response->baseResponse->getContent()
        );
    }

    public function testGetFeesInvalidArguments(): void
    {
        $response = $this->get('api/calculate-fees?price=invalid&vehicleType=invalid');

        $response->assertStatus(422);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"The price field must be a number. (and 1 more error)","errors":{"price":["The price field must be a number."],"vehicleType":["The selected vehicle type is invalid."]}}',
            $response->baseResponse->getContent()
        );
    }

    public function testGetFeesMissingArguments(): void
    {
        $response = $this->get('api/calculate-fees');

        $response->assertStatus(422);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"The price field is required. (and 1 more error)","errors":{"price":["The price field is required."],"vehicleType":["The vehicle type field is required."]}}',
            $response->baseResponse->getContent()
        );
    }

    public function testStoreWorksCorrectly(): void
    {
        Carbon::setTestNow(Carbon::parse('2024-01-01 12:00:00'));

        Queue::fake([
            CreateVehicleJob::class,
        ]);

        DB::shouldReceive("beginTransaction")
            ->andReturn(true);
        DB::shouldReceive("commit")
            ->andReturn(true);

        $vehicle = new Vehicle(['price' => 100, 'type' => Vehicle::TYPE_COMMON, 'sold_for' => 150]);
        $vehicle->id = 1;
        $vehicle->setRelation('fees', []);

        $vehicleRepository = m::mock(VehicleRepositoryInterface::class);
        $vehicleRepository->shouldReceive('create')
            ->once()
            ->with(['price' => 100, 'type' => Vehicle::TYPE_COMMON, 'sold_for' => 200])
            ->andReturn($vehicle);
        $this->app->instance(VehicleRepositoryInterface::class, $vehicleRepository);

        $feeRepository = m::mock(FeeRepositoryInterface::class);
        $feeRepository->shouldReceive('create')
            ->once()
            ->with([['type' => 'storage', 'amount' => 100, 'vehicle_id' => 1, 'created_at' => Carbon::now()]])
            ->andReturn(true);
        $this->app->instance(FeeRepositoryInterface::class, $feeRepository);

        $response = $this->postJson('/api/vehicles', [
            'price' => 100,
            'type' => 'common',
            'soldFor' => 200,
            'fees' => [0 => ['type' => 'storage', 'amount' => 100]]
        ]);

        $response->assertStatus(200);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"price":100,"type":"common","sold_for":150,"id":1}',
            $response->baseResponse->getContent()
        );
    }

    public function testStoreCouldNotSave(): void
    {
        Queue::fake([
            CreateVehicleJob::class,
        ]);

        DB::shouldReceive("beginTransaction")
            ->andReturn(true);
        DB::shouldReceive("rollback")
            ->andReturn(true);

        $vehicle = new Vehicle(['price' => 100, 'type' => Vehicle::TYPE_COMMON, 'sold_for' => 150]);
        $vehicle->setRelation('fees', []);

        $vehicleRepository = m::mock(VehicleRepositoryInterface::class);
        $vehicleRepository->shouldReceive('create')
            ->once()
            ->andReturn($vehicle);
        $this->app->instance(VehicleRepositoryInterface::class, $vehicleRepository);

        $feeRepository = m::mock(FeeRepositoryInterface::class);
        $feeRepository->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('Some exception.'));
        $this->app->instance(FeeRepositoryInterface::class, $feeRepository);

        $response = $this->postJson('/api/vehicles', [
            'price' => 100,
            'type' => 'common',
            'soldFor' => 200,
            'fees' => [0 => ['type' => 'storage', 'amount' => 100]]
        ]);

        $response->assertStatus(500);
    }

    public function testStoreMissingType(): void
    {
        $response = $this->postJson('/api/vehicles', [
            'price' => 100,
            'soldFor' => 200,
            'fees' => [0 => ['type' => 'storage', 'amount' => 100]]
        ]);

        $response->assertStatus(422);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"The type field is required.","errors":{"type":["The type field is required."]}}',
            $response->baseResponse->getContent()
        );
    }

    public function testStoreInvalidType(): void
    {
        $response = $this->postJson('/api/vehicles', [
            'price' => 100,
            'soldFor' => 200,
            'type' => 'invalid',
            'fees' => [0 => ['type' => 'storage', 'amount' => 100]]
        ]);

        $response->assertStatus(422);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"The selected type is invalid.","errors":{"type":["The selected type is invalid."]}}',
            $response->baseResponse->getContent()
        );
    }

    public function testStoreMissingPrice(): void
    {
        $response = $this->postJson('/api/vehicles', [
            'type' => 'common',
            'fees' => [0 => ['type' => 'storage', 'amount' => 100]]
        ]);

        $response->assertStatus(422);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"The price field is required. (and 1 more error)","errors":{"price":["The price field is required."],"soldFor":["The sold for field is required."]}}',
            $response->baseResponse->getContent()
        );
    }

    public function testStoreInvalidPrice(): void
    {
        $response = $this->postJson('/api/vehicles', [
            'type' => 'common',
            'price' => 'invalid',
            'soldFor' => 'invalid',
            'fees' => [0 => ['type' => 'storage', 'amount' => 100]]
        ]);

        $response->assertStatus(422);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"The price field must be a number. (and 1 more error)","errors":{"price":["The price field must be a number."],"soldFor":["The sold for field must be a number."]}}',
            $response->baseResponse->getContent()
        );
    }

    public function testStoreMissingFees(): void
    {
        $response = $this->postJson('/api/vehicles', [
            'type' => 'common',
            'price' => 100,
            'soldFor' => 200
        ]);

        $response->assertStatus(422);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"The fees field is required.","errors":{"fees":["The fees field is required."]}}',
            $response->baseResponse->getContent()
        );
    }

    public function testStoreInvalidFees(): void
    {
        $response = $this->postJson('/api/vehicles', [
            'type' => 'common',
            'price' => 150,
            'soldFor' => 175,
            'fees' => [0 => ['type' => 'invalid', 'amount' => 'invalid']]
        ]);

        $response->assertStatus(422);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"The selected fees.0.type is invalid. (and 1 more error)","errors":{"fees.0.type":["The selected fees.0.type is invalid."],"fees.0.amount":["The fees.0.amount field must be a number."]}}',
            $response->baseResponse->getContent()
        );
    }

    public function testUpdateWorksCorrectly(): void
    {
        Carbon::setTestNow(Carbon::parse('2024-01-01 12:00:00'));

        Queue::fake([
            UpdateVehicleJob::class,
        ]);

        DB::shouldReceive("beginTransaction")
            ->andReturn(true);
        DB::shouldReceive("commit")
            ->andReturn(true);

        $vehicle = new Vehicle(['price' => 100, 'type' => Vehicle::TYPE_COMMON, 'sold_for' => 150]);
        $vehicle->id = 1;
        $vehicle->setRelation('fees', []);

        $vehicleRepository = m::mock(VehicleRepositoryInterface::class);
        $vehicleRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($vehicle);
        $vehicleRepository->shouldReceive('update')
            ->once()
            ->with($vehicle, ['price' => 150, 'type' => Vehicle::TYPE_COMMON, 'sold_for' => 175])
            ->andReturn(true);
        $this->app->instance(VehicleRepositoryInterface::class, $vehicleRepository);

        $feeRepository = m::mock(FeeRepositoryInterface::class);
        $feeRepository->shouldReceive('deleteByVehicleId')
            ->once()
            ->with($vehicle->id)
            ->andReturn(true);
        $feeRepository->shouldReceive('create')
            ->once()
            ->with([['type' => 'storage', 'amount' => 100, 'vehicle_id' => 1, 'created_at' => Carbon::now()]])
            ->andReturn(true);
        $this->app->instance(FeeRepositoryInterface::class, $feeRepository);

        $response = $this->patchJson('/api/vehicles/1', [
            'price' => 150,
            'type' => 'common',
            'soldFor' => 175,
            'fees' => [0 => ['type' => 'storage', 'amount' => 100]]
        ]);

        $response->assertStatus(200);
    }

    public function testUpdateCouldNotSave(): void
    {
        Carbon::setTestNow(Carbon::parse('2024-01-01 12:00:00'));

        Queue::fake([
            UpdateVehicleJob::class,
        ]);

        DB::shouldReceive("beginTransaction")
            ->andReturn(true);
        DB::shouldReceive("rollback")
            ->andReturn(true);

        $vehicle = new Vehicle(['price' => 100, 'type' => Vehicle::TYPE_COMMON, 'sold_for' => 150]);
        $vehicle->id = 1;
        $vehicle->setRelation('fees', []);

        $vehicleRepository = m::mock(VehicleRepositoryInterface::class);
        $vehicleRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($vehicle);
        $vehicleRepository->shouldReceive('update')
            ->once()
            ->with($vehicle, ['price' => 150, 'type' => Vehicle::TYPE_COMMON, 'sold_for' => 175])
            ->andReturn(true);
        $this->app->instance(VehicleRepositoryInterface::class, $vehicleRepository);

        $feeRepository = m::mock(FeeRepositoryInterface::class);
        $feeRepository->shouldReceive('deleteByVehicleId')
            ->once()
            ->with($vehicle->id)
            ->andReturn(true);
        $feeRepository->shouldReceive('create')
            ->once()
            ->with([['type' => 'storage', 'amount' => 100, 'vehicle_id' => 1, 'created_at' => Carbon::now()]])
            ->andThrow(new \Exception('Some exception.'));
        $this->app->instance(FeeRepositoryInterface::class, $feeRepository);

        $response = $this->patchJson('/api/vehicles/1', [
            'price' => 150,
            'type' => 'common',
            'soldFor' => 175,
            'fees' => [0 => ['type' => 'storage', 'amount' => 100]]
        ]);

        $response->assertStatus(500);
    }

    public function testUpdateModelNotFound(): void
    {
        $vehicleRepository = m::mock(VehicleRepositoryInterface::class);
        $vehicleRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andThrow(new ModelNotFoundException());
        $this->app->instance(VehicleRepositoryInterface::class, $vehicleRepository);

        $response = $this->patchJson('/api/vehicles/1', [
            'price' => 150,
            'type' => 'common',
            'soldFor' => 175,
            'fees' => [0 => ['type' => 'storage', 'amount' => 100]]
        ]);

        $response->assertStatus(404);
    }

    public function testUpdateMissingType(): void
    {
        $response = $this->patchJson('/api/vehicles/1', [
            'price' => 150,
            'soldFor' => 175,
            'fees' => [0 => ['type' => 'storage', 'amount' => 100]]
        ]);

        $response->assertStatus(422);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"The type field is required.","errors":{"type":["The type field is required."]}}',
            $response->baseResponse->getContent()
        );
    }

    public function testUpdateInvalidType(): void
    {
        $response = $this->patchJson('/api/vehicles/1', [
            'type' => 'invalid',
            'price' => 150,
            'soldFor' => 175,
            'fees' => [0 => ['type' => 'storage', 'amount' => 100]]
        ]);

        $response->assertStatus(422);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"The selected type is invalid.","errors":{"type":["The selected type is invalid."]}}',
            $response->baseResponse->getContent()
        );
    }

    public function testUpdateMissingPrice(): void
    {
        $response = $this->patchJson('/api/vehicles/1', [
            'type' => 'common',
            'fees' => [0 => ['type' => 'storage', 'amount' => 100]]
        ]);

        $response->assertStatus(422);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"The price field is required. (and 1 more error)","errors":{"price":["The price field is required."],"soldFor":["The sold for field is required."]}}',
            $response->baseResponse->getContent()
        );
    }

    public function testUpdateInvalidPrice(): void
    {
        $response = $this->patchJson('/api/vehicles/1', [
            'type' => 'common',
            'price' => 'invalid',
            'soldFor' => 'invalid',
            'fees' => [0 => ['type' => 'storage', 'amount' => 100]]
        ]);

        $response->assertStatus(422);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"The price field must be a number. (and 1 more error)","errors":{"price":["The price field must be a number."],"soldFor":["The sold for field must be a number."]}}',
            $response->baseResponse->getContent()
        );
    }

    public function testUpdateMissingFees(): void
    {
        $response = $this->patchJson('/api/vehicles/1', [
            'type' => 'common',
            'price' => 150,
            'soldFor' => 175
        ]);

        $response->assertStatus(422);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"The fees field is required.","errors":{"fees":["The fees field is required."]}}',
            $response->baseResponse->getContent()
        );
    }

    public function testUpdateInvalidFees(): void
    {
        $response = $this->patchJson('/api/vehicles/1', [
            'type' => 'common',
            'price' => 150,
            'soldFor' => 175,
            'fees' => [0 => ['type' => 'invalid', 'amount' => 'invalid']]
        ]);

        $response->assertStatus(422);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"The selected fees.0.type is invalid. (and 1 more error)","errors":{"fees.0.type":["The selected fees.0.type is invalid."],"fees.0.amount":["The fees.0.amount field must be a number."]}}',
            $response->baseResponse->getContent()
        );
    }

    public function testDeleteWorksCorrectly(): void
    {
        Queue::fake([
            DeleteVehicleJob::class,
        ]);

        DB::shouldReceive("beginTransaction")
            ->andReturn(true);
        DB::shouldReceive("commit")
            ->andReturn(true);

        $vehicle = new Vehicle(['price' => 100, 'type' => Vehicle::TYPE_COMMON, 'sold_for' => 150]);
        $vehicle->id = 1;

        $vehicleRepository = m::mock(VehicleRepositoryInterface::class);
        $vehicleRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($vehicle);
        $vehicleRepository->shouldReceive('delete')
            ->once()
            ->with($vehicle)
            ->andReturn(true);
        $this->app->instance(VehicleRepositoryInterface::class, $vehicleRepository);

        $feeRepository = m::mock(FeeRepositoryInterface::class);
        $feeRepository->shouldReceive('deleteByVehicleId')
            ->once()
            ->with($vehicle->id)
            ->andReturn(true);
        $this->app->instance(FeeRepositoryInterface::class, $feeRepository);

        $response = $this->deleteJson('/api/vehicles/1');
        $response->assertStatus(200);
    }

    function testDeleteModelNotFound(): void
    {
        $vehicleRepository = m::mock(VehicleRepositoryInterface::class);
        $vehicleRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andThrow(new ModelNotFoundException());
        $this->app->instance(VehicleRepositoryInterface::class, $vehicleRepository);

        $response = $this->deleteJson('/api/vehicles/1');
        $response->assertStatus(404);
    }

    function testDeleteMethodNotAllowedOnRoute(): void
    {
        $response = $this->deleteJson('/api/vehicles');
        $response->assertStatus(405);
    }
}
