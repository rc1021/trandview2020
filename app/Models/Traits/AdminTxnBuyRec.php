<?php

namespace App\Models\Traits;

use BinanceApi\Enums\SymbolType;
use App\Enums\TxnDirectType;
use Illuminate\Support\Arr;
use BinanceApi\Models\BinanceApi;
use Exception;
use App\Models\AdminUser;
use App\Models\AdminTxnEntryRec;

trait AdminTxnBuyRec
{
    // 建立實際開倉紀錄
    public static function createRec(AdminTxnEntryRec $entry, AdminUser $user)
    {
        $rec = new \App\Models\AdminTxnBuyRec;
        $rec->user_id = $user->id;
        $rec->txn_entry_id = $entry->id;

        // 購買虛擬幣
        $symbol = SymbolType::coerce((int)$user->transactionSetting->transaction_matching);

        // 建立連線
        $api = app()->makeWith(BinanceApi::class, $user->keysecret()->toArray());

        // 開始時間
        $start_at = time();
        $position_start_at = date("Y-m-d H:i:s", $start_at);
        // 購買
        $order = $api->buy($symbol->key, $entry->quantity, $entry->price);
        // 結束時間
        $done_at = time();
        $position_done_at = date("Y-m-d H:i:s", $done_at);
        $position_duration = $done_at - $start_at;

        $rec->F29 = $position_start_at;
        $rec->F30 = $position_done_at;
        $rec->F31 = $position_duration;
        $rec->save();

        if(is_null($order) or count($order['fills']) == 0) {
            $api->cancel($order['symbol'], $order['orderId']);
            throw new Exception('未立即完成訂單(撤單)');
        }

        $rec->calculate();
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

    }
}
