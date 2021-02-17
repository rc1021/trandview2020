<?php

namespace App\Providers;

class AppCode
{
    public static function setBinanceExchangeInfo($values = null)
    {
        $GLOBALS['binance_exchange_info'] = $values;
    }

    public static function BinanceExchangeInfo($symbol = null)
    {
        // 檢查是否有 exchangeinfo
        if(!array_key_exists('binance_exchange_info', $GLOBALS))
            call_user_func(new \App\Schedules\UpdateBinanceExchangeInfo);
        // 檢查 exchangeinfo 是否超過時間
        $now = gmdate("Y-m-d\TH:i:s\Z");
        if(strtotime("+5 minutes", $GLOBALS['binance_exchange_info']['data']['serverTime']) < strtotime($now))
            call_user_func(new \App\Schedules\UpdateBinanceExchangeInfo);
        // 取得指定資訊
        if(!is_null($symbol))
            return $GLOBALS['binance_exchange_info']['data']['symbols'][$symbol];
        // 回傳整個資訊
        return $GLOBALS['binance_exchange_info']['data'];
    }
}
