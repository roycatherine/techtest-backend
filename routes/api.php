<?php

use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Response::json([
        'message' => 'Bid Calculation Tool - API',
    ]);
});

Route::group(['prefix' => 'vehicles'], function () {
    Route::get('/', [VehicleController::class, 'index']);
    Route::post('/', [VehicleController::class, 'store']);

    Route::get('/{id}', [VehicleController::class, 'show']);
    Route::patch('/{id}', [VehicleController::class, 'update']);
    Route::delete('/{id}', [VehicleController::class, 'delete']);
});

Route::get('/calculate-fees', [VehicleController::class, 'getFeesAndTotalByPriceAndType']);
