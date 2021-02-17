<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\AdminTxnSellRec as AdminTxnSellRecTrait;

class AdminTxnSellRec extends Model
{
    use HasFactory, AdminTxnSellRecTrait;

    public function txnExitRec()
    {
        return $this->belongsTo(AdminTxnExitRec::class, 'txn_exit_id');
    }
}
