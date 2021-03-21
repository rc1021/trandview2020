<?php

namespace App\Admin\Models\TransactionLog;

use Illuminate\Contracts\Support\Renderable;
use App\Models\SignalHistory;
use BinanceApi\Enums\OrderType;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Box;

class ShowCalcLog implements Renderable
{
    public function render($key = null)
    {
        $signal = SignalHistory::find($key);
        $html = $signal->calc_log_html;
        if($html) {
            // $box = new Box(null, $html);
            // $box = str_replace('box-body', 'box-body table-responsive no-padding', $box);
            // return $box;
            return <<<HTML
                <div class="table-responsive no-padding">$html</div>
            HTML;
        }
        return 'No Data';
    }
}
