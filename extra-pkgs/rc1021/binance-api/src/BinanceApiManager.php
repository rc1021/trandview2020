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
     * BTC/USDT 槓桿逐倉市價賣出(自动还款交易订单)
     *
     * @param $symbol ENUM
     * @param $quantity DECIMAL 下單數量
     * @return array containing the response
     * @throws \Exception
     */
    public function doIsolateExit(SymbolType $symbol, DirectType $direct)
    {
        $result = [
            'error' => null,
            'order' => null,
            'stop_orders' => null,
            'rm_stop_orders' => null,
        ];

        try {
            $symbol_key = $symbol->key;
            // 取得所有掛單
            $open_orders = collect($this->api->marginIsolatedOpenOrders($symbol_key));
            // 取得止損單
            $stop_order_type = OrderType::fromValue(OrderType::STOP_LOSS_LIMIT);
            $stop_orders = $open_orders->where('type', $stop_order_type->key);
            // 撤销单一交易对的所有逐倉挂单
            // $del_orders = collect($this->api->marginDeleteIsolatedOpenOrders($symbol_key));
            // 撤銷止損單
            $rm_stop_orders = collect([]);
            $stop_orders->each(function ($stop, $key) use ($symbol_key, $rm_stop_orders) {
                $rm_stop_orders->push($this->api->marginDeleteIsolatedOrder($symbol_key, $stop['orderId'], $stop['clientOrderId']));
            });
            // 市價出場成交單
            $account = $this->api->marginIsolatedAccountByKey($symbol_key);
            $borrowed = floatval(data_get($account, "assets.$symbol_key.baseAsset.borrowed"));
            $free = floatval(data_get($account, "assets.$symbol_key.baseAsset.free"));
            if($direct->is(DirectType::LONG))
            {
                // 市價賣出
                $side = SideType::fromValue(SideType::SELL);
                $type = OrderType::fromValue(OrderType::MARKET);
                $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
                $effect = SideEffectType::fromValue(SideEffectType::AUTO_REPAY);
                $force = TimeInForce::fromValue(TimeInForce::GTC);
                $order = $this->api->marginIsolatedOrder($symbol_key, $side->key, $type->key, $this->floor_dec($borrowed + $free, 5), null, null, null, null, null, $resp->key, $effect->key, $force->key);
            }
            else {
                // 計算數量 + 手續費
                $trade_fee = $this->api->tradeFee($symbol_key);
                $taker = floatval(data_get($trade_fee, 'tradeFee.taker', 0.001));
                // 市價賣出
                $side = SideType::fromValue(SideType::BUY);
                $type = OrderType::fromValue(OrderType::MARKET);
                $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
                $effect = SideEffectType::fromValue(SideEffectType::AUTO_REPAY);
                $force = TimeInForce::fromValue(TimeInForce::GTC);
                $order = $this->api->marginIsolatedOrder($symbol_key, $side->key, $type->key, $this->ceil_dec(($borrowed + $free) * (1 + $taker), 5), null, null, null, null, null, $resp->key, $effect->key, $force->key);
            }
            // $freeQuantity = data_get($stopOrder, 'origQty', 0);
            // $status = data_get($this->marginDeleteIsolatedOrder($symbol->key, $stopOrderId), 'status', '');
            // if(strtoupper($status) !== "CANCELED")
            //     throw new Exception("Delete StopLossLimit failure. origQty: $freeQuantity");
            $result['order'] = $order;
            $result['stop_orders'] = $stop_orders->all();
            $result['rm_stop_orders'] = $rm_stop_orders->all();
        }
        catch(Exception $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 多餘的BTC賣出
     *
     * @param $symbol ENUM
     * @param $sell_quantity DECIMAL 下單數量
     * @return array containing the response
     * @throws \Exception
     */
    public function doIsolateEntryButSell(SymbolType $symbol, float $sell_quantity)
    {
        $result = [
            'error' => null,
            'order_sell' => null
        ];

        try {
            $symbol_key = $symbol->key;
            // 市價賣出
            $side = SideType::fromValue(SideType::SELL);
            $type = OrderType::fromValue(OrderType::MARKET);
            $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
            $effect = SideEffectType::fromValue(SideEffectType::AUTO_REPAY);
            $force = TimeInForce::fromValue(TimeInForce::GTC);
            $order = $this->api->marginIsolatedOrder($symbol_key, $side->key, $type->key, $this->floor_dec($sell_quantity, 5), null, null, null, null, null, $resp->key, $effect->key, $force->key);

            $result['order_sell'] = $order;
        }
        catch(Exception $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
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
            $result['order'] = call_user_func_array([$this, sprintf('doIsolate%sEntry', decamelize($this->direct->key))], [$symbol, $quantity, $price]);
            // 止損單
            $stop_quantity = collect(data_get($result['order'], 'fills', []))->sum('qty');
            $stop_price = $this->floor_dec($stop_price, 2);
            $sell_price = $this->floor_dec($sell_price, 2);
            $result['stop_order'] = call_user_func_array([$this, sprintf('doIsolate%sEntryStop', decamelize($this->direct->key))], [$symbol, $stop_quantity, $stop_price, $sell_price]);
        }
        catch(Exception $e) {
            $result['error'] = $e->getMessage();
            // TODO: 如果 order 存在，表示止損單建立失敗，需要通知用戶
            if(!is_null($result['order'])) {

            }
        }
        // 回傳結果
        return $result;
    }

    /**
     * 槓桿逐倉下單(自動借貸) 做多
     *
     * @param $symbol ENUM
     * @param $quantity DECIMAL 下單數量
     * @param $price DECIMAL 限價
     * @return array containing the response
     * @throws \Exception
     */
    private function doIsolateLongEntry(SymbolType $symbol, $quantity, $price)
    {
        $type = OrderType::fromValue(OrderType::LIMIT);
        $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
        $effect = SideEffectType::fromValue(SideEffectType::MARGIN_BUY);
        $force = TimeInForce::fromValue(TimeInForce::GTC);
        $order = $this->api->marginIsolatedOrder($symbol->key, SideType::fromValue(SideType::BUY)->key, $type->key, $this->floor_dec($quantity, 5), null, $this->floor_dec($price, 2), null, null, null, $resp->key, $effect->key, $force->key);
        if(is_null($order) or count($order['fills']) == 0) {
            $this->marginDeleteIsolatedOrder($order['symbol'], $order['orderId']);
            throw new Exception('未立即完成訂單(撤單)');
        }
        return $order;
    }

    /**
     * 槓桿逐倉下單(自動借貸) 做空
     *
     * @param $symbol ENUM
     * @param $quantity DECIMAL 下單數量
     * @param $price DECIMAL 限價
     * @return array containing the response
     * @throws \Exception
     */
    private function doIsolateShortEntry(SymbolType $symbol, $quantity, $price)
    {
        $side = SideType::fromValue(SideType::SELL);
        $type = OrderType::fromValue(OrderType::LIMIT);
        $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
        $effect = SideEffectType::fromValue(SideEffectType::MARGIN_BUY);
        $force = TimeInForce::fromValue(TimeInForce::GTC);
        $order = $this->api->marginIsolatedOrder($symbol->key, $side->key, $type->key, $this->floor_dec($quantity, 5), null, $this->floor_dec($price, 2), null, null, null, $resp->key, $effect->key, $force->key);

        if(is_null($order) or count($order['fills']) == 0) {
            $this->marginDeleteIsolatedOrder($order['symbol'], $order['orderId']);
            throw new Exception('未立即完成訂單(撤單)');
        }
        return $order;
    }

    /**
     * 止損單 做多
     *
     * @param $symbol ENUM
     * @param $quantity DECIMAL 下單數量
     * @param $stop_price DECIMAL 止盈止損單-觸發價
     * @param $sell_price DECIMAL 止盈止損單-限價
     * @return array containing the response
     * @throws \Exception
     */
    private function doIsolateLongEntryStop(SymbolType $symbol, $quantity, $stop_price, $sell_price)
    {
        $type = OrderType::fromValue(OrderType::STOP_LOSS_LIMIT);
        $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
        $effect = SideEffectType::fromValue(SideEffectType::AUTO_REPAY);
        $force = TimeInForce::fromValue(TimeInForce::GTC);

        // 取得目前帳戶 $symbol 數量
        $freeQuantity = $this->getFreeQuantity($symbol);
        $freeQuantity = ($freeQuantity < $quantity) ? $freeQuantity : $this->floor_dec($quantity, 5);

        return $this->api->marginIsolatedOrder($symbol->key, SideType::fromValue(SideType::SELL)->key, $type->key, $freeQuantity, null, $sell_price, $stop_price, null, null, $resp->key, $effect->key, $force->key);
    }

    /**
     * 止損單 做空
     *
     * @param $symbol ENUM
     * @param $quantity DECIMAL 下單數量
     * @param $stop_price DECIMAL 止盈止損單-觸發價
     * @param $sell_price DECIMAL 止盈止損單-限價
     * @return array containing the response
     * @throws \Exception
     */
    private function doIsolateShortEntryStop(SymbolType $symbol, $quantity, $stop_price, $sell_price)
    {
        $type = OrderType::fromValue(OrderType::STOP_LOSS_LIMIT);
        $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
        $effect = SideEffectType::fromValue(SideEffectType::AUTO_REPAY);
        $force = TimeInForce::fromValue(TimeInForce::GTC);

        // 取得目前帳戶 $symbol 數量
        $freeQuantity = $this->getFreeQuantity($symbol);
        $freeQuantity = ($freeQuantity < $quantity) ? $freeQuantity : $this->floor_dec($quantity, 5);
        return $this->api->marginIsolatedOrder($symbol->key, SideType::fromValue(SideType::BUY)->key, $type->key, $freeQuantity, null, $sell_price, $stop_price, null, null, $resp->key, $effect->key, $force->key);
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
