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
        $this->app->singleton(\BinanceApi\Models\BinanceApi::class, function ($app, $params) {
            return new \BinanceApi\Models\BinanceApi($params['key'], $params['secret']);
        });
    }
}
