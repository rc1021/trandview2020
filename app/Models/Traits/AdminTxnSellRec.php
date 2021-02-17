<?php

namespace App\Models\Traits;


trait AdminTxnSellRec
{
    // 建立實際平倉紀錄
    public static function addRec($orders, $txn_exit_id, $liquidation_start_at, $liquidation_done_at, $liquidation_duration, $user_id)
    {
        $rec = new \App\Models\AdminTxnSellRec;
        $rec->user_id = $user_id;
        $rec->txn_exit_id = $txn_exit_id;
        $rec->response = json_encode($orders);
        $rec->liquidation_start_at = $liquidation_start_at; // 平倉交易起始日期時間
        $rec->liquidation_done_at = $liquidation_done_at; // 平倉交易結束日期時間
        $rec->liquidation_duration = $liquidation_duration; // 平倉交易持續時間

        $collection = collect($orders);
        $count_tranding = $collection->count(); // 購買次數
        $rec->liquidation_price_avg =  $collection->sum('fills.0.price') / $count_tranding;
        $rec->transaction_fee =  $collection->sum('fills.0.commission');
        $rec->gain_funds =  $collection->sum('cummulativeQuoteQty');

        $rec->calculateProfitAndLoss();
        $rec->calculateProfitAndLossRate();
        $rec->calculateRValue();
        $rec->calculateSellTotalFunds();
        $rec->save();
        return $rec;
    }
    // 計算: 損益
    public function calculateProfitAndLoss()
    {
        $txnEntryRec = $this->txnExitRec->txnEntryRec->toArray();
        $this->profit_and_loss = $this->attributes['gain_funds'] - $txnEntryRec['position_price'];
    }

    // 計算: 損益率(%)
    public function calculateProfitAndLossRate()
    {
        $txnEntryRec = $this->txnExitRec->txnEntryRec->toArray();
        $this->profit_and_loss_rate = $this->attributes['profit_and_loss'] / $txnEntryRec['position_price'];
    }

    // 計算: R值
    public function calculateRValue()
    {
        $txnBuyRec = $this->txnExitRec->txnEntryRec->txnBuyRec->toArray();
        if($this->attributes['liquidation_price_avg'] > 0)
        {
            $this->r_value = $this->attributes['profit_and_loss'] / $txnBuyRec['funds_risk'];
        }
    }

    // 計算: 交易後可交易總資金
    public function calculateSellTotalFunds()
    {
        $txnEntryRec = $this->txnExitRec->txnEntryRec->toArray();
        $this->sell_total_funds = $txnEntryRec['avaiable_total_funds'] + $this->attributes['profit_and_loss'];
    }
}
