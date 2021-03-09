<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\AdminTxnSellRecTrait;

class AdminTxnSellRec extends Model
{
    use HasFactory, AdminTxnSellRecTrait;

    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }

    public function txnExitRec()
    {
        return $this->belongsTo(AdminTxnExitRec::class, 'txn_exit_id');
    }

    public function stopLossLimit()
    {
        return $this->belongsTo(TxnMarginOrder::class, 'stop_ord_id')->withTrashed();
    }

    public function market()
    {
        return $this->belongsTo(TxnMarginOrder::class, 'ord_id');
    }
}
