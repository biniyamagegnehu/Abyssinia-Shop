<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\CategoryRepositoryInterface::class,
            \App\Repositories\Eloquent\CategoryRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ProductRepositoryInterface::class,
            \App\Repositories\Eloquent\ProductRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ProductImageRepositoryInterface::class,
            \App\Repositories\Eloquent\ProductImageRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\CartRepositoryInterface::class,
            \App\Repositories\Eloquent\CartRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\CartItemRepositoryInterface::class,
            \App\Repositories\Eloquent\CartItemRepository::class
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
