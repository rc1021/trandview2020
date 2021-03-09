<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use BinanceApi\Enums\SymbolType;
use BinanceApi\BinanceApiManager;

class AdminTxnSetting extends Model
{
    use HasFactory;

    private $_transaction_fees;

    protected $fillable = [
        'initial_tradable_total_funds',
        'transaction_matching',
        'initial_capital_risk',
        'lever_switch',
        'transaction_fees',
        'prededuct_handling_fee',
    ];

    function getTxnSymbolTypeAttribute() : SymbolType
    {
        return SymbolType::coerce((int)$this->attributes['transaction_matching']);
    }

    function getTransactionFeesAttribute()
    {
        if(is_null($this->_transaction_fees)) {
            $this->_transaction_fees = 0.001;
            $symbol = $this->TxnSymbolType;
            $trade_fee = app()->makeWith(BinanceApiManager::class, $this->user->keysecret()->toArray())->tradeFee($symbol->key);
            if(data_get($trade_fee, 'success', false))
                $this->_transaction_fees = data_get($trade_fee, 'tradeFee.0.taker', $this->_trade_fee);
        }
        return $this->_transaction_fees;
    }

    // 初始資金風險
    function getInitialCapitalRisk () {
        return $this->initial_tradable_total_funds * $this->capital_risk_per_transaction / 100;
    }

    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }
}
