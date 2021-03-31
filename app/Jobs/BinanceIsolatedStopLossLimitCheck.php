<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TxnMarginOrder;
use App\Models\AdminUser;
use BinanceApi\BinanceApiManager;
use BinanceApi\Enums\SideType;
use Illuminate\Support\Arr;

class BinanceIsolatedStopLossLimitCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order_id)
    {
        $this->order_id = $order_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = TxnMarginOrder::with(['user', 'user.keysecrets'])->find($this->order_id);
        $user = $order->user;
        $ks = $user->keysecrets->first()->toArray();
        $api = new BinanceApiManager(data_get($ks, 'key', ''), data_get($ks, 'secret', ''));
        $current = $api->marginGetIsolatedOrder($order->symbol, $order->id);

        // 如果狀態有改
        if($current['status'] != $order->status) {

            // 如果"做空"止損單被觸發, 就把買入不足還利息的標的幣，再還掉利息
            $side = SideType::fromValue(SideType::BUY)->key;
            if($order->side == $side && $current['status'] == 'FILLED') {
                $api->IsolatedBaseAssetRepay($order->symbol);
            }

            TxnMarginOrder::where('id', $this->order_id)
                    ->update(Arr::only($current, ["signal_id", "user_id", "fills", "symbol", "orderId", "clientOrderId", "transactTime", "price", "origQty", "executedQty", "cummulativeQuoteQty", "status", "timeInForce", "type", "side", "marginBuyBorrowAsset", "marginBuyBorrowAmount", "isIsolated"]));

            $notify_message  = "止損單狀態改變 from " . $order->status . " to " . $current['status'] . "\n";
            $notify_message .= "詳情: \n";
            $notify_message .= print_r($current, true);
            $user->notify(print_r($notify_message, true));
        }
    }
}
