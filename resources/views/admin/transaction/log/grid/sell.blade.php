@if($model->txnSellRec)
<table class="table table-bordered">
    <tbody>
        <tr>
            <th colspan="2">
            </th>
            <th>
                差異
            </th>
        </tr>
        <tr>
            <td class="col-sm-4">平倉交易起始日期時間</td>
            <td class="col-sm-4">{{ $model->txnSellRec->liquidation_start_at }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">平倉交易結束日期時間</td>
            <td class="col-sm-4">{{ $model->txnSellRec->liquidation_done_at }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">平倉交易持續時間</td>
            <td class="col-sm-4">{{ $model->txnSellRec->liquidation_duration }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">平倉價位(均價)</td>
            <td class="col-sm-4">{{ $model->txnSellRec->liquidation_price_avg }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">交易手續費</td>
            <td class="col-sm-4">{{ $model->txnSellRec->transaction_fee }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">取回資金</td>
            <td class="col-sm-4">{{ $model->txnSellRec->gain_funds }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">損益</td>
            <td class="col-sm-4">{{ $model->txnSellRec->profit_and_loss }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">損益率(%)</td>
            <td class="col-sm-4">{{ $model->txnSellRec->profit_and_loss_rate }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">R值</td>
            <td class="col-sm-4">{{ $model->txnSellRec->r_value }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">交易後可交易總資金</td>
            <td class="col-sm-4">{{ $model->txnSellRec->sell_total_funds }}</td>
            <td class="col-sm-4"></td>
        </tr>
  </tbody>
</table>

@else
<table class="table table-bordered">
    <tbody>
        <tr>
            <th colspan="2">
            </th>
            <th>
                差異
            </th>
        </tr>
        <tr>
            <td colspan="3">尚未有資料</td>
        </tr>
  </tbody>
</table>
@endif
