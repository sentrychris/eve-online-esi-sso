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

Route::post('/import/{type}/{subtype}', [\App\Http\Controllers\ImportController::class, 'import'])->name('import');

Route::prefix('route')->group(function() {
    Route::get('planner', [\App\Http\Controllers\RouteController::class, 'index'])->name('route.planner');
    Route::post('planner', [\App\Http\Controllers\RouteController::class, 'plan'])->name('route.planner.submit');
});