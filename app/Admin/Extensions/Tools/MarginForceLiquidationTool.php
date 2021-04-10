<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;

class MarginForceLiquidationTool extends AbstractTool
{
    public function render()
    {
        //強制平倉按鈕
        return view('admin.navbar.force-liquidation', [
            'user' => Admin::user()
        ]);
    }
}
