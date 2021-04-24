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
use BinanceApi\Enums\SideType;
use BinanceApi\Enums\OrderStatusType;
use BinanceApi\Enums\OrderType;
use Exception;

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
                try {
                // 取得槓桿交易記錄
                $report_daily_summary = $this->marginDailySummary($user);
                // 發通知訊息
                $user->notify($report_daily_summary);
                }
                catch(Exception $e) {
                    // var_dump($e->getMessage());
                }
            }
        });
    }

    private function content_format($arr)
    {

    }

    private function marginDailySummary(AdminUser $user)
    {
        $ks = $user->keysecret()->toArray();
        $api = new BinanceApiManager(data_get($ks, 'key', ''), data_get($ks, 'secret', ''));
        $start = $this->build_at->modify('-1 day');
        $startms = (int) (($start->getTimestamp() + 1) . $start->format('v'));

        foreach (SymbolType::getKeys() as $symbol) {
            $orders = collect($api->marginGetIsolatedAllOrders($symbol, null, $startms));
            $orders = $orders->filter(function ($value, $key) {
                return OrderStatusType::fromKey($value['status'])->is(OrderStatusType::FILLED);
            });
            if($orders->count() > 0)
            {
                // 如果第一筆資料不是 Entry 的話，就再抓取前一天的資料，找到前一筆Entry
                $first = TxnMarginOrder::where('orderId', data_get($orders->first(), 'orderId'))->first();
                if(!is_null($first)) {
                    if(!$first->signal->txnExchangeType->is(TxnExchangeType::Entry)) {
                        $first = TxnMarginOrder::with(['signal'])->whereHas('signal', function (Builder $query) {
                            $query->where('message', 'like', '交易執行類別=%Entry%');
                        })->where('status', OrderStatusType::fromValue(OrderStatusType::FILLED))
                        ->where('created_at', '<', $start->format('Y-m-d H:i:s'))->first();
                        if($first)
                            $orders->prepend($first->toArr);
                    }
                }

                // 計算進出差額
                $buy_times = 0;
                $sell_times = 0;
                $total = $orders->reduce(function ($carry, $order) use (&$buy_times, &$sell_times) {
                    $side = data_get($order, 'side');
                    $cummulative = data_get($order, 'cummulativeQuoteQty');

                    if(SideType::fromKey($side)->is(SideType::BUY)) {
                        $buy_times++;
                        return $carry - $cummulative;
                    }
                    else if(SideType::fromKey($side)->is(SideType::SELL)) {
                        $sell_times++;
                        return $carry + $cummulative;
                    }
                    return $carry;
                }, 0);

                $total = $api->floor_dec($total, 2);
                $times = (int) floor(($buy_times + $sell_times) / 2);
                $less = $sell_times - $buy_times;

                $last = $orders->last();
                $type = data_get($last, 'type');

                if($less != 0) {
                    return <<<EOF
                    每日統計
                    本日營收：$total
                    進出次數：$times

                    *目前尚有持倉未出場
                    EOF;
                }
                return <<<EOF
                每日統計
                本日營收：$total
                進出次數：$times
                EOF;
            }

        }
    }
}
