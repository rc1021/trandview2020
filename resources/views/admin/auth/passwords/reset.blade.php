@extends('layouts.app')

@section('content')

<div class="register-box">
    <div class="register-logo">
      <a href="{{ admin_url('/') }}">{!! config('admin.logo') !!}</a>
    </div>
  <div class="register-box-body">

    <p class="register-box-msg">{{ __('Reset Password') }}</p>

    <div class="card">

        <div class="card-body">
            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group has-feedback {!! !$errors->has('email') ?: 'has-error' !!}">
                    @error('email')
                        <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
                    @enderror
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus placeholder="{{ __('E-Mail Address') }}">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>

                <div class="form-group has-feedback {!! !$errors->has('password') ?: 'has-error' !!}">
                    @error('password')
                        <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
                    @enderror
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="{{ __('Password') }}">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>

                <div class="form-group has-feedback">
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="{{ __('Confirm Password') }}">
                    <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Reset Password') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
  </div>
</div>
@endsection
