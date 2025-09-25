<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExchangerateController;
use App\Http\Controllers\MovementController;
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

Route::post('/login',[UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout',[UserController::class, 'logout']);
    Route::get('/user',[UserController::class, 'getUser']);

    //User
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/addusers', [UserController::class, 'store']);
    Route::put('/updateusers/{id}', [UserController::class, 'update']);
    Route::delete('/deleteusers/{id}', [UserController::class, 'destroy']);

    //Account
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::get('/accounts/{id}', [AccountController::class, 'show']);
    Route::post('/addaccounts', [AccountController::class, 'store']);
    Route::put('/updateaccounts/{id}', [AccountController::class, 'update']);
    Route::delete('/deleteaccounts/{id}', [AccountController::class, 'destroy']);

    //Client
    Route::get('/clients', [ClientController::class, 'index']);
    Route::get('/clients/{id}', [ClientController::class, 'show']);
    Route::post('/addclients', [ClientController::class, 'store']);
    Route::put('/updateclients/{id}', [ClientController::class, 'update']);
    Route::delete('/deleteclients/{id}', [ClientController::class, 'destroy']);

    //Currency
    Route::get('/currencies', [CurrencyController::class, 'index']);
    Route::get('/currencies/{id}', [CurrencyController::class, 'show']);
    Route::post('/addcurrencies', [CurrencyController::class, 'store']);
    Route::put('/updatecurrencies/{id}', [CurrencyController::class, 'update']);
    Route::delete('/deletecurrencies/{id}', [CurrencyController::class, 'destroy']);

    //Exchange Rate
    Route::get('/exchangerates', [ExchangerateController::class, 'index']);
    Route::get('/exchangerates/{id}', [ExchangerateController::class, 'show']);
    Route::post('/addexchangerates', [ExchangerateController::class, 'store']);
    Route::put('/updateexchangerates/{id}', [ExchangerateController::class, 'update']);
    Route::delete('/deleteexchangerates/{id}', [ExchangerateController::class, 'destroy']);

    //Movement
    Route::get('/movements', [MovementController::class, 'index']);
    Route::get('/movements/{id}', [MovementController::class, 'show']);
    Route::post('/addmovements', [MovementController::class, 'store']);
    Route::put('/updatemovements/{id}', [MovementController::class, 'update']);
    Route::delete('/deletemovements/{id}', [MovementController::class, 'destroy']);

    //DashboardController
    Route::get('/total-balance', [DashboardController::class, 'totalBalance']);
    Route::get('/deposits-summary', [DashboardController::class, 'depositsSummary']);
    Route::get('/withdrawals-summary', [DashboardController::class, 'withdrawalsSummary']);
    Route::get('/total-clients', [DashboardController::class, 'totalClients']);
    Route::get('/financial-summary', [DashboardController::class, 'financialSummary']);
    Route::get('/exchange-rates-donut', [DashboardController::class, 'exchangeRatesDonut']);
    Route::get('/last-clients', [DashboardController::class, 'lastClients']);
    Route::get('/last-movements', [DashboardController::class, 'lastMovements']);

});
