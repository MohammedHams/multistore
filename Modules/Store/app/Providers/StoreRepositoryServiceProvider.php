<?php

namespace Modules\Store\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Store\app\Repositories\Eloquent\StoreRepository;
use Modules\Store\app\Repositories\Interfaces\StoreRepositoryInterface;

class StoreRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
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
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
