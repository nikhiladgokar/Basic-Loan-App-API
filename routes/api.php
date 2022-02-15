<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanController;

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

//routes: before authentication
Route::group(['prefix' => '/v1','middleware' => ['auth:api']], function () {
    Route::group(['prefix' => '/loan'], function () {
        Route::get('/calculate', [LoanController::class, 'calculate']);
        Route::post('/apply', [LoanController::class, 'apply']);
        Route::post('/{loanId}/approve', [LoanController::class, 'approve']);
        Route::post('/{loanId}/repay', [LoanController::class, 'repay']);
        Route::get('/{loanId}/details', [LoanController::class, 'show']);
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
