<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\AdminTxnBuyRec as AdminTxnBuyRecTrait;
use App\Models\AdminTxnEntryRec;

class AdminTxnBuyRec extends Model
{
    use HasFactory, AdminTxnBuyRecTrait;

    protected $dates = [
        'position_start_at',
        'position_done_at',
    ];

    // user
    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }


    public function txnEntryRec()
    {
        return $this->belongsTo(AdminTxnEntryRec::class, 'txn_entry_id');
    }
}
