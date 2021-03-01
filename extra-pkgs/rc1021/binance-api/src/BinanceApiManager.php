<?php

namespace BinanceApi;

use BinanceApi\Enums\SymbolType;
use BinanceApi\Enums\OrderType;
use BinanceApi\Enums\OrderTypeRespType;
use BinanceApi\Enums\SideEffectType;
use BinanceApi\Enums\TimeInForce;
use Binance;
use Exception;

class BinanceApiManager
{
    protected $key, $secret, $api;

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

    //無條件進位
    function ceil_dec($v, $precision){
        $c = pow(10, $precision);
        return ceil($v*$c)/$c;
    }
    //無條件捨去
    function floor_dec($v, $precision){
        $c = pow(10, $precision);
        return floor($v*$c)/$c;
    }

    /**
     * BTC/USDT 槓桿逐倉下單(自動借貸) + 市價止損單
     *
     * @param $symbol ENUM 購買目標對
     * @param $quantity DECIMAL 下單數量
     * @param $price DECIMAL 限價
     * @param $stop_price DECIMAL 止盈止損單-觸發價
     * @param $sell_price DECIMAL 止盈止損單-限價
     * @return array containing the response
     * @throws \Exception
     */
    public function do1(SymbolType $symbol, $quantity, $price, $stop_price, $sell_price)
    {
        try {
            // 槓桿逐倉下單(自動借貸)
            $order = $this->do1buy($symbol, $quantity, $price);
            // 市價止損單
            $sell_quantity = collect(data_get($order, 'fills', []))->sum('qty');
            $stop_order = $this->do1stop($symbol, $sell_quantity, $stop_price, $sell_price);
            // 回傳結果
            return array_merge(['error' => null], compact('order', 'stop_order'));
        }
        catch(Exception $e) {
            return [
                'error' => $e,
                'order' => null,
                'stop_order' => null,
            ];
        }
    }

    private function do1buy(SymbolType $symbol, $quantity, $price)
    {
        $type = OrderType::fromValue(OrderType::LIMIT);
        $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
        $effect = SideEffectType::fromValue(SideEffectType::MARGIN_BUY);
        $force = TimeInForce::fromValue(TimeInForce::GTC);
        $quantity = $this->floor_dec($quantity, 5);
        var_dump($quantity);
        $order = $this->api->marginIsolatedOrder($symbol->key, 'BUY', $type->key, $quantity, null, $price, null, null, null, $resp->key, $effect->key, $force->key);
        if(is_null($order) or count($order['fills']) == 0) {
            $this->marginDeleteIsolatedOrder($order['symbol'], $order['orderId']);
            throw new Exception('未立即完成訂單(撤單)');
        }
        return $order;
    }

    private function do1stop(SymbolType $symbol, $quantity, $stop_price, $sell_price)
    {
        // 取得目前

        $type = OrderType::fromValue(OrderType::STOP_LOSS_LIMIT);
        $resp = OrderTypeRespType::fromValue(OrderTypeRespType::ACK);
        $effect = SideEffectType::fromValue(SideEffectType::AUTO_REPAY);
        $force = TimeInForce::fromValue(TimeInForce::GTC);
        return $this->api->marginIsolatedOrder($symbol->key, 'SELL', $type->key, $quantity, null, $sell_price, $stop_price, null, null, $resp->key, $effect->key, $force->key);
    }
}
