<?php

namespace Darkraul79\SimpleCartPayFlow;

use Darkraul79\Cartify\CartifyServiceProvider;
use Darkraul79\Payflow\PayflowServiceProvider;
use Illuminate\Support\ServiceProvider;

class SimpleCartPayFlowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register both Cartify and Payflow service providers
        $this->app->register(CartifyServiceProvider::class);
        $this->app->register(PayflowServiceProvider::class);

        // Merge configuration for convenience
        $this->mergeConfigFrom(__DIR__.'/../config/simple-cart-payflow.php', 'simple-cart-payflow');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish unified configuration
            $this->publishes([
                __DIR__.'/../config/simple-cart-payflow.php' => config_path('simple-cart-payflow.php'),
            ], 'simple-cart-payflow-config');

            // Optionally publish individual package configs
            $this->publishes([
                __DIR__.'/../config/simple-cart-payflow.php' => config_path('simple-cart-payflow.php'),
            ], 'config');
        }

        // Here you can add integration logic between Cart and Payment
        // For example: events, observers, or helper methods
    }
}
