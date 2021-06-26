<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use BinanceApi\Enums\OrderStatusType;
use BinanceApi\Enums\OrderType;
use App\Models\AdminUser;
use App\Models\SignalHistory;

class TxnMarginOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ["user_id", "signal_id", "symbol", "clientOrderId", "origClientOrderId", "orderId", "type", "side", "transactTime", "price", "origQty", "executedQty", "cummulativeQuoteQty", "status", "timeInForce", "marginBuyBorrowAmount", "marginBuyBorrowAsset", "fills", "loan_ratio"];

    protected $hidden = ['user_id', 'signal_id', 'updated_at', 'fills', 'isIsolated', 'error', 'deleted_at'];

    protected $dates = [
        'transactTime',
    ];

    protected $casts = [
        'fills' => 'array',
    ];

    public function scopeFilterStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeStatusNew($query)
    {
        return $query->where('status', OrderStatusType::fromValue(OrderStatusType::NEW)->key);
    }

    public function scopeFilterType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeStopLossLimit($query)
    {
        return $query->where('type', OrderType::fromValue(OrderType::STOP_LOSS_LIMIT)->key);
    }

    public function txnSellRec()
    {
        return $this->hasMany(AdminTxnSellRec::class, 'stop_ord_id');
    }

    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }

    public function signal()
    {
        return $this->belongsTo(SignalHistory::class, 'signal_id');
    }
}
