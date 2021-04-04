<?php

namespace App\Http\Repositories\Admin;

use App\Models\AdminUser;
use App\Admin\Forms\Transaction\Setting;
use Illuminate\Http\Request;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Box;

class TransactionRepository
{
    public function getSetting(Content $content)
    {
        $content->title('訂單交易設定');

        // 狀態 & 總交易紀錄
        // $status = AdminUser::find(Admin::user()->id)->txnStatus;
        // if(is_null($status)) {
        //     AdminUser::find(Admin::user()->id)->txnStatus()->create();
        //     $status = AdminUser::find(Admin::user()->id)->txnStatus;
        // }
        // $box = new Box('狀態', Admin::component('admin.transaction.status', compact('status')));
        // $box->style('info');
        // $content->row($box);

        // 交易設置
        $content->row(new Setting());

        return $content;
    }
}
