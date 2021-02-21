<?php

namespace App\Models\Traits;

use BinanceApi\Enums\SymbolType;
use App\Enums\TxnDirectType;
use Illuminate\Support\Arr;

trait AdminTxnEntryRec
{
    // 建立Entry訊號接收到時數據
    public static function addRec($transaction_matching, $position_at, $avaiable_total_funds, $funds_risk, $tranding_long_short, $transaction_fee, $risk_start_price, $hight_position_price, $low_position_price, $entry_price, $leverage, $prededuct_handling_fee, $user_id, $signal_history_id)
    {
        $rec = new \App\Models\AdminTxnEntryRec;
        $rec->user_id = $user_id;
        $rec->signal_history_id = $signal_history_id;
        $rec->transaction_matching = $transaction_matching; // 交易配對
        $rec->position_at = $position_at; // 應開倉日期時間
        $rec->avaiable_total_funds = $avaiable_total_funds; // 交易前可交易總資金
        $rec->funds_risk = $funds_risk; // 資金風險(%)
        $rec->tranding_long_short = $tranding_long_short; // 交易方向(多/空)
        $rec->transaction_fee = $transaction_fee; // 交易手續費
        $rec->risk_start_price = $risk_start_price; // 起始風險價位(止損價位)
        $rec->hight_position_price = $hight_position_price; // 開倉價格容差(最高價位)
        $rec->low_position_price = $low_position_price; // 開倉價格容差(最低價位)
        $rec->entry_price = $entry_price; // Entry訊號價位(當時的價位)
        $rec->leverage = $leverage; // 槓桿使用
        $rec->prededuct_handling_fee = $prededuct_handling_fee; // 預先扣除手續費
        $rec->calculateFundsRiskAmount();
        $rec->calculateRiskStart();
        $rec->calculatePositionPrice();
        $rec->calculatePositionFewAmount();
        $rec->calculateRiskStart();
        $rec->calculateLeveragePositionPrice();
        $rec->calculateTrandingFeeAmount();
        $rec->save();
        return $rec;
    }

    // 計算: 資金風險金額
    public function calculateFundsRiskAmount()
    {
        $this->funds_risk_amount = $this->attributes['avaiable_total_funds'] * $this->attributes['funds_risk'];
    }

    // 計算: 起始風險%(1R)
    public function calculateRiskStart()
    {
        $type = TxnDirectType::coerce(Arr::get($this->attributes, 'tranding_long_short', 0));
        if($this->attributes['risk_start_price'] > 0 && !is_null($type))
        {
            $this->risk_start = ($type->is(TxnDirectType::LONG))
                ? ($this->attributes['entry_price'] - $this->attributes['risk_start_price']) / $this->attributes['entry_price']
                : ($this->attributes['risk_start_price'] - $this->attributes['entry_price']) / $this->attributes['entry_price'];
        }
    }

    // 計算: 應開倉部位大小(未加上槓桿量)
    public function calculatePositionPrice()
    {
        if($this->attributes['risk_start_price'] > 0)
        {
            $this->position_price = ($this->attributes['risk_start'] >= $this->attributes['funds_risk'])
                ? $this->attributes['funds_risk_amount'] / $this->attributes['risk_start']
                : $this->attributes['avaiable_total_funds'];
            $this->calculateLeveragePower();
        }
    }

    // 計算: 應使用槓桿(倍數)
    public function calculateLeveragePower()
    {
        if($this->attributes['risk_start_price'] > 0)
        {
            $this->leverage_power = ($this->attributes['risk_start'] >= $this->attributes['funds_risk'])
                ? 1
                : $this->attributes['funds_risk_amount'] / $this->attributes['risk_start'] / $this->attributes['avaiable_total_funds'];
            $this->calculateLeveragePrice();
        }
    }

    // 計算: 應使用槓桿(金額)
    public function calculateLeveragePrice()
    {
        if($this->attributes['risk_start_price'] > 0)
        {
            $this->leverage_price = 0;
            if ($this->attributes['risk_start'] < $this->attributes['funds_risk'])
                $this->leverage_price = $this->attributes['funds_risk_amount'] / $this->attributes['risk_start'] - $this->attributes['avaiable_total_funds'];
            $this->calculateLeveragePositionPrice();
        }
    }

    // 計算: 應開倉部位大小(加上槓桿量)
    public function calculateLeveragePositionPrice()
    {
        if($this->attributes['leverage'] == 1)
        {
            $this->leverage_position_price = ($this->attributes['risk_start'] < $this->attributes['funds_risk'])
                ? $this->attributes['position_price'] + $this->attributes['leverage_price']
                : $this->attributes['position_price'];
        }
        else
            $this->leverage_position_price = $this->attributes['position_price'];
        $this->calculatePositionFew();
    }

    // 計算: 應開倉(幾口)
    public function calculatePositionFew()
    {
        if($this->attributes['risk_start_price'] > 0 )
            $this->position_few = $this->attributes['leverage_position_price'] / $this->attributes['entry_price'];
        $this->calculatePositionFewAmount();
    }

    // 計算: 應開倉(預先扣除手續費)
    public function calculatePositionFewAmount()
    {
        $this->position_few_amount = $this->attributes['position_few'] / ( 1 + $this->attributes['transaction_fee'] );
        $this->calculateTrandingFeeAmount();
        $this->calculatePositionPriceWithFee();
    }

    // 計算: 交易手續費
    public function calculateTrandingFeeAmount()
    {
        $this->tranding_fee_amount = ($this->attributes['prededuct_handling_fee'] == 1)
            ? $this->attributes['position_few'] - $this->attributes['position_few_amount']
            : $this->attributes['position_few'] * $this->attributes['transaction_fee'];
    }

    // 計算: 應開倉部位大小(預先扣除手續費)
    public function calculatePositionPriceWithFee()
    {
        if($this->attributes['prededuct_handling_fee'] == 1)
            $this->position_price_with_fee = $this->attributes['position_few_amount'] * $this->attributes['entry_price'];
        $this->calculateLeveragePriceWithFee();
        $this->calculateLeveragePowerWithFee();
    }

    // 計算: 應開倉(預先扣除手續費)
    public function calculateLeveragePriceWithFee()
    {
        $this->leverage_price_with_fee = $this->attributes['position_price_with_fee'] - $this->attributes['position_price'];
    }

    // 計算: 應開倉(預先扣除手續費)
    public function calculateLeveragePowerWithFee()
    {
        $this->leverage_power_with_fee = $this->attributes['position_price_with_fee'] / $this->attributes['position_price'];
    }
}
