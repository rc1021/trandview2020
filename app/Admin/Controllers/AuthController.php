<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AuthController as BaseAuthController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use App\Http\Repositories\Admin\AuthKeySecretRepository;
use App\Admin\Controllers\Traits\LINENotifyFunc;
use App\Admin\Controllers\Traits\TwoFactorAuthTrait;
use Encore\Admin\Form;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;

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

    /**
     * Get a validator for an incoming login request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function loginValidator(array $data)
    {
        return Validator::make($data, [
            config('googlerecaptcha.input') => ['required', 'string', new \App\Rules\GoogleRecapchaV3Case],
            $this->username()   => 'required',
            'password'          => 'required',
        ]);
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

        $form->email('email', trans('admin.email'))->disable();
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

        $form->html($this->twoFactorView(), __('Two Factor Authentication'));

        return $form;
    }

    public function twoFactorView()
    {
        $user = $this->guard()->user();
        if(!empty($user->two_factor_secret))
            return view('admin.encore.form.twofactor-done');
        return view('admin.encore.form.twofactor-start');
    }
}
