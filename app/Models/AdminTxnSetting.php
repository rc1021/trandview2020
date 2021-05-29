<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TxnSettingType;
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
        return data_get((object)$this->options, 'base_asset_daily_interest', 0.0003);
    }

    function getQuoteAssetDailyInterestAttribute() : float
    {
        return data_get((object)$this->options, 'quote_asset_daily_interest', 0.0015);
    }

    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }
}
