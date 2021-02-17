<?php

namespace App\Schedules;

use App\Enums\SymbolType;

class UpdateBinanceExchangeInfo
{
    public function __invoke()
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://api.binance.com/api/v3/exchangeInfo');
        $keys = [];
        $symbol_keys = SymbolType::getKeys();
        $arr = json_decode($response->getBody(), true);
        $binance_exchangeInfo = $arr;
        $binance_exchangeInfo['symbols'] = null;
        foreach($arr['symbols'] as $key => $value){
            array_push($keys, $value['symbol']);
            if(in_array($value['symbol'], $symbol_keys))
                $binance_exchangeInfo['symbols'][$value['symbol']] = $value;
        }
        \App\Providers\AppCode::setBinanceExchangeInfo([
            'keys' => $keys,
            'data' => $binance_exchangeInfo
        ]);
    }
}
