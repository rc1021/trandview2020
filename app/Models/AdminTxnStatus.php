<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AdminTxnStatus extends Model
{
    use HasFactory;

    // 當前總資金
    public $currentTotalFunds = 0;

    // == 資料表欄位 ==
    // 當前狀態
    // 初始總資金
    // 交易程式狀態
    // 總交易次數
    // 總做空次數
    // 總做多次數
    // 使用槓桿率(%)
    // 期間平均資金風險(%)
    // 總獲利次數
    // 總損失次數
    // 總獲利
    // 總損失
    // 總損益

    // == 計算 ==
    // 損益(%)
    function getPL() {
        return ($this->currentTotalFunds -  $this->initialTotalCapital) / Arr::get($this, 'initialTotalCapital', 1);
    }
    // 獲利因子(PF)
    function getPF() {
        return $this->totalProfit / Arr::get($this, 'totalLoss', 1);
    }
    // 報酬期望值(RM )
    function getRM() {
        return "?";
    }
    // 勝率(WR )
    function getWR() {
        return $this->totalProfitTimes / Arr::get($this, 'totalTransactionTimes', 1);
    }
    // 最優化風險建議(Kelly formula)
    function getKellyFormula() {
        $wr = $this->getWR();
        $rm = $this->getRM();
        if($rm == 0)
            $rm = 1;
        return ($wr * 100) - ((1 - $wr) * 100) / $rm;
    }
}
