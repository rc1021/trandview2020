<?php

namespace App\Admin\Controllers\Transaction;

use App\Models\SignalHistory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Carbon\Carbon;
use BenSampo\Enum\Enum;
use BinanceApi\Enums\OrderType;
use BinanceApi\Enums\DirectType;
use BinanceApi\Enums\SideType;
use BinanceApi\Enums\OrderStatusType;
use App\Enums\TxnExchangeType;
use Illuminate\Support\Facades\Storage;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;
use App\Admin\Models\TransactionLog\ShowTxnOrder;
use App\Admin\Models\TransactionLog\ShowCalcLog;
use App\Admin\Extensions\Tools\MarginForceLiquidationTool;
use App\Models\AdminTxnSetting;
use App\Models\FormulaTable;
use App\Enums\TxnSettingType;

class MarginSettingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '訂單交易設定';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AdminTxnSetting);

        $grid->column('pair', __('txn.margin.setting.pair'))->sortable();
        $grid->column('lever_switch', __('txn.margin.setting.lever_switch'))->display(function($val, $column) {
            return $this[$column->getName()];
        })->bool();

        $dynamic_columns = [ 'initial_tradable_total_funds', 'initial_capital_risk', 'base_asset_daily_interest', 'quote_asset_daily_interest'];
        foreach ($dynamic_columns as $column) {
            $grid->column($column, __('txn.margin.setting.'.$column))->display(function($val, $column) {
                $data = $this[$column->getName()];
                switch($column->getName()) {
                    case 'initial_tradable_total_funds':
                    case 'initial_capital_risk':
                    case 'base_asset_daily_interest':
                    case 'quote_asset_daily_interest':
                        return sprintf('%s%% | %s', $data * 100, $data);
                        break;
                    case 'lever_switch':
                        if($data) {
                            return <<<HTML
                            <i class="fa fa-fw fa-check text-success"></i>
                            HTML;
                        }
                        return <<<HTML
                        <i class="fa fa-fw fa-close text-muted"></i>
                        HTML;
                        break;
                }
                return $data;
            });
        }
        $grid->column('updated_at', __('txn.margin.setting.updated_at'))->sortable();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('pair', __('txn.margin.setting.pair'));
            $filter->between('updated_at', __('txn.margin.setting.updated_at'))->datetime();
        });
        $grid->model()->where('type', TxnSettingType::Margin)->where('user_id', Admin::user()->id);
        $grid->model()->orderBy('id', 'desc');
        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });


        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        Admin::style('.form-group > label.asterisk:before {color: #dd4b39;}');

        $form = new Form(new AdminTxnSetting());

        $form->hidden('user_id', __('txn.margin.setting.user_id'))->value(Admin::user()->id);
        $form->hidden('type', __('txn.margin.setting.type'))->default(TxnSettingType::Margin)->value(TxnSettingType::Margin);

        $pairs = FormulaTable::select('pair')->groupBy('pair')->get()->pluck('pair')->all();
        $form->select('pair', __('txn.margin.setting.pair'))->options(array_combine($pairs, $pairs))->rules('required');

        $form->embeds('options', '各項交易屬性', function ($form) {
            $states = [
                'on'  => ['value' => 1, 'text' => '打開', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '開閉', 'color' => 'danger'],
            ];
            $form->text('initial_tradable_total_funds', __('txn.margin.setting.form.initial_tradable_total_funds'))->rules('required')->default(1.0);
            $form->text('initial_capital_risk', __('txn.margin.setting.form.initial_capital_risk'))->rules('required')->default(0.07);
            $form->switch('lever_switch', __('txn.margin.setting.lever_switch'))->rules('required')->default(true)->states($states);
        });

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });

        return $form;
    }
}
