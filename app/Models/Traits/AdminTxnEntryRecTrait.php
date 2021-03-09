<?php

namespace App\Models\Traits;

use BinanceApi\Enums\SymbolType;
use App\Enums\TxnDirectType;
use Illuminate\Support\Arr;
use Exception;
use App\Models\AdminUser;
use App\Models\AdminTxnBuyRec;
use App\Models\SignalHistory;

trait AdminTxnEntryRecTrait
{
    // 建立Entry訊號接收到時數據
    public static function createRec(AdminUser $user, SignalHistory $signal)
    {
        $rec = new \App\Models\AdminTxnEntryRec;
        $rec->user_id = $user->id;
        $rec->signal_history_id = $signal->id;

        // 輸入資訊
        $rec->B30 = date('Y-m-d H:i:s', $signal->position_at);
        // $rec->B31 = $user->txnSetting->initial_tradable_total_funds;
        // $rec->B32 = $signal->txn_direct_type->value;
        // $rec->B33 = $user->txnSetting->initial_capital_risk;
        // $rec->B34 = $user->txnSetting->transaction_matching;
        // $rec->B35 = $user->txnSetting->lever_switch;
        // $rec->B36 = $user->txnSetting->prededuct_handling_fee;
        // $rec->B37 = $user->txnSetting->transaction_fees;
        // $rec->B38 = $signal->risk_start_price;
        $rec->B39 = $signal->hight_position_price;
        // $rec->B40 = $signal->low_position_price;
        // $rec->B41 = $signal->entry_price;

        // 檢查必要資料是否存在

        // 計算其它數據
        // $rec->calculate();
        $rec->save();

        // 建立實際購買訊號
        AdminTxnBuyRec::createRec($rec, $user, $signal);
    }

    public function getQuantityAttribute()
    {

    }

    public function getPriceAttribute()
    {

    }

    // 計算其它數據
    public function calculate()
    {
        // B31×B33
        $this->B43 = $this->B31 * $this->B33;

        // IF(ISBLANK(B38),"",IF(B32="多",(B41−B38)÷B41,(B38−B41)÷B41))
        if(!is_null($this->B38))
            $this->B44 = ($this->B32 == TxnDirectType::LONG) ? ($this->B41 - $this->B38) / $this->B41 : ($this->B38 - $this->B41) / $this->B41;

        // IF(B44≥B33,B43÷B44,B31)
        $this->B45 = ($this->B44 >= $this->B33) ? $this->B43 / $this->B44 : $this->B31;

        // IF(B44≥B33,0,(B43÷B44)−B45)
        $this->B46 = ($this->B44 >= $this->B33) ? 0 : $this->B43 / $this->B44 - $this->B45;

        // (未完成) IF(B46−($B$2−B31)>0,$B$2−B31,B46)
        $this->B47 = ($this->B46 - ($this->B31) > 0) ? $this->B31 : $this->B46;

        // IF(B35="是",B46−B47,0)
        $this->B48 = ($this->B35 == 1) ? $this->B46 - $this->B47 : 0;

        // B47+B45
        $this->B49 = $this->B47 + $this->B45;
    }
}
