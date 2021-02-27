<?php

namespace App\Models\Traits;


trait AdminTxnSellRec
{
    // 建立實際平倉紀錄
    public static function addRec($txn_exit_id, $user_id)
    {
        $rec = new \App\Models\AdminTxnSellRec;
        $rec->user_id = $user_id;
        $rec->txn_exit_id = $txn_exit_id;

        $rec->save();
        return $rec;
    }
}
