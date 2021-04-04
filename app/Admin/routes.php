<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');
    $router->get('notify-cancel', 'AuthController@lineNotifyCancel')->name('admin-line-notify.cancel');
    $router->get('notify-callback', 'AuthController@lineNotifyCallback')->name('admin-line-notify.callback');

    Route::group([
        'prefix'        => 'txn',
        'as'            => 'txn.',
    ], function (Router $router) {
        $router->get('key-secrets', AuthController::class.'@getKeySecret')->name('keysecret');
        $router->put('key-secrets', AuthController::class.'@putKeySecret')->name('keysecret');
        $router->get('setting', TransactionController::class.'@setting')->name('setting');
    });

    Route::group([
        'prefix'        => 'txn',
        'namespace'     => 'Transaction',
        'as'            => 'txn.',
    ], function (Router $router) {
        $router->get('logs/calc/{signal_history}', LogController::class.'@calc')->name('logs.calc');
        $router->resource('logs', LogController::class);
        $router->get('formula/{key}/preview', FormulaController::class.'@preview')->name('formula.preview');
        $router->resource('formula', FormulaController::class);
    });
});
