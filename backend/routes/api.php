<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeatureController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// Register
Route::post('/register', [AuthController::class, 'register']);
// Login
Route::post('/login', [AuthController::class, 'login']);
// sources
Route::get('/sources', [FeatureController::class, 'getSources']);
// categories
Route::get('/categories', [FeatureController::class, 'getCategories']);
// search
Route::get('/search', [FeatureController::class, 'search']);
// preference
Route::get('/preference', [FeatureController::class, 'preferenceSearch']);


