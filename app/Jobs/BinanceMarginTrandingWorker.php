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
use BinanceApi\Enums\DirectType;
use BinanceApi\Enums\OrderType;
use BinanceApi\Enums\SideType;
use BinanceApi\BinanceApiManager;
use App\Models\TxnMarginOrder;
use App\Models\SignalHistory;
use App\Models\AdminUser;
use App\Models\AdminTxnSellRec;
use App\Models\AdminTxnExitRec;
use App\Models\AdminTxnEntryRec;
use App\Models\AdminTxnBuyRec;
use App\Models\FormulaTable;
use App\Enums\TxnExchangeType;
use App\Enums\TxnDirectType;
use App\Enums\TradingPlatformType;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use Exception;

class BinanceMarginTrandingWorker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $signal, $user, $txn_setting, $spreadsheet, $sheet, $api, $formulaTable, $notifyMessage, $timer = [];

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
            $symbol_key = $this->signal->symbol_type;

            $this->txn_setting = $this->user->txnSettings()->where('pair', $this->signal->symbol_type)->with('user')->first();
            $this->user->signals()->attach($this->signal);

            $exchange = $this->signal->txn_exchange_type;
            $this->api = $this->user->binance_api;

            // 記錄交易前的資產情況
            $account = $this->api->marginIsolatedAccountByKey($symbol_key);
            $this->user->signals()->updateExistingPivot($this->signal, ['before_asset' => data_get($account, "assets.$symbol_key", [])]);

            if($this->signal->txn_direct_type->is(DirectType::FORCE))
            {
                $price = $this->api->marginPrice($symbol_key);
                $this->signal->current_price = $this->api->floor_dec($price, 2);
                $this->signal->save();
                // 強制出場
                $this->forceLiquidation();
            }
            else
            {
                // 買入
                if($exchange->is(TxnExchangeType::Entry))
                {
                    $this->initWorksheet($account);

                    for($i = 2.0; $i >= 0.0; $i = $i - 0.1) {
                        try {
                            $this->sheet->setCellValue($this->formulaTable->setcol31, ($i > 1.0) ? 1.0 : $i);
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
            $this->user->lineNotify($e->getMessage());
        }

        $this->time_duration = $this->timer;
        $this->signal->save();

        // 記錄交易後的資產情況
        $account = $this->api->marginIsolatedAccountByKey($symbol_key);
        $this->user->signals()->updateExistingPivot($this->signal, ['after_asset' => data_get($account, "assets.$symbol_key", [])]);

        if(!is_null($error))
            $this->user->signals()->updateExistingPivot($this->signal, compact('error'));
        $this->user->save();
    }

    // 強制平倉
    public function forceLiquidation()
    {
            $symbol_key = $this->signal->symbol_type;
            // 做多進場狀態, 所以做多出場
            $account = $this->api->marginIsolatedAccountByKey($symbol_key);
            $quote_asset_borrowed = data_get($account, "assets.$symbol_key.quoteAsset.borrowed", 0);
            if($quote_asset_borrowed > 0) {
                $result = $this->api->doMarginExit($symbol_key, DirectType::fromValue(DirectType::LONG));
                $this->timer['force_liquidation_quoteAsset_borrowed'] = self::DuringTimer(function () use (&$result)
                {
                    if(array_key_exists('orders', $result) and $result['orders'])
                        $this->createTxnOrderFromOrders($result['orders']);
                });
            }
            // 做空進場狀態, 所以做空出場
            $account = $this->api->marginIsolatedAccountByKey($symbol_key);
            $base_asset_borrowed = data_get($account, "assets.$symbol_key.baseAsset.borrowed", 0);
            if($base_asset_borrowed > 0) {
                $result = $this->api->doMarginExit($symbol_key, DirectType::fromValue(DirectType::SHORT));
                $this->timer['force_liquidation_baseAsset_borrowed'] = self::DuringTimer(function () use (&$result)
                {
                    if(array_key_exists('orders', $result) and $result['orders'])
                        $this->createTxnOrderFromOrders($result['orders']);
                });
            }
    }


    /**
     * 從orders陣列新增訂單紀錄
     *
     * @return void
     */
    public function createTxnOrderFromOrders(array $orders)
    {
        foreach ($orders as $order) {
            if($order['price'] == 0 and OrderType::fromKey($order['type'])->is(OrderType::MARKET)) {
                $order['price'] = $order['cummulativeQuoteQty'] / $order['executedQty'];
            }
            TxnMarginOrder::create(array_merge([
                'user_id' => $this->user->id,
                'signal_id' => $this->signal->id
            ], Arr::only($order, ["symbol", "orderId", "clientOrderId", "transactTime", "price", "origQty", "executedQty", "cummulativeQuoteQty", "status", "timeInForce", "type", "side", "marginBuyBorrowAsset", "marginBuyBorrowAmount", "isIsolated", "fills", "loan_ratio"])));
        }
    }

    private function initWorksheet(array $account)
    {
        $symbol_key = $this->signal->symbol_type;
        $formulaTable = $this->formulaTable = FormulaTable::pair($symbol_key)->version()->first();
        if(is_null($formulaTable))
            throw new Exception(sprintf('未上傳%s公式表', $symbol_key));
        $this->spreadsheet = $formulaTable->spreadsheet;
        if(is_null($this->spreadsheet))
            throw new Exception(sprintf('%s公式表載入錯誤, 版本號: %d', $symbol_key, $formulaTable->id));

        // 當前表試算表
        $sheet = $this->sheet = $this->spreadsheet->getActiveSheet();

        // 當前總資金(計價幣)
        $sheet->setCellValue($formulaTable->setcol1, data_get($account, "assets.$symbol_key.quoteAsset.free"));
        // 當前總資金(標的幣)
        $sheet->setCellValue($formulaTable->setcol2, data_get($account, "assets.$symbol_key.baseAsset.free"));
        // 標的幣借款利息(24h)
        $sheet->setCellValue($formulaTable->setcol5, $this->txn_setting->base_asset_daily_interest);
        // 計價幣借款利息(24h)
        $sheet->setCellValue($formulaTable->setcol6, $this->txn_setting->quote_asset_daily_interest);
        // 交易配對
        $sheet->setCellValue($formulaTable->setcol7, $symbol_key);
        // 每次初始可交易總資金(%)
        $sheet->setCellValue($formulaTable->setcol8, $this->txn_setting->initial_tradable_total_funds);
        // 每次交易資金風險(%)
        $sheet->setCellValue($formulaTable->setcol9, $this->txn_setting->initial_capital_risk);
        // 槓桿使用
        $sheet->setCellValue($formulaTable->setcol10, ($this->txn_setting->lever_switch) ? "Yes" : "No");
        // 應開倉日期時間
        $sheet->setCellValue($formulaTable->setcol11, date('Y-m-d H:i:s', $this->signal->positionAt));
        // 交易方向(多/空)
        $sheet->setCellValue($formulaTable->setcol12, ($this->signal->txnDirectType->is(DirectType::LONG)) ? "Entry Long" : "Entry Short");
        // Entry訊號價位(當時的價位)
        $sheet->setCellValue($formulaTable->setcol13, $this->signal->currentPrice);
        // 起始風險價位(止損價位)
        $sheet->setCellValue($formulaTable->setcol14, $this->signal->riskStartPrice);
        // 開倉價格容差(最高價位)
        $sheet->setCellValue($formulaTable->setcol15, $this->signal->hightPositionPrice);
        // 開倉價格容差(最低價位)
        $sheet->setCellValue($formulaTable->setcol16, $this->signal->lowPositionPrice);
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

        $this->timer['isolate_entry'] = self::DuringTimer(function () use (&$result)
        {
            $direct = $this->signal->txn_direct_type;

            if($direct->is(DirectType::LONG)) {
                // 購買虛擬幣
                list($symbol, $current, $capital, $price, $stop_price, $sell_price, $sell_quantity, $auto_liquidation) = $this->getCalculatedValues([
                    $this->formulaTable->setcol7,
                    $this->formulaTable->setcol13,
                    $this->formulaTable->setcol22,
                    $this->formulaTable->setcol15,
                    $this->formulaTable->setcol27,
                    $this->formulaTable->setcol28,
                    $this->formulaTable->setcol23,
                    $this->formulaTable->setcol30,
                ]);
                if($sell_quantity != '-') {
                    $result = $this->api->doMarginEntryButSell($symbol, floatval($sell_quantity));
                }
                else {
                    $quantity = $capital / $current;
                    if($quantity <= 0)
                        throw new Exception(sprintf('數量小於 0(%.5f)', $quantity));
                    $result = $this->api->doMarginEntry($symbol, $direct, $quantity, $price, $stop_price, $sell_price);
                }
            }
            else {
                // 購買虛擬幣
                list($symbol, $current, $quantity, $price, $stop_price, $sell_price, $auto_liquidation) = $this->getCalculatedValues([
                    $this->formulaTable->setcol7,
                    $this->formulaTable->setcol13,
                    $this->formulaTable->setcol26,
                    $this->formulaTable->setcol16,
                    $this->formulaTable->setcol27,
                    $this->formulaTable->setcol28,
                    $this->formulaTable->setcol30,
                ]);

                $result = $this->api->doMarginEntry($symbol, $direct, $quantity, $price, $stop_price, $sell_price);
            }
        });

        $this->timer['record_orders'] = self::DuringTimer(function () use (&$result)
        {
            if(array_key_exists('orders', $result) and $result['orders'])
            {
                foreach ($result['orders'] as $key => $order) {
                    if(OrderType::fromKey($order['type'])->is(OrderType::LIMIT))
                    {
                        list($loan_ratio) = $this->getCalculatedValues([$this->formulaTable->setcol31]);
                        $result['orders'][$key]['loan_ratio'] = $loan_ratio;
                    }
                }
                $this->createTxnOrderFromOrders($result['orders']);
            }
        });

        // excel 快照
        $html = new Html($this->spreadsheet);
        Storage::disk('local')->put("excel-logs/{$this->user->id}/{$this->signal->id}/index.html", $html->generateSheetData());

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

        $this->timer['isolate_exit'] = self::DuringTimer(function () use (&$result) {
            $result = $this->api->doMarginExit($this->signal->symbol_type, $this->signal->txnDirectType);
        });

        $this->timer['record_orders'] = self::DuringTimer(function () use (&$result)
        {
            if(array_key_exists('orders', $result) and $result['orders'])
                $this->createTxnOrderFromOrders($result['orders']);
        });

        if(array_key_exists('error', $result) and $result['error'])
            throw new Exception($result['error']);
    }
}
