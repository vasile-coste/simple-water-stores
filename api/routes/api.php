<?php

use App\Http\Controllers\Store;
use App\Http\Controllers\Water;
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



Route::group(['prefix' => 'store'], function () {
    Route::get('find/{store_id}', [Store::class, 'getStore']);
    Route::get('all', [Store::class, 'getAllStores']);
    Route::post('add', [Store::class, 'addStore']);
    Route::post('update', [Store::class, 'updateStore']);
    Route::post('delete', [Store::class, 'deleteStore']);
});

Route::group(['prefix' => 'water'], function () {
    Route::get('random/{store_id}/{water_id}', [Water::class, 'randomUpdateWaterStock']);
    Route::get('{store_id}/all', [Water::class, 'getAllWater']);
    Route::post('add', [Water::class, 'addWater']);
    Route::post('update', [Water::class, 'updateWater']);
    Route::post('delete', [Water::class, 'deleteWater']);
});