<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\MealController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('register', [AuthController::class, 'register'])->name('register');

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::get('/{slug}', [CategoryController::class, 'show'])->name('show');
        });

        Route::prefix('meals')->name('meals.')->group(function () {
            Route::get('/', [MealController::class, 'index'])->name('index');
            Route::get('/{slug}', [MealController::class, 'show'])->name('show');
        });

        Route::apiResource('addresses', AddressController::class);
        Route::patch('addresses/{address}/set-default', [AddressController::class, 'setDefault'])->name('addresses.set-default');

        Route::prefix('favorites')->name('favorites.')->middleware('throttle:60,1')->group(function () {
            Route::get('/', [FavoriteController::class, 'index'])->name('index');
            Route::post('/', [FavoriteController::class, 'store'])->name('store');
            Route::delete('/{meal}', [FavoriteController::class, 'destroy'])->name('destroy');
            Route::get('/check/{meal}', [FavoriteController::class, 'check'])->name('check');
        });


        Route::get('profile', [ProfileController::class, 'show'])->name('profile');
        Route::put('profile', [ProfileController::class, 'updateProfile'])->name('updateProfile');
        Route::put('change-password', [ProfileController::class, 'changePassword'])->name('changePassword');

        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
});
