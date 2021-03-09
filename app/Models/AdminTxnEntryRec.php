<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\AdminTxnEntryRecTrait;

class AdminTxnEntryRec extends Model
{
    use HasFactory, AdminTxnEntryRecTrait;

    protected $dates = [
        'position_at',
    ];

    // user
    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }

    // tranding view 傳入的訊號
    public function signal()
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
        return $this->hasOneThrough(
            AdminTxnExitRec::class,
            AdminTxnBuyRec::class,
            'txn_entry_id',
            'txn_buy_id',
            'id',
            'id'
        );
    }

}
