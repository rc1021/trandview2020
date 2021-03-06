@extends('layouts.app')

@section('title'){{ __('Register') }}@endsection

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
    <p class="login-box-msg">{{ __('Register') }}</p>

    <form id="frm" method="POST" action="{{ route('register') }}">
        @csrf
      <div class="form-group has-feedback {!! !$errors->has('name') ?: 'has-error' !!}">
        @error('name')
            <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
        @enderror
        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="{{ __('Name') }}" required autocomplete="name" autofocus>
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback {!! !$errors->has('email') ?: 'has-error' !!}">
        @error('email')
            <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
        @enderror
        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="{{ __('E-Mail Address') }}">
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback {!! !$errors->has('password') ?: 'has-error' !!}">
        @error('password')
            <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
        @enderror
        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="{{ __('Password') }}">
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="{{ __('Confirm Password') }}">
        <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
      </div>
      <div class="row">
        <div class="col-xs-12 {!! !$errors->has('agree') ?: 'has-error' !!}">
        @error('agree')
            <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
        @enderror
          <div class="checkbox icheck">
            <label>
              <input type="checkbox" name="agree" value="1">
              {!! __('I agree to the :terms_of_service and :privacy_policy', [
                'terms_of_service' => '<a href="#">'.__('Terms of Service').'</a>',
                'privacy_policy' => '<a href="#">'.__('Privacy Policy').'</a>',
              ]) !!}
            </label>
          </div>
        </div>
        <!-- /.col -->
        <div class="col-xs-12 {!! !$errors->has(config('googlerecaptcha.input')) ?: 'has-error' !!}">
            @error(config('googlerecaptcha.input'))
                <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
            @enderror
          <button class="g-recaptcha btn btn-primary btn-block btn-flat"
            data-sitekey="{{ config('googlerecaptcha.client_id') }}"
            data-callback='onSubmit'
            data-action='submit'>{{ __('Register') }}</button>
        </div>
        <!-- /.col -->
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
        <a href="{{ route('admin.login') }}" class="text-center">{{ __('Already registered?') }}</a>
    </div>
  </div>
  <!-- /.form-box -->
</div>

@endsection
