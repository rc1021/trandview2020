<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminTxnSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'initial_tradable_total_funds',
        'transaction_matching',
        'initial_capital_risk',
        'lever_switch',
        'transaction_fees',
        'prededuct_handling_fee',
    ];

    // 初始資金風險
    function getInitialCapitalRisk () {
        return $this->initial_tradable_total_funds * $this->capital_risk_per_transaction / 100;
    }
}
