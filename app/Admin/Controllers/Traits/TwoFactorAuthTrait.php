<?php

namespace App\Admin\Controllers\Traits;

use App\Models\TxnMarginOrder;
use BinanceApi\Enums\OrderType;
use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;
use PragmaRX\Google2FALaravel\Support\Authenticator;
use Illuminate\Http\JsonResponse;

trait TwoFactorAuthTrait
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

    public function twoFactorView()
    {
        $user = $this->guard()->user();
        if(!empty($user->two_factor_secret))
            return view('admin.encore.form.twofactor-done');
        return view('admin.encore.form.twofactor-start');
    }

    public function enableTwoFactor()
    {
        $user = $this->guard()->user();
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

    public function disableTwoFactor()
    {
        $user = $this->guard()->user();
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
