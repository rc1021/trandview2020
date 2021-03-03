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
    function ceil_dec($v, $precision) : float{
        $c = pow(10, $precision);
        return ceil($v*$c)/$c;
    }
    //無條件捨去
    function floor_dec($v, $precision) : float{
        $c = pow(10, $precision);
        return floor($v*$c)/$c;
    }

    /**
     * BTC/USDT 槓桿逐倉市價賣出(自动还款交易订单)
     *
     * @param $symbol ENUM
     * @param $quantity DECIMAL 下單數量
     * @return array containing the response
     * @throws \Exception
     */
    public function doLongExit(SymbolType $symbol, array $stopLossLimit)
    {
        try {
            $stopOrder = $this->marginGetIsIsolatedOrder($symbol->key, $stopLossLimit['orderId']);

            // 一些历史订单的 cummulativeQuoteQty < 0, 是指当前数据不存在。
            $cummulativeQuoteQty = floatval(data_get($stopOrder, 'cummulativeQuoteQty', 0));
            if($cummulativeQuoteQty < 0)
                throw new Exception("數據不存在");

            $freeQuantity = data_get($stopOrder, 'origQty', 0);
            $status = data_get($this->marginDeleteIsolatedOrder($symbol->key, $stopLossLimit['orderId']), 'status', '');
            if(strtoupper($status) !== "CANCELED")
                throw new Exception("Delete StopLossLimit failure. origQty: $freeQuantity");

            // 市價賣出
            $type = OrderType::fromValue(OrderType::MARKET);
            $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
            $effect = SideEffectType::fromValue(SideEffectType::AUTO_REPAY);
            $force = TimeInForce::fromValue(TimeInForce::GTC);
            $order = $this->api->marginIsolatedOrder($symbol->key, 'SELL', $type->key, $this->floor_dec($freeQuantity, 5), null, null, null, null, null, $resp->key, $effect->key, $force->key);

            return array_values(array_merge(['error' => null], compact('order')));
        }
        catch(Exception $e) {
            return array_values([
                'error' => $e->getMessage(),
                'order' => null,
            ]);
        }
    }

    /**
     * BTC/USDT 槓桿逐倉下單(自動借貸) + 市價止損單
     *
     * @param $symbol ENUM
     * @param $quantity DECIMAL 下單數量
     * @param $price DECIMAL 限價
     * @param $stop_price DECIMAL 止盈止損單-觸發價
     * @param $sell_price DECIMAL 止盈止損單-限價
     * @return array containing the response
     * @throws \Exception
     */
    public function doLongEntry(SymbolType $symbol, $quantity, $price, $stop_price, $sell_price) : array
    {
        try {
            // 槓桿逐倉下單(自動借貸)
            $order = $this->doLongEntryBuy($symbol, $quantity, $price);
            // 止損單
            $sell_quantity = collect(data_get($order, 'fills', []))->sum('qty');
            $stop_order = $this->doLongEntryStop($symbol, $sell_quantity, $stop_price, $sell_price);
            // 回傳結果
            return array_values([
                'error' => null,
                'order' => $order,
                'stop_order' => $stop_order,
            ]);
        }
        catch(Exception $e) {
            return array_values([
                'error' => $e,
                'order' => null,
                'stop_order' => null,
            ]);
        }
    }

    /**
     * 槓桿逐倉下單(自動借貸)
     *
     * @param $symbol ENUM
     * @param $quantity DECIMAL 下單數量
     * @param $price DECIMAL 限價
     * @return array containing the response
     * @throws \Exception
     */
    private function doLongEntryBuy(SymbolType $symbol, $quantity, $price)
    {
        $type = OrderType::fromValue(OrderType::LIMIT);
        $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
        $effect = SideEffectType::fromValue(SideEffectType::MARGIN_BUY);
        $force = TimeInForce::fromValue(TimeInForce::GTC);
        $order = $this->api->marginIsolatedOrder($symbol->key, 'BUY', $type->key, $quantity, null, $this->floor_dec($price, 5), null, null, null, $resp->key, $effect->key, $force->key);
        if(is_null($order) or count($order['fills']) == 0) {
            $this->marginDeleteIsolatedOrder($order['symbol'], $order['orderId']);
            throw new Exception('未立即完成訂單(撤單)');
        }
        return $order;
    }

    /**
     * 止損單
     *
     * @param $symbol ENUM
     * @param $quantity DECIMAL 下單數量
     * @param $stop_price DECIMAL 止盈止損單-觸發價
     * @param $sell_price DECIMAL 止盈止損單-限價
     * @return array containing the response
     * @throws \Exception
     */
    private function doLongEntryStop(SymbolType $symbol, $quantity, $stop_price, $sell_price)
    {
        $type = OrderType::fromValue(OrderType::STOP_LOSS_LIMIT);
        $resp = OrderTypeRespType::fromValue(OrderTypeRespType::ACK);
        $effect = SideEffectType::fromValue(SideEffectType::AUTO_REPAY);
        $force = TimeInForce::fromValue(TimeInForce::GTC);

        // 取得目前帳戶 $symbol 數量
        $freeQuantity = $this->getFreeQuantity($symbol);
        $freeQuantity = ($freeQuantity < $quantity) ? $freeQuantity : $this->floor_dec($quantity, 5);

        return $this->api->marginIsolatedOrder($symbol->key, 'SELL', $type->key, $freeQuantity, null, $sell_price, $stop_price, null, null, $resp->key, $effect->key, $force->key);
    }

    /**
     * 取得目前帳戶 $symbol 數量
     *
     * @param $symbol ENUM
     * @return array containing the response
     * @throws \Exception
     */
    private function getFreeQuantity(SymbolType $symbol) : float
    {
        $account = $this->api->marginIsolatedAccount();
        $assets = collect(data_get($account, 'assets', []))->keyBy('symbol')->toArray();
        return $this->floor_dec(floatval(data_get($assets, "$symbol->key.baseAsset.free", 0)), 5);
    }
}
