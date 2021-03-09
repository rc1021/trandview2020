<?php

namespace App\Models\Traits;

use App\Models\AdminUser;
use App\Models\TxnMarginOrder;
use Illuminate\Support\Arr;
use App\Models\AdminTxnExitRec;
use BinanceApi\BinanceApiManager;
use BinanceApi\Enums\SymbolType;
use BinanceApi\Enums\DirectType;

trait AdminTxnSellRecTrait
{
    // 建立實際平倉紀錄
    public static function createRec(AdminTxnExitRec $exit, AdminUser $user, TxnMarginOrder $stop = null)
    {
        if(is_null($stop))
            $stop = $exit->txnBuyRec->stopLossLimit;

        $rec = new \App\Models\AdminTxnSellRec;
        $rec->user_id = $user->id;
        $rec->txn_exit_id = $exit->id;

        $ks = $user->keysecret()->toArray();
        $api = new BinanceApiManager(data_get($ks, 'key', ''), data_get($ks, 'secret', ''));
        $symbol = $user->txnSetting->txnSymbolType;

        // 開始時間
        $start_at = time();
        $position_start_at = date("Y-m-d H:i:s", $start_at);

        list($err, $order) = $api->doIsolateExit($symbol, $exit->signal->txnDirectType, $stop->orderId);

        // 結束時間
        $done_at = time();
        $position_done_at = date("Y-m-d H:i:s", $done_at);
        $position_duration = $done_at - $start_at;

        $rec->J29 = $position_start_at;
        $rec->J30 = $position_done_at;
        $rec->J31 = $position_duration;

        if(!is_null($err)) {
            $stop->error = $err;
            $stop->save();
        }

        if(!is_null($order)) {

            $txnOrder = TxnMarginOrder::create(array_merge([
                'user_id' => $user->id,
                'fills' => json_encode($order['fills'])
            ], Arr::only($order, ["symbol", "orderId", "clientOrderId", "transactTime", "price", "origQty", "executedQty", "cummulativeQuoteQty", "status", "timeInForce", "type", "side", "marginBuyBorrowAsset", "marginBuyBorrowAmount", "isIsolated"])));

            $rec->ord_id = $txnOrder->id;
            $stop->delete();
        }

        $rec->save();
        return $rec;
    }
}
