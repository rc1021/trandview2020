<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AuthController as BaseAuthController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use App\Http\Repositories\Admin\AuthKeySecretRepository;
use App\Admin\Controllers\Traits\LINENotifyFunc;
use Encore\Admin\Form;

class AuthController extends BaseAuthController
{
    use LINENotifyFunc;

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        return 'email';
    }

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
        $class = config('admin.database.users_model');

        $form = new Form(new $class());

        $form->email('email', trans('admin.username'))->disable();
        $form->text('name', trans('admin.name'))->rules('required');
        $form->image('avatar', trans('admin.avatar'));
        $form->password('password', trans('admin.password'))->rules('confirmed|required');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->setAction(admin_url('auth/setting'));

        $form->ignore(['password_confirmation']);

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        $form->saved(function () {
            admin_toastr(trans('admin.update_succeeded'));

            return redirect(admin_url('auth/setting'));
        });

        $form->slider('vip_level', 'VIP Level')->options([
            'max'       => 9,
            'min'       => 0,
            'step'      => 1,
            'prefix'    => 'Vip ',
        ]);
        $form->linenotify('line_notify_token', 'LINE 通知')->attribute([
            'readonly'=>true,
            'data-callbackurl' => route('admin-line-notify.callback', ['id' => Admin::user()->id]),
            'data-cancelurl' => route('admin-line-notify.cancel', ['id' => Admin::user()->id]),
            'data-lineclientid' => config('app.line_notify_client_id')
            ]);
        return $form;
    }

    public function verifyNotice(Request $request)
    {
        return 5;
    }
}
