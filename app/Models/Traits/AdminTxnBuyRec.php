<?php

namespace App\Models\Traits;

use BinanceApi\Enums\SymbolType;
use App\Enums\TxnDirectType;
use Illuminate\Support\Arr;
use BinanceApi\BinanceApiManager;
use Exception;
use App\Models\AdminUser;
use App\Models\AdminTxnEntryRec;
use App\Models\TxnMarginOrder;

trait AdminTxnBuyRec
{
    // 建立實際開倉紀錄
    public static function createRec(AdminTxnEntryRec $entry, AdminUser $user)
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
        $symbol = SymbolType::coerce((int)$user->transactionSetting->transaction_matching);
        // list($err, $order, $stop_order) = $api->doLongEntry($symbol, $entry->quantity, $entry->price, $entry->stop_price, $entry->sell_price);
        var_dump($entry->B39);
        list($err, $order, $stop_order) = $api->doLongEntry($symbol, 0.006575, $entry->B39, 47900.00000, 47852.10000);

        // 結束時間
        $done_at = time();
        $position_done_at = date("Y-m-d H:i:s", $done_at);
        $position_duration = $done_at - $start_at;

        $rec->F29 = $position_start_at;
        $rec->F30 = $position_done_at;
        $rec->F31 = $position_duration;

        if(!is_null($err))
            throw $err;

        $txnOrder = TxnMarginOrder::create([
            'user_id' => $user->id,
            'symbol' => $symbol->value,
            'orderId' => $order['orderId'],
            'type' => $order['type'],
            'result' => json_encode($order)
        ]);

        $txnStopOrder = TxnMarginOrder::create([
            'user_id' => $user->id,
            'symbol' => $symbol->value,
            'orderId' => $stop_order['orderId'],
            'type' => $stop_order['type'],
            'result' => json_encode($stop_order)
        ]);

        var_dump([$txnOrder->id, $txnStopOrder->id]);

        $rec->ord_id = $txnOrder->id;
        $rec->stop_ord_id = $txnStopOrder->id;

        // $rec->calculate();
        $rec->save();

        // 變更用戶狀態
        $user->transactionStatus->current_state = 1;
        $user->transactionStatus->total_transaction_times++;
        $txnDirectType = TxnDirectType::fromValue($entry->signal->txn_direct_type);
        if($txnDirectType->is(TxnDirectType::LONG))
            $user->transactionStatus->total_number_of_long_times++;
        else
            $user->transactionStatus->total_number_of_short_times++;
        $user->transactionStatus->save();

        return $rec;
    }

    // 計算其它數據
    public function calculate()
    {
        return;
    }
}
