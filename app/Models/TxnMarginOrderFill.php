<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TxnMarginOrderFill extends Model
{
    use HasFactory;

    private $data = [];

    public function __get($name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

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

    public function setData($value)
    {
        $this->data = $value;
    }

    public function getPriceAttribute()
    {
        return $this->price;
    }

    public function getQtyAttribute()
    {
        return $this->qty;
    }

    public function getCommissionAttribute()
    {
        return $this->commission;
    }

    public function getCommissionAssetAttribute()
    {
        return $this->commissionAsset;
    }
}
