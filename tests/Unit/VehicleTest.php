<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * TODO: Add tests for store/update/delete methods
 */
class VehicleTest extends TestCase
{
    public function testIndexWorksCorrectly(): void
    {
        $response = $this->get('api/');

        $response->assertStatus(200);
        $this->assertJson($response->baseResponse->getContent());
        $this->assertJsonStringEqualsJsonString('{"message":"Bid Calculation Tool - API"}', $response->baseResponse->getContent());
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
}
