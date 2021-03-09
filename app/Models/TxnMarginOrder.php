<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TxnMarginOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ["user_id", "fills", "symbol", "orderId", "clientOrderId", "transactTime", "price", "origQty", "executedQty", "cummulativeQuoteQty", "status", "timeInForce", "type", "side", "marginBuyBorrowAsset", "marginBuyBorrowAmount", "isIsolated"];

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

    public function scopeStopLossLimit($query)
    {
        return $query->where('type', 'STOP_LOSS_LIMIT');
    }

    public function txnSellRec()
    {
        return $this->hasMany(AdminTxnSellRec::class, 'stop_ord_id');
    }

    //datetime
    public function getTransactTimeAttribute()
    {
        return $this->transactTime;
    }

    public function getSideAttribute()
    {
        return $this->side;
    }

    public function getIsIsolatedAttribute() : bool
    {
        return $this->isIsolated;
    }

    public function getPriceAttribute()
    {
        return $this->price;
    }

    public function getOrigQtyAttribute()
    {
        return $this->origQty;
    }

    public function getExecutedQtyAttribute()
    {
        return $this->executedQty;
    }

    public function getCummulativeQuoteQtyAttribute()
    {
        return $this->cummulativeQuoteQty;
    }

    public function getStatusAttribute()
    {
        return $this->status;
    }

    public function getTimeInForceAttribute()
    {
        return $this->timeInForce;
    }

    public function getMarginBuyBorrowAssetAttribute()
    {
        return $this->marginBuyBorrowAsset;
    }

    public function getMarginBuyBorrowAmountAttribute()
    {
        return $this->marginBuyBorrowAmount;
    }

    public function getFillsAttribute() : array
    {
        $fills = [];
        foreach ($this->fills as $key => $value) {
            $fill = new TxnMarginOrderFill;
            $fill->data = $value;
            array_push($fills, $fill);
        }
        return $fills;
    }
}
