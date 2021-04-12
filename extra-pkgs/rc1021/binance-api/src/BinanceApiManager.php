<?php

namespace BinanceApi;

use BinanceApi\Enums\SymbolType;
use BinanceApi\Enums\DirectType;
use BinanceApi\Enums\SideType;
use BinanceApi\Enums\OrderStatusType;
use BinanceApi\Enums\OrderType;
use BinanceApi\Enums\OrderTypeRespType;
use BinanceApi\Enums\SideEffectType;
use BinanceApi\Enums\TimeInForce;
use Binance;
use Exception;

class BinanceApiManager
{
    protected $key, $secret, $api, $direct, $redo_times=0;

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
    public function ceil_dec($v, $precision) : float{
        $c = pow(10, $precision);
        return ceil($v*$c)/$c;
    }
    //無條件捨去
    public function floor_dec($v, $precision) : float{
        $c = pow(10, $precision);
        return floor($v*$c)/$c;
    }

    /**
     * 做空止損單被觸發後手動还款不足的借款
     *
     * @param $symbol_key string
     * @param $account array symbol_key帳戶內容
     * @return array containing the response
     * @throws \Exception
     */
    public function IsolatedBaseAssetRepay($symbol_key, $account = null)
    {
        $result = [
            'error' => null,
            'orders' => [],
        ];

        try {
            if(is_null($account))
                $account = $this->api->marginIsolatedAccountByKey($symbol_key);

            $asset_key = data_get($account, "assets.$symbol_key.baseAsset.asset", substr($symbol_key, 0, 3));
            $borrowed = data_get($account, "assets.$symbol_key.baseAsset.borrowed", 0);

            // 沒有借款，不需要執行
            if($borrowed == 0)
                return $result;

            // 計算買多少標的幣
            $quantity = $borrowed;
            $minq = 10 / data_get($this->api->marginPriceIndex("BTCUSDT"), 'price');
            if($minq > $quantity)
                $quantity = $minq;
            $quantity = $this->ceil_dec($quantity, 5);

            // 買入足夠還款標的幣(自動還款)
            $side = SideType::fromValue(SideType::BUY);
            $type = OrderType::fromValue(OrderType::MARKET);
            $effect = SideEffectType::fromValue(SideEffectType::AUTO_REPAY);
            $force = TimeInForce::fromValue(TimeInForce::GTC);
            $order1 = $this->api->marginIsolatedOrder($symbol_key, $side->key, $type->key, $quantity, null, null, null, null, null, null, $effect->key, $force->key);
            array_push($result['orders'], $order1);

            // 把標地幣多餘的數量賣掉
            $account = $this->api->marginIsolatedAccountByKey($symbol_key);
            $free = $this->floor_dec(data_get($account, "assets.$symbol_key.baseAsset.free", 0), 5);
            if($free > 0) {
                $side = SideType::fromValue(SideType::SELL);
                $type = OrderType::fromValue(OrderType::MARKET);
                $effect = SideEffectType::fromValue(SideEffectType::NO_SIDE_EFFECT);
                $force = TimeInForce::fromValue(TimeInForce::GTC);
                $order2 = $this->api->marginIsolatedOrder($symbol_key, $side->key, $type->key, $free, null, null, null, null, null, null, $effect->key, $force->key);
                array_push($result['orders'], $order2);
            }
        }
        catch(Exception $e) {
            $req = $this->getLastRequest();
            $result['error'] = $e->getMessage() . "\n" . print_r($req, true);;
        }

        return $result;
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
            'orders' => [],
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
                $quantity = $this->floor_dec($borrowed + $free, 5);
                if($quantity > 0) {
                    // 市價賣出
                    $side = SideType::fromValue(SideType::SELL);
                    $type = OrderType::fromValue(OrderType::MARKET);
                    $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
                    $effect = SideEffectType::fromValue(SideEffectType::AUTO_REPAY);
                    $force = TimeInForce::fromValue(TimeInForce::GTC);
                    $order = $this->api->marginIsolatedOrder($symbol_key, $side->key, $type->key, $quantity, null, null, null, null, null, $resp->key, $effect->key, $force->key);
                    array_push($result['orders'], $order);
                }
            }
            else {
                // 計算數量 + 手續費
                $trade_fee = $this->api->tradeFee($symbol_key);
                $taker = floatval(data_get($trade_fee, 'tradeFee.taker', 0.001));
                $quantity = $this->ceil_dec(($borrowed + $free) * (1 + $taker), 5);
                if($quantity > 0) {
                    // 市價賣出
                    $side = SideType::fromValue(SideType::BUY);
                    $type = OrderType::fromValue(OrderType::MARKET);
                    $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
                    $effect = SideEffectType::fromValue(SideEffectType::AUTO_REPAY);
                    $force = TimeInForce::fromValue(TimeInForce::GTC);
                    $order = $this->api->marginIsolatedOrder($symbol_key, $side->key, $type->key, $quantity, null, null, null, null, null, $resp->key, $effect->key, $force->key);
                    array_push($result['orders'], $order);
                }
            }
            // $freeQuantity = data_get($stopOrder, 'origQty', 0);
            // $status = data_get($this->marginDeleteIsolatedOrder($symbol->key, $stopOrderId), 'status', '');
            // if(strtoupper($status) !== "CANCELED")
            //     throw new Exception("Delete StopLossLimit failure. origQty: $freeQuantity");
            $result['orders'] = array_merge($rm_stop_orders->all(), $result['orders']);
        }
        catch(Exception $e) {
            $req = $this->getLastRequest();
            $result['error'] = $e->getMessage() . "\n" . print_r($req, true);
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
            'orders' => [],
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

            array_push($result['orders'], $order);
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
            'orders' => [],
        ];

        try {
            // 槓桿逐倉下單(自動借貸)
            $order = call_user_func_array([$this, sprintf('doIsolate%sEntry', decamelize($direct->key))], [$symbol, $quantity, $price]);
            array_push($result['orders'], $order);
            if(!OrderStatusType::fromKey($order['status'])->is(OrderStatusType::FILLED)) {
                $this->marginDeleteIsolatedOrder($order['symbol'], $order['orderId']);
                $ord = print_r($order, true);
                throw new Exception(<<<EOF
                    未立即完成訂單(撤單)
                    訂單細節:
                    $ord
                EOF);
            }
            // 等一下再去下止損單
            sleep(1);
            // 止損單
            $stop_quantity = $this->floor_dec(collect(data_get($order, 'fills', []))->sum('qty'), 5);
            // 做多時，先查看總資產有多少數量的標的幣
            if($direct->is(DirectType::LONG)) {
                $symbol_key = $order['symbol'];
                $account = $this->marginIsolatedAccountByKey($symbol_key);
                $stop_quantity = $this->floor_dec(data_get($account, "assets.$symbol_key.baseAsset.free", 0), 5);
                if($stop_quantity == 0) {
                    $account_detail = print_r($account, true);
                    $account2 = $this->marginIsolatedAccountByKey($symbol_key);
                    $account_detail2 = print_r($account2, true);
                    throw new Exception(<<<EOF
                        下止損單時發現標的幣的數量為 0
                        當下資產詳情:
                        $account_detail
                        --1秒後的詳情--
                        $account_detail2
                    EOF);
                }
            }
            $stop_price = $this->floor_dec($stop_price, 2);
            $sell_price = $this->floor_dec($sell_price, 2);
            $stop_order = call_user_func_array([$this, sprintf('doIsolate%sEntryStop', decamelize($direct->key))], [$symbol, $stop_quantity, $stop_price, $sell_price]);
            array_push($result['orders'], $stop_order);
        }
        catch(Exception $e)
        {
            $req = $this->getLastRequest();
            // if The system does not have enough asset now.
            if(data_get($req, 'json.code', 0) == -3045) {
                throw new Exception(data_get($req, 'json.msg'));
            }
            $result['error'] = $e->getMessage() . "\n" . print_r($req, true);
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
        return $this->api->marginIsolatedOrder($symbol->key, SideType::fromValue(SideType::BUY)->key, $type->key, $this->floor_dec($quantity, 5), null, $this->floor_dec($price, 2), null, null, null, $resp->key, $effect->key, $force->key);
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
        return $this->api->marginIsolatedOrder($symbol->key, $side->key, $type->key, $this->floor_dec($quantity, 5), null, $this->floor_dec($price, 2), null, null, null, $resp->key, $effect->key, $force->key);
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

        return $this->api->marginIsolatedOrder($symbol->key, SideType::fromValue(SideType::SELL)->key, $type->key, $quantity, null, $sell_price, $stop_price, null, null, $resp->key, $effect->key, $force->key);
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
        return $this->api->marginIsolatedOrder($symbol->key, SideType::fromValue(SideType::BUY)->key, $type->key, $quantity, null, $sell_price, $stop_price, null, null, $resp->key, $effect->key, $force->key);
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
