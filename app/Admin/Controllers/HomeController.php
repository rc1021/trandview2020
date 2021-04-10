<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Exception;
use App\Jobs\BinanceIsolatedTrandingWorker;
use Encore\Admin\Facades\Admin;
use App\Models\SignalHistory;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->title('更新日誌')
            ->view('home.index');
    }

    public function forceLiquidation()
    {
        try {

            // 記錄訊息
            $rec = new SignalHistory;
            $rec->clock = '1';
            $rec->message = sprintf('交易執行類別=Force Exit,交易所=BINANCE,交易配對=BTCUSDT,執行日期時間=%s,現價=null,交易執行價格=null,做多起始風險價位=null,做空起始風險價位=null,開倉價格容差(最高價位)=null,開倉價格容差(最低價位)=null', now());
            $rec->save();

            $worker = new BinanceIsolatedTrandingWorker(Admin::user(), $rec);
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
