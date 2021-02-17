@if($model->txnExitRec)
<table class="table table-bordered">
    <tbody>
        <tr>
            <th colspan="2">
                @if (!empty($model->SignalHistory->error))
                <span class="label bg-red">Error</span>
                @endif
                &nbsp;
            </th>
        </tr>
        @if (!empty($model->SignalHistory->error))
        <tr>
            <td colspan="2" class="bg-red disabled color-palette">{{ $model->SignalHistory->error }}</td>
        </tr>
        @endif
        <tr>
            <td class="col-sm-6">應平倉日期時間</td>
            <td class="col-sm-6">{{ $model->txnExitRec->liquidation_at }}</td>
        </tr>
        <tr>
            <td class="col-sm-6">Exit訊號價位(當時的價位)</td>
            <td class="col-sm-6">{{ $model->txnExitRec->liquidation_price }}</td>
        </tr>
  </tbody>
</table>
@else
<table class="table table-bordered">
    <tbody>
        <tr>
            <th colspan="2">
                &nbsp;
            </th>
        </tr>
        <tr>
            <td colspan="2">尚未有資料</td>
        </tr>
  </tbody>
</table>
@endif
