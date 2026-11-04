<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Operator\ReportController;
use App\Http\Controllers\Leader\LeaderDashboardController;
use App\Http\Controllers\Supervisor\SupervisorDashboardController;
use App\Http\Controllers\ExportController;

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        return match(auth()->user()->role) {
            'operator' => redirect()->route('operator.reports.create'),
            'line_leader' => redirect()->route('leader.dashboard'),
            'supervisor' => redirect()->route('supervisor.dashboard'),
            'admin' => redirect()->route('admin.users'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

// Auth
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ============ Operator Routes ============
Route::prefix('operator')->name('operator.')->middleware(['auth', 'role:operator'])->group(function () {
    Route::get('/', fn () => redirect()->route('operator.reports.create'));
    Route::get('/dashboard', fn () => redirect()->route('operator.reports.create'));
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    // Edit laporan setelah submit -> laporan masuk antrian verifikasi Line Leader
    Route::get('/reports/{report}/edit', [ReportController::class, 'edit'])->name('reports.edit');
    Route::put('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');
});

// ============ Line Leader Routes ============
Route::prefix('leader')->name('leader.')->middleware(['auth', 'role:line_leader'])->group(function () {
    Route::get('/dashboard', [LeaderDashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports', [LeaderDashboardController::class, 'reports'])->name('reports');
    Route::get('/reports/{report}', [LeaderDashboardController::class, 'show'])->name('report.show');
    Route::post('/reports/{report}/verify', [LeaderDashboardController::class, 'verify'])->name('report.verify');
    Route::post('/reports/{report}/reject', [LeaderDashboardController::class, 'reject'])->name('report.reject');
    Route::get('/export', [ExportController::class, 'export'])->name('export');
});

// ============ Supervisor Routes ============
Route::prefix('supervisor')->name('supervisor.')->middleware(['auth', 'role:supervisor'])->group(function () {
    Route::get('/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports', [SupervisorDashboardController::class, 'reports'])->name('reports');
    Route::get('/export', [ExportController::class, 'export'])->name('export');
});

// ============ Admin Routes (manajemen user) ============
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', fn () => redirect()->route('admin.users'));
    Route::get('/users', [AdminUserController::class, 'index'])->name('users');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.delete');
});
