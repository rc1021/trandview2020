<?php

use App\Enums\TradingPlatformType;
use BinanceApi\Enums\OrderType;
use BinanceApi\Enums\DirectType;
use BinanceApi\Enums\SideType;
use BinanceApi\Enums\OrderStatusType;
use App\Enums\TxnExchangeType;

return [

    TradingPlatformType::class => [
        TradingPlatformType::BINANCE => '幣安',
    ],

    DirectType::class => [
        DirectType::SHORT => '做空',
        DirectType::LONG => '做多',
        DirectType::FORCE => '強制',
        DirectType::BOTH => 'Both',
    ],

    TxnExchangeType::class => [
        TxnExchangeType::Entry => '進場',
        TxnExchangeType::Exit => '出場',
    ],

    SideType::class => [
        SideType::BUY => '買入',
        SideType::SELL => '賣出',
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

    OrderStatusType::class => [
        OrderStatusType::NEW => '新訂單',
        OrderStatusType::PARTIALLY_FILLED => '部分成交',
        OrderStatusType::FILLED => '完全成交',
        OrderStatusType::CANCELED => '撤銷訂單',
        OrderStatusType::PENDING_CANCEL => '撤銷中',
        OrderStatusType::REJECTED => '訂單沒有被交易引擎接受，也沒被處理',
        OrderStatusType::EXPIRED => '訂單被交易引擎取消',
    ]

];
