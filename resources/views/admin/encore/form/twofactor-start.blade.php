<div id="two_factor_response" class="form-control" style="height: auto; min-height: 34px;">
    <p style="color: #fff;">
        {{ __('You have not enabled two factor authentication.') }}
    </p>
    <p class="text-muted">
        {{ __('Add additional security to your account using two factor authentication.') }}
        {{ __("When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone's Google Authenticator application.") }}
    </p>
    <p class="error text-danger"></p>
    <a data-rel="2fa_fire" data-remote="true" data-method="post" href="{{ route('auth.2fa.enable') }}" class="btn btn-primary">{{ __('Enable') }}{{ __('Two Factor Authentication') }}</a>
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
