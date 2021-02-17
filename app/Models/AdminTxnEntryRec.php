<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\AdminTxnEntryRec as AdminTxnEntryRecTrait;
use App\Models\AdminTxnBuyRec;
use App\Models\AdminTxnExitRec;

class AdminTxnEntryRec extends Model
{
    use HasFactory, AdminTxnEntryRecTrait;

    protected $dates = [
        'position_at',
    ];

    // tranding view 傳入的訊號
    public function SignalHistory()
    {
        return $this->belongsTo(SignalHistory::class, 'signal_history_id');
    }

    // 實際買入數據
    public function txnBuyRec()
    {
        return $this->hasOne(AdminTxnBuyRec::class, 'txn_entry_id');
    }

    // Exit訊號接收到時數據
    public function txnExitRec()
    {
        return $this->hasOne(AdminTxnExitRec::class, 'txn_entry_id');
    }

    // 實際賣出
    public function txnSellRec()
    {
        return $this->hasOneThrough(AdminTxnSellRec::class, AdminTxnExitRec::class, 'txn_entry_id', 'txn_exit_id');
    }

}
