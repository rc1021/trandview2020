<?php

namespace App\Models;

use App\Models\Traits\SignalHistoryTrait;
use App\Models\TxnMarginOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Encore\Admin\Facades\Admin;

class SignalHistory extends Model
{
    use HasFactory, SignalHistoryTrait;

    // 解構訊號內容
    protected $_signal = [];

    protected $dates = ['created_at'];

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
                $this->is_valid = !empty($this->symbol_type);
            }
        }
        if(!is_null($key))
            return $this->_signal[$key];
        return $this->_signal;
    }

    public function setSignal($key, $val = null)
    {
        if(count($this->_signal) == 0) {
            $arr = [];
            $count = preg_match_all('/([^?=,]+)=([^,]*)?/', $this->message, $arr);
            $this->is_valid = ($count == 10 && count($arr) == 3);
            if($this->is_valid) {
                $this->_signal = array_combine($arr[1], $arr[2]);
                $this->is_valid = !empty($this->symbol_type);
            }
        }
        $this->_signal[$key] = $val;
        $tmp = [];
        foreach ($this->_signal as $key => $value)
            array_push($tmp, sprintf('%s=%s', $key, $value));
        $this->message = implode(',', $tmp);
    }

    public function getCalcLogPathAttribute()
    {
        if (!Admin::user())
            return null;
        $path = "excel-logs/".Admin::user()->id."/{$this->id}/index.html";
        if(!Storage::disk('local')->exists($path))
            return null;
        return $path;
    }

    public function getCalcLogHtmlAttribute()
    {
        $path = $this->calc_log_path;
        if($path) {
            $html = Storage::disk('local')->get($path);
            $html = str_replace('gridlines', 'gridlines table table-bordered', $html);
            return $html;
        }
        return null;
    }

    public function users()
    {
        return $this->belongsToMany(AdminUser::class, 'signal_history_user');
    }

    public function txnMargOrders()
    {
        if(!Admin::user())
            return $this->hasMany(TxnMarginOrder::class, 'signal_id')->where('user_id', 0);
        return $this->hasMany(TxnMarginOrder::class, 'signal_id')->where('user_id', Admin::user()->id);
    }

    public function txnFeatOrders()
    {
        if(!Admin::user())
            return $this->hasMany(TxnFuturesOrder::class, 'signal_id')->where('user_id', 0);
        return $this->hasMany(TxnFuturesOrder::class, 'signal_id')->where('user_id', Admin::user()->id);
    }
}
