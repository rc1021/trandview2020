<?php

namespace App\Models;

use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Auth\Database\Administrator;
use App\Models\SignalHistory;
use App\Models\TxnMarginOrder;
use App\Jobs\LineNotify;
use App\Enums\TradingPlatformType;
use Illuminate\Database\Eloquent\Builder;

class AdminUser extends Administrator
{
    public function notify($message)
    {
        if(!empty($this->line_notify_token)) {
            LineNotify::dispatch($this->line_notify_token, $message);
        }
    }

    /**
     * 取得有設定指定交易設置的用戶
     *
     * @param $query Builder content
     * @param $type TradingPlatformType 訊號型別：feature(合約)、margin(槓桿)
     * @param $pair string 交易對
     * @return Builder containing the response
     * @throws \Exception
     */
    public function scopeMatchTypePair($query, TradingPlatformType $type, $pair)
    {
        return $query->whereHas('keysecrets', function (Builder $query) use ($type) {
            $query->where('type', $type);
        })->whereHas('txnSettings', function (Builder $query) use ($pair) {
            $query->where('pair', $pair);
        });
    }

    public function signals()
    {
        return $this->belongsToMany(SignalHistory::class, 'signal_history_user')->withPivot('error', 'asset');;
    }

    public function orders()
    {
        return $this->hasOne(TxnMarginOrder::class, 'user_id');
    }

    public function txnSettings()
    {
        return $this->hasMany(AdminTxnSetting::class, 'user_id');
    }

    public function txnFeatSettings()
    {
        return $this->hasMany(AdminTxnFeatSetting::class, 'user_id');
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
