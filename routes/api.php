<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\MealController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function(){
    Route::prefix('categories')->name('categories.')->group(function(){
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/{slug}', [CategoryController::class, 'show'])->name('show');
    });
    Route::prefix('meals')->name('meals.')->group(function(){
        Route::get('/', [MealController::class, 'index'])->name('index');
        Route::get('/{slug}', [MealController::class, 'show'])->name('show');
    });
});
