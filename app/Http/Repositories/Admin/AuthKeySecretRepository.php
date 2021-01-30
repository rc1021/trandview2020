<?php

namespace App\Http\Repositories\Admin;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use App\Models\KeySecret;
use App\Models\AdminUser;
use App\Enums\PerformanceApiType;
use Illuminate\Http\Request;
use Zxing\QrReader;

class AuthKeySecretRepository
{
    public function getKeySecret(Content $content)
    {
        $form = $this->keySecretForm();

        $form->divider(__('admin.auth.keysecret.or_qrcode_upload'));
        $form->image('qrcode', __('File upload'))
             ->help(__('admin.auth.keysecret.or_qrcode_upload_helper'));

        $form->tools(function (Form\Tools $tools) {
            $tools->disableList();
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        $user = AdminUser::find(Admin::user()->id);
        $keysecret = $user->keysecrets()->first();
        if(!$keysecret)
            $keysecret = $user->keysecrets()->create([
                'alias' => md5(uniqid($user->id, true)),
                'type' => PerformanceApiType::Binance,
                'key' => '',
                'secret' => ''
            ]);

        return $content
            ->title(trans('admin.auth.keysecret.title'))
            ->body($form->edit($keysecret->id));
    }

    public function putKeySecret(Request $request)
    {
        try {
            if($request->hasFile('qrcode')) {
                $qrcode = new QrReader($request->qrcode->path());
                $text = $qrcode->text();
                $result = json_decode($text, false, 512, JSON_THROW_ON_ERROR);
                if(json_last_error() == JSON_ERROR_NONE) {
                    if(!empty($result->apiKey) && !empty($result->secretKey)) {
                        $request->merge([
                            'key' => $result->apiKey,
                            'secret' => $result->secretKey,
                        ]);
                    }
                    if(!empty($result->comment))
                        $request->merge([
                            'alias' => $result->comment,
                        ]);
                }
            }
        }
        catch (\JsonException $e) {
            admin_toastr(__('QR Code parse error.'), 'warning');
            return redirect(admin_url('auth/key-secrets'));
        }
        catch(Exception $e) { /* fetch qr code from image error */ }
        finally { $request->files->remove('qrcode'); }

        $user = AdminUser::find(Admin::user()->id);
        $keysecret = $user->keysecrets()->first();
        return $this->keySecretForm()->update($keysecret->id);
    }

    protected function keySecretForm()
    {
        $form = new Form(new KeySecret());
        $form->setAction(admin_url('auth/key-secrets'));

        // $form->display('type', __('admin.auth.keysecret.type'))->with(function ($value) {
        //     return PerformanceApiType::getDescription($value);
        // });
        $form->text('alias', __('admin.auth.keysecret.alias'))->rules('required')
             ->help(__('admin.auth.keysecret.alias_unique'));
        $form->text('key', __('admin.auth.keysecret.apikey'))->rules('required');
        $form->text('secret', __('admin.auth.keysecret.secretkey'))->rules('required');

        $form->saved(function () {
            admin_toastr(trans('admin.update_succeeded'));
            return redirect(admin_url('auth/key-secrets'));
        });

        return $form;
    }

}
