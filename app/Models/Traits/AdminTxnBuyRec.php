<?php

namespace App\Models\Traits;

use App\Enums\SymbolType;
use App\Enums\TxnDirectType;
use Illuminate\Support\Arr;

trait AdminTxnBuyRec
{
    // 建立實際開倉紀錄
    public static function addRec($orders, $txn_entry_id, $position_start_at, $position_done_at, $position_duration, $user_id)
    {
        $rec = new \App\Models\AdminTxnBuyRec;
        $rec->user_id = $user_id;
        $rec->txn_entry_id = $txn_entry_id;
        $rec->response = json_encode($orders);
        $rec->position_start_at = $position_start_at; // 開倉交易起始日期時間
        $rec->position_done_at = $position_done_at; // 開倉交易完成日期時間
        $rec->position_duration = $position_duration; // 開倉交易持續時間

        $collection = collect($orders);
        $count_tranding = $collection->count(); // 購買次數
        // position_price_avg: price
        $rec->position_price_avg = $collection->sum('fills.0.price') / $count_tranding;
        // position_quota: qty
        $rec->position_quota = $collection->sum('fills.0.qty');
        // transaction_fee: commission
        $rec->transaction_fee = $collection->sum('fills.0.commission');
        // position_price: cummulativeQuoteQty +  price * commission
        // cummulativeQuoteQty =  price * qty
        $rec->position_price = $collection->sum('cummulativeQuoteQty') + $collection->sum(function ($order) {
            return data_get($order, 'price', 0) + data_get($order, 'commission', 0);
        });

        $rec->calculateRiskStart();
        $rec->calculateLeveragePower();
        $rec->calculateLeveragePrice();
        $rec->calculateFundsRisk();
        $rec->save();
        return $rec;
    }

    // 計算: 實際起始風險%(1R)
    public function calculateRiskStart()
    {
        $txnEntryRec = $this->txnEntryRec->toArray();
        $type = TxnDirectType::coerce(Arr::get($txnEntryRec, 'tranding_long_short', 0));
        if($txnEntryRec['risk_start_price'] > 0 && !is_null($type))
        {
            $this->risk_start = ($type->is(TxnDirectType::LONG))
                ? ($this->attributes['position_price_avg'] - $txnEntryRec['risk_start_price']) / $this->attributes['position_price_avg']
                : ($txnEntryRec['risk_start_price'] - $this->attributes['position_price_avg']) / $this->attributes['position_price_avg'];
        }
    }

    // 計算: 實際資金風險(金額)
    public function calculateFundsRisk()
    {
        $txnEntryRec = $this->txnEntryRec->toArray();
        $this->funds_risk = $this->attributes['position_price'] * $this->attributes['risk_start'];
        $this->calculateFundsRiskLess();
        $this->calculateTargetRate();
    }

    // 計算: 剩餘資金風險(金額)
    public function calculateFundsRiskLess()
    {
        $txnEntryRec = $this->txnEntryRec->toArray();
        $this->funds_risk_less = $txnEntryRec['funds_risk_amount'] - $this->attributes['funds_risk'];
    }

    // 計算: 開倉目標達成率
    public function calculateTargetRate()
    {
        $txnEntryRec = $this->txnEntryRec->toArray();
        $this->target_rate = $this->attributes['funds_risk'] / $txnEntryRec['funds_risk_amount'];
    }

    // 計算: 實際使用槓桿(倍數)
    public function calculateLeveragePower()
    {
        $txnEntryRec = $this->txnEntryRec->toArray();
        $this->leverage_power = ($txnEntryRec['prededuct_handling_fee'] == 1)
            ? $txnEntryRec['leverage_power_with_fee']
            : $txnEntryRec['leverage_power'];
    }

    // 計算: 實際使用槓桿(金額)
    public function calculateLeveragePrice()
    {
        $txnEntryRec = $this->txnEntryRec->toArray();
        $this->leverage_price = ($txnEntryRec['prededuct_handling_fee'] == 1)
            ? $txnEntryRec['leverage_price_with_fee']
            : $txnEntryRec['leverage_price'];
    }
}
