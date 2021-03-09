<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\AdminTxnExitRecTrait;
use App\Models\AdminTxnEntryRec;

class AdminTxnExitRec extends Model
{
    use HasFactory, AdminTxnExitRecTrait;

    protected $dates = [
        'liquidation_at',
    ];

    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }

    // tranding view 傳入的訊號
    public function signal()
    {
        return $this->belongsTo(SignalHistory::class, 'signal_history_id');
    }

    public function txnBuyRec()
    {
        return $this->belongsTo(AdminTxnBuyRec::class, 'txn_buy_id');
    }
}
