<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use App\Models\SignalHistory;
use App\Models\SignalHistoryUser;
use App\Models\TxnMarginOrder;
use App\Enums\TradingPlatformType;
use App\Enums\TxnExchangeType;
use App\Enums\TxnSettingType;
use BinanceApi\BinanceApiManager;
use BinanceApi\Enums\OrderStatusType;
use BinanceApi\Enums\OrderType;
use BinanceApi\Enums\DirectType;
use Illuminate\Database\Eloquent\Builder;
use App\Admin\Extensions\Tools\MarginForceLiquidationTool;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContracts;
use Illuminate\Notifications\Notifiable;
use App\Notifications\LineNotify;

use Exception;

class AdminUser extends Administrator implements CanResetPasswordContract, MustVerifyEmailContracts
{
    use CanResetPassword, MustVerifyEmail, Notifiable;

    protected $fillable = ['email', 'password', 'name', 'avatar'];

    protected $appends = ['username'];

    public function lineNotify($message)
    {
        if(!empty($this->line_notify_token)) {
            $this->notify(new LineNotify($message));
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

    public function getUsernameAttribute($username)
    {
        return $this->email;
    }

    public function signals()
    {
        // return $this->belongsToMany(SignalHistory::class, 'signal_history_user')->withPivot('error', 'before_asset', 'after_asset');
        return $this->belongsToMany(SignalHistory::class, 'signal_history_user')->using(SignalHistoryUser::class)->withPivot('error', 'before_asset', 'after_asset');
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

    /**
     * 取得指定交易對是否正在場內?
     *
     * @param $symbol_key string 交易对
     * @return bool containing the response
     * @throws \Exception
     */
    public function IsTxnEntryStatus(string $symbol_key) : bool
    {
        $signal = $this->signals()->filterSymbol($symbol_key)->latest()->first();
        if(is_null($signal))
            return false;
        return $signal->txn_exchange_type->is(TxnExchangeType::Entry);
    }

    /**
     * 取得指定交易對最後一筆進場訊號
     *
     * @param $symbol_key string 交易对
     * @return SignalHistory containing the response
     * @throws \Exception
     */
    public function latestTxnEntrySignal(string $symbol_key = null) : SignalHistory
    {
        if(is_null($symbol_key))
            return $this->signals()->filterEntry()->latest()->first();
        return $this->signals()->filterEntry()->filterSymbol($symbol_key)->latest()->first();
    }

    /**
     * 取得 Binacne Api Manager
     *
     * @return BinanceApiManager containing the response
     * @throws \Exception
     */
    public function getBinanceApiAttribute() : BinanceApiManager
    {
        $ks = $this->keysecrets()->first();
        $key = data_get($ks, 'key', null);
        $secret = data_get($ks, 'secret', null);
        if(is_null($key) || is_null($secret))
            throw new Exception('請先設定金鑰 <a href="'.route('txn.keysecret').'">前往設定</a>');
        return new BinanceApiManager($key, $secret);
    }

    /**
     * 取得 進場中的交易列表
     *
     * @return array containing the response
     * @throws \Exception
     */
    public function getCurrentMarginTxnsAttribute() : array
    {
        $api = $this->binance_api;
        return $this->txnSettings()->filterType(TxnSettingType::Margin)->get()->map(function ($item, $key) use ($api) {
            $is_entry = $this->IsTxnEntryStatus($item->pair);
            $icon = $is_entry ? '<i class="fa fa-check text-green"></i>' : '<i class="fa fa-close text-red"></i>';
            if($is_entry) {
                try {
                    $signal = $this->latestTxnEntrySignal($item->pair);
                    $free = data_get($signal, 'pivot.after_asset.quoteAsset.free', 0);
                    $txn = $signal->txnMargOrders()
                        ->filterStatus(OrderStatusType::fromValue(OrderStatusType::FILLED)->key)
                        ->filterType(OrderType::fromValue(OrderType::LIMIT)->key)->first();
                    $avg_price = ceil_dec(collect(data_get($txn, 'fills', []))->avg('price'), 2);
                    $current_price = $api->floor_dec($api->marginPrice($item->pair), 2);
                    $account = $api->marginIsolatedAccountByKey($item->pair);
                    $quoteQty = $txn->executedQty * $current_price;
                    $quoteInterest = data_get($account, 'assets.'.$item->pair.'.baseAsset.interest', 0) * $current_price; // 利息
                    $gap = $quoteQty - $txn->cummulativeQuoteQty - $quoteInterest;
                    if($signal->txn_direct_type->is(DirectType::SHORT))
                        $gap = $txn->cummulativeQuoteQty - $quoteQty - $quoteInterest;
                    $gap_rate = $api->floor_dec($gap / $free * 100, 3);
                    $btn = MarginForceLiquidationTool::NewInstance($item->pair);
                    return [
                        $item->pair,
                        $icon,
                        $api->floor_dec($free, 2),
                        $avg_price,
                        $current_price,
                        $api->floor_dec($gap, 2) . ' | '. $gap_rate . '%',
                        $btn->render(),
                    ];
                }
                catch(Exception $e) {
                    return [
                        $item->pair,
                        $icon,
                        [
                            'col' => 5,
                            'content' => '計算發生錯誤: ' . $e->getMessage()
                        ]
                    ];
                }
            }
            return [$item->pair, $icon, [
                'col' => 5,
                'content' => '目前未交易'
            ]];
        })->all();
    }
}
