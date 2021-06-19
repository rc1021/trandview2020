@extends('layouts.app')

@section('title'){{ __('Confirm Password') }}@endsection

@section('content')

<div class="register-box">
    <div class="register-logo">
      <a href="{{ admin_url('/') }}">{!! config('admin.logo') !!}</a>
    </div>
  <div class="register-box-body">

    <p class="register-box-msg">{{ __('Confirm Password') }}</p>

    <div class="card">

        <div class="card-body">
            {{ __('Please confirm your password before continuing.') }}

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="form-group has-feedback {!! !$errors->has('password') ?: 'has-error' !!}">
                    @error('password')
                        <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
                    @enderror
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="{{ __('Password') }}">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>

                </div>

                <div class="form-group row mb-0">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Confirm Password') }}
                        </button>

                        @if (Route::has('password.request'))
                            <a class="btn btn-link" href="{{ route('password.request') }}">
                                {{ __('Forgot Your Password?') }}
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
  </div>
</div>
@endsection
