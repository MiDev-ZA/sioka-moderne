<?php

namespace App\Providers;

use App\Models\FlashInfo;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('partials.ticker', function ($view) {
            $view->with('flashInfos', FlashInfo::with('category')->where('is_active', true)->latest()->take(10)->get());
        });
    }
}
