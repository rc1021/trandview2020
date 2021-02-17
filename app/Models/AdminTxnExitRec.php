<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\AdminTxnExitRec as AdminTxnExitRecTrait;
use App\Models\AdminTxnEntryRec;

class AdminTxnExitRec extends Model
{
    use HasFactory, AdminTxnExitRecTrait;

    protected $dates = [
        'liquidation_at',
    ];

    // tranding view 傳入的訊號
    public function SignalHistory()
    {
        return $this->belongsTo(SignalHistory::class, 'signal_history_id');
    }

    public function txnEntryRec()
    {
        return $this->belongsTo(AdminTxnEntryRec::class, 'txn_entry_id');
    }
}
