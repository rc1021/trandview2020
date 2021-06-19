@extends('layouts.app')

@section('title'){{ __('Reset Password') }}@endsection

@push('scripts')
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script>
    function onSubmit(token) {
        document.getElementById("frm").submit();
    }
    </script>
@endpush

@section('content')

<div class="register-box">
    <div class="register-logo">
      <a href="{{ admin_url('/') }}">{!! config('admin.logo') !!}</a>
    </div>
  <div class="register-box-body">

    <p class="register-box-msg">{{ __('Reset Password') }}</p>

    <div class="card">

        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form id="frm" method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group has-feedback {!! !$errors->has('email') ?: 'has-error' !!}">
                    @error('email')
                        <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
                    @enderror
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="{{ __('E-Mail Address') }}">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-md-12">
                        <button class="g-recaptcha btn btn-primary"
                        data-sitekey="{{ config('googlerecaptcha.client_id') }}"
                        data-callback='onSubmit'
                        data-action='submit'>
                            {{ __('Send Password Reset Link') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
  </div>
</div>
@endsection
