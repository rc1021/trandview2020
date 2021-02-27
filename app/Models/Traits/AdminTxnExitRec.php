<?php

namespace App\Models\Traits;


trait AdminTxnExitRec
{
    // 建立Exit訊號接收到時數據
    public static function addRec($txn_entry_id, $signal_history_id, $user_id)
    {
        $rec = new \App\Models\AdminTxnExitRec;
        $rec->user_id = $user_id;
        $rec->txn_entry_id = $txn_entry_id;
        $rec->signal_history_id = $signal_history_id;

        $rec->save();
        return $rec;
    }
}
