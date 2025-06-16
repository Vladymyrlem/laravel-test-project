<?php

namespace app\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\File;
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
        Paginator::useBootstrapFive(); // ⬅️ Це вмикає Bootstrap-версію пагінатора

        $uploadsPath = public_path('uploads');

        if (!File::exists($uploadsPath)) {
            File::makeDirectory($uploadsPath, 0775, true);
        }
    }
}
