<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $authController = config('admin.auth.controller');
    $withoutVerified = ['verified', '2fa'];
    $router->getRoutes()->getByAction($authController.'@getLogin')->withoutMiddleware($withoutVerified);
    $router->getRoutes()->getByAction($authController.'@postLogin')->withoutMiddleware($withoutVerified);
    $router->getRoutes()->getByAction($authController.'@getLogout')->withoutMiddleware($withoutVerified);

    $router->get('/', 'HomeController@index')->name('home');
    $router->get('notify-cancel', 'AuthController@lineNotifyCancel')->name('admin-line-notify.cancel');
    $router->get('notify-callback', 'AuthController@lineNotifyCallback')->name('admin-line-notify.callback');

    // Registration Routes...
    $router->get('register', 'Auth\RegisterController@showRegistrationForm')->name('register')->withoutMiddleware($withoutVerified);
    $router->post('register', 'Auth\RegisterController@register')->withoutMiddleware($withoutVerified);

    // Password Reset Routes...
    Route::group([
        'prefix'        => 'auth/twofactor',
        'as'            => 'auth.2fa.',
    ], function (Router $router) use ($withoutVerified) {
        $router->post('enable', 'Auth\TwoFactorAuthController@enableTwoFactor')->name('enable');
        $router->post('disable', 'Auth\TwoFactorAuthController@disableTwoFactor')->name('disable');
        $router->post('verify', 'Auth\TwoFactorAuthController@verifyTwoFactor')->name('verify');
    });

    // Password Reset Routes...
    Route::group([
        'prefix'        => 'password',
        'as'            => 'password.',
    ], function (Router $router) use ($withoutVerified) {
        $router->get('reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('request')->withoutMiddleware($withoutVerified);
        $router->post('email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('email')->withoutMiddleware($withoutVerified);
        $router->get('reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('reset')->withoutMiddleware($withoutVerified);
        $router->post('reset', 'Auth\ResetPasswordController@reset')->name('update')->withoutMiddleware($withoutVerified);
    });

    // Password Confirmation Routes...
    Route::group([
        'prefix'        => 'password',
        'as'            => 'password.',
    ], function (Router $router) {
        $router->get('confirm', 'Auth\ConfirmPasswordController@showConfirmForm')->name('confirm');
        $router->post('confirm', 'Auth\ConfirmPasswordController@confirm');
    });

    // Email Verification Routes...
    Route::group([
        'prefix'        => 'email',
        'as'            => 'verification.',
    ], function (Router $router) use ($withoutVerified) {
        $router->get('verify', 'Auth\VerificationController@show')->name('notice')->withoutMiddleware($withoutVerified);
        $router->get('verify/{id}/{hash}', 'Auth\VerificationController@verify')->name('verify')->withoutMiddleware($withoutVerified);
        $router->post('resend', 'Auth\VerificationController@resend')->name('resend')->withoutMiddleware($withoutVerified);
    });

    Route::group([
        'prefix'        => 'txn',
        'as'            => 'txn.',
    ], function (Router $router) {
        $router->get('key-secrets', AuthController::class.'@getKeySecret')->name('keysecret')->middleware('2fa.less.pw.confirm');
        $router->put('key-secrets', AuthController::class.'@putKeySecret')->name('keysecret');
    });

    Route::group([
        'prefix'        => 'txn/margin',
        'namespace'     => 'Transaction',
        'as'            => 'txn.margin.',
    ], function (Router $router) {
        $router->get('logs/calc/{signal_history}', MarginLogController::class.'@calc')->name('logs.calc');
        $router->resource('logs', MarginLogController::class);
        $router->get('formula/{key}/preview', MarginFormulaController::class.'@preview')->name('formula.preview');
        $router->resource('formula', MarginFormulaController::class);
        $router->resource('setting', MarginSettingController::class);
        $router->post('force-liquidation/{pair}', MarginLogController::class.'@forceLiquidation')->name('forceLiquidation');
    });

});
