<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AuthController as BaseAuthController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use App\Http\Repositories\Admin\AuthKeySecretRepository;
use App\Admin\Controllers\Traits\LINENotifyFunc;

class AuthController extends BaseAuthController
{
    use LINENotifyFunc;

    public function getKeySecret(Content $content, AuthKeySecretRepository $rep)
    {
        return $rep->getKeySecret($content);
    }

    public function putKeySecret(Request $request, AuthKeySecretRepository $rep)
    {
        return $rep->putKeySecret($request);
    }

    protected function settingForm()
    {
        $form = parent::settingForm();
        $form->slider('vip_level', 'VIP Level')->options([
            'max'       => 9,
            'min'       => 0,
            'step'      => 1,
            'prefix'    => 'Vip ',
        ]);
        $form->linenotify('line_notify_token', 'LINE 通知')->attribute([
            'readonly'=>true,
            'data-callbackurl' => route('admin.admin-line-notify.callback', ['username' => Admin::user()->username]),
            'data-cancelurl' => route('admin.admin-line-notify.cancel', ['username' => Admin::user()->username]),
            'data-lineclientid' => config('app.line_notify_client_id')
            ]);
        return $form;
    }
}
