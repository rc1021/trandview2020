<?php

namespace App\Models\Traits;

use App\Models\AdminUser;
use App\Models\SignalHistory;

trait AdminTxnExitRec
{
    // 建立Exit訊號接收到時數據
    public static function createRec(AdminUser $user, SignalHistory $signal)
    {
        $rec = new \App\Models\AdminTxnExitRec;
        $rec->user_id = $user->id;
        $rec->signal_history_id = $signal->id;

        $rec->H29 = date('Y-m-d H:i:s', $signal->position_at);

        $rec->save();
        return $rec;
    }
}
