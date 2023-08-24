<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DomainController;
use App\Http\Controllers\API\LanguageController;
use App\Http\Controllers\API\LrController;
use App\Http\Controllers\API\ControlController;
use App\Http\Controllers\API\SettingsController;
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

Route::post('login', [AuthController::class, 'signin']);
//Route::post('register', [AuthController::class, 'signup']);
     
Route::middleware('auth:sanctum')->group( function () {
    Route::resource('domains', DomainController::class);
    Route::post('/domains/add', [DomainController::class, 'store_arr']);
    Route::post('/domains/check', [DomainController::class, 'check']);
    Route::post('/domains/get_did', [DomainController::class, 'get_did']);
    Route::post('/domains/get_domain', [DomainController::class, 'get_domain']);
    Route::resource('languages', LanguageController::class);
    Route::resource('lrs', LrController::class);
    Route::resource('controls', ControlController::class);
    Route::resource('user-settings', SettingsController::class);
    
    //Route::get('/settings/{uid}/{type?}', [SettingsController::class, 'show']);
    //Route::get('/settings/search/{uid}', function (Request $request, $uid) {
      //return [SettingsController::class, 'search'];
    //});
    
    Route::get('/user-settings/search/{uid}', [SettingsController::class, 'search']);
    Route::post('/user-settings/update/{uid}', [SettingsController::class, 'update2']);
    Route::get('/controls/set_lr/{cid}/{lr}', [ControlController::class, 'set_cid_lr']);
    Route::get('/controls/did_data/{uid}', [ControlController::class, 'get_did_data']);
    Route::get('/groups/{uid}', [ControlController::class, 'get_group_name']);
    Route::get('/lrs/search/{search}', [LrController::class, 'search']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
