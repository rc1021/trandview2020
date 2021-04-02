<?php

namespace App\Jobs;

use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Builder;
use App\Models\AdminUser;
use App\Models\TxnMarginOrder;
use App\Enums\TxnExchangeType;
use BinanceApi\BinanceApiManager;

class DailySummaryBTCUSDT implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $start, $end, $symbol = 'BTCUSDT';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->end   = new DateTime();
        $this->start = $this->end->modify('-1 day');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        AdminUser::chunk(200, function ($users) {
            foreach ($users as $user)
            {
                // 取得槓桿交易記錄
                $content = $this->isolatedDailySummary($user);
                // 發通知訊息
                $user->notify($content);
            }
        });
    }

    private function content_format($arr)
    {

    }

    private function isoLatedDailySummary(AdminUser $user)
    {
        $ks = $user->keysecret()->toArray();
        $api = new BinanceApiManager(data_get($ks, 'key', ''), data_get($ks, 'secret', ''));
        $startms = (int) ($this->start->getTimestamp() . $this->start->format('v'));
        $arr = $api->marginGetIsolatedAllOrders($this->symbol, null, $startms);
    }
}
