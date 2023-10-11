<?php

use App\Http\Controllers\ResponseController;
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

Route::get('/patreon', [ResponseController::class, 'index']);
Route::patch('/patreon/update', [ResponseController::class, 'update']);
Route::get('/last-updated', [ResponseController::class, 'lastUpdated']);
