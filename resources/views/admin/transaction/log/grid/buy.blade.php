@if($model->txnBuyRec)
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
            <td class="col-sm-4">開倉交易起始日期時間</td>
            <td class="col-sm-4">{{ $model->txnBuyRec->position_start_at }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">開倉交易完成日期時間</td>
            <td class="col-sm-4">{{ $model->txnBuyRec->position_done_at }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">開倉交易持續時間</td>
            <td class="col-sm-4">{{ $model->txnBuyRec->position_duration }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">實際開倉部位大小</td>
            <td class="col-sm-4">{{ $model->txnBuyRec->position_price }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">實際開倉價位(均價)</td>
            <td class="col-sm-4">{{ $model->txnBuyRec->position_price_avg }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">實際開倉(幾口)</td>
            <td class="col-sm-4">{{ $model->txnBuyRec->position_quota }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">實際使用槓桿(倍數)</td>
            <td class="col-sm-4">{{ $model->txnBuyRec->leverage_power }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">實際使用槓桿(金額)</td>
            <td class="col-sm-4">{{ $model->txnBuyRec->leverage_price }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">實際起始風險%(1R)</td>
            <td class="col-sm-4">{{ $model->txnBuyRec->risk_start }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">交易手續費(幾口)</td>
            <td class="col-sm-4">{{ $model->txnBuyRec->transaction_fee }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">實際資金風險(金額)</td>
            <td class="col-sm-4">{{ $model->txnBuyRec->funds_risk }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">剩餘資金風險(金額)</td>
            <td class="col-sm-4">{{ $model->txnBuyRec->funds_risk_less }}</td>
            <td class="col-sm-4"></td>
        </tr>
        <tr>
            <td class="col-sm-4">開倉目標達成率</td>
            <td class="col-sm-4">{{ $model->txnBuyRec->target_rate }}</td>
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
