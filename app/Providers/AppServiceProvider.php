<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;

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
        Schema::defaultStringLength(119);
        //        Schema::defaultStringLength(191);

        //        $this->app->bind(Client::class, function () {
//            return new Client([
//                'verify' => 'C:\laragon\etc\ssl\cacert.pem',
//            ]);
//        });
    }
}
