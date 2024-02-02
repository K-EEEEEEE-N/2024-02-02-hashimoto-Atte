<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [AttendanceController::class, 'index']);
Route::post('/register', [AttendanceController::class, 'register']);
Route::post('/login', [AttendanceController::class, 'login'])
    ->middleware(['guest'])
    ->name('login');
Route::get('/attendance', [AttendanceController::class, 'attendance']);
Route::middleware('auth')->group(function () {
    Route::get('/', [AttendanceController::class, 'index']);
});

// フォームからの勤務開始アクション
Route::post('/beginWork', [AttendanceController::class, 'beginWork'])->name('beginWork');

// フォームからの勤務終了アクション
Route::post('/endWork', [AttendanceController::class, 'endWork'])->name('endWork');

// フォームからの休憩開始アクション
Route::post('/beginBreak', [AttendanceController::class, 'beginBreak'])->name('beginBreak');

// フォームからの休憩終了アクション
Route::post('/endBreak', [AttendanceController::class, 'endBreak'])->name('endBreak');

//ユーザー一覧ページ
Route::get('/users', [AttendanceController::class, 'users']);
