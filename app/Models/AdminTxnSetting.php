<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use BinanceApi\BinanceApiManager;

class AdminTxnSetting extends Model
{
    use HasFactory;

    protected $casts = [
        'options' => 'array',
    ];

    function scopeFilterType($query, $type)
    {
        return $query->where('type', $type);
    }

    function getInitialTradableTotalFundsAttribute() : float
    {
        return data_get((object)$this->options, 'initial_tradable_total_funds', 1.0);
    }

    function getInitialCapitalRiskAttribute() : float
    {
        return data_get((object)$this->options, 'initial_capital_risk', 0.07);
    }

    function getLeverSwitchAttribute() : bool
    {
        return data_get((object)$this->options, 'lever_switch', true);
    }

    function getBaseAssetDailyInterestAttribute() : float
    {
        try {
            $exchange = BinanceApiManager::ExchangeInfo();
            $base = data_get($exchange, "symbols.$this->pair.baseAsset");
            $viplist  = BinanceApiManager::MarginVipSpecList();
            $viplevel = $this->user->vip_level;
            return data_get($viplist, "data.$base.specs.$viplevel.dailyInterestRate", 0.0005);
        }
        catch(Exception $e) {
            return 0.0005;
        }
    }

    function getQuoteAssetDailyInterestAttribute() : float
    {
        try {
            $exchange = BinanceApiManager::ExchangeInfo();
            $quote = data_get($exchange, "symbols.$this->pair.quoteAsset");
            $viplist  = BinanceApiManager::MarginVipSpecList();
            $viplevel = $this->user->vip_level;
            return data_get($viplist, "data.$quote.specs.$viplevel.dailyInterestRate", 0.0009);
        }
        catch(Exception $e) {
            return 0.0009;
        }
    }

    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }
}
