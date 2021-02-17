<?php

use App\Enums\SymbolType;
use App\Enums\TradingPlatformType;

return [

    TradingPlatformType::class => [
        TradingPlatformType::BINANCE => '幣安',
    ],

    SymbolType::class => [
        SymbolType::BTCUSDT => 'BTC/USDT',
        // SymbolType::BTCBUSD => 'BTC/BUSD',
    ],

];
