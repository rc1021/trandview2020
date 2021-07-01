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
            $status = OrderStatusType::fromKey($txnMarginOrder->status);
            if(in_array($status->value, $txnMarginOrder->user->setting_notify_order)) {
                $direct = DirectType::fromValue($txnMarginOrder->signal->txn_direct_type);
                $exchange = TxnExchangeType::fromValue($txnMarginOrder->signal->txn_exchange_type);
                $data = sprintf('%s%s', $direct->description, $exchange->description) . self::GetMessage($txnMarginOrder->toArray());
                $txnMarginOrder->user->lineNotify($data);
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
        unset($order['fills']);
        unset($order['id']);
        $data = '';
        foreach ($order as $key => $value) {
            $data .= "\n" . __('txn.order.'.$key) . ': ' . $value;
        }
        return $data;
    }
}
