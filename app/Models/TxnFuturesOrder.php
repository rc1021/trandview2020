<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TxnFuturesOrder extends Model
{
    use HasFactory;

    protected $fillable = ["clientOrderId", "cumQty", "cumQuote", "executedQty", "orderId", "avgPrice", "origQty", "price", "reduceOnly", "side", "positionSide", "status", "stopPrice", "closePosition", "symbol", "timeInForce", "type", "origType", "activatePrice", "priceRate", "updateTime", "workingType", "priceProtect"];
}
