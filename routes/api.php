<?php

use App\Http\Controllers\QueroPassagemController;
use Illuminate\Support\Facades\Route;

Route::get('/companies', [QueroPassagemController::class, 'getCompanies']);
Route::get('/stops/{keyword}', [QueroPassagemController::class, 'getStops']);

Route::post('/search-order', [QueroPassagemController::class, 'searchOrder']);
Route::post('/seat-search', [QueroPassagemController::class, 'getSeats']);
