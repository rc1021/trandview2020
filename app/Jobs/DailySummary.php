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
use BinanceApi\Enums\SymbolType;

class DailySummary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $build_at;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->build_at = new DateTime();
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
                $report_daily_summary = $this->isolatedDailySummary($user);
                // 發通知訊息
                $user->notify($report_daily_summary);
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
        $start = $this->build_at->modify('-1 day');
        $startms = (int) (($start->getTimestamp() + 1) . $start->format('v'));

        foreach (SymbolType::getKeys() as $symbol) {
            $orders = $api->marginGetIsolatedAllOrders($symbol, null, $startms);
        }
    }
}
