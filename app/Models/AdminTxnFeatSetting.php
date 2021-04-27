<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use BinanceApi\Enums\SymbolType;
use BinanceApi\BinanceApiManager;

class AdminTxnFeatSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'initial_tradable_total_funds',
        'initial_capital_risk',
        'lever',
    ];

    // 初始資金風險
    function getInitialCapitalRisk () {
        return $this->initial_tradable_total_funds * $this->capital_risk_per_transaction / 100;
    }

    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }
}
