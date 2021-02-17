<div class="row">
    <div class="col-sm-4">{{ __('admin.txn.current_total_funds') }}：{{ $status->currentTotalFunds }}</div>
    <div class="col-sm-4">{{ __('admin.txn.initial_total_capital') }}：{{ $status->initial_total_capital }}</div>
    <div class="col-sm-4">
        {{ __('admin.txn.status.title') }}：{!! ($status->current_state) ? __('admin.txn.status.position') : __('admin.txn.status.liquidation') !!}
        @if ($status->current_state)
            &nbsp;&nbsp;<a href="#" class="btn btn-danger btn-xs">{{ __('admin.txn.status.force_liquidation') }}</a>
        @endif
    </div>
</div>
<table class="table table-bordered" style="margin-top: 10px;">
    <tbody>
        <tr>
            <th colspan="3">
                {{ __('admin.txn.total_record') }}
            </th>
        </tr>
        <tr>
            <td class="col-sm-4">{{ __('admin.txn.total_transaction_times') }}：{{ $status->total_transaction_times }}</td>
            <td class="col-sm-4">{{ __('admin.txn.total_profit_times') }}：{{ $status->total_profit_times }}</td>
            <td class="col-sm-4">{{ __('admin.txn.PL') }}：{{ $status->getPL() }}</td>
        </tr>
        <tr>
            <td class="col-sm-4">{{ __('admin.txn.total_number_of_short_times') }}：{{ $status->total_number_of_short_times }}</td>
            <td class="col-sm-4">{{ __('admin.txn.total_loss_times') }}：{{ $status->total_loss_times }}</td>
            <td class="col-sm-4">{{ __('admin.txn.PF') }}：{{ $status-> getPF()}}</td>
        </tr>
        <tr>
            <td class="col-sm-4">{{ __('admin.txn.total_number_of_long_times') }}：{{ $status->total_number_of_long_times }}</td>
            <td class="col-sm-4">{{ __('admin.txn.total_profit') }}：{{ $status->total_profit }}</td>
            <td class="col-sm-4">{{ __('admin.txn.RM') }}：{{ $status->getRM() }}</td>
        </tr>
        <tr>
            <td class="col-sm-4">{{ __('admin.txn.use_leverage') }}：{{ $status->use_leverage }}</td>
            <td class="col-sm-4">{{ __('admin.txn.total_loss') }}：{{ $status->total_loss }}</td>
            <td class="col-sm-4">{{ __('admin.txn.WR') }}：{{ $status->getWR() }}</td>
        </tr>
        <tr>
            <td class="col-sm-4">{{ __('admin.txn.average_capital_risk_during_the_period') }}：{{ $status->average_capital_risk_during_the_period }}</td>
            <td class="col-sm-4">{{ __('admin.txn.profit_and_loss') }}：{{ $status->profit_and_loss }}</td>
            <td class="col-sm-4">{{ __('admin.txn.KellyFormula') }}：{{ $status->KellyFormula }}<br>(Kelly formula)</td>
        </tr>
  </tbody>
</table>
