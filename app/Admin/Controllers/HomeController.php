<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Exception;
use Encore\Admin\Facades\Admin;
use App\Models\SignalHistory;

class HomeController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(['auth', '2fa']);
    // }

    public function index(Content $content)
    {
        return $content
            ->title('更新日誌')
            ->view('home.index');
    }

    public function test()
    {
        return 5;
    }
}
