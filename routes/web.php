<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\StaffAuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Applicant\DashboardController as ApplicantDashboardController;
use App\Http\Controllers\ConsentController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\Applicant\IdentityController;
use App\Http\Controllers\Applicant\ConfirmationController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

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
    Route::get('/company/dashboard', [CompanyController::class, 'dashboard'])->name('company.dashboard');

    // 企業会員プロフィール
    Route::get('/company/profile', [CompanyController::class, 'showProfile'])->name('company.profile');
    Route::post('/company/profile', [CompanyController::class, 'updateProfile'])->name('company.profile.update');

    // 個人会員
    Route::get('/applicant/dashboard', [ApplicantDashboardController::class, 'index'])
        ->name('applicant.dashboard');

    // 管理チーム
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Admin/Dashboard');
    })->name('admin.dashboard');

    Route::get('/applicant/consent', [ConsentController::class, 'show'])->name('applicant.consent');
    Route::post('/applicant/consent', [ConsentController::class, 'store'])->name('applicant.consent.store');

    // ★ここに追加★
    Route::get('/applicant/cv', [CvController::class, 'index'])->name('applicant.cv');
    Route::post('/applicant/cv/profile', [CvController::class, 'updateProfile'])->name('applicant.cv.profile.update');
    Route::post('/applicant/cv/education', [CvController::class, 'storeEducation'])->name('applicant.cv.education.store');
    Route::delete('/applicant/cv/education/{id}', [CvController::class, 'destroyEducation'])->name('applicant.cv.education.destroy');
    Route::post('/applicant/cv/education/{id}', [CvController::class, 'updateEducation'])->name('applicant.cv.education.update');
    Route::post('/applicant/cv/work', [CvController::class, 'storeWork'])->name('applicant.cv.work.store');
    Route::post('/applicant/cv/work/{id}', [CvController::class, 'updateWork'])->name('applicant.cv.work.update');
    Route::delete('/applicant/cv/work/{id}', [CvController::class, 'destroyWork'])->name('applicant.cv.work.destroy');
    Route::post('/applicant/cv/certification', [CvController::class, 'storeCertification'])->name('applicant.cv.certification.store');
    Route::post('/applicant/cv/certification/{id}', [CvController::class, 'updateCertification'])->name('applicant.cv.certification.update');
    Route::delete('/applicant/cv/certification/{id}', [CvController::class, 'destroyCertification'])->name('applicant.cv.certification.destroy');
    Route::get('/applicant/identity', [IdentityController::class, 'show'])->name('applicant.identity');
    Route::post('/applicant/identity', [IdentityController::class, 'update'])->name('applicant.identity.update');  
    Route::get('/applicant/confirmation', [ConfirmationController::class, 'show'])->name('applicant.confirmation');
    Route::post('/applicant/confirmation', [ConfirmationController::class, 'store'])->name('applicant.confirmation.store');
    Route::get('/applicant/profile', [App\Http\Controllers\Applicant\ProfileController::class, 'show'])->name('applicant.profile');
    Route::post('/applicant/profile', [App\Http\Controllers\Applicant\ProfileController::class, 'update'])->name('applicant.profile.update');

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

// Google OAuth（個人会員のみ）
Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
});

require __DIR__.'/auth.php';