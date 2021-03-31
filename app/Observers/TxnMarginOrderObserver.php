<?php

namespace App\Observers;

use App\Models\TxnMarginOrder;
use Exception;
use Illuminate\Support\Facades\Log;
use BinanceApi\Enums\OrderType;
use BinanceApi\Enums\DirectType;
use BinanceApi\Enums\SideType;
use BinanceApi\Enums\OrderStatusType;
use App\Enums\TxnExchangeType;
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
            $data = '';
            $order = $txnMarginOrder->toArray();
            if(array_key_exists('side', $order))
                $order['side'] = SideType::fromKey($order['side'])->description;
            if(array_key_exists('type', $order))
                $order['type'] = OrderType::fromKey($order['type'])->description;
            if(array_key_exists('status', $order))
                $order['status'] = OrderStatusType::fromKey($order['status'])->description;
            if(array_key_exists('marginBuyBorrowAmount', $order) and array_key_exists('marginBuyBorrowAsset', $order)) {
                $order['marginBuyBorrowAmount'] .= ' '.$order['marginBuyBorrowAsset'];
                unset($order['marginBuyBorrowAsset']);
            }
            // $order['transactTime'] = Carbon::parse($order['transactTime'])->setTimezone('Asia/Taipei')->format('Y-m-d H:i:s');
            if(array_key_exists('created_at', $order))
                $order['created_at'] = Carbon::parse($order['created_at'])->setTimezone('Asia/Taipei')->format('Y-m-d H:i:s');
            if(array_key_exists('transactTime', $order))
                unset($order['transactTime']);
            if(array_key_exists('clientOrderId', $order))
                unset($order['clientOrderId']);
            if(array_key_exists('id', $order))
                unset($order['id']);
            foreach ($order as $key => $value) {
                $data .= "\n" . __('admin.txn.order.'.$key) . ': ' . $value;
            }
            $data = sprintf('%s%s', DirectType::fromValue($txnMarginOrder->signal->txn_direct_type)->description, TxnExchangeType::fromValue($txnMarginOrder->signal->txn_exchange_type)->description) . $data;
            $txnMarginOrder->user->notify($data);
        }
        catch(Exception $e) {
            Log::warning($e->getMessage());
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
}
