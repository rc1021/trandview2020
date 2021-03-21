<?php

namespace App\Models\Traits;

use Illuminate\Support\Arr;
use App\Enums\TradingPlatformType;
use BinanceApi\Enums\SymbolType;
use BinanceApi\Enums\DirectType;
use App\Enums\TxnExchangeType;
use App\Jobs\ProcessSignal;

trait SignalHistoryTrait
{
    // 建立一筆訊號，並執行訊息內容
    public static function parseAndPlay($clock, $message)
    {
        // 記錄訊息
        $rec = new \App\Models\SignalHistory;
        $rec->clock = $clock;
        $rec->message = $message;
        $rec->save();

        ProcessSignal::dispatchSync($rec);
    }

    // 0	=>	交易執行類別: 交易方向
    public function getTxnDirectTypeAttribute() : DirectType
    {
        switch($this->getSignal('交易執行類別'))
        {
            case "Short Exit":
            case "Short Entry":
                return DirectType::fromValue(DirectType::SHORT);
                break;
            case "Long Exit":
            case "Long Entry":
                return DirectType::fromValue(DirectType::LONG);
                break;
        }
    }

    // 0	=>	交易執行類別: 買入/賣出
    public function getTxnExchangeTypeAttribute()
    {
        switch($this->getSignal('交易執行類別'))
        {
            case "Long Exit":
            case "Short Exit":
                return TxnExchangeType::fromValue(TxnExchangeType::Exit);
                break;
            case "Short Entry":
            case "Long Entry":
                return TxnExchangeType::fromValue(TxnExchangeType::Entry);
                break;
        }
    }

    // 1	=>	交易所
    public function getTradingPlatformTypeAttribute()
    {
        return TradingPlatformType::coerce($this->getSignal('交易所'));
    }

    // 2	=>	交易配對
    public function getSymbolTypeAttribute()
    {
        return SymbolType::coerce($this->getSignal('交易配對'));
    }

    // 3	=>	執行日期時間
    public function getPositionAtAttribute()
    {
        return strtotime($this->getSignal('執行日期時間'));
    }

    // 4	=>	現價
    public function getCurrentPriceAttribute()
    {
        return floatval($this->getSignal('現價'));
    }

    // 5	=>	交易執行價格
    public function getEntryPriceAttribute()
    {
        return floatval($this->getSignal('交易執行價格'));
    }

    // 6	=>	做多起始風險價位
    // 7	=>	做空起始風險價位
    public function getRiskStartPriceAttribute()
    {
        $type = DirectType::fromValue($this->getTxnDirectTypeAttribute());
        if($type->is(DirectType::LONG)) {
            return floatval($this->getSignal('做多起始風險價位'));
        }
        elseif ($type->is(DirectType::SHORT)) {
            return floatval($this->getSignal('做空起始風險價位'));
        }
        return null;
    }

    // 8	=>	開倉價格容差(最高價位)
    public function getHightPositionPriceAttribute()
    {
        return floatval($this->getSignal('開倉價格容差(最高價位)'));
    }

    // 9	=>	開倉價格容差(最低價位)
    public function getLowPositionPriceAttribute()
    {
        return floatval($this->getSignal('開倉價格容差(最低價位)'));
    }

    // 開倉價格
    public function getPositionPriceAttribute()
    {
        switch($this->getSignal('交易執行類別'))
        {
            case "Short Entry":
                return floatval($this->getSignal('開倉價格容差(最低價位)'));
                break;
            case "Long Entry":
                return floatval($this->getSignal('開倉價格容差(最高價位)'));
                break;
        }
    }
}
