<?php

namespace App\Models;

use App\Models\Traits\SignalHistoryTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignalHistory extends Model
{
    use HasFactory, SignalHistoryTrait;

    // 解構訊號內容
    protected $_signal = [];

    public function setMessageAttribute($value)
    {
        $this->attributes['message'] = $value;
        $this->getSignal();
    }

    public function getSignal($key = null)
    {
        if(count($this->_signal) == 0) {
            $arr = [];
            $count = preg_match_all('/([^?=,]+)=([^,]*)?/', $this->message, $arr);
            $this->is_valid = ($count == 10 && count($arr) == 3);
            if($this->is_valid) {
                $this->_signal = array_combine($arr[1], $arr[2]);
                $this->is_valid = !is_null($this->symbol_type);
            }
        }
        if(!is_null($key))
            return $this->_signal[$key];
        return $this->_signal;
    }

    public function txnEntryRecs()
    {
        return $this->hasMany(AdminTxnEntryRec::class, 'signal_history_id');
    }

    public function txnExitRecs()
    {
        return $this->hasMany(AdminTxnExitRec::class, 'signal_history_id');
    }
}
