<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AuthController as BaseAuthController;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use App\Http\Repositories\Admin\TransactionRepository;
use App\Models\AdminTxnEntryRec;

class TransactionController extends BaseAuthController
{
    protected $title = '訂單交易設定';

    public function setting(Content $content, TransactionRepository $rep)
    {
        return $rep->getSetting($content);
    }
}
