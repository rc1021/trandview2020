@extends('layouts.app')

@section('title'){{ __('Login') }}@endsection

@push('scripts')
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script>
    function onSubmit(token) {
        document.getElementById("frm").submit();
    }
    </script>
@endpush

@section('content')
<div class="login-box">
  <div class="login-logo">
    <a href="{{ admin_url('/') }}">{!! config('admin.logo') !!}</a>
  </div>
  <div class="login-box-body">
    <p class="login-box-msg">{{ trans('admin.login') }}</p>

    <form id="frm" action="{{ admin_url('auth/login') }}" method="post">
      <div class="form-group has-feedback {!! !$errors->has('email') ?: 'has-error' !!}">

        @if($errors->has('email'))
          @foreach($errors->get('email') as $message)
            <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
          @endforeach
        @endif

        <input type="text" class="form-control" placeholder="{{ __('E-Mail Address') }}" name="email" value="{{ old('email') }}" required autofocus>
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback {!! !$errors->has('password') ?: 'has-error' !!}">

        @if($errors->has('password'))
          @foreach($errors->get('password') as $message)
            <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
          @endforeach
        @endif

        <input type="password" class="form-control" placeholder="{{ trans('admin.password') }}" name="password" required>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="row {!! !$errors->has(config('googlerecaptcha.input')) ?: 'has-error' !!}">
        @error(config('googlerecaptcha.input'))
            <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
        @enderror
      </div>
      <div class="row">
        <div class="col-xs-8">
          @if(config('admin.auth.remember'))
          <div class="checkbox icheck">
            <label>
              <input type="checkbox" name="remember" value="1" {{ (!old('username') || old('remember')) ? 'checked' : '' }}>
              {{ trans('admin.remember_me') }}
            </label>
          </div>
          @endif
        </div>
        <div class="col-xs-4">
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <button class="g-recaptcha btn btn-primary btn-block btn-flat"
            data-sitekey="{{ config('googlerecaptcha.client_id') }}"
            data-callback='onSubmit'
            data-action='submit'>{{ trans('admin.login') }}</button>
        </div>
      </div>
    </form>

    <div class="social-auth-links text-center">
      {{--  <p>- OR -</p>
      <a href="#" class="btn btn-block btn-social btn-facebook btn-flat" style="color: #fff;"><i class="fa fa-facebook"></i> Sign up using
        Facebook</a>
      <a href="#" class="btn btn-block btn-social btn-google btn-flat" style="color: #fff;"><i class="fa fa-google-plus"></i> Sign up using
        Google+</a>  --}}
    </div>

    <div class="text-center">
        <a href="{{ route('register') }}">{{ __('Create Account') }}</a>

        @if (Route::has('password.request'))
        <span>&nbsp;|&nbsp;</span>
        <a href="{{ route('password.request') }}">
            {{ __('Forgot Your Password?') }}
        </a>
    @endif
    </div>
  </div>
</div>
@endsection
