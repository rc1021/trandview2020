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
use App\Enums\TxnSettingType;
use App\Models\TxnMarginOrder;
use App\Models\FormulaTable;
use App\Models\AdminTxnSetting;
use App\Models\AdminUser;
use Exception;
use App\Jobs\BinanceMarginTrandingWorker;
use HTML5;

class MarginLogController extends AdminController
{
    private $api, $pairs = []; // 儲存已查詢的 pair 資料

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '交易紀錄';

    private function getBinanceApi()
    {
        if(empty($this->api)) {
            $this->api = Admin::user()->binance_api;
        }
        return $this->api;
    }

    private function getPair($symbol)
    {
        try {
            if(!array_key_exists($symbol, $this->pairs)) {
                $this->pairs[$symbol] = $this->getBinanceApi()->marginIsolatedPairs($symbol);
            }
            return $this->pairs[$symbol];
        }
        catch(Exception $e) { }
        return null;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        \Encore\Admin\Admin::style('.modal-dialog td[class^=column] { min-width: 125px; }');

        try {
            $instance = new TxnMarginOrder;
            $grid = new Grid($instance);
            $controller = $this;

            $grid->column('created_at', __('txn.order.created_at'))->display(function($data, $column) {
                $html = <<<HTML
                    <i class="fa fa-fw fa-check text-success"></i>
                HTML;
                if(!empty($this->signal->error)) {
                    $html = <<<HTML
                        <i class="fa fa-fw fa-exclamation-circle text-danger"></i>
                    HTML;
                }
                return $html . '&nbsp;' . Carbon::parse($data)->setTimezone('Asia/Taipei')->format('Y-m-d H:i:s');
            });
            $grid->column('symbol', __('txn.order.symbol'));
            $grid->column('txn_type', __('txn.order.txn_type'))->display(function($model) {
                return sprintf('%s%s'
                , DirectType::fromValue($this->signal->txn_direct_type)->description
                , TxnExchangeType::fromValue($this->signal->txn_exchange_type)->description);
            });
            $grid->column('price', __('txn.order.price'))->display(function($data, $column) {
                return ceil_dec($data, 2);
            });
            $grid->column('executedPrice', __('txn.order.executedPrice'))->display(function($data, $column) {
                $data = '未記錄';
                if(!empty($this->fills) and gettype($this->fills) == 'array')
                    $data = ceil_dec(collect($this->fills)->avg('price'), 2);
                return $data;
            });
            $grid->column('origQty', __('txn.order.origQty'))->display(function($data, $column) use ($controller) {
                return sprintf('%s %s', ceil_dec($data, 2), data_get($controller->getPair($this->symbol), 'base', ''));
            });
            $grid->column('executedQty', __('txn.order.executedQty'))->display(function($data, $column) use ($controller) {
                return sprintf('%s %s', ceil_dec($data, 2), data_get($controller->getPair($this->symbol), 'base', ''));
            });
            $grid->column('cummulativeQuoteQty', __('txn.order.cummulativeQuoteQty'))->display(function($data, $column) {
                // return sprintf('%s %s', ceil_dec($data, 2), data_get($controller->getPair($this->symbol), 'quote', ''));
                return ceil_dec($data, 2);
            });
            $grid->column('marginBuyBorrowAmount', __('txn.order.marginBuyBorrowAmount'))->display(function($data, $column) {
                $data = ceil_dec($data, 2);
                if($data == 0)
                    $data = '';
                else if(!empty($this->loan_ratio) and $this->loan_ratio > 0)
                    $data = sprintf('%s %s (%s%%)', $data, $this->marginBuyBorrowAsset, $this->loan_ratio * 100);
                return $data;
            });
            // $grid->column('created_at', __('txn.order.created_at'))->display(function($name, $column) use ($tippyJs) {
            // });

            $grid->disableActions();

            $grid->filter(function($filter) {
                $filter->disableIdFilter();
                $pairs = FormulaTable::select('pair')->groupBy('pair')->get()->pluck('pair')->all();
                $filter->equal('symbol', __('txn.order.symbol'))->select(array_combine($pairs, $pairs));
                $filter->between('created_at', __('txn.order.created_at'))->datetime();
            });

            $grid->model()->with('user', 'signal')->where('status', OrderStatusType::getKey(OrderStatusType::FILLED))->where('user_id', Admin::user()->id);
            $grid->model()->orderBy('id', 'desc');
            $grid->disableCreateButton();
            $grid->disableRowSelector();
            $grid->disableExport();
            $grid->disableColumnSelector();
            $grid->paginate(50);

            return $this->txnEntryRows() . $grid->render();
        }
        catch(Exception $e) {
            return <<<HTML
            執行發生錯誤 (error: {$e->getMessage()})
        HTML;
        }
    }

