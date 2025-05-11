<?php

namespace App\Providers;

use App\Repositories\Eloquent\StoreRepository;
use App\Repositories\Interfaces\StoreRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            StoreRepositoryInterface::class,
            StoreRepository::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
