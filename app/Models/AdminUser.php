<?php

namespace App\Models;

use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Auth\Database\Administrator;
use App\Models\SignalHistory;
// use App\Models\AdminTxnSetting;
// use App\Models\AdminTxnStatus;
// use App\Models\AdminTxnEntryRec;
// use App\Models\AdminTxnBuyRec;
// use App\Models\AdminTxnExitRec;
// use App\Models\AdminTxnSellRec;
// use App\Models\TxnMarginOrder;

class AdminUser extends Administrator
{
    public function signals()
    {
        return $this->belongsToMany(SignalHistory::class, 'signal_history_user');
    }

    public function txnSetting()
    {
        return $this->hasOne(AdminTxnSetting::class, 'user_id');
    }

    public function txnStatus()
    {
        return $this->hasOne(AdminTxnStatus::class, 'user_id');
    }

    public function keysecrets()
    {
        return $this->hasMany(KeySecret::class, 'user_id');
    }

    public function keysecret()
    {
        return $this->keysecrets()->first();
    }
}
