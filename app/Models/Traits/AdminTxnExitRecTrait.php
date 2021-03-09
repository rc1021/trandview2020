<?php

namespace App\Models\Traits;

use App\Models\AdminUser;
use App\Models\AdminTxnSellRec;
use App\Models\AdminTxnBuyRec;
use App\Models\SignalHistory;
use App\Models\TxnMarginOrder;

trait AdminTxnExitRecTrait
{
    // 建立Exit訊號接收到時數據
    public static function createRec(AdminUser $user, SignalHistory $signal, AdminTxnBuyRec $buy)
    {
        $rec = new \App\Models\AdminTxnExitRec;
        $rec->user_id = $user->id;
        $rec->signal_history_id = $signal->id;
        $rec->txn_buy_id = $buy->id;

        $rec->H29 = date('Y-m-d H:i:s', $signal->position_at);

        $rec->save();

        // 建立實際賣出訊號
        AdminTxnSellRec::createRec($rec, $user, $buy->stopLossLimit);
    }
}
