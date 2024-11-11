<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Response::json([
        'message' => 'Bid Calculation Tool - API',
    ]);
})->name('index');

Route::get('/user', function (Request $request) {
    return $request->user();
});

/**
 * Appointments routing
 */
Route::group([
    'middleware' => ['oauth', 'context', 'group-organization', 'content:json'],
    'prefix' => 'accounts/{account}/organizations/{organization}/appointments',
    'as' => 'accounts.appointments.',
    'namespace' => 'Account'
], function () {

    //Show appointment
    Route::get('{appointment}', [
        'as' => 'show',
        'uses' => 'AppointmentController@show'
    ]);
});
