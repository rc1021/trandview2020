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
            $txnMarginOrder->user->notify($data);

            // 如果是市價單出場就結算這次的輸贏
            if(OrderType::fromKey($txnMarginOrder->type)->is(OrderType::MARKET)) {
                $limit = TxnMarginOrder::where('user_id', $txnMarginOrder->user_id)
                ->where('type', OrderType::fromValue(OrderType::LIMIT)->key)
                ->where('id', '<', $txnMarginOrder->id)
                ->orderBy('id', 'desc')
                ->first();

                // 做空出場
                if(SideType::fromKey($txnMarginOrder->side)->is(SideType::BUY)) {
                    $txnMarginOrder->user->notify(sprintf("(測試功能)\n本次盈虧：%s", $limit->cummulativeQuoteQty - $txnMarginOrder->cummulativeQuoteQty));
                }
                else {
                    $txnMarginOrder->user->notify(sprintf("(測試功能)\n本次盈虧：%s", $txnMarginOrder->cummulativeQuoteQty - $limit->cummulativeQuoteQty));
                }
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
        $order['created_at'] = Carbon::parse($order['created_at'])->setTimezone('Asia/Taipei')->format('Y-m-d H:i:s');
        unset($order['transactTime']);
        unset($order['clientOrderId']);
        unset($order['id']);
        $data = '';
        foreach ($order as $key => $value) {
            $data .= "\n" . __('admin.txn.order.'.$key) . ': ' . $value;
        }
        return $data;
    }
}
