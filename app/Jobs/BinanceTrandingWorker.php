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
use BinanceApi\Enums\SymbolType;
use App\Enums\TxnDirectType;
use App\Enums\TxnExchangeType;
use App\Enums\TradingPlatformType;
use App\Models\AdminTxnEntryRec;
use App\Models\AdminTxnBuyRec;
use App\Models\AdminTxnExitRec;
use App\Models\AdminTxnSellRec;
use Exception;

class BinanceTrandingWorker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $signal, $user;

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

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
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
    }

    private function buying()
    {
        try {
            // Entry訊號接收到時數據
            $entry = AdminTxnEntryRec::createRec($this->user, $this->signal);
            // 建立實際購買訊號
            AdminTxnBuyRec::createRec($entry, $this->user);
        }
        catch(Exception $e) {
            $this->signal->error = $e->getMessage();
            $this->signal->save();
        }
    }

    private function selling()
    {
        try {
            // 建立連線
            $api = app()->makeWith(BinanceApi::class, $this->user->keysecret()->toArray());

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

            // 開始時間
            $start_at = time();
            $liquidation_start_at = date("Y-m-d H:i:s", $start_at);
            // 購買
            $order = $api->marketSell($symbol->key, $quantity);
            // 結束時間
            $done_at = time();
            $liquidation_done_at = date("Y-m-d H:i:s", $done_at);
            $liquidation_duration = $done_at - $start_at;

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
