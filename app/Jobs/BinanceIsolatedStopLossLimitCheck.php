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
use BinanceApi\Enums\OrderStatusType;
use Illuminate\Support\Arr;
use App\Observers\TxnMarginOrderObserver;
use Exception;
use Illuminate\Support\Facades\Log;

class BinanceIsolatedStopLossLimitCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // 取得所有尚未結束的止損單
        TxnMarginOrder::stopLossLimit()->statusNew()->chunk(200, function ($orders) {
            foreach ($orders as $order)
            {
                $this->fire($order->id);
            }
        });
    }

    private function fire($orderID)
    {
        $order = TxnMarginOrder::with(['user', 'user.keysecrets'])->find($orderID);
        $user = $order->user;
        $ks = $user->keysecrets->first()->toArray();
        $api = new BinanceApiManager(data_get($ks, 'key', ''), data_get($ks, 'secret', ''));

        try {
            $current = $api->marginGetIsolatedOrder($order->symbol, $order->id);

            // 如果狀態有改
            if($current['status'] != $order->status) {

                // 如果"做空"止損單被觸發, 就把買入不足還利息的標的幣，再還掉利息
                $side = SideType::fromValue(SideType::BUY)->key;
                if($order->side == $side && $current['status'] == 'FILLED') {
                    $api->IsolatedBaseAssetRepay($order->symbol);
                }

                TxnMarginOrder::where('id', $orderID)
                        ->update(Arr::only($current, ["signal_id", "user_id", "fills", "symbol", "orderId", "clientOrderId", "transactTime", "price", "origQty", "executedQty", "cummulativeQuoteQty", "status", "timeInForce", "type", "side", "marginBuyBorrowAsset", "marginBuyBorrowAmount", "isIsolated"]));

                unset($order['isIsolated']);
                unset($order['error']);
                unset($order['deleted_at']);
                $notify_message  = "止損單狀態改變 from " . $order->status . " to " . $current['status'] . "\n";
                $notify_message .= "詳情: \n";
                $notify_message .= TxnMarginOrderObserver::GetMessage($current);
                $user->notify(print_r($notify_message, true));
            }
        }
        catch(Exception $e) {
            $req = $api->getLastRequest();
            // Order does not exist.
            if(data_get($req, 'json.code', 0) == -2013) {
                $order->status = OrderStatusType::fromValue(OrderStatusType::CANCELED)->key;
                $order->save();
                return ;
            }
            Log::warning(sprintf('執行 <code>StopLossLimitCheck(%s)</code> 時，發生錯誤：%s', $orderID, $e->getMessage()));
        }
    }
}
