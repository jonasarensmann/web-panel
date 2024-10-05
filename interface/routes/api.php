<?php

use App\Http\Controllers\Domains\DNSController;
use App\Http\Controllers\Domains\DomainController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(DomainController::class)->group(function () {
        Route::get('/domains', 'api_index');
        Route::get('/domains/{domain}', 'api_show');
    });
    Route::controller(DNSController::class)->group(function () {
        Route::get('/dns/getByName/', 'api_getByName');
        Route::get('/dns/{domain]', 'api,index');
        Route::post('/dns/{domain}', 'api_update');
        Route::delete('/dns/{domain}', 'api_destroy');
        Route::patch('/dns/{domain}', 'api_update');
    });
});
