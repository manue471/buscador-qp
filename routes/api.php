<?php

use App\Http\Controllers\QueroPassagemController;
use Illuminate\Support\Facades\Route;

Route::get('/companies', [QueroPassagemController::class, 'getCompanies']);
Route::get('/stops', [QueroPassagemController::class, 'getStops']);
Route::get('/stops/{keyword}', [QueroPassagemController::class, 'getStopsByKeyword']);

Route::post('/search-order', [QueroPassagemController::class, 'searchOrder']);
