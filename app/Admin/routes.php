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

    // Route::group([
    //     'prefix'        => 'txn/features',
    //     'namespace'     => 'Transaction',
    //     'as'            => 'txn.features.',
    // ], function (Router $router) {
    //     $router->get('logs/calc/{signal_history}', FeaturesLogController::class.'@calc')->name('logs.calc');
    //     $router->resource('logs', FeaturesLogController::class);
    //     $router->get('formula/{key}/preview', FeaturesFormulaController::class.'@preview')->name('formula.preview');
    //     $router->resource('formula', FeaturesFormulaController::class);
    // });
});
