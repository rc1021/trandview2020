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
use App\Models\TxnMarginOrder;

class MarginIsolatedLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '交易紀錄';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        \Encore\Admin\Admin::style('.modal-dialog td[class^=column] { min-width: 132px; }');

        $instance = new TxnMarginOrder();
        $grid = new Grid($instance);

        $grid->tools(function ($tools) {
            $tools->append(new MarginForceLiquidationTool());
        });

        $grid->column('created_at', __('admin.txn.order.created_at'))->display(function($created_at) {
            $html = <<<HTML
                <i class="fa fa-fw fa-check text-success"></i>
            HTML;
            if(!empty($this->signal->error)) {
                $html = <<<HTML
                    <i class="fa fa-fw fa-exclamation-circle text-danger"></i>
                HTML;
            }
            return $html . '&nbsp;' . Carbon::parse($created_at)->setTimezone('Asia/Taipei')->format('Y-m-d H:i:s');
        });

        // "orderId"
        $dynamic_columns = ["symbol", "txn_type", "type", "side", "price", "origQty", "executedQty", "cummulativeQuoteQty", "status", "timeInForce", "marginBuyBorrowAmount", 'loan_ratio'];
        foreach ($dynamic_columns as $column) {
            $grid->column($column, __('admin.txn.order.'.$column))->display(function($name, $column) {
                $data = $this[$column->getName()];
                switch($column->getName()) {
                    case 'txn_type':
                        return sprintf('%s%s'
                                , DirectType::fromValue($this->signal->txn_direct_type)->description
                                , TxnExchangeType::fromValue($this->signal->txn_exchange_type)->description);
                    case 'side':
                        $data = SideType::fromKey($data);
                        break;
                    case 'type':
                        $data = OrderType::fromKey($data);
                        break;
                    case 'status':
                        $data = OrderStatusType::fromKey($data);
                        break;
                    case 'loan_ratio':
                        $data = ($data > 0) ? $data * 100 . '%' : '';
                        break;
                }
                if($data instanceof Enum)
                    return $data->description;
                return $data;
            });
        }

        // $grid->column('txnOrders', __('admin.rec.signal.txn_orders'))->display(function ($txnOrders) {
        //     $count = count($txnOrders);
        //     return __('admin.rec.signal.txn_order.count', compact('count'));
        // })->expand(function ($model) {
        //     // "symbol", "clientOrderId", "origClientOrderId"
        //     $only = ["orderId", "type", "side", "created_at", "price", "origQty", "executedQty", "cummulativeQuoteQty", "status", "timeInForce", "marginBuyBorrowAmount"];
        //     $txnOrders = $model->txnOrders()->get()->map(function ($origin) use ($only) {
        //         $order = $origin->only($only);
        //         $order['side'] = SideType::fromKey($order['side'])->description;
        //         $order['type'] = OrderType::fromKey($order['type'])->description;
        //         $order['status'] = OrderStatusType::fromKey($order['status'])->description;
        //         $order['marginBuyBorrowAmount'] .= ' '.$origin['marginBuyBorrowAsset'];
        //         // $order['transactTime'] = Carbon::parse($origin['transactTime'])->setTimezone('Asia/Taipei')->format('Y-m-d H:i:s');
        //         return $order;
        //     })->toArray();
        //     $columns = collect($only)->map(function ($column) {
        //         return __('admin.txn.order.'.$column);
        //     })->toArray();
        //     return new Table($columns, $txnOrders);
        // });

        $grid->column('log', __('admin.rec.signal.log'))->display(function($log) {
            if($this->signal->calc_log_path)
                return __('admin.rec.signal.detail');
            return 'No Data';
        })->modal(__('admin.rec.signal.log'), ShowCalcLog::class);

        $grid->column('error', __('admin.rec.signal.error'))->display(function($log) {
            if($this->signal->error)
                return __('admin.rec.signal.detail');
            return 'No Data';
        })->expand(function ($model) {
            $title = __('admin.rec.signal.error');
            return <<<HTML
            <div class="box">
                <div class="box-header">
                    <i class="fa fa-warning text-red"></i> $title
                </div>
                <div class="box-body">
                    <div style="white-space: pre-wrap;background: #333;color: #fff; padding: 10px;">$model->signal->error</div>
                </div>
            </div>
            HTML;
        });

        $grid->disableActions();

        $grid->filter(function($filter) {
            $filter->disableIdFilter();
            $filter->between('created_at', __('admin.rec.signal.created_at'))->datetime();
        });

        $grid->model()->load(['signal'])->where('user_id', Admin::user()->id);
        $grid->model()->orderBy('id', 'desc');
        $grid->disableCreateButton();
        $grid->disableRowSelector();
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->paginate(100);

        return $grid;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid_signal_hisotry()
    {
        \Encore\Admin\Admin::style('td[class^=column] { min-width: 125px; }');

        $instance = new SignalHistory();
        $grid = new Grid($instance);

        $grid->tools(function ($tools) {
            $tools->append(new MarginForceLiquidationTool());
        });

        $grid->column('created_at', __('admin.rec.signal.created_at'))->display(function($created_at) {
            $html = <<<HTML
                <i class="fa fa-fw fa-check text-success"></i>
            HTML;
            if(!empty($this->error)) {
                $html = <<<HTML
                    <i class="fa fa-fw fa-exclamation-circle text-danger"></i>
                HTML;
            }
            return $html . '&nbsp;' . Carbon::parse($created_at)->setTimezone('Asia/Taipei')->format('Y-m-d H:i:s');
        });

        $dynamic_columns = [ 'txn_type', 'symbol_type', 'current_price', 'entry_price', 'risk_start_price', 'position_price'];
        foreach ($dynamic_columns as $column) {
            $grid->column($column, __('admin.rec.signal.'.$column))->display(function($name, $column) {
                if($column->getName() == 'txn_type')
                    return sprintf('%s%s'
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
            // "symbol", "clientOrderId", "origClientOrderId"
            $only = ["orderId", "type", "side", "created_at", "price", "origQty", "executedQty", "cummulativeQuoteQty", "status", "timeInForce", "marginBuyBorrowAmount"];
            $txnOrders = $model->txnOrders()->get()->map(function ($origin) use ($only) {
                $order = $origin->only($only);
                $order['side'] = SideType::fromKey($order['side'])->description;
                $order['type'] = OrderType::fromKey($order['type'])->description;
                $order['status'] = OrderStatusType::fromKey($order['status'])->description;
                $order['marginBuyBorrowAmount'] .= ' '.$origin['marginBuyBorrowAsset'];
                // $order['transactTime'] = Carbon::parse($origin['transactTime'])->setTimezone('Asia/Taipei')->format('Y-m-d H:i:s');
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

        $grid->column('error', __('admin.rec.signal.error'))->display(function($log) {
            if($this->error)
                return __('admin.rec.signal.detail');
            return 'No Data';
        })->expand(function ($model) {
            $title = __('admin.rec.signal.error');
            return <<<HTML
            <div class="box">
                <div class="box-header">
                    <i class="fa fa-warning text-red"></i> $title
                </div>
                <div class="box-body">
                    <div style="white-space: pre-wrap;background: #333;color: #fff; padding: 10px;">$model->error</div>
                </div>
            </div>
            HTML;
        });

        $grid->disableActions();

        $grid->filter(function($filter) {
            $filter->disableIdFilter();
            $filter->between('created_at', __('admin.rec.signal.created_at'))->datetime();
        });

        $grid->model()->select($instance->getTable().'.*', 'signal_history_user.error')->join('signal_history_user', function ($join) use ($instance) {
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
