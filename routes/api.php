<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('signal', [App\Http\Controllers\API\SignalController::class, 'fire'])->name('signal');
Route::post('margin/signal', [App\Http\Controllers\API\SignalController::class, 'fire'])->name('margin.signal');
Route::post('feature/signal', [App\Http\Controllers\API\FeatureSignalController::class, 'fire'])->name('feature.signal');
