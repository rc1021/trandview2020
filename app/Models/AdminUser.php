<?php

namespace App\Models;

use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Auth\Database\Administrator;
use App\Models\SignalHistory;
use App\Models\SignalHistoryUser;
use App\Models\TxnMarginOrder;
use App\Jobs\LineNotify;
use App\Enums\TradingPlatformType;
use App\Enums\TxnExchangeType;
use App\Enums\TxnSettingType;
use BinanceApi\BinanceApiManager;
use BinanceApi\Enums\OrderStatusType;
use BinanceApi\Enums\OrderType;
use BinanceApi\Enums\DirectType;
use Illuminate\Database\Eloquent\Builder;
use Exception;
use App\Admin\Extensions\Tools\MarginForceLiquidationTool;

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
        // return $this->belongsToMany(SignalHistory::class, 'signal_history_user')->withPivot('error', 'asset');
        return $this->belongsToMany(SignalHistory::class, 'signal_history_user')->using(SignalHistoryUser::class)->withPivot('error', 'asset');
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
            return null;
        return new BinanceApiManager($key, $secret);
    }

    /**
     * 取得 進場中的交易列表
     *
     * @return array containing the response
     * @throws \Exception
     */
    public function getCurrentMarginTxns() : array
    {
        $api = $this->binance_api;
        return $this->txnSettings()->filterType(TxnSettingType::Margin)->get()->map(function ($item, $key) use ($api) {
            $is_entry = $this->IsTxnEntryStatus($item->pair);
            $icon = $is_entry ? '<i class="fa fa-check text-green"></i>' : '<i class="fa fa-close text-red"></i>';
            if($is_entry) {
                try {
                    $signal = $this->latestTxnEntrySignal($item->pair);
                    $free = data_get($signal, 'pivot.asset.quoteAsset.free', 0);
                    $txn = $signal->txnMargOrders()
                        ->filterStatus(OrderStatusType::fromValue(OrderStatusType::FILLED)->key)
                        ->filterType(OrderType::fromValue(OrderType::LIMIT)->key)->first();
                    $current_price = $api->floor_dec($api->price($item->pair), 2);
                    $account = $api->marginIsolatedAccountByKey($item->pair);
                    $quoteQty = $txn->executedQty * $current_price;
                    $quoteInterest = data_get($account, 'assets.'.$item->pair.'.baseAsset.interest', 0) * $current_price; // 利息
                    $gap = $quoteQty - $txn->cummulativeQuoteQty - $quoteInterest;
                    if($signal->txn_direct_type->is(DirectType::SHORT))
                        $gap = $txn->cummulativeQuoteQty - $quoteQty - $quoteInterest;
                    $gap_rate = $api->floor_dec($gap / $free * 100, 2);
                    $btn = MarginForceLiquidationTool::NewInstance($item->pair);
                    return [
                        $item->pair,
                        $icon,
                        $api->floor_dec($free, 2),
                        $current_price,
                        $txn->price,
                        $api->floor_dec($gap, 2) . ' | '. $gap_rate . '%',
                        $btn->render(),
                    ];
                }
                catch(Exception $e) {
                    return [
                        $item->pair,
                        $icon,
                        '計算發生錯誤: ' . $e->getMessage()
                    ];
                }
            }
            return [$item->pair, $icon];
        })->all();
    }
}
