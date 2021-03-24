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

class BinanceTrandingWorker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $signal, $user, $spreadsheet, $sheet, $api, $formulaTable, $timer = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AdminUser $user, SignalHistory $signal)
    {
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
        try {
            $this->user->signals()->attach($this->signal);
            $this->user->save();
            $this->user->load('txnSetting')->refresh();

            $this->user->txnStatus->trading_program_status = 1;
            $this->user->txnStatus->save();
            $exchange = $this->signal->txn_exchange_type;

            // 買入
            if($exchange->is(TxnExchangeType::Entry))
            {
                $this->timer['init'] = self::DuringTimer(function () {
                    $this->initBinanceApi();
                    $this->initWorksheet();
                });
                $this->entryHandle();
            }
            // 賣出
            elseif($exchange->is(TxnExchangeType::Exit))
            {
                $this->timer['init'] = self::DuringTimer(function () {
                    $this->initBinanceApi();
                });
                $this->exitHandle();
            }
        }
        catch(Exception $e) {
            $this->signal->error = $e->getMessage();
        }

        $this->time_duration = $this->timer;
        $this->signal->save();

        $this->user->txnStatus->trading_program_status = 0;
        $this->user->txnStatus->save();
    }

    private function initBinanceApi()
    {
        $ks = $this->user->keysecret()->toArray();
        $this->api = new BinanceApiManager(data_get($ks, 'key', ''), data_get($ks, 'secret', ''));
    }

    private function initWorksheet()
    {
        $formulaTable = $this->formulaTable = FormulaTable::version()->first();
        if(is_null($formulaTable))
            throw new Exception('未上傳公式表');
        $this->spreadsheet = $formulaTable->spreadsheet;
        if(is_null($this->spreadsheet))
            throw new Exception(sprintf('公式表載入錯誤, 版本號: %d', $formulaTable->id));

        $symbol_key = $this->signal->symbolType->key;

        // 當前表試算表
        $sheet = $this->sheet = $this->spreadsheet->getActiveSheet();

        $account = $this->api->marginIsolatedAccountByKey($symbol_key);
        // 當前總資金(計價幣)
        $sheet->setCellValue($formulaTable->setcol1, data_get($account, "assets.$symbol_key.quoteAsset.free"));
        // 當前總資金(標的幣)
        $sheet->setCellValue($formulaTable->setcol2, data_get($account, "assets.$symbol_key.baseAsset.free"));
        // 標的幣借款利息(24h)
        $sheet->setCellValue($formulaTable->setcol5, $this->user->txnSetting->btc_daily_interest);
        // 計價幣借款利息(24h)
        $sheet->setCellValue($formulaTable->setcol6, $this->user->txnSetting->usdt_daily_interest);
        // 交易配對
        $sheet->setCellValue($formulaTable->setcol7, $symbol_key);
        // 每次初始可交易總資金(%)
        $sheet->setCellValue($formulaTable->setcol8, $this->user->txnSetting->initial_tradable_total_funds);
        // 每次交易資金風險(%)
        $sheet->setCellValue($formulaTable->setcol9, $this->user->txnSetting->initial_capital_risk);
        // 槓桿使用
        $sheet->setCellValue($formulaTable->setcol10, ($this->user->txnSetting->lever_switch) ? "Yes" : "No");
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

        // excel 快照
        $html = new Html($this->spreadsheet);
        Storage::disk('local')->put("excel-logs/{$this->user->id}/{$this->signal->id}/index.html", $html->generateSheetData());
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

        $this->timer['isolate_entry'] = self::DuringTimer(function () use (&$result) {
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
                $symbol = SymbolType::fromKey($symbol);
                if($sell_quantity != '-') {
                    $result = $this->api->doIsolateEntryButSell($symbol, floatval($sell_quantity));
                }
                else {
                    $quantity = $capital / $current;
                    if($quantity <= 0)
                        throw new Exception(sprintf('數量小於 0(%.5f)', $quantity));
                    $result = $this->api->doIsolateEntry($symbol, $direct, $quantity, $price, $stop_price, $sell_price);
                    // 設定自動賣出時間
                    if($auto_liquidation > 0 and array_key_exists('order', $result) and $result['order']) {
                        $at = Carbon::now()->addHours($auto_liquidation)->format('Y-m-d H:i:s');
                        $this->signal->auto_liquidation_at = $at;
                        $this->signal->save();
                    }
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

                $symbol = SymbolType::fromKey($symbol);
                $result = $this->api->doIsolateEntry($symbol, $direct, $quantity, $price, $stop_price, $sell_price);
                // 設定自動賣出時間
                if($auto_liquidation > 0 and array_key_exists('order', $result) and $result['order']) {
                    $at = Carbon::now()->addHours($auto_liquidation)->format('Y-m-d H:i:s');
                    $this->signal->auto_liquidation_at = $at;
                    $this->signal->save();
                }
            }
        });

        $this->timer['record_orders'] = self::DuringTimer(function () use (&$result)
        {
            $fetch_keys = ["symbol", "orderId", "clientOrderId", "transactTime", "price", "origQty", "executedQty", "cummulativeQuoteQty", "status", "timeInForce", "type", "side", "marginBuyBorrowAsset", "marginBuyBorrowAmount", "isIsolated"];

            if(array_key_exists('order_sell', $result) and $result['order_sell']) {
                TxnMarginOrder::create(array_merge([
                    'user_id' => $this->user->id,
                    'signal_id' => $this->signal->id,
                    'fills' => json_encode($result['order']['fills'])
                ], Arr::only($result['order_sell'], $fetch_keys)));
            }

            if(array_key_exists('order', $result) and $result['order']) {
                TxnMarginOrder::create(array_merge([
                    'user_id' => $this->user->id,
                    'signal_id' => $this->signal->id,
                    'fills' => json_encode($result['order']['fills'])
                ], Arr::only($result['order'], $fetch_keys)));
            }

            if(array_key_exists('stop_order', $result) and $result['stop_order']) {
                TxnMarginOrder::create(array_merge([
                    'user_id' => $this->user->id,
                    'signal_id' => $this->signal->id,
                    'fills' => json_encode($result['stop_order']['fills'])
                ], Arr::only($result['stop_order'], $fetch_keys)));
            }
        });

        if(array_key_exists('error', $result) and $result['error'])
            throw new Exception($result['error']);

        // 變更用戶狀態
        $this->user->txnStatus->current_state = 1;
        $this->user->txnStatus->total_transaction_times++;
        if($this->signal->txn_direct_type->is(DirectType::LONG))
            $this->user->txnStatus->total_number_of_long_times++;
        else
            $this->user->txnStatus->total_number_of_short_times++;
        $this->user->txnStatus->save();
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
            $result = $this->api->doIsolateExit($this->signal->symbolType, $this->signal->txnDirectType);
        });

        $this->timer['record_orders'] = self::DuringTimer(function () use (&$result)
        {
            $fetch_keys = ["symbol", "orderId", "clientOrderId", "transactTime", "price", "origQty", "executedQty", "cummulativeQuoteQty", "status", "timeInForce", "type", "side", "marginBuyBorrowAsset", "marginBuyBorrowAmount", "isIsolated"];

            if(array_key_exists('order', $result) and $result['order']) {
                TxnMarginOrder::create(array_merge([
                    'user_id' => $this->user->id,
                    'signal_id' => $this->signal->id,
                    'fills' => json_encode($result['order']['fills'])
                ], Arr::only($result['order'], $fetch_keys)));
            }

            if(array_key_exists('stop_orders', $result) and $result['stop_orders']) {
                foreach ($result['stop_orders'] as $stop_order) {
                    TxnMarginOrder::create(array_merge([
                        'user_id' => $this->user->id,
                        'signal_id' => $this->signal->id,
                    ], Arr::only($stop_order, $fetch_keys)));
                }
            }

            // if(array_key_exists('rm_stop_orders', $result) and $result['rm_stop_orders']) {
            //     foreach ($result['rm_stop_orders'] as $rm_stop_orders) {
            //         TxnMarginOrder::create(array_merge([
            //             'user_id' => $this->user->id,
            //             'signal_id' => $this->signal->id,
            //         ], Arr::only($rm_stop_orders, $fetch_keys)));
            //     }
            // }
        });

        if(array_key_exists('error', $result) and $result['error'])
            throw new Exception($result['error']);

        // 變更用戶狀態
        $this->user->txnStatus->current_state = 0;
        $this->user->txnStatus->total_transaction_times++;
        if($this->signal->txn_direct_type->is(DirectType::LONG))
            $this->user->txnStatus->total_number_of_long_times++;
        else
            $this->user->txnStatus->total_number_of_short_times++;
        $this->user->txnStatus->save();
    }
}
