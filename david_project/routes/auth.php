<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/auth/register', [AuthController::class, 'create'])->name('register.create');
Route::post('/auth/register', [AuthController::class, 'store'])->name('register.store');
Route::get('/auth/login', [AuthController::class, 'loginCreate'])->name('login.create');
Route::post('/auth/login',    [AuthController::class, 'loginPost'])  ->name('login.post');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/login', fn () => redirect()->route('login.create'))->name('login');