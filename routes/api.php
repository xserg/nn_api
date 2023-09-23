<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DomainController;
use App\Http\Controllers\API\LanguageController;
use App\Http\Controllers\API\LrController;
use App\Http\Controllers\API\ControlController;
use App\Http\Controllers\API\SettingsController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\API\NotifyController;
use App\Http\Controllers\API\SchedulerController;
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

Route::post('/token/add', [AuthController::class, 'signin']);
Route::delete('/token/delete', [AuthController::class, 'delete_token']);
Route::post('/token/update', [AuthController::class, 'update_token']);
//Route::post('register', [AuthController::class, 'signup']);

Route::resource('domains', DomainController::class)->middleware(['auth:sanctum', 'ability:domain-get']);
Route::post('/domains/add', [DomainController::class, 'store_arr'])->middleware(['auth:sanctum', 'ability:domain-add']);
Route::post('/domains/check', [DomainController::class, 'check'])->middleware(['auth:sanctum', 'ability:domain-check']);
Route::post('/domains/get_did', [DomainController::class, 'get_did'])->middleware(['auth:sanctum', 'ability:domain-get-did']);
Route::post('/domains/get_domain', [DomainController::class, 'get_domain'])->middleware(['auth:sanctum', 'ability:domain-get-domain']);
Route::resource('languages', LanguageController::class)->middleware(['auth:sanctum', 'ability:languages-get']);
Route::get('/lrs', [LrController::class, 'index'])->middleware(['auth:sanctum', 'ability:lrs-get']);
Route::get('/lrs/{lr}', [LrController::class, 'show'])->middleware(['auth:sanctum', 'ability:lrs-get']);
Route::get('/lrs/search/{search}', [LrController::class, 'search'])->middleware(['auth:sanctum', 'ability:lrs-get']); 

Route::get('/user-settings/search/{uid}', [SettingsController::class, 'search'])->middleware(['auth:sanctum', 'ability:settings-search']);
Route::get('/user-settings/{uid}', [SettingsController::class, 'search'])->middleware(['auth:sanctum', 'ability:settings-get']);
Route::post('/user-settings/{uid}', [SettingsController::class, 'store'])->middleware(['auth:sanctum', 'ability:settings-add']);
Route::get('/user-settings/{uid}/{sid}', [SettingsController::class, 'show'])->middleware(['auth:sanctum', 'ability:settings-get']);
Route::post('/user-settings/{uid}/update', [SettingsController::class, 'update2'])->middleware(['auth:sanctum', 'ability:settings-update']);
Route::delete('/user-settings/{uid}/{sid}', [SettingsController::class, 'destroy'])->middleware(['auth:sanctum', 'ability:settings-delete']);

Route::get('/control/{uid}/groups', [ControlController::class, 'get_group_name'])->middleware(['auth:sanctum', 'ability:control-groups']);  
Route::post('/control/{uid}/add/', [ControlController::class, 'store'])->middleware(['auth:sanctum', 'ability:control-add']);
Route::get('/control/{uid}/{cid}/set_lr/{lr}', [ControlController::class, 'set_cid_lr'])->middleware(['auth:sanctum', 'ability:control-set-lr']);
Route::get('/control/did_data/{uid}', [ControlController::class, 'get_did_data'])->middleware(['auth:sanctum', 'ability:control-get-did-data']);
Route::get('/control/{uid}/{cid}', [ControlController::class, 'get_did_data'])->middleware(['auth:sanctum', 'ability:control-get']);
Route::get('/control/{cid}', [ControlController::class, 'show'])->middleware(['auth:sanctum', 'ability:control-get']);
Route::delete('/control/{uid}/{cid}', [ControlController::class, 'destroy'])->middleware(['auth:sanctum', 'ability:control-delete']);

Route::resource('tasks', TaskController::class)->middleware('auth:sanctum');
Route::resource('notify', NotifyController::class)->middleware('auth:sanctum');

Route::get('/notify/get/{uid}/{status}', [NotifyController::class, 'search'])->middleware(['auth:sanctum', 'ability:notify-get']);
Route::post('/notify/set_status', [NotifyController::class, 'set_status'])->middleware(['auth:sanctum', 'ability:notify-set']);
Route::get('/schedulers/get/{uid}', [SchedulerController::class, 'search'])->middleware(['auth:sanctum', 'ability:scheduler-get']);

Route::post('/schedulers/get_cid', [SchedulerController::class, 'get_cid'])->middleware(['auth:sanctum', 'ability:scheduler-get']);
Route::get('/schedulers/get_expect', [SchedulerController::class, 'get_expect'])->middleware(['auth:sanctum', 'ability:scheduler-get']);
Route::post('/schedulers/is_exists', [SchedulerController::class, 'is_exists'])->middleware(['auth:sanctum', 'ability:scheduler-get']);
Route::post('/schedulers/next_time', [SchedulerController::class, 'next_time'])->middleware(['auth:sanctum', 'ability:scheduler-get']);
Route::post('/schedulers/update_time/{sid}', [SchedulerController::class, 'update_time'])->middleware(['auth:sanctum', 'ability:scheduler-update']);
Route::post('/schedulers/update/{sid}', [SchedulerController::class, 'update'])->middleware(['auth:sanctum', 'ability:scheduler-update']);
Route::resource('schedulers', SchedulerController::class)->middleware('auth:sanctum');
