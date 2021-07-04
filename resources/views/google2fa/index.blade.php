@extends('layouts.app')

@push('scripts')

<script>
    $(function () {
        $('#{{ config('google2fa.otp_input') }}')
            .on('keyup', function () {
                if($(this).val().length >= 6)
                    $(this).closest('form').find('[type="submit"]').click();
            });
    });
</script>

@endpush

@section('content')

<div class="register-box">
    <div class="register-logo">
      <a href="{{ admin_url('/') }}">{!! config('admin.logo') !!}</a>
    </div>
  <div class="register-box-body">

    <p class="register-box-msg">
        {{ __('Two Factor Authentication') }}{{ __('Confirm') }}<br>
        {{ __('For your account security, please complete the following verification operations.') }}
    </p>

    <div class="card">

            <form action="{{ url()->current() }}">
                @csrf

                <div class="form-group has-feedback {!! !Session::has(config('google2fa.otp_input')) ?: 'has-error' !!}">
                    @if (Session::has(config('google2fa.otp_input')))
                        <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{ Session::get(config('google2fa.otp_input')) }}</label><br>
                    @endif
                    <input id="{{ config('google2fa.otp_input') }}" type="text" autofocus autocomplete="off" class="form-control @error(config('google2fa.otp_input')) is-invalid @enderror" name="{{ config('google2fa.otp_input') }}" required placeholder="{{ __('Google 2fa') }}">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    <span >{{ __('Please confirm your 6 characters Google 2fa code.') }}</span>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary" data-disable-with="{{ __('Verifying...') }}">
                            {{ __('Confirm') }}{{ __('Code') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
  </div>
</div>
@endsection
