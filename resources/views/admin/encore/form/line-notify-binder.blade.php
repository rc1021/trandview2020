<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')
        <div class="input-group">

            @if ($prepend)
            <span class="input-group-addon">{!! $prepend !!}</span>
            @endif

            <input {!! $attributes !!} />

            @if ($append)
                <span class="input-group-btn clearfix">{!! $append !!}</span>
            @endif

        </div>

        @include('admin::form.help-block')
    </div>
</div>

<script>
    var status = "{{Session::get('status')}}";
    if ( status !== ""){
        setTimeout(function(){
            toastr.success(status);
        }, 500);
    }
    //
    function oAuth2() {
        var cancelable = "{{$cancelable}}";
        if (cancelable === "false") {
            var lineClientId = $('input[id="line_notify_token"]').data('lineclientid');
            var callbackUrl = $('input[id="line_notify_token"]').data('callbackurl');
            var URL = 'https://notify-bot.line.me/oauth/authorize?';
            URL += 'response_type=code';
            URL += '&client_id='+lineClientId;
            URL += '&redirect_uri='+callbackUrl;
            URL += '&scope=notify';
            URL += '&state=NO_STATE';
            window.location.href = URL;
        } else {
            var cancelurl = $('input[id="line_notify_token"]').data('cancelurl');
            window.location.href = cancelurl;
        }

    }

    window.oAuth2 = oAuth2;
</script>
