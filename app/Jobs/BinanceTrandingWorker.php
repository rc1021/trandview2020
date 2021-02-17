<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Builder;
use App\Models\SignalHistory;
use App\Models\AdminUser;
use App\Enums\SymbolType;
use App\Enums\TxnDirectType;
use App\Enums\TxnExchangeType;
use App\Enums\TradingPlatformType;
use App\Models\AdminTxnEntryRec;
use App\Models\AdminTxnBuyRec;
use App\Models\AdminTxnExitRec;
use App\Models\AdminTxnSellRec;
use App\Providers\AppCode;
use Binance;
use Exception;

class BinanceTrandingWorker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $signal, $user, $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AdminUser $user, TradingPlatformType $type, SignalHistory $signal)
    {
        $this->user = $user->withoutRelations();
        $this->signal = $signal->withoutRelations();
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // try {
            $this->user->refresh();
            $this->user->transactionStatus->trading_program_status = 1;
            $this->user->transactionStatus->save();
            $exchange = TxnExchangeType::fromValue($this->signal->txn_exchange_type);
            // 買入
            if($exchange->is(TxnExchangeType::BUYING))
            {
                $this->buying();
            }
            // 賣出
            elseif($exchange->is(TxnExchangeType::SELLING))
            {
                $this->selling();
            }
            $this->user->transactionStatus->trading_program_status = 0;
            $this->user->transactionStatus->save();
        // }
        // catch(Exception $e) {

        // }
    }

    private function binance_filter($symbol_key, &$quantity, &$price)
    {
        $exchange = AppCode::BinanceExchangeInfo($symbol_key);
        $quantity = round($quantity, data_get($exchange, 'baseAssetPrecision', 8));
        $price = round($price, data_get($exchange, 'quoteAssetPrecision', 8));
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
                case 'LOT_SIZE':
                    $less = strlen(substr(strrchr(rtrim($filter['minQty'], '0'), "."), 1));
                    $quantity = round($quantity, $less);
                    if($quantity > (int)$filter['maxQty'])
                        $quantity = (int)$filter['maxQty'];
                    break;
            }
        }
    }

    private function buying()
    {
        // Entry訊號接收到時數據
        $entry = AdminTxnEntryRec::addRec(
            $this->user->transactionSetting->transaction_matching,
            $this->signal->position_at,
            $this->user->transactionSetting->initial_tradable_total_funds,
            $this->user->transactionSetting->initial_capital_risk,
            $this->signal->txn_direct_type,
            $this->user->transactionSetting->transaction_fees,
            $this->signal->risk_start_price,
            $this->signal->hight_position_price,
            $this->signal->low_position_price,
            $this->signal->entry_price,
            $this->user->transactionSetting->lever_switch,
            $this->user->transactionSetting->prededuct_handling_fee,
            $this->user->id,
            $this->signal->id
        );

        // 買入數量
        $quantity = ($this->user->transactionSetting->prededuct_handling_fee) ? $entry->position_few_amount : $entry->position_few;
        // 購買虛擬幣
        $symbol = SymbolType::coerce((int)$this->user->transactionSetting->transaction_matching);
        // 購入金額
        $price = $this->signal->position_price;
        // 過濾購買資料
        $this->binance_filter($symbol->key, $quantity, $price);

        // 建立連線
        $apidata = $this->user->keysecret();
        $api = new Binance\API($apidata->key,$apidata->secret);

        try {

            // 開始時間
            $start_at = time();
            $position_start_at = date("Y-m-d H:i:s", $start_at);
            // 購買
            $order = $api->buy($symbol->key, $quantity, $price);
            // 結束時間
            $done_at = time();
            $position_done_at = date("Y-m-d H:i:s", $done_at);
            $position_duration = $done_at - $start_at;

            if(is_null($order) or count($order['fills']) == 0) {
                $api->cancel($order['symbol'], $order['orderId']);
                throw new Exception('未立即完成訂單(撤單)');
            }

            // 建立實際購買訊號
            $buy = AdminTxnBuyRec::addRec(
                [$order],
                $entry->id,
                $position_start_at,
                $position_done_at,
                $position_duration,
                $this->user->id
            );

            // 變更用戶狀態
            $this->user->transactionStatus->current_state = 1;
            $this->user->transactionStatus->total_transaction_times++;
            $txnDirectType = TxnDirectType::fromValue($this->signal->txn_direct_type);
            if($txnDirectType->is(TxnDirectType::LONG))
                $this->user->transactionStatus->total_number_of_long_times++;
            else
                $this->user->transactionStatus->total_number_of_short_times++;
            $this->user->transactionStatus->save();
        }
        catch(Exception $e) {
            $this->signal->error = $e->getMessage() . " { \"quantity\": $quantity, \"price\": $price }";
            $this->signal->save();
        }
    }

    private function selling()
    {
        try {
            // 取得最後一次持倉的資訊
            $entry = $this->user->transactionEntryRecords()->doesntHave('txnExitRec')->latest()->first();
            $buy = $entry->txnBuyRec;
            if(is_null($entry) or is_null($buy))
                throw new Exception("找不到持倉中的訊息");
            // Exit訊號接收到時數據
            $exit = AdminTxnExitRec::addRec(
                $entry->id,
                $this->signal->id,
                $this->signal->position_at,
                $this->signal->entry_price,
                $this->user->id
            );

            // 買入數量
            $quantity = $buy->position_quota - $buy->position_quota * $this->user->transactionSetting->transaction_fees;
            // 購買虛擬幣
            $symbol = SymbolType::coerce((int)$this->user->transactionSetting->transaction_matching);

            // 建立連線
            $apidata = $this->user->keysecret();
            $api = new Binance\API($apidata->key,$apidata->secret);

            // 取得手續賣
            $trade_fee = $api->tradeFee('BTCUSDT');
            if(data_get($trade_fee, 'success', false))
                $quantity -= $quantity * data_get($trade_fee, 'tradeFee.0.maker', 0.001);

            // 開始時間
            $start_at = time();
            $liquidation_start_at = date("Y-m-d H:i:s", $start_at);
            // 購買
            $order = $api->marketSell($symbol->key, $quantity);
            // 結束時間
            $done_at = time();
            $liquidation_done_at = date("Y-m-d H:i:s", $done_at);
            $liquidation_duration = $done_at - $start_at;

            var_dump($order);

            $sell = AdminTxnSellRec::addRec(
                [$order],
                $exit->id,
                $liquidation_start_at,
                $liquidation_done_at,
                $liquidation_duration,
                $this->user->id
            );

            $this->user->transactionStatus->current_state = 0;
            $this->user->transactionStatus->save();
        }
        catch(Exception $e) {
            $this->signal->error = $e->getMessage();
            $this->signal->save();
        }
    }

}
