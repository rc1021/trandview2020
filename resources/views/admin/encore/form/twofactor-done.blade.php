<div id="two_factor_response" class="form-control" style="height: auto; min-height: 34px;">
    <p style="color: #fff;">{{ __('You have enabled two factor authentication.') }}</p>
    <a data-rel="2fa_fire" data-remote="true" data-method="post" href="{{ route('auth.2fa.disable') }}" class="btn btn-danger" style="color: #fff;">{{ __('Disable') }}{{ __('Two Factor Authentication') }}</a>
</div>

<script>
    $(function () {
        $('[data-rel="2fa_fire"]')
            .on('ajax:success', function (event, data, status, xhr) {
                $('#two_factor_response').replaceWith(data);
            })
            .on('ajax:error', function (event, xhr, status, error) {
                console.log([event, xhr, status, error]);
            });
    });
</script>
