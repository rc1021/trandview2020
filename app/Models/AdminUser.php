<?php

namespace App\Models;

use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Auth\Database\Administrator;
use App\Models\KeySecret;
use App\Models\AdminTxnSetting;
use App\Models\AdminTxnStatus;
use App\Models\AdminTxnEntryRec;
use App\Models\AdminTxnBuyRec;
use App\Models\AdminTxnExitRec;
use App\Models\AdminTxnSellRec;

class AdminUser extends Administrator
{
    // Entry訊號接收到時數據
    public function transactionEntryRecords()
    {
        return $this->hasMany(AdminTxnEntryRec::class, 'user_id');
    }

    // 實際開倉紀錄
    public function transactionBuyRecords()
    {
        return $this->hasMany(AdminTxnBuyRec::class, 'user_id');
    }

    // Exit訊號接收到時數據
    public function transactionExitRecords()
    {
        return $this->hasMany(AdminTxnExitRec::class, 'user_id');
    }

    // 實際平倉紀錄
    public function transactionSellRecords()
    {
        return $this->hasMany(AdminTxnSellRec::class, 'user_id');
    }

    public function transactionSetting()
    {
        return $this->hasOne(AdminTxnSetting::class, 'user_id');
    }

    public function transactionStatus()
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
