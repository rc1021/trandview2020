<?php

use App\Enums\TradingPlatformType;
use BinanceApi\Enums\SymbolType;

return [

    TradingPlatformType::class => [
        TradingPlatformType::BINANCE => '幣安',
    ],

    SymbolType::class => [
        SymbolType::BTCUSDT => 'BTCUSDT',
    ],

];
