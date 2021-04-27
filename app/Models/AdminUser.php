<?php

namespace App\Models;

use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Auth\Database\Administrator;
use App\Models\SignalHistory;
use App\Models\TxnMarginOrder;
use App\Jobs\LineNotify;

class AdminUser extends Administrator
{
    public function notify($message)
    {
        if(!empty($this->line_notify_token)) {
            LineNotify::dispatch($this->line_notify_token, $message);
        }
    }

    public function signals()
    {
        return $this->belongsToMany(SignalHistory::class, 'signal_history_user');
    }

    public function orders()
    {
        return $this->hasOne(TxnMarginOrder::class, 'user_id');
    }

    public function txnSetting()
    {
        return $this->hasOne(AdminTxnSetting::class, 'user_id');
    }

    public function txnFeatSetting()
    {
        return $this->hasOne(AdminTxnFeatSetting::class, 'user_id');
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
