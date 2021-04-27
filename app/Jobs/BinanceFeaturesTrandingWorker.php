<?php

namespace App\Jobs;

use Closure;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use BinanceApi\Enums\SymbolType;
use BinanceApi\Enums\DirectType;
use BinanceApi\Enums\OrderType;
use BinanceApi\Enums\SideType;
use BinanceApi\BinanceApiManager;
use App\Models\TxnFuturesOrder;
use App\Models\SignalHistory;
use App\Models\AdminUser;
use App\Models\AdminTxnSellRec;
use App\Models\AdminTxnExitRec;
use App\Models\AdminTxnEntryRec;
use App\Models\AdminTxnBuyRec;
use App\Models\FuturesFormula;
use App\Enums\TxnExchangeType;
use App\Enums\TxnDirectType;
use App\Enums\TradingPlatformType;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use Exception;

class BinanceFeaturesTrandingWorker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $signal, $user, $spreadsheet, $sheet, $api, $formulaTable, $notifyMessage, $timer = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AdminUser $user, SignalHistory $signal)
    {
        $this->notifyMessage = '';
        $this->user = $user->withoutRelations();
        $this->signal = $signal->withoutRelations();
    }

    public static function DuringTimer(Closure $callback) : array
    {
        // 開始時間
        $start_at = time();
        $dt_start_at = date("Y-m-d H:i:s", $start_at);

        $callback();

        // 結束時間
        $done_at = time();
        $dt_done_at = date("Y-m-d H:i:s", $done_at);
        $dt_duration = $done_at - $start_at;

        return compact('dt_start_at', 'dt_done_at', 'dt_duration');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $error = null;
        try {
            $this->user->load('txnFeatSetting')->refresh();

            $this->user->txnStatus->trading_program_status = 1;
            $this->user->txnStatus->save();
            $exchange = $this->signal->txn_exchange_type;
            $this->initBinanceApi();

            if($this->signal->txn_direct_type->is(DirectType::FORCE))
            {
                $symbol_key = $this->signal->symbolType->key;
                $index = $this->api->futuresTickerPrice($symbol_key);
                $this->signal->current_price = $this->api->floor_dec(data_get($index, 'price', 0), 2);
                $this->signal->save();
                // 強制出場
                $this->forceLiquidation();
            }
            else
            {
                // 買入
                if($exchange->is(TxnExchangeType::Entry))
                {
                    // 如果狀態已經是進場就略過此次訊號並發佈訊息
                    if($this->user->txnStatus->current_feat_state)
                        throw new Exception('持倉中收到進場訊號(略過本次訊息)');

                    $this->initWorksheet();

                    for($i = 2.0; $i >= 0.0; $i = $i - 0.1) {
                        try {
                            $this->sheet->setCellValue($this->formulaTable->setcol14, ($i > 1.0) ? 1.0 : $i);
                            $this->entryHandle();
                        }
                        catch(Exception $e)
                        {
                            $req = $this->api->getLastRequest();
                            // 沒有足夠的餘額可借就降10%
                            if(data_get($req, 'json.code', 0) == -3045) {
                                $this->spreadsheet->clearCalculationCache();
                                $this->sheet = $this->spreadsheet->getActiveSheet();
                                sleep(1);
                                continue;
                            }
                            throw $e;
                        }
                        break;
                    }
                }
                // 賣出
                elseif($exchange->is(TxnExchangeType::Exit))
                {
                    $this->exitHandle();
                }
            }
        }
        catch(Exception $e) {
            $error = $e->getMessage();
            $this->user->notify(print_r($error, true));
        }

        $this->time_duration = $this->timer;
        $this->signal->save();

        $this->user->signals()->attach($this->signal, !is_null($error) ? compact('error') : []);
        $this->user->save();

        $this->user->txnStatus->trading_program_status = 0;
        $this->user->txnStatus->save();
    }

    // 強制平倉
    public function forceLiquidation()
    {
        try {
            $symbol_key = $this->signal->symbolType->key;
            // 做多進場狀態, 所以做多出場
            $account = $this->api->marginIsolatedAccountByKey($symbol_key);
            $quote_asset_borrowed = data_get($account, "assets.$symbol_key.quoteAsset.borrowed", 0);
            if($quote_asset_borrowed > 0) {
                $result = $this->api->doMarginExit($this->signal->symbolType, DirectType::fromValue(DirectType::LONG));
                $this->timer['force_liquidation_quoteAsset_borrowed'] = self::DuringTimer(function () use (&$result)
                {
                    if(array_key_exists('orders', $result) and $result['orders']) {
                        foreach ($result['orders'] as $order) {
                            if($order['price'] == 0 and OrderType::fromKey($order['type'])->is(OrderType::MARKET)) {
                                $order['price'] = $order['cummulativeQuoteQty'] / $order['executedQty'];
                            }
                            TxnFuturesOrder::create(array_merge([
                                'user_id' => $this->user->id,
                                'signal_id' => $this->signal->id
                            ], Arr::only($order, ['clientOrderId', 'cumQty', 'cumQuote', 'executedQty', 'orderId', 'avgPrice', 'origQty', 'price', 'reduceOnly', 'side', 'positionSide', 'status', 'stopPrice', 'closePosition', 'symbol', 'timeInForce', 'type', 'origType', 'activatePrice', 'priceRate', 'updateTime', 'workingType', 'priceProtect'])));
                        }
                    }
                });
            }
            // 做空進場狀態, 所以做空出場
            $account = $this->api->marginIsolatedAccountByKey($symbol_key);
            $base_asset_borrowed = data_get($account, "assets.$symbol_key.baseAsset.borrowed", 0);
            if($base_asset_borrowed > 0) {
                $result = $this->api->doMarginExit($this->signal->symbolType, DirectType::fromValue(DirectType::SHORT));
                $this->timer['force_liquidation_baseAsset_borrowed'] = self::DuringTimer(function () use (&$result)
                {
                    if(array_key_exists('orders', $result) and $result['orders']) {
                        foreach ($result['orders'] as $order) {
                            if($order['price'] == 0 and OrderType::fromKey($order['type'])->is(OrderType::MARKET)) {
                                $order['price'] = $order['cummulativeQuoteQty'] / $order['executedQty'];
                            }
                            TxnFuturesOrder::create(array_merge([
                                'user_id' => $this->user->id,
                                'signal_id' => $this->signal->id
                            ], Arr::only($order, ['clientOrderId', 'cumQty', 'cumQuote', 'executedQty', 'orderId', 'avgPrice', 'origQty', 'price', 'reduceOnly', 'side', 'positionSide', 'status', 'stopPrice', 'closePosition', 'symbol', 'timeInForce', 'type', 'origType', 'activatePrice', 'priceRate', 'updateTime', 'workingType', 'priceProtect'])));
                        }
                    }
                });
            }
            // 出掉所有的標的幣
            $account = $this->api->marginIsolatedAccountByKey($symbol_key);
            $base_asset_free = data_get($account, "assets.$symbol_key.baseAsset.free", 0);
            if($base_asset_free > 0) {
                $result = $this->api->doMarginExit($this->signal->symbolType, DirectType::fromValue(DirectType::LONG));
                $this->timer['force_liquidation_baseAsset_free'] = self::DuringTimer(function () use (&$result)
                {
                    if(array_key_exists('orders', $result) and $result['orders']) {
                        foreach ($result['orders'] as $order) {
                            if($order['price'] == 0 and OrderType::fromKey($order['type'])->is(OrderType::MARKET)) {
                                $order['price'] = $order['cummulativeQuoteQty'] / $order['executedQty'];
                            }
                            TxnFuturesOrder::create(array_merge([
                                'user_id' => $this->user->id,
                                'signal_id' => $this->signal->id
                            ], Arr::only($order, ['clientOrderId', 'cumQty', 'cumQuote', 'executedQty', 'orderId', 'avgPrice', 'origQty', 'price', 'reduceOnly', 'side', 'positionSide', 'status', 'stopPrice', 'closePosition', 'symbol', 'timeInForce', 'type', 'origType', 'activatePrice', 'priceRate', 'updateTime', 'workingType', 'priceProtect'])));
                        }
                    }
                });
            }
        }
        catch(Exception $e) {
            // 平倉失敗記錄起來
        }

        // 變更用戶狀態
        $this->user->txnStatus->current_feat_state = 0;
        $this->user->txnStatus->total_transaction_times++;
        $this->user->txnStatus->save();
    }

    private function initBinanceApi()
    {
        $ks = $this->user->keysecret()->toArray();
        $this->api = new BinanceApiManager(data_get($ks, 'key', ''), data_get($ks, 'secret', ''));
    }

    private function initWorksheet()
    {
        $formulaTable = $this->formulaTable = FuturesFormula::version()->first();
        if(is_null($formulaTable))
            throw new Exception('未上傳公式表');
        $this->spreadsheet = $formulaTable->spreadsheet;
        if(is_null($this->spreadsheet))
            throw new Exception(sprintf('公式表載入錯誤, 版本號: %d', $formulaTable->id));

        $symbol_key = $this->signal->symbolType->key;

        // 當前表試算表
        $sheet = $this->sheet = $this->spreadsheet->getActiveSheet();

        $account = $this->api->futuresAccount();
        // 初始帳戶餘額
        $sheet->setCellValue($formulaTable->setcol1, data_get($account, "availableBalance"));
        // 交易配對
        $sheet->setCellValue($formulaTable->setcol2, $symbol_key);
        // 每次初始可交易總資金(%)
        $sheet->setCellValue($formulaTable->setcol3, $this->user->txnFeatSetting->initial_tradable_total_funds);
        // 每次交易資金風險(%)
        $sheet->setCellValue($formulaTable->setcol4, $this->user->txnFeatSetting->initial_capital_risk);
        // 應開倉日期時間
        $sheet->setCellValue($formulaTable->setcol5, date('Y-m-d H:i:s', $this->signal->positionAt));
        // 交易方向(多/空)
        $sheet->setCellValue($formulaTable->setcol6, ($this->signal->txnDirectType->is(DirectType::LONG)) ? "Long Entry" : "Short Entry");
        // Entry訊號價位(當時的價位)
        $sheet->setCellValue($formulaTable->setcol7, $this->signal->currentPrice);
        // 起始風險價位(止損價位)
        $sheet->setCellValue($formulaTable->setcol8, $this->signal->riskStartPrice);
        // 開倉價格容差(最高價位)
        $sheet->setCellValue($formulaTable->setcol9, $this->signal->hightPositionPrice);
        // 開倉價格容差(最低價位)
        $sheet->setCellValue($formulaTable->setcol10, $this->signal->lowPositionPrice);
        // 危及強平價格%
        $sheet->setCellValue($formulaTable->setcol11, $this->user->txnFeatSetting->liquidation_prices);
    }

    /**
     * 取得計算出來的欄位值
     *
     * @param array $keys 要抓取的欄位
     * @return array
     * @throws \Exception
     */
    private function getCalculatedValues(array $keys = []) : array
    {
        $tmp = [];
        foreach ($keys as $key)
            array_push($tmp, $this->sheet->getCell($key)->getCalculatedValue());
        return $tmp;
    }

    /**
     * 進場: 計時、下單(+止盈損單)、變更用戶狀態
     *
     * @throws \Exception
     */
    private function entryHandle()
    {
        $result = [];

        $this->timer['entry'] = self::DuringTimer(function () use (&$result)
        {
            $direct = $this->signal->txn_direct_type;

            list($symbol, $leverage, $price, $quantity, $time_in_force, $stop_price, $sell_price, $stop_time_in_force) = $this->getCalculatedValues([
                $this->formulaTable->setcol2,
                $this->formulaTable->setcol15,
                $this->formulaTable->setcol16,
                $this->formulaTable->setcol17,
                $this->formulaTable->setcol19,
                $this->formulaTable->setcol20,
                $this->formulaTable->setcol21,
                $this->formulaTable->setcol25,
            ]);
            if($quantity == '-' or $quantity <= 0)
                throw new Exception(sprintf('數量小於 0(%.5f)', $quantity));
            $symbol = SymbolType::fromKey($symbol);
            $result = $this->api->doFeaturesEntry($symbol, $direct, $leverage, $price, $quantity, $time_in_force, $stop_price, $sell_price, $stop_time_in_force);
        });

        $this->timer['record_orders'] = self::DuringTimer(function () use (&$result)
        {
            if(array_key_exists('orders', $result) and $result['orders']) {
                foreach ($result['orders'] as $order) {
                    $arr = [
                        'user_id' => $this->user->id,
                        'signal_id' => $this->signal->id,
                    ];
                    if(OrderType::fromKey($order['type'])->is(OrderType::LIMIT))
                    {
                        list($loan_ratio) = $this->getCalculatedValues([$this->formulaTable->setcol14]);
                        $arr['loan_ratio'] = $loan_ratio;
                    }
                    TxnFuturesOrder::create(array_merge($arr, Arr::only($order, ['clientOrderId', 'cumQty', 'cumQuote', 'executedQty', 'orderId', 'avgPrice', 'origQty', 'price', 'reduceOnly', 'side', 'positionSide', 'status', 'stopPrice', 'closePosition', 'symbol', 'timeInForce', 'type', 'origType', 'activatePrice', 'priceRate', 'updateTime', 'workingType', 'priceProtect'])));
                }
            }
        });

        // excel 快照
        $html = new Html($this->spreadsheet);
        Storage::disk('local')->put("excel-logs/{$this->user->id}/{$this->signal->id}/index.html", $html->generateSheetData());

        // 變更用戶狀態
        $this->user->txnStatus->current_feat_state = 1;
        $this->user->txnStatus->total_transaction_times++;
        if($this->signal->txn_direct_type->is(DirectType::LONG))
            $this->user->txnStatus->total_number_of_long_times++;
        else
            $this->user->txnStatus->total_number_of_short_times++;
        $this->user->txnStatus->save();

        if(array_key_exists('error', $result) and $result['error'])
            throw new Exception($result['error']);
    }

    /**
     * 出場: 計算出場數量、出場、變更用戶狀態
     *
     * @throws \Exception
     */
    private function exitHandle()
    {
        $result = [];

        $this->timer['exit'] = self::DuringTimer(function () use (&$result) {
            $result = $this->api->doFeaturesExit($this->signal->symbolType, $this->signal->txnDirectType);
        });

        $this->timer['record_orders'] = self::DuringTimer(function () use (&$result)
        {
            if(array_key_exists('orders', $result) and $result['orders']) {
                foreach ($result['orders'] as $order) {

                    if($order['price'] == 0 and OrderType::fromKey($order['type'])->is(OrderType::MARKET)) {
                        $order['price'] = $order['cumQuote'] / $order['executedQty'];
                    }

                    TxnFuturesOrder::create(array_merge([
                        'user_id' => $this->user->id,
                        'signal_id' => $this->signal->id
                    ], Arr::only($order, ['clientOrderId', 'cumQty', 'cumQuote', 'executedQty', 'orderId', 'avgPrice', 'origQty', 'price', 'reduceOnly', 'side', 'positionSide', 'status', 'stopPrice', 'closePosition', 'symbol', 'timeInForce', 'type', 'origType', 'activatePrice', 'priceRate', 'updateTime', 'workingType', 'priceProtect'])));
                }
            }
        });

        // 變更用戶狀態
        $this->user->txnStatus->current_feat_state = 0;
        $this->user->txnStatus->total_transaction_times++;
        if($this->signal->txn_direct_type->is(DirectType::LONG))
            $this->user->txnStatus->total_number_of_long_times++;
        else
            $this->user->txnStatus->total_number_of_short_times++;
        $this->user->txnStatus->save();

        if(array_key_exists('error', $result) and $result['error'])
            throw new Exception($result['error']);
    }
}
