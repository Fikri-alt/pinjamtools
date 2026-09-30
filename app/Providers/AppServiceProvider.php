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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Render menyediakan RENDER_EXTERNAL_URL (https) otomatis -
        // pakai sebagai APP_URL agar route()/asset() menghasilkan link benar.
        if ($url = env('RENDER_EXTERNAL_URL')) {
            config(['app.url' => $url]);
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
