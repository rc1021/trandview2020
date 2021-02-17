<?php

namespace App\Models\Traits;


trait AdminTxnExitRec
{
    // 建立Exit訊號接收到時數據
    public static function addRec($txn_entry_id, $signal_history_id, $liquidation_at, $liquidation_price, $user_id)
    {
        $rec = new \App\Models\AdminTxnExitRec;
        $rec->user_id = $user_id;
        $rec->txn_entry_id = $txn_entry_id;
        $rec->signal_history_id = $signal_history_id;
        $rec->liquidation_at = $liquidation_at; // 應平倉日期時間
        $rec->liquidation_price = $liquidation_price; // Exit訊號價位(當時的價位)
        $rec->save();
        return $rec;
    }
}
