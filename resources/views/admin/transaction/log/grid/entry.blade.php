<table class="table table-bordered">
    <tbody>
        <tr>
            <th colspan="4">
                ID: {{ $model->id }}
                @if (!empty($model->SignalHistory->error))
                <span class="label bg-red">Error</span>
                @endif
            </th>
        </tr>
        @if (!empty($model->SignalHistory->error))
        <tr>
            <td colspan="4" class="bg-red disabled color-palette">{{ $model->SignalHistory->error }}</td>
        </tr>
        @endif
        <tr>
            <td class="col-sm-3">應開倉日期時間</td>
            <td class="col-sm-3">{{ $model->position_at }}</td>
            <td class="col-sm-3">資金風險金額</td>
            <td class="col-sm-3">{{ $model->funds_risk_amount }}</td>
        </tr>
        <tr>
            <td class="col-sm-3">交易前可交易總資金</td>
            <td class="col-sm-3">{{ $model->avaiable_total_funds }}</td>
            <td class="col-sm-3">起始風險%(1R)</td>
            <td class="col-sm-3">{{ $model->risk_start }}</td>
        </tr>
        <tr>
            <td class="col-sm-3">交易方向(多/空)</td>
            <td class="col-sm-3">{{ \App\Enums\TxnDirectType::fromValue($model->tranding_long_short)->description }}</td>
            <td class="col-sm-3">應開倉部位大小(未加上槓桿量)</td>
            <td class="col-sm-3">{{ $model->position_price }}</td>
        </tr>
        <tr>
            <td class="col-sm-3">資金風險(%)</td>
            <td class="col-sm-3">{{ $model->funds_risk }}</td>
            <td class="col-sm-3">應使用槓桿(倍數)</td>
            <td class="col-sm-3">{{ $model->leverage_power }}</td>
        </tr>
        <tr>
            <td class="col-sm-3">交易配對</td>
            <td class="col-sm-3">{{ $model->transaction_matching }}</td>
            <td class="col-sm-3">應使用槓桿(金額)</td>
            <td class="col-sm-3">{{ $model->leverage_price }}</td>
        </tr>
        <tr>
            <td class="col-sm-3">槓桿使用</td>
            <td class="col-sm-3">{{ ($model->leverage) ? "是" : "否" }}</td>
            <td class="col-sm-3">應開倉部位大小(加上槓桿量)</td>
            <td class="col-sm-3">{{ $model->leverage_position_price }}</td>
        </tr>
        <tr>
            <td class="col-sm-3">預先扣除手續費</td>
            <td class="col-sm-3">{{ ($model->prededuct_handling_fee) ? "是" : "否" }}</td>
            <td class="col-sm-3">應開倉(幾口)</td>
            <td class="col-sm-3">{{ $model->position_few }}</td>
        </tr>
        <tr>
            <td class="col-sm-3">交易手續費</td>
            <td class="col-sm-3">{{ $model->transaction_fee }}</td>
            <td class="col-sm-3">應開倉(預先扣除手續費)</td>
            <td class="col-sm-3">{{ $model->position_few_amount }}</td>
        </tr>
        <tr>
            <td class="col-sm-3">起始風險價位(止損價位)</td>
            <td class="col-sm-3">{{ $model->risk_start_price }}</td>
            <td class="col-sm-3">交易手續費(幾口)</td>
            <td class="col-sm-3">{{ $model->tranding_fee_amount }}</td>
        </tr>
        <tr>
            <td class="col-sm-3">開倉價格容差(最高價位)</td>
            <td class="col-sm-3">{{ $model->hight_position_price }}</td>
            <td class="col-sm-3">應開倉部位大小(預先扣除手續費)</td>
            <td class="col-sm-3">{{ $model->position_price_with_fee }}</td>
        </tr>
        <tr>
            <td class="col-sm-3">開倉價格容差(最低價位)</td>
            <td class="col-sm-3">{{ $model->low_position_price }}</td>
            <td class="col-sm-3">應使用槓桿(金額)(預先扣除手續費)</td>
            <td class="col-sm-3">{{ $model->leverage_price_with_fee }}</td>
        </tr>
        <tr>
            <td class="col-sm-3">Entry訊號價位(當時的價位)</td>
            <td class="col-sm-3">{{ $model->entry_price }}</td>
            <td class="col-sm-3">應使用槓桿(倍數)(預先扣除手續費)</td>
            <td class="col-sm-3">{{ $model->leverage_power_with_fee }}</td>
        </tr>
  </tbody>
</table>
