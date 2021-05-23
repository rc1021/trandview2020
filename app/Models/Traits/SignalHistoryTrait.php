<?php

namespace App\Models\Traits;

use Illuminate\Support\Arr;
use App\Enums\TradingPlatformType;
use App\Enums\TxnSettingType;
use BinanceApi\Enums\DirectType;
use App\Enums\TxnExchangeType;
use App\Jobs\ProcessSignal;
use App\Models\AdminUser;
use Exception;

trait SignalHistoryTrait
{
    /**
     * 建立一筆訊號，並執行訊息內容
     *
     * @param $message string content
     * @param $type string 訊號型別：feature(合約)、margin(槓桿)
     * @return array containing the response
     * @throws \Exception
     */
    public static function parseAndPlay($message, TxnSettingType $type)
    {
        // 記錄訊息
        $rec = new \App\Models\SignalHistory;
        $rec->clock = '1';
        $rec->type = $type->key;
        $rec->message = $message;
        $rec->save();

        try {
            AdminUser::matchTypePair($rec->trading_platform_type, $rec->symbol_type)->chunk(200, function ($users) use ($message, $type) {
                foreach ($users as $user)
                {
                    $user->notify(sprintf("%s訊號\n%s", $type->key, str_replace('=', ': ', str_replace(',', "\n", $message))));
                }
            });
        }
        catch(Exception $e) {}

        ProcessSignal::dispatchSync($rec);
    }

    // 0	=>	交易執行類別: 交易方向
    public function getTxnDirectTypeAttribute() : DirectType
    {
        switch($this->getSignal('交易執行類別'))
        {
            case "Force Exit":
                return DirectType::fromValue(DirectType::FORCE);
                break;
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
            case "Force Exit":
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
        return $this->getSignal('交易配對');
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

    public function setCurrentPriceAttribute($val = null)
    {
        return $this->setSignal('現價', floatval($val));
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
