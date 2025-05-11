<?php

namespace Modules\Product\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Product\app\Repositories\Eloquent\ProductRepository;
use Modules\Product\app\Repositories\Interfaces\ProductRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );
    }
}