    private function txnEntryRows()
    {
        $user = Admin::guard()->user();
        $txns = $user->current_margin_txns;
        $header = ['交易對', '進場中', '進場前原始資金', '進場當時均價', '目前價位', '目前未出場獲利', '操作'];
        $box = new Box('進場中的交易列表', new Table($header, $txns));
        $box->collapsable();
        $box->style('info');
        return str_replace('"box-body"', '"box-body table-responsive no-padding"' ,$box->render());
    }

    /**
     * tippy
     *
     * @return function
     */
    private function tippyJs()
    {
        return function (string $text, Array $attrs = [], Array $opts = []) {
            $attribues = [];
            foreach ($attrs as $key => $value) {
                array_push($attribues, $key.'="'.$value.'"');
            }
            $attribues = implode(' ', $attribues);
            $data_attribues = [];
            foreach ($opts as $key => $value) {
                array_push($data_attribues, 'data-tippy-'.$key.'="'.$value.'"');
            }
            $data_attribues = implode(' ', $data_attribues);
            return <<<HTML
            <span $attribues $data_attribues>$text</span>
            HTML;
        };
    }

    private function expandTxnType()
    {
        return function (TxnMarginOrder $model) : Table
        {
            $columns = collect(["orderId", "timeInForce", "side", "type"])->map(function ($col) {
                return __('txn.order.'.$col);
            });
            return new Table($columns, [
                [
                    $model->orderId,
                    $model->timeInForce,
                    SideType::fromKey($model->side)->description,
                    OrderType::fromKey($model->type)->description,
                ]
            ]);
        };
    }

    /**
     * 用表格形式展開錯誤訊息
     *
     * @return function
     */
    private function expandError()
    {
        return function (SignalHistory $model) {
            $title = __('rec.signal.error');
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
        };
    }

    /**
     * 用表格形式展開資產前後變化內容
     *
     * @return function
     */
    private function modalAssetChangeTable()
    {
        return function (SignalHistory $model) : Table {
            $beforeAsset = json_decode($model->before_asset, true);
            $afterAsset = json_decode($model->after_asset, true);
            $columns = ["free", "locked", "borrowed", "interest", "netAsset", "totalAsset"];

            $row1 = [__('rec.signal.before_asset'), [
                'col' => 3,
                'content' => '未記錄'
            ]];
            if(!empty($beforeAsset)) {
                $row1 = [__('rec.signal.before_asset'), data_get($beforeAsset, 'indexPrice')];
                $row1[2] = implode('<br>', collect($columns)->map(function ($column) use ($beforeAsset) {
                    return __('rec.signal.asset.'.$column) . '：' . data_get($beforeAsset, 'baseAsset.'.$column, '');
                })->toArray());
                $row1[3] = implode('<br>', collect($columns)->map(function ($column) use ($beforeAsset) {
                    return __('rec.signal.asset.'.$column) . '：' . data_get($beforeAsset, 'quoteAsset.'.$column, '');
                })->toArray());
            }

            $row2 = [__('rec.signal.after_asset'), [
                'col' => 3,
                'content' => '未記錄'
            ]];
            if(!empty($afterAsset)) {
                $row2 = [__('rec.signal.after_asset'), data_get($afterAsset, 'indexPrice')];
                $row2[2] = implode('<br>', collect($columns)->map(function ($column) use ($afterAsset) {
                    return __('rec.signal.asset.'.$column) . '：' . data_get($afterAsset, 'baseAsset.'.$column, '');
                })->toArray());
                $row2[3] = implode('<br>', collect($columns)->map(function ($column) use ($afterAsset) {
                    return __('rec.signal.asset.'.$column) . '：' . data_get($afterAsset, 'quoteAsset.'.$column, '');
                })->toArray());
            }

            $columns = [
                __('rec.signal.asset_unit'),
                __('rec.signal.asset.indexPrice'),
                data_get($beforeAsset, 'baseAsset.asset', data_get($afterAsset, 'baseAsset.asset', __('rec.signal.asset.base'))),
                data_get($beforeAsset, 'quoteAsset.asset', data_get($afterAsset, 'quoteAsset.asset', __('rec.signal.asset.quote'))),
            ];
            return new Table($columns, [$row1, $row2]);
        };
    }

