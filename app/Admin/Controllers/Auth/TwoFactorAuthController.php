<?php

namespace App\Admin\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use PragmaRX\Google2FALaravel\Support\Authenticator;
use Illuminate\Http\JsonResponse;
use Encore\Admin\Facades\Admin;

class TwoFactorAuthController extends Controller
{
    public function verifyTwoFactor(Request $request)
    {
        $authenticator = app(Authenticator::class)->bootStateless($request);
        if ($authenticator->isAuthenticated()) {
            return $request->wantsJson()
                        ? new JsonResponse([], 204)
                        : redirect()->back();
        }
        return "otp auth f!";
    }

    public function enableTwoFactor(Request $request)
    {
        $user = $request->user();
        if(!empty($user->two_factor_secret))
            return view('admin.encore.form.twofactor-done');
        else {
            $google2fa = app('pragmarx.google2fa');
            $user->two_factor_secret = $google2fa->generateSecretKey();
            $inlineUrl = $google2fa->getQRCodeInline(
                config('app.name'),
                $user->email,
                $user->two_factor_secret
            );
            $recovery = new \PragmaRX\Recovery\Recovery;
            $arrRecovery = json_decode((string)$recovery->toJson(), true);
            $user->two_factor_recovery_codes = $arrRecovery;
            $user->save();
            return view('admin.encore.form.twofactor-show', compact('inlineUrl', 'arrRecovery'));
        }
    }

    public function disableTwoFactor(Request $request)
    {
        $user = $request->user();
        if(empty($user->two_factor_secret))
            return view('admin.encore.form.twofactor-start');
        else {
            $user->two_factor_secret = null;
            $user->two_factor_recovery_codes = null;
            $user->save();
            return view('admin.encore.form.twofactor-start');
        }
    }
}
