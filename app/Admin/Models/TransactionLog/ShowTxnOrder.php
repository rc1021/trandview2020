<?php

namespace App\Admin\Models\TransactionLog;

use Illuminate\Contracts\Support\Renderable;
use App\Models\TxnMarginOrder;
use BinanceApi\Enums\OrderType;
use Encore\Admin\Widgets\Table;

class ShowTxnOrder implements Renderable
{
    public function render($key = null)
    {
        $only = ['orderId', 'type', 'transactTime', 'price', 'origQty', 'executedQty', 'cummulativeQuoteQty', 'timeInForce', 'marginBuyBorrowAsset', 'marginBuyBorrowAmount'];
        $txnMargOrders = TxnMarginOrder::where('signal_id', $key)->get()->map(function ($orders) use ($only) {
            $order = $orders->only($only);
            $order['type'] = OrderType::fromKey($order['type'])->description;
            return $order;
        })->toArray();
        $columns = collect($only)->map(function ($column) {
            return __('admin.txn.order.'.$column);
        })->toArray();
        return (new Table($columns, $txnMargOrders))->render();
    }
}
