<?php

namespace BinanceApi\Models;

use Binance;
use BinanceApi\Enums\SymbolType;

class BinanceApi
{
    protected $key, $secret, $api;

    public static function setBinanceExchangeInfo($values = null)
    {
        $GLOBALS['binance_exchange_info'] = $values;
    }

    public static function BinanceExchangeInfo($symbol = null)
    {
        // 檢查是否有 exchangeinfo
        if(!array_key_exists('binance_exchange_info', $GLOBALS))
            self::UpdateExchangeInfo();
        // 檢查 exchangeinfo 是否超過時間
        $now = gmdate("Y-m-d\TH:i:s\Z");
        if(strtotime("+5 minutes", $GLOBALS['binance_exchange_info']['data']['serverTime']) < strtotime($now))
            self::UpdateExchangeInfo();
        // 取得指定資訊
        if(!is_null($symbol))
            return $GLOBALS['binance_exchange_info']['data']['symbols'][$symbol];
        // 回傳整個資訊
        return $GLOBALS['binance_exchange_info']['data'];
    }

    public static function UpdateExchangeInfo()
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://api.binance.com/api/v3/exchangeInfo');
        $keys = [];
        $symbol_keys = SymbolType::getKeys();
        $arr = json_decode($response->getBody(), true);
        $binance_exchangeInfo = $arr;
        $binance_exchangeInfo['symbols'] = null;
        // 只記錄在 SymbolType 裡存在的資訊
        foreach($arr['symbols'] as $key => $value){
            array_push($keys, $value['symbol']);
            if(in_array($value['symbol'], $symbol_keys))
                $binance_exchangeInfo['symbols'][$value['symbol']] = $value;
        }
        self::setBinanceExchangeInfo([
            'keys' => $keys,
            'data' => $binance_exchangeInfo
        ]);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->api, $name], $arguments);
    }

    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->api = new Binance\API($key, $secret);
    }

    public function buy ($symbol, &$quantity, &$price)
    {
        $order = $this->api->buy($symbol, $this->filter_quantity($symbol, $quantity), $this->filter_price($symbol, $price));
        return $order;
    }

    public function marketSell($symbol, &$quantity)
    {
        return $this->api->marketSell($symbol, $this->filter_quantity($symbol, $quantity));
    }

    public function cancel ($symbol, $order_id)
    {
        return $this->api->cancel($symbol, $order_id);
    }

    private function filter_quantity($symbol_key, &$value)
    {
        $exchange = self::BinanceExchangeInfo($symbol_key);
        $quantity = round($value, data_get($exchange, 'baseAssetPrecision', 8));
        foreach ($exchange['filters'] as $key => $filter)
        {
            switch($filter['filterType'])
            {
                case 'LOT_SIZE':
                    $less = strlen(substr(strrchr(rtrim($filter['minQty'], '0'), "."), 1));
                    $quantity = round($quantity, $less);
                    if($quantity > (int)$filter['maxQty'])
                        $quantity = (int)$filter['maxQty'];
                    break;
            }
        }
        return $quantity;
    }

    private function filter_price($symbol_key, &$value)
    {
        $exchange = self::BinanceExchangeInfo($symbol_key);
        $price = round($value, data_get($exchange, 'quoteAssetPrecision', 8));
        foreach ($exchange['filters'] as $key => $filter)
        {
            switch($filter['filterType'])
            {
                case 'PRICE_FILTER':
                    $less = strlen(substr(strrchr(rtrim($filter['minPrice'], '0'), "."), 1));
                    $price = round($price, $less);
                    if($price > (int)$filter['maxPrice'])
                        $price = (int)$filter['maxPrice'];
                    break;
            }
        }
        return $price;
    }
}
