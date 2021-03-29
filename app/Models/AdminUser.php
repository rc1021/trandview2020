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
    public function notify($message)
    {
        if(!empty($this->line_notify_token)) {
            $apiUrl = "https://notify-api.line.me/api/notify";
            $params = [
                'message' => $message,
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->line_notify_token
            ]);
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            $output = curl_exec($ch);
            curl_close($ch);
        }
    }

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
