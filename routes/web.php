<?php

use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Dashboard\Dashboard;
use App\Http\Controllers\MeterController;
use App\Http\Controllers\MeterList;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('auth.login');
})->name('auth.landing');

Route::post('login', [Login::class, 'login'])->name('auth.login');
Route::post('logout', [Login::class, 'logout'])->name('auth.logout');

// Route::middleware(['apiauth'])->group(function () {

    Route::get('dashboard', [Dashboard::class, 'index'])->name('dashboard.index');
    // Route::post('/meter-list', [MeterList::class, 'list'])->name('meter.list');
    // Route::post('/meter-update', [MeterController::class, 'update'])->name('meter.update');

    Route::post('/api/meter/list', [MeterController::class, 'list']);
Route::post('/api/meter/update', [MeterController::class, 'update']);

// });
