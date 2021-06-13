<div id="two_factor_response" class="form-control" style="height: auto; min-height: 34px;">
    <p style="color: #fff;">
        {{ __("Two factor authentication is now enabled. Scan the following QR code using your phone's authenticator application.") }}
    </p>
    {!! $inlineUrl !!}
    <p style="color: #fff;">
        {{ __("Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.") }}
    </p>
    <div class="bg-gray disabled color-palette" style="padding: 10px; margin-bottom: 5px;">
        @foreach ($arrRecovery as $line)
            <div>{{ $line }}</div>
        @endforeach
    </div>
</div>
