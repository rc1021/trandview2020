<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use BinanceApi\Enums\OrderStatusType;
use BinanceApi\Enums\OrderType;
use App\Models\AdminUser;

class TxnMarginOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ["signal_id", "user_id", "fills", "symbol", "orderId", "clientOrderId", "transactTime", "price", "origQty", "executedQty", "cummulativeQuoteQty", "status", "timeInForce", "type", "side", "marginBuyBorrowAsset", "marginBuyBorrowAmount", "isIsolated"];

    protected $dispatchesEvents = [
        'saving' => TxnMarginOrderSaving::class,
    ];

    protected $dates = [
        'transactTime',
    ];

    private $data = null;

    public function __get($name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        if(is_null($this->data) and !empty($this->result))
            $this->data = json_decode($this->result, true);

        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    public function scopeStatusNew($query)
    {
        return $query->where('status', OrderStatusType::fromValue(OrderStatusType::NEW)->key);
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
}
