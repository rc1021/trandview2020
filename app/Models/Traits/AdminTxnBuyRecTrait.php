<?php

namespace App\Models\Traits;

use BinanceApi\Enums\SymbolType;
use BinanceApi\Enums\SideType;
use BinanceApi\Enums\DirectType;
use Illuminate\Support\Arr;
use BinanceApi\BinanceApiManager;
use Exception;
use App\Models\AdminUser;
use App\Models\AdminTxnEntryRec;
use App\Models\SignalHistory;
use App\Models\TxnMarginOrder;

trait AdminTxnBuyRecTrait
{
    // 建立實際開倉紀錄
    public static function createRec(AdminTxnEntryRec $entry, AdminUser $user, SignalHistory $signal)
    {
        $rec = new \App\Models\AdminTxnBuyRec;
        $rec->user_id = $user->id;
        $rec->txn_entry_id = $entry->id;

        // 開始時間
        $start_at = time();
        $position_start_at = date("Y-m-d H:i:s", $start_at);

        // 購買虛擬幣
        $ks = $user->keysecret()->toArray();
        $api = new BinanceApiManager(data_get($ks, 'key', ''), data_get($ks, 'secret', ''));
        $symbol = $user->txnSetting->TxnSymbolType;
        // list($err, $order, $stop_order) = $api->doIsolateEntry($symbol, $signal->txn_direct_type, $entry->quantity, $entry->price, $entry->stop_price, $entry->sell_price);
        list($err, $order, $stop_order) = $api->doIsolateEntry($symbol, $signal->txn_direct_type, 0.007090, 50700.76, 51000.00000, 51200.10000);

        // 結束時間
        $done_at = time();
        $position_done_at = date("Y-m-d H:i:s", $done_at);
        $position_duration = $done_at - $start_at;

        $rec->F29 = $position_start_at;
        $rec->F30 = $position_done_at;
        $rec->F31 = $position_duration;

        $fetch_keys = ["symbol", "orderId", "clientOrderId", "transactTime", "price", "origQty", "executedQty", "cummulativeQuoteQty", "status", "timeInForce", "type", "side", "marginBuyBorrowAsset", "marginBuyBorrowAmount", "isIsolated"];

        if(!is_null($order)) {
            $txnOrder = TxnMarginOrder::create(array_merge([
                'user_id' => $user->id,
                'fills' => json_encode($order['fills'])
            ], Arr::only($order, $fetch_keys)));
            $rec->ord_id = $txnOrder->id;
        }

        if(!is_null($order)) {
            $txnStopOrder = TxnMarginOrder::create(array_merge([
                'user_id' => $user->id,
                'fills' => json_encode($stop_order['fills'])
            ], Arr::only($stop_order, $fetch_keys)));
            $rec->stop_ord_id = $txnStopOrder->id;
        }

        // $rec->calculate();
        $rec->save();

        if(!is_null($err))
            throw $err;

        // 變更用戶狀態
        $user->txnStatus->current_state = 1;
        $user->txnStatus->total_transaction_times++;
        if($signal->txn_direct_type->is(DirectType::LONG))
            $user->txnStatus->total_number_of_long_times++;
        else
            $user->txnStatus->total_number_of_short_times++;
        $user->txnStatus->save();

        return $rec;
    }

    // 計算其它數據
    public function calculate()
    {
        return;
    }
}
