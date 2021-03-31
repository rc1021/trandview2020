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

class DailySummary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $start, $end;

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
                // 取得交易記錄
                $arr = [];
                $orders = $user->load(['orders' => function ($query) {
                    return $query->whereBetween('created_at', [
                            $this->start->format('Y-m-d H:i:s'),
                            $this->end->format('Y-m-d H:i:s')
                        ])
                        ->orderBy('created_at', 'asc');
                }, 'orders.signal'])->get();
                if($orders->count() > 0)
                {
                    // 如果第一筆資料不是 Entry 的話，就再抓取前一天的資料，找到前一筆Entry
                    if(!$orders->first()->signal->txnExchangeType->is(TxnExchangeType::Entry)) {
                        $orders->prepend(TxnMarginOrder::with(['signal'])->whereHas('signal', function (Builder $query) {
                            $query->where('message', 'like', '交易執行類別=%Entry%');
                        })->where('created_at', '<', $this->start->format('Y-m-d H:i:s'))->first());
                    }

                    // 計算進出差額
                }

                // 發通知訊息
                $user->notify($this->content_format($arr));
            }
        });
    }

    private function content_format($arr)
    {

    }
}
