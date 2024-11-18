<?php

namespace Tests\Unit;

use App\Helpers\Fee as FeeHelper;
use App\Models\Vehicle;
use Illuminate\Foundation\Application;
use Tests\TestCase;

/**
 * @property Application $app
 */
class FeeHelperTest extends TestCase
{
    public function testCalculateBuyersFee(): void
    {
        $this->assertEquals(39.800000000000004, FeeHelper::calculateBuyersFee(398.00, Vehicle::TYPE_COMMON));
        $this->assertEquals(50, FeeHelper::calculateBuyersFee(501.00, Vehicle::TYPE_COMMON));
        $this->assertEquals(10, FeeHelper::calculateBuyersFee(57.00, Vehicle::TYPE_COMMON));
        $this->assertEquals(180, FeeHelper::calculateBuyersFee(1800.00, Vehicle::TYPE_LUXURY));
        $this->assertEquals(50, FeeHelper::calculateBuyersFee(1100.00, Vehicle::TYPE_COMMON));
        $this->assertEquals(200, FeeHelper::calculateBuyersFee(1000000.00, Vehicle::TYPE_LUXURY));
    }

    public function testCalculateSellersFee(): void
    {
        $this->assertEquals(7.96, FeeHelper::calculateSellersFee(398.00, Vehicle::TYPE_COMMON));
        $this->assertEquals(10.02, FeeHelper::calculateSellersFee(501.00, Vehicle::TYPE_COMMON));
        $this->assertEquals(1.1400000000000001, FeeHelper::calculateSellersFee(57.00, Vehicle::TYPE_COMMON));
        $this->assertEquals(72, FeeHelper::calculateSellersFee(1800.00, Vehicle::TYPE_LUXURY));
        $this->assertEquals(22, FeeHelper::calculateSellersFee(1100.00, Vehicle::TYPE_COMMON));
        $this->assertEquals(40000, FeeHelper::calculateSellersFee(1000000.00, Vehicle::TYPE_LUXURY));
    }

    public function testCalculateAssociationFee(): void
    {
        $this->assertEquals(5, FeeHelper::calculateAssociationFee(398.00));
        $this->assertEquals(10, FeeHelper::calculateAssociationFee(501.00));
        $this->assertEquals(5, FeeHelper::calculateAssociationFee(57.00));
        $this->assertEquals(15, FeeHelper::calculateAssociationFee(1800.00));
        $this->assertEquals(15, FeeHelper::calculateAssociationFee(1100.00));
        $this->assertEquals(20, FeeHelper::calculateAssociationFee(1000000.00));
    }
}
