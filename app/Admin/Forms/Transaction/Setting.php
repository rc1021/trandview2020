<?php

namespace App\Admin\Forms\Transaction;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;
use App\Http\Repositories\Admin\AuthKeySecretRepository;
use App\Models\AdminUser;
use App\Models\AdminTxnSetting;
use BinanceApi\Enums\SymbolType;
use Illuminate\Support\Arr;

class Setting extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = '交易設置';

    /**
     * Handle the form request.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        $states = $this->commonSwitch();
        $request->merge([
            'lever_switch' => Arr::get($states, $request->lever_switch, ['value' => 0])['value'],
            'prededuct_handling_fee' => Arr::get($states, $request->prededuct_handling_fee, ['value' => 0])['value'],
        ]);

        $setting = $this->data();
        $setting->fill($request->all())->save();
        admin_toastr(trans('admin.update_succeeded'));
        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $rate_func = function ($value) {
            return $value * 100;
        };
        $states = $this->commonSwitch();
        $this->rate('initial_tradable_total_funds', __('admin.txn.initial_tradable_total_funds'))->rules('required|numeric')->customFormat($rate_func);
        $this->select('transaction_matching', __('admin.txn.transaction_matching'))->options(SymbolType::asSelectArray())->rules('required');
        $this->switch('lever_switch', __('admin.txn.lever_switch'))->rules('required')->states($states);
        $this->rate('initial_capital_risk', __('admin.txn.initial_capital_risk'))->rules('required|numeric')->customFormat($rate_func);
        $this->rate('btc_daily_interest', __('admin.txn.btc_daily_interest'))->rules('required|numeric')->customFormat($rate_func);
        $this->rate('usdt_daily_interest', __('admin.txn.usdt_daily_interest'))->rules('required|numeric')->customFormat($rate_func);
        $this->switch('prededuct_handling_fee', __('admin.txn.prededuct_handling_fee'))->rules('required')->states($states);
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data() : AdminTxnSetting
    {
        $user = AdminUser::find(Admin::user()->id);
        $setting = $user->txnSetting()->firstOrCreate();
        return $user->txnSetting;
    }

    private function commonSwitch()
    {
        return [
            'on'  => ['value' => 1, 'text' => __('admin.txn.switch.on'), 'color' => 'success'],
            'off' => ['value' => 0, 'text' => __('admin.txn.switch.off')],
        ];
    }
}
