<?php

namespace BinanceApi;

use BinanceApi\Enums\SymbolType;
use BinanceApi\Enums\DirectType;
use BinanceApi\Enums\SideType;
use BinanceApi\Enums\OrderType;
use BinanceApi\Enums\OrderTypeRespType;
use BinanceApi\Enums\SideEffectType;
use BinanceApi\Enums\TimeInForce;
use Binance;
use Exception;

class BinanceApiManager
{
    protected $key, $secret, $api, $direct;

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
     * 做多做空的買賣陣列
     *
     * @return string containing the response
     * @throws \Exception
     */
    private function getSideKey($ind=0)
    {
        // 做多做空
        $side = ($this->direct->is(DirectType::LONG))
                    ? [SideType::fromValue(SideType::BUY)->key, SideType::fromValue(SideType::SELL)->key]
                    : [SideType::fromValue(SideType::SELL)->key, SideType::fromValue(SideType::BUY)->key];
        return $side[$ind];
    }

    /**
     * BTC/USDT 槓桿逐倉市價賣出(自动还款交易订单)
     *
     * @param $symbol ENUM
     * @param $quantity DECIMAL 下單數量
     * @return array containing the response
     * @throws \Exception
     */
    public function doIsolateExit(SymbolType $symbol, DirectType $direct, string $stopOrderId)
    {
        try {
            // 記錄做單方向
            $this->direct = $direct;

            $stopOrder = $this->marginGetIsIsolatedOrder($symbol->key, $stopOrderId);

            // 一些历史订单的 cummulativeQuoteQty < 0, 是指当前数据不存在。
            $cummulativeQuoteQty = floatval(data_get($stopOrder, 'cummulativeQuoteQty', 0));
            if($cummulativeQuoteQty < 0)
                throw new Exception("數據不存在, cummulativeQuoteQty 小於 0");

            $freeQuantity = data_get($stopOrder, 'origQty', 0);
            $status = data_get($this->marginDeleteIsolatedOrder($symbol->key, $stopOrderId), 'status', '');
            if(strtoupper($status) !== "CANCELED")
                throw new Exception("Delete StopLossLimit failure. origQty: $freeQuantity");

            // 市價賣出
            $type = OrderType::fromValue(OrderType::MARKET);
            $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
            $effect = SideEffectType::fromValue(SideEffectType::AUTO_REPAY);
            $force = TimeInForce::fromValue(TimeInForce::GTC);
            $order = $this->api->marginIsolatedOrder($symbol->key, $this->getSideKey(1), $type->key, $this->floor_dec($freeQuantity, 5), null, null, null, null, null, $resp->key, $effect->key, $force->key);

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
     * @param $direct ENUM 做單方向
     * @param $quantity DECIMAL 下單數量
     * @param $price DECIMAL 限價
     * @param $stop_price DECIMAL 止盈止損單-觸發價
     * @param $sell_price DECIMAL 止盈止損單-限價
     * @return array containing the response
     * @throws \Exception
     */
    public function doIsolateEntry(SymbolType $symbol, DirectType $direct, $quantity, $price, $stop_price, $sell_price) : array
    {
        // 記錄做單方向
        $this->direct = $direct;

        $result = [
            'error' => null,
            'order' => null,
            'stop_order' => null,
        ];

        try {
            // 槓桿逐倉下單(自動借貸)
            $result['order'] = $this->doIsolateEntryBuy($symbol, $quantity, $price);
            // 止損單
            $sell_quantity = collect(data_get($result['order'], 'fills', []))->sum('qty');
            $result['stop_order'] = $this->doIsolateEntryStop($symbol, $sell_quantity, $stop_price, $sell_price);
        }
        catch(Exception $e) {
            $result['error'] = $e;

            // TODO: 如果 order 存在，表示止損單建立失敗，需要通知用戶
            if(!is_null($result['order'])) {

            }
        }
        // 回傳結果
        return array_values($result);
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
    private function doIsolateEntryBuy(SymbolType $symbol, $quantity, $price)
    {
        $type = OrderType::fromValue(OrderType::LIMIT);
        $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
        $effect = SideEffectType::fromValue(SideEffectType::MARGIN_BUY);
        $force = TimeInForce::fromValue(TimeInForce::GTC);
        $order = $this->api->marginIsolatedOrder($symbol->key, $this->getSideKey(), $type->key, $quantity, null, $this->floor_dec($price, 5), null, null, null, $resp->key, $effect->key, $force->key);
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
    private function doIsolateEntryStop(SymbolType $symbol, $quantity, $stop_price, $sell_price)
    {
        $type = OrderType::fromValue(OrderType::STOP_LOSS_LIMIT);
        $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
        $effect = SideEffectType::fromValue(SideEffectType::AUTO_REPAY);
        $force = TimeInForce::fromValue(TimeInForce::GTC);

        // 取得目前帳戶 $symbol 數量
        $freeQuantity = $this->getFreeQuantity($symbol);
        $freeQuantity = ($freeQuantity < $quantity) ? $freeQuantity : $this->floor_dec($quantity, 5);

        return $this->api->marginIsolatedOrder($symbol->key, $this->getSideKey(1), $type->key, $freeQuantity, null, $sell_price, $stop_price, null, null, $resp->key, $effect->key, $force->key);
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