    /**
     * 用表格形式展開訂單交易記錄
     *
     * @return function
     */
    private function expandTxnMargOrderTable()
    {
        return function (SignalHistory $model) : Table {
            // "symbol", "clientOrderId", "origClientOrderId"
            $only = ["orderId", "type", "side", "price", "avg_price", "origQty", "executedQty", "cummulativeQuoteQty", "status", "timeInForce", "marginBuyBorrowAmount", "loan_ratio", "fills"];
            $txnMargOrders = $model->txnMargOrders->map(function ($origin) use ($only, $model) {
                $order = $origin->only($only);
                $order['side'] = SideType::fromKey($order['side'])->description;
                $order['type'] = OrderType::fromKey($order['type'])->description;
                $order['status'] = OrderStatusType::fromKey($order['status'])->description;
                $order['loan_ratio'] = sprintf('%s%%', $origin['loan_ratio'] * 100);
                $order['price'] = ceil_dec($origin['price'], 2);
                $order['cummulativeQuoteQty'] = ceil_dec($origin['cummulativeQuoteQty'], 2);
                $order['marginBuyBorrowAmount'] .= ' '.$origin['marginBuyBorrowAsset'];

                if(empty($order['fills'])) {
                    $order['fills'] = '';
                    $order['avg_price'] = 0;
                }
                else {
                    $fills_data = [
                        'url'     => null,
                        'async'   => false,
                        'grid'    => false,
                        'key'     => $model->id . '_' . $origin->id,
                        'name'    => 'signal-' . $model->id . '-order-' . $origin->id,
                        'title'   => __('txn.order.fills') . __('rec.signal.detail'),
                        'value'   => __('rec.signal.detail'),
                    ];
                    try {
                        $columns = collect(['price', 'qty', 'commission', 'amount'])->map(function ($column) {
                            return __('txn.order.fills_detail.'.$column);
                        })->toArray();
                        $collect = collect($order['fills']);
                        $order['avg_price'] = ceil_dec($collect->avg('price'), 2);
                        $fills_data['html'] = (new Table($columns, $collect->map(function ($fill) {
                            return [
                                'price' => $fill['price'],
                                'qty' => $fill['qty'],
                                'commission' => $fill['commission'] . ' ' . $fill['commissionAsset'],
                                'amount' => ($fill['price'] * $fill['qty']) . ' ' . $fill['commissionAsset']
                            ];
                        })->toArray()))->render();
                    }
                    catch(Exception $e) {
                        $content = json_encode($order['fills']);
                        $fills_data['title'] = __('rec.signal.error');
                        $fills_data['value'] = $e->getMessage();
                        $fills_data['html'] = <<<HTML
                        <div> {$e->getMessage()} </div>
                        <div> {$content} </div>
                        HTML;
                    }
                    $order['fills'] = Admin::component('admin::components.column-modal', $fills_data);
                }
                return $order;
            })->toArray();
            $columns = collect($only)->map(function ($column) {
                return __('txn.order.'.$column);
            })->toArray();
            return new Table($columns, $txnMargOrders);
        };
    }

    public function forceLiquidation(string $pair)
    {
        try {
            // 記錄訊息
            $rec = new SignalHistory;
            $rec->clock = '1';
            $rec->type = TxnSettingType::Margin;
            $rec->message = sprintf('交易執行類別=Force Exit,交易所=BINANCE,交易配對=%s,執行日期時間=%s,現價=null,交易執行價格=null,做多起始風險價位=null,做空起始風險價位=null,開倉價格容差(最高價位)=null,開倉價格容差(最低價位)=null', $pair, now());
            $rec->save();

            $worker = new BinanceMarginTrandingWorker(Admin::user(), $rec);
            $worker->handle();

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => '已強制平倉 ok!'
            ]);
        }
        catch(Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
