<?php

namespace Modules\Store\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Store\Repositories\Eloquent\StoreRepository;
use Modules\Store\Repositories\Interfaces\StoreRepositoryInterface;

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
