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
            try {
                // Entry訊號接收到時數據
                $entry = AdminTxnEntryRec::createRec($this->user, $this->signal);
                // 建立實際購買訊號
                $buy = AdminTxnBuyRec::createRec($entry, $this->user);
            }
            catch(Exception $e) {
                $this->signal->error = $e->getMessage();
                $this->signal->save();
            }
        }
        // 賣出
        elseif($exchange->is(TxnExchangeType::SELLING))
        {
        }
        $this->user->transactionStatus->trading_program_status = 0;
        $this->user->transactionStatus->save();
    }
}
