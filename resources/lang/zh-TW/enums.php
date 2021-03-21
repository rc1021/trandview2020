<?php

use App\Enums\TradingPlatformType;
use BinanceApi\Enums\SymbolType;
use BinanceApi\Enums\OrderType;

return [

    TradingPlatformType::class => [
        TradingPlatformType::BINANCE => '幣安',
    ],

    SymbolType::class => [
        SymbolType::BTCUSDT => 'BTCUSDT',
    ],

    OrderType::class => [
        OrderType::LIMIT => '限價單',
        OrderType::MARKET => '市價單',
        OrderType::STOP_LOSS => '止損單',
        OrderType::STOP_LOSS_LIMIT => '限價止損單',
        OrderType::TAKE_PROFIT => '止盈單',
        OrderType::TAKE_PROFIT_LIMIT => '限價止盈單',
        OrderType::LIMIT_MAKER => '限價隻掛單',
    ],

];
