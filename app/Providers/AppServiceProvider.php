<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\TxnMarginOrder;
use App\Observers\TxnMarginOrderObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        TxnMarginOrder::observe(TxnMarginOrderObserver::class);
    }
}
