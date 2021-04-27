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

class FeaturesSetting extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'U本位交易設置';

    /**
     * Handle the form request.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
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
        $this->text('initial_tradable_total_funds', __('admin.txn.feat.initial_tradable_total_funds'))->rules('required|numeric');
        $this->text('initial_capital_risk', __('admin.txn.feat.initial_capital_risk'))->rules('required|numeric');
        $this->text('lever', __('admin.txn.feat.lever'))->rules('required|numeric');
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data() : AdminTxnSetting
    {
        $user = AdminUser::find(Admin::user()->id);
        $setting = $user->txnFeatSetting()->firstOrCreate();
        return $user->txnFeatSetting;
    }
}
