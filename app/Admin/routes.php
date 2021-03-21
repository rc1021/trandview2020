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

    $router->get('auth/key-secrets', 'AuthController@getKeySecret')->name('keysecret');
    $router->put('auth/key-secrets', 'AuthController@putKeySecret');

    $router->get('auth/transaction/setting', TransactionController::class.'@setting')->name('transaction.setting');
    $router->get('auth/transaction/logs/calc/{signal_history}', TransactionLogController::class.'@calc')->name('logs.calc');
    $router->resource('auth/transaction/logs', TransactionLogController::class);

    $router->get('formula-tables/{key}/preview', FormulaTableController::class.'@preview')->name('formula.preview');
    $router->resource('formula-tables', FormulaTableController::class);
});
