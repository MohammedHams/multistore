<?php

namespace Modules\Order\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Order\app\Repositories\Eloquent\OrderRepository;
use Modules\Order\app\Repositories\Interfaces\OrderRepositoryInterface;
use Modules\Order\app\Repositories\Eloquent\OrderItemRepository;
use Modules\Order\app\Repositories\Interfaces\OrderItemRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->bind(
            OrderRepositoryInterface::class,
            OrderRepository::class
        );
        
        $this->app->bind(
            OrderItemRepositoryInterface::class,
            OrderItemRepository::class
        );
    }
}
