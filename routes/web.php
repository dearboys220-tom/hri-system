<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\StaffAuthController;

Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

Route::get('/register/company', [CompanyController::class, 'create'])->name('register.company');
Route::post('/register/company', [CompanyController::class, 'store'])->name('register.company.store');

Route::middleware('auth')->group(function () {
    // 共通ダッシュボード（fallback）
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // 企業会員
    Route::get('/company/dashboard', function () {
        return Inertia::render('Company/Dashboard');
    })->name('company.dashboard');

    // 個人会員
    Route::get('/applicant/dashboard', function () {
        return Inertia::render('Applicant/Dashboard');
    })->name('applicant.dashboard');

    // 管理チーム
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Admin/Dashboard');
    })->name('admin.dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::prefix('investigator')
            ->name('investigator.')
            ->group(function () {
                Route::get('/', function () {
                    return Inertia::render('Admin/Investigator/InvestigatorMain');
                })->name('index');
            });
        Route::prefix('reviewer')
            ->name('reviewer.')
            ->group(function () {
                Route::get('/', function () {
                    return Inertia::render('Admin/Reviewer/ReviewerMain');
                })->name('index');
            });
    });

    // スタッフ用ログイン
Route::middleware('guest')->group(function () {
    Route::get('/staff/login', [StaffAuthController::class, 'create'])->name('staff.login');
    Route::post('/staff/login', [StaffAuthController::class, 'store'])->name('staff.login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/staff/logout', [StaffAuthController::class, 'destroy'])->name('staff.logout');
});

require __DIR__.'/auth.php';