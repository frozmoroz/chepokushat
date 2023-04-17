<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecipeController;

Auth::routes();

// Основные роуты
Route::prefix('recipe')->group(function () {
    Route::get('/{id}', [RecipeController::class, 'find']);
    Route::get('/search/{string}', [RecipeController::class, 'search']);
});

// Какая то магия для фронта
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('{all}', function () {
    return view('home');
})->where('all', '.*');
