<?php

namespace App\Providers;

use App\Support\AdminCurrency;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AdminCurrency::class, function () {
            return new AdminCurrency();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('adminCurrency', app(AdminCurrency::class));
        });
    }
}
