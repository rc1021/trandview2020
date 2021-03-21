<?php

namespace App\Admin\Controllers;

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
use App\Enums\TxnExchangeType;
use Illuminate\Support\Facades\Storage;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;
use App\Admin\Models\TransactionLog\ShowTxnOrder;
use App\Admin\Models\TransactionLog\ShowCalcLog;

class TransactionLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SignalHistory';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        \Encore\Admin\Admin::style('td[class^=column] { min-width: 125px; }');

        $instance = new SignalHistory();
        $grid = new Grid($instance);

        $grid->column('created_at', __('admin.rec.signal.created_at'))->display(function($created_at) {
            $html = <<<HTML
                <i class="fa fa-fw fa-check text-success"></i>
            HTML;
            if(!empty($this->error)) {
                $err = $this->error;
                $html = <<<HTML
                    <i class="fa fa-fw fa-exclamation-circle text-danger" data-toggle="tooltip" title="$err"></i>
                HTML;
            }
            return $html . '&nbsp;' . Carbon::parse($created_at)->format('Y-m-d H:i:s');
        });

        $dynamic_columns = [ 'txn_type', 'symbol_type', 'entry_price', 'risk_start_price', 'position_price', 'auto_liquidation_at'];
        foreach ($dynamic_columns as $column) {
            $grid->column($column, __('admin.rec.signal.'.$column))->display(function($name, $column) {
                if($column->getName() == 'txn_type')
                    return sprintf('%s %s'
                            , DirectType::fromValue($this->txn_direct_type)->description
                            , TxnExchangeType::fromValue($this->txn_exchange_type)->description);
                $data = $this[$column->getName()];
                if($data instanceof Enum)
                    return $data->description;
                return $data;
            });
        }

        $grid->column('txnOrders', __('admin.rec.signal.txn_orders'))->display(function ($txnOrders) {
            $count = count($txnOrders);
            return __('admin.rec.signal.txn_order.count', compact('count'));
        })->expand(function ($model) {
            $only = ['orderId', 'type', 'transactTime', 'price', 'origQty', 'executedQty', 'cummulativeQuoteQty', 'timeInForce', 'marginBuyBorrowAmount'];
            $txnOrders = $model->txnOrders()->get()->map(function ($origin) use ($only) {
                $order = $origin->only($only);
                $order['type'] = OrderType::fromKey($order['type'])->description;
                $order['marginBuyBorrowAmount'] .= ' '.$origin['marginBuyBorrowAsset'];
                return $order;
            })->toArray();
            $columns = collect($only)->map(function ($column) {
                return __('admin.txn.order.'.$column);
            })->toArray();
            return new Table($columns, $txnOrders);
        });

        $grid->column('log', __('admin.rec.signal.log'))->display(function($log) {
            if($this->calc_log_path)
                return __('admin.rec.signal.detail');
            return 'No Data';
        })->modal(__('admin.rec.signal.log'), ShowCalcLog::class);

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });

        $grid->filter(function($filter) {
            $filter->disableIdFilter();
            $filter->between('position_at', __('admin.rec.entry.position_at'))->datetime();
            $filter->expand();
        });

        $grid->model()->join('signal_history_user', function ($join) use ($instance) {
            $join->on($instance->getTable().'.id', '=', 'signal_history_user.signal_history_id')
                ->where('admin_user_id', Admin::user()->id);
        });
        $grid->model()->orderBy('id', 'desc');
        $grid->disableCreateButton();
        $grid->disableRowSelector();
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
        $show = new Show(SignalHistory::findOrFail($id));

        $show->field('created_at', __('admin.rec.signal.created_at'));
        $show->field('message', __('admin.rec.signal.message'));
        $show->field('error', __('admin.rec.signal.error'));

        return $show;
    }

    public function calc(SignalHistory $signal_history, Content $content)
    {
        $html = $signal_history->calc_log_html;
        if($html) {
            $box = new Box(null, $html);
            $box = str_replace('box-body', 'box-body table-responsive no-padding', $box);
            return $content->body($box);
        }
        abort(404);
    }
}
