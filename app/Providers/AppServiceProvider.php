<?php

namespace App\Providers;

use App\Repositories\FeeRepository;
use App\Repositories\FeeRepositoryInterface;
use App\Repositories\VehicleRepository;
use App\Repositories\VehicleRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            VehicleRepositoryInterface::class,
            VehicleRepository::class
        );
        $this->app->bind(
            FeeRepositoryInterface::class,
            FeeRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
