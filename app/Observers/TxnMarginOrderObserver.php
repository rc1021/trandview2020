<?php

namespace App\Observers;

use App\Models\TxnMarginOrder;
use App\Models\AdminUser;
use Exception;
use Illuminate\Support\Facades\Log;
use BinanceApi\Enums\OrderType;
use BinanceApi\Enums\DirectType;
use BinanceApi\Enums\SideType;
use BinanceApi\Enums\OrderStatusType;
use App\Enums\TxnExchangeType;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class TxnMarginOrderObserver
{
    /**
     * Handle the TxnMarginOrder "created" event.
     *
     * @param  \App\Models\TxnMarginOrder  $txnMarginOrder
     * @return void
     */
    public function created(TxnMarginOrder $txnMarginOrder)
    {
        try {
            $txnMarginOrder->withoutRelations();
            $data = self::GetMessage($txnMarginOrder->toArray());
            $data = sprintf('%s%s', DirectType::fromValue($txnMarginOrder->signal->txn_direct_type)->description, TxnExchangeType::fromValue($txnMarginOrder->signal->txn_exchange_type)->description) . $data;
            $txnMarginOrder->user->lineNotify($data);

            // 如果是市價單出場就結算這次的盈虧
            if(OrderType::fromKey($txnMarginOrder->type)->is(OrderType::MARKET)) {
                $msg = ['出場結算'];
                $symbol_key = $txnMarginOrder->symbol;
                // 取得進場時原始資產
                $lastSignal = $txnMarginOrder->user->latestTxnEntrySignal($symbol_key);
                if(is_null($lastSignal))
                    throw new Exception("No Latest EntrySignal: ". $symbol_key);
                array_push($msg, sprintf("交易對：%s", $lastSignal->symbol_type));
                $originQuoteAssetFree = data_get($lastSignal->pivot->asset, 'quoteAsset.free', 0);
                array_push($msg, sprintf("進場前原資金：%s", $originQuoteAssetFree));
                // 取得帳戶現有資產
                $currentAsset = $txnMarginOrder->user->binance_api->marginIsolatedAccountByKey($symbol_key);
                $currentQuoteAssetFree = data_get($currentAsset, 'assets.'.$symbol_key.'.quoteAsset.free', 0);
                array_push($msg, sprintf("目前帳戶資金：%s", $currentQuoteAssetFree));
                array_push($msg, sprintf("本次盈虧：%s", $currentQuoteAssetFree - $originQuoteAssetFree));
                // 通知盈虧
                $txnMarginOrder->user->lineNotify(implode("\n", $msg));
            }
        }
        catch(Exception $e) {
            Log::warning($e->getMessage(), $e->getTrace());
        }
    }

    /**
     * Handle the TxnMarginOrder "updated" event.
     *
     * @param  \App\Models\TxnMarginOrder  $txnMarginOrder
     * @return void
     */
    public function updated(TxnMarginOrder $txnMarginOrder)
    {
        //
    }

    /**
     * Handle the TxnMarginOrder "deleted" event.
     *
     * @param  \App\Models\TxnMarginOrder  $txnMarginOrder
     * @return void
     */
    public function deleted(TxnMarginOrder $txnMarginOrder)
    {
        //
    }

    /**
     * Handle the TxnMarginOrder "restored" event.
     *
     * @param  \App\Models\TxnMarginOrder  $txnMarginOrder
     * @return void
     */
    public function restored(TxnMarginOrder $txnMarginOrder)
    {
        //
    }

    /**
     * Handle the TxnMarginOrder "force deleted" event.
     *
     * @param  \App\Models\TxnMarginOrder  $txnMarginOrder
     * @return void
     */
    public function forceDeleted(TxnMarginOrder $txnMarginOrder)
    {
        //
    }

    public static function GetMessage(array $order)
    {
        if(array_key_exists('loan_ratio', $order))
            $order['loan_ratio'] = sprintf('%s%%', $order['loan_ratio'] * 100);
        $order['side'] = SideType::fromKey($order['side'])->description;
        $order['type'] = OrderType::fromKey($order['type'])->description;
        $order['status'] = OrderStatusType::fromKey($order['status'])->description;
        if(array_key_exists('marginBuyBorrowAmount', $order) and array_key_exists('marginBuyBorrowAsset', $order)) {
            $order['marginBuyBorrowAmount'] .= ' '.$order['marginBuyBorrowAsset'];
            unset($order['marginBuyBorrowAsset']);
        }
        // $order['transactTime'] = Carbon::parse($order['transactTime'])->setTimezone('Asia/Taipei')->format('Y-m-d H:i:s');
        if(array_key_exists('created_at', $order))
            $order['created_at'] = Carbon::parse($order['created_at'])->setTimezone('Asia/Taipei')->format('Y-m-d H:i:s');
        unset($order['transactTime']);
        unset($order['clientOrderId']);
        unset($order['isIsolated']);
        unset($order['id']);
        $data = '';
        foreach ($order as $key => $value) {
            $data .= "\n" . __('admin.txn.order.'.$key) . ': ' . $value;
        }
        return $data;
    }
}
