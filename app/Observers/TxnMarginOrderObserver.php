<?php

namespace App\Observers;

use App\Models\TxnMarginOrder;

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
        $content = '';
        $txnMarginOrder->user->notify($content);
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
