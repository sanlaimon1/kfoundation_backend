<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\MD5\MD5Hasher;

class MD5HashServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('hash', function () {
            return new MD5Hasher();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    public function provides()
    {
        return ['hash'];
    }
}
