<?php

namespace App\Admin\Controllers;

use App\Models\AdminTxnEntryRec;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TransactionLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'AdminTxnEntryRec';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AdminTxnEntryRec());

        $grid->column('Entry數據')->style('width: 30%;')->view('admin.transaction.log.grid.entry');
        $grid->column('txnBuyRec', '實際開倉紀錄')->style('width: 25%;')->view('admin.transaction.log.grid.buy');
        $grid->column('txnExitRec', 'Exit數據')->style('width: 20%;')->view('admin.transaction.log.grid.exit');
        $grid->column('txnExitRec.txnEntryRec', '實際平倉紀錄')->style('width: 25%;')->view('admin.transaction.log.grid.sell');

        $grid->filter(function($filter) {
            $filter->disableIdFilter();
            $filter->between('position_at', __('admin.rec.entry.position_at'))->datetime();
            $filter->expand();
        });

        $grid->model()->where('user_id', Admin::user()->id);
        $grid->model()->orderBy('id', 'desc');
        $grid->disableCreateButton();
        $grid->disableRowSelector();
        $grid->disableActions();
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->paginate(50);

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(AdminTxnEntryRec::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('position_at', __('Position at'));
        $show->field('avaiable_total_funds', __('Avaiable total funds'));
        $show->field('tranding_long_short', __('Tranding long short'));
        $show->field('funds_risk', __('Funds risk'));
        $show->field('transaction_matching', __('Transaction matching'));
        $show->field('leverage', __('Leverage'));
        $show->field('prededuct_handling_fee', __('Prededuct handling fee'));
        $show->field('transaction_fee', __('Transaction fee'));
        $show->field('risk_start_price', __('Risk start price'));
        $show->field('hight_position_price', __('Hight position price'));
        $show->field('low_position_price', __('Low position price'));
        $show->field('entry_price', __('Entry price'));
        $show->field('funds_risk_amount', __('Funds risk amount'));
        $show->field('risk_start', __('Risk start'));
        $show->field('position_price', __('Position price'));
        $show->field('leverage_power', __('Leverage power'));
        $show->field('leverage_price', __('Leverage price'));
        $show->field('leverage_position_price', __('Leverage position price'));
        $show->field('position_few', __('Position few'));
        $show->field('tranding_fee_amount', __('Tranding fee amount'));
        $show->field('position_few_amount', __('Position few amount'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AdminTxnEntryRec());

        $form->number('user_id', __('User id'));
        $form->datetime('position_at', __('Position at'))->default(date('Y-m-d H:i:s'));
        $form->number('avaiable_total_funds', __('Avaiable total funds'));
        $form->switch('tranding_long_short', __('Tranding long short'))->default(1);
        $form->decimal('funds_risk', __('Funds risk'))->default(0.0000000000);
        $form->text('transaction_matching', __('Transaction matching'))->default('1');
        $form->switch('leverage', __('Leverage'));
        $form->switch('prededuct_handling_fee', __('Prededuct handling fee'));
        $form->decimal('transaction_fee', __('Transaction fee'))->default(0.0000000000);
        $form->decimal('risk_start_price', __('Risk start price'))->default(0.0000000000);
        $form->decimal('hight_position_price', __('Hight position price'))->default(0.0000000000);
        $form->decimal('low_position_price', __('Low position price'))->default(0.0000000000);
        $form->decimal('entry_price', __('Entry price'))->default(0.0000000000);
        $form->number('funds_risk_amount', __('Funds risk amount'));
        $form->decimal('risk_start', __('Risk start'))->default(0.0000000000);
        $form->decimal('position_price', __('Position price'))->default(0.0000000000);
        $form->decimal('leverage_power', __('Leverage power'))->default(0.0000000000);
        $form->decimal('leverage_price', __('Leverage price'))->default(0.0000000000);
        $form->decimal('leverage_position_price', __('Leverage position price'))->default(0.0000000000);
        $form->decimal('position_few', __('Position few'))->default(0.0000000000);
        $form->decimal('tranding_fee_amount', __('Tranding fee amount'))->default(0.0000000000);
        $form->decimal('position_few_amount', __('Position few amount'))->default(0.0000000000);

        return $form;
    }
}
