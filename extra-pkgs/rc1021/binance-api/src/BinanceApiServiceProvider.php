<?php

namespace BinanceApi;

use Illuminate\Support\ServiceProvider;

class BinanceApiServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->singleton(BinanceApiManager::class, function ($app, $params) {
            return new BinanceApiManager($params['key'], $params['secret']);
        });
    }
}
