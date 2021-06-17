<?php

namespace App\Listeners;
use Illuminate\Support\Facades\Session;

class Google2FALaravelEventListener
{
    public function onLoginFailed($event)
    {
        Session::flash(config('google2fa.otp_input'), __('The provided two factor authentication code was invalid.'));
    }

    /**
     * 註冊監聽器的訂閱者。
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'PragmaRX\Google2FALaravel\Events\LoginFailed',
            'App\Listeners\Google2FALaravelEventListener@onLoginFailed'
        );
    }

}
