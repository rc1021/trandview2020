<?php

namespace BinanceApi;

use BinanceApi\Enums\DirectType;
use BinanceApi\Enums\SideType;
use BinanceApi\Enums\OrderStatusType;
use BinanceApi\Enums\OrderType;
use BinanceApi\Enums\OrderTypeRespType;
use BinanceApi\Enums\SideEffectType;
use BinanceApi\Enums\TimeInForce;
use BinanceApi\Enums\WorkingType;
use Binance;
use Exception;
use Illuminate\Support\Facades\Cache;

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

    public static function HttpRequestClient($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => ['Cookie: cid=kIck7YK5'],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    /**
     * 交易规范信息
     *
     * @return array containing the response
     * @throws \Exception
     */
    public static function ExchangeInfo($no_cache = false, $seconds = 300)
    {
        if($no_cache)
            Cache::forget(__FUNCTION__);

        return Cache::remember(__FUNCTION__, $seconds, function () {
            $response = self::HttpRequestClient('https://www.binance.com/api/v3/exchangeInfo');
            $resp = json_decode($response, true);
            if(array_key_exists('symbols', $resp)) {;
                $tmp = [];
                foreach($resp['symbols'] as $key => $value){
                    $tmp[$value['symbol']] = $value;
                }
                $resp['symbols'] = $tmp;
            }
            return $resp;
        });
    }

    /**
     * 取得 杠杆借贷利率
     *
     * @return array containing the response
     * @throws \Exception
     */
    public static function MarginVipSpecList($no_cache = false, $seconds = 300)
    {
        if($no_cache)
            Cache::forget(__FUNCTION__);

        return Cache::remember(__FUNCTION__, $seconds, function () {
            $response = self::HttpRequestClient('https://www.binance.com/gateway-api/v1/friendly/margin/vip/spec/list-all');
            $resp = json_decode($response, true);
            if(array_key_exists('data', $resp)) {;
                $tmp = [];
                foreach($resp['data'] as $key => $value){
                    $tmp[$value['assetName']] = $value;
                }
                $resp['data'] = $tmp;
            }
            $resp['serverTime'] = time();
            return $resp;
        });
    }

    /**
     * 無條件進位
     *
     * @param $v float
     * @param $precision int 小數點位數
     * @return float
     * @throws \Exception
     */
    public function ceil_dec($v, $precision) : float{
        $c = pow(10, $precision);
        return ceil($v*$c)/$c;
    }

    /**
     * 無條件捨去
     *
     * @param $v float
     * @param $precision int 小數點位數
     * @return float
     * @throws \Exception
     */
    public function floor_dec($v, $precision) : float{
        $c = pow(10, $precision);
        return floor($v*$c)/$c;
    }

    /**
     * 合約賬戶: U本位合约出場
     *
     * @param $symbol_key string
     * @param $quantity DECIMAL 下單數量
     * @return array containing the response
     * @throws \Exception
     */
    public function doFeaturesExit(string $symbol_key, DirectType $direct)
    {
        $result = [
            'error' => null,
            'orders' => [],
        ];

        try {
            try {
                // 撤销全部订单 (TRADE)
                // 因為合約撤消全訂單會出現 Exception with message 'signedRequest error: {"code": 200,"msg": "The operation of cancel all open order is done."}'
                // 所以用 try catch 處理
                collect($this->api->futuresDeleteAllOpenOrders($symbol_key));
            }
            catch(Exception $e) {}

            if($direct->is(DirectType::LONG))
            {
                // 市價賣出
                $side = SideType::fromValue(SideType::SELL);
                $type = OrderType::fromValue(OrderType::MARKET);
                $order = $this->api->featuresClosePositionOrder($symbol_key, $side->key, $type->key);
                array_push($result['orders'], $order);
            }
            else {
                // 市價賣出
                $side = SideType::fromValue(SideType::BUY);
                $type = OrderType::fromValue(OrderType::MARKET);
                $order = $this->api->featuresClosePositionOrder($symbol_key, $side->key, $type->key);
                array_push($result['orders'], $order);
            }
        }
        catch(Exception $e) {
            $req = $this->getLastRequest();
            $result['error'] = $e->getMessage() . "\n" . print_r($req, true);
        }

        return $result;
    }

    /**
     * 合約賬戶: U本位合约進場
     *
     * @param $symbol_key string
     * @param $direct ENUM 執行方向
     * @param $leverage int 槓桿倍數
     * @param $price int 限價單價格
     * @param $quantity int 限價單數量
     * @param $time_in_force string 下單種類
     * @param $stop_price int 止損單觸發價格
     * @param $sell_price int 止損單價格
     * @param $stop_time_in_force string 止損單下單種類
     * @return array containing the response
     * @throws \Exception
     */
    public function doFeaturesEntry(string $symbol_key, DirectType $direct, $leverage, $price, $quantity, $time_in_force, $stop_price, $sell_price, $stop_time_in_force)
    {
        // 記錄做單方向
        $this->direct = $direct;

        $result = [
            'error' => null,
            'orders' => [],
        ];

        try {
            // 先設定倍數
            $this->api->futuresLeverage($symbol_key, $leverage);

            // 下單進場
            $side = SideType::fromValue(SideType::BUY);
            if($direct->is(DirectType::SHORT))
                $side = SideType::fromValue(SideType::SELL);
            $type = OrderType::fromValue(OrderType::LIMIT);
            $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
            $working = WorkingType::fromValue(WorkingType::MARK_PRICE);
            $force = TimeInForce::fromValue(TimeInForce::GTC);
            $order = $this->api->futuresOrder($symbol_key, $side->key, $type->key, null, null, $this->floor_dec($quantity, 5), $this->floor_dec($price, 2), null, null, null, null, null, $force->key, null, null, $resp->key);
            $order_id = data_get($order, 'orderId', false);
            if(!$order_id)
                throw new Exception('No OrderID');
            // 每隔 1 秒確認訂單狀態, 10秒後依然沒成交就取消
            $i = 1;
            do {
                sleep(1);
                $order = $this->api->futuresGetOrder($symbol_key, $order_id);
                if(OrderStatusType::fromKey($order['status'])->is(OrderStatusType::FILLED))
                    break;
            } while(++$i <= 10);

            if(OrderStatusType::fromKey($order['status'])->is(OrderStatusType::NEW)) {
                $this->futuresDeleteOrder($order['symbol'], $order['orderId']);
                $ord = print_r($order, true);
                throw new Exception(<<<EOF
                    10秒內未立即完成訂單(撤單)
                    訂單細節:
                    $ord
                EOF);
            }
            array_push($result['orders'], $order);

            // 設定止損單
            $side = SideType::fromValue(SideType::SELL);
            if($direct->is(DirectType::SHORT))
                $side = SideType::fromValue(SideType::BUY);
            $type = OrderType::fromValue(OrderType::STOP_MARKET);
            $order = $this->api->futuresOrder($symbol_key, $side->key, $type->key, null, null, null, null, null, $stop_price, null, null, null, $force->key, null, null, $resp->key);
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
     * 做空止損單被觸發後手動还款不足的借款
     *
     * @param $symbol_key string
     * @param $account array symbol_key帳戶內容
     * @return array containing the response
     * @throws \Exception
     */
    public function MarginBaseAssetRepay($symbol_key, $account = null)
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
     * @param $symbol_key string 交易對
     * @param $quantity DECIMAL 下單數量
     * @return array containing the response
     * @throws \Exception
     */
    public function doMarginExit(string $symbol_key, DirectType $direct)
    {
        $result = [
            'error' => null,
            'orders' => [],
        ];

        try {
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
            $result['orders'] = array_merge($rm_stop_orders->all(), $result['orders']);

            // 市價出場成交單
            $account = $this->api->marginIsolatedAccountByKey($symbol_key);
            if($direct->is(DirectType::LONG))
            {
                $free = floatval(data_get($account, "assets.$symbol_key.baseAsset.free"));
                $quantity = $this->floor_dec($free, 5);
                try{
                    // 市價賣出
                    $side = SideType::fromValue(SideType::SELL);
                    $type = OrderType::fromValue(OrderType::MARKET);
                    $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
                    $effect = SideEffectType::fromValue(SideEffectType::AUTO_REPAY);
                    $force = TimeInForce::fromValue(TimeInForce::GTC);
                    $order = $this->api->marginIsolatedOrder($symbol_key, $side->key, $type->key, $quantity, null, null, null, null, null, $resp->key, $effect->key, $force->key);
                    array_push($result['orders'], $order);
                }
                catch(Exception $e) {
                    $req = $this->getLastRequest();
                    if(data_get($req, 'json.code', 0) == -1013) {
                        throw new Exception(sprintf("做多出場，但市價賣出數量不足量(%f)，請查看帳戶詳情", $quantity));
                    }
                    throw $e;
                }
            }
            else {
                $trade_fee = $this->api->tradeFee($symbol_key);
                $taker = floatval(data_get($trade_fee, 'tradeFee.taker', 0.001));
                $netAsset = floatval(data_get($account, "assets.$symbol_key.baseAsset.netAsset"));
                $quantity = $this->ceil_dec(abs($netAsset) / (1 - $taker), 5);
                try {
                    // 市價賣出，但不自動還款
                    $side = SideType::fromValue(SideType::BUY);
                    $type = OrderType::fromValue(OrderType::MARKET);
                    $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
                    $effect = SideEffectType::fromValue(SideEffectType::NO_SIDE_EFFECT);
                    $force = TimeInForce::fromValue(TimeInForce::GTC);
                    $order = $this->api->marginIsolatedOrder($symbol_key, $side->key, $type->key, $quantity, null, null, null, null, null, $resp->key, $effect->key, $force->key);
                    array_push($result['orders'], $order);
                    // 執行還款
                    $asset = data_get($account, "assets.$symbol_key.baseAsset.asset");
                    $borrowed = floatval(data_get($account, "assets.$symbol_key.baseAsset.borrowed"));
                    $interest = floatval(data_get($account, "assets.$symbol_key.baseAsset.interest"));
                    $repay = $borrowed + $interest;
                    $this->api->marginIsolatedRepay($asset, $repay, $symbol_key);
                }
                catch(Exception $e) {
                    $req = $this->getLastRequest();
                    if(data_get($req, 'json.code', 0) == -1013) {
                        throw new Exception(sprintf("做空出場，但市價買入數量不足量(%f)，請查看帳戶詳情", $quantity));
                    }
                    throw $e;
                }
            }
            // $freeQuantity = data_get($stopOrder, 'origQty', 0);
            // $status = data_get($this->marginDeleteIsolatedOrder($symbol->key, $stopOrderId), 'status', '');
            // if(strtoupper($status) !== "CANCELED")
            //     throw new Exception("Delete StopLossLimit failure. origQty: $freeQuantity");
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
    public function doMarginEntryButSell(string $symbol_key, float $sell_quantity)
    {
        $result = [
            'error' => null,
            'orders' => [],
        ];

        try {
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
     * @param $symbol string
     * @param $direct ENUM 做單方向
     * @param $quantity DECIMAL 下單數量
     * @param $price DECIMAL 限價
     * @param $stop_price DECIMAL 止盈止損單-觸發價
     * @param $sell_price DECIMAL 止盈止損單-限價
     * @return array containing the response
     * @throws \Exception
     */
    public function doMarginEntry(string $symbol, DirectType $direct, $quantity, $price, $stop_price, $sell_price) : array
    {
        // 記錄做單方向
        $this->direct = $direct;

        $result = [
            'error' => null,
            'orders' => [],
        ];

        try {
            // 槓桿逐倉下單(自動借貸)
            $order = call_user_func_array([$this, sprintf('doMargin%sEntry', decamelize($direct->key))], [$symbol, $quantity, $price]);
            array_push($result['orders'], $order);
            if(!OrderStatusType::fromKey($order['status'])->is(OrderStatusType::FILLED)) {
                $this->marginDeleteIsolatedOrder($order['symbol'], $order['orderId']);
                $ord = print_r($order, true);
                throw new Exception('未立即完成訂單(撤單)');
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
            $stop_order = call_user_func_array([$this, sprintf('doMargin%sEntryStop', decamelize($direct->key))], [$symbol, $stop_quantity, $stop_price, $sell_price]);
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
     * @param $symbol string
     * @param $quantity DECIMAL 下單數量
     * @param $price DECIMAL 限價
     * @return array containing the response
     * @throws \Exception
     */
    private function doMarginLongEntry(string $symbol, $quantity, $price)
    {
        $type = OrderType::fromValue(OrderType::LIMIT);
        $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
        $effect = SideEffectType::fromValue(SideEffectType::MARGIN_BUY);
        $force = TimeInForce::fromValue(TimeInForce::GTC);
        return $this->api->marginIsolatedOrder($symbol, SideType::fromValue(SideType::BUY)->key, $type->key, $this->floor_dec($quantity, 5), null, $this->floor_dec($price, 2), null, null, null, $resp->key, $effect->key, $force->key);
    }

    /**
     * 槓桿逐倉下單(自動借貸) 做空
     *
     * @param $symbol string
     * @param $quantity DECIMAL 下單數量
     * @param $price DECIMAL 限價
     * @return array containing the response
     * @throws \Exception
     */
    private function doMarginShortEntry(string $symbol, $quantity, $price)
    {
        $side = SideType::fromValue(SideType::SELL);
        $type = OrderType::fromValue(OrderType::LIMIT);
        $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
        $effect = SideEffectType::fromValue(SideEffectType::MARGIN_BUY);
        $force = TimeInForce::fromValue(TimeInForce::GTC);
        return $this->api->marginIsolatedOrder($symbol, $side->key, $type->key, $this->floor_dec($quantity, 5), null, $this->floor_dec($price, 2), null, null, null, $resp->key, $effect->key, $force->key);
    }

    /**
     * 止損單 做多
     *
     * @param $symbol string
     * @param $quantity DECIMAL 下單數量
     * @param $stop_price DECIMAL 止盈止損單-觸發價
     * @param $sell_price DECIMAL 止盈止損單-限價
     * @return array containing the response
     * @throws \Exception
     */
    private function doMarginLongEntryStop(string $symbol, $quantity, $stop_price, $sell_price)
    {
        $type = OrderType::fromValue(OrderType::STOP_LOSS_LIMIT);
        $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
        $effect = SideEffectType::fromValue(SideEffectType::AUTO_REPAY);
        $force = TimeInForce::fromValue(TimeInForce::GTC);

        return $this->api->marginIsolatedOrder($symbol, SideType::fromValue(SideType::SELL)->key, $type->key, $quantity, null, $sell_price, $stop_price, null, null, $resp->key, $effect->key, $force->key);
    }

    /**
     * 止損單 做空
     *
     * @param $symbol string
     * @param $quantity DECIMAL 下單數量
     * @param $stop_price DECIMAL 止盈止損單-觸發價
     * @param $sell_price DECIMAL 止盈止損單-限價
     * @return array containing the response
     * @throws \Exception
     */
    private function doMarginShortEntryStop(string $symbol, $quantity, $stop_price, $sell_price)
    {
        $type = OrderType::fromValue(OrderType::STOP_LOSS_LIMIT);
        $resp = OrderTypeRespType::fromValue(OrderTypeRespType::FULL);
        $effect = SideEffectType::fromValue(SideEffectType::AUTO_REPAY);
        $force = TimeInForce::fromValue(TimeInForce::GTC);

        // 取得目前帳戶 $symbol 數量
        return $this->api->marginIsolatedOrder($symbol, SideType::fromValue(SideType::BUY)->key, $type->key, $quantity, null, $sell_price, $stop_price, null, null, $resp->key, $effect->key, $force->key);
    }

    /**
     * 取得目前帳戶 $symbol 數量
     *
     * @param $symbol ENUM
     * @return array containing the response
     * @throws \Exception
     */
    private function getFreeQuantity(string $symbol) : float
    {
        $account = $this->api->marginIsolatedAccount();
        $assets = collect(data_get($account, 'assets', []))->keyBy('symbol')->toArray();
        return $this->floor_dec(floatval(data_get($assets, "$symbol.baseAsset.free", 0)), 5);
    }
}
