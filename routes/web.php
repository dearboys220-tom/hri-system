<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobController;
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

    // 求人管理
    Route::get('/company/jobs', [JobController::class, 'index'])->name('company.jobs.index');
    Route::get('/company/jobs/create', [JobController::class, 'create'])->name('company.jobs.create');
    Route::post('/company/jobs', [JobController::class, 'store'])->name('company.jobs.store');
    Route::get('/company/jobs/{id}', [JobController::class, 'show'])->name('company.jobs.show');
    Route::get('/company/jobs/{id}/edit', [JobController::class, 'edit'])->name('company.jobs.edit');
    Route::post('/company/jobs/{id}', [JobController::class, 'update'])->name('company.jobs.update');
    Route::delete('/company/jobs/{id}', [JobController::class, 'destroy'])->name('company.jobs.destroy');
    Route::get('/company/jobs/{jobId}/applications', [App\Http\Controllers\CompanyApplicationController::class, 'index'])->name('company.applications.index');
    Route::post('/company/applications/{appId}/status', [App\Http\Controllers\CompanyApplicationController::class, 'updateStatus'])->name('company.applications.status');
    Route::get('/company/applications/{appId}', [App\Http\Controllers\CompanyApplicationController::class, 'show'])->name('company.applications.show');

    // 個人会員
    Route::get('/applicant/dashboard', [ApplicantDashboardController::class, 'index'])
        ->name('applicant.dashboard');

    // 管理チーム
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Admin/Dashboard');
    })->name('admin.dashboard');

    // 公開求人ページ（認証不要）
    Route::get('/jobs', [App\Http\Controllers\PublicJobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/{id}', [App\Http\Controllers\PublicJobController::class, 'show'])->name('jobs.show');

    Route::post('/jobs/{id}/apply', [App\Http\Controllers\JobApplicationController::class, 'store'])->name('jobs.apply')->middleware('auth');

    Route::get('/applicant/consent', [ConsentController::class, 'show'])->name('applicant.consent');
    Route::post('/applicant/consent', [ConsentController::class, 'store'])->name('applicant.consent.store');
    // 求人投稿支払い
    Route::post('/company/jobs/{jobId}/payment', [App\Http\Controllers\JobPaymentController::class, 'createSnap'])->name('company.jobs.payment');
    Route::post('/company/jobs/payment/callback', [App\Http\Controllers\JobPaymentController::class, 'callback'])->name('company.jobs.payment.callback');
    Route::get('/company/jobs/payment/finish', [App\Http\Controllers\JobPaymentController::class, 'finish'])->name('company.jobs.payment.finish');

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
    Route::get('/applicant/applications', [App\Http\Controllers\Applicant\ApplicationController::class, 'index'])->name('applicant.applications');
    Route::post('/applicant/confirmation', [ConfirmationController::class, 'store'])->name('applicant.confirmation.store');
    Route::get('/applicant/profile', [App\Http\Controllers\Applicant\ProfileController::class, 'show'])->name('applicant.profile');
    Route::post('/applicant/profile', [App\Http\Controllers\Applicant\ProfileController::class, 'update'])->name('applicant.profile.update');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ブックマーク
    Route::get('/applicant/bookmarks', [App\Http\Controllers\Applicant\BookmarkController::class, 'index'])->name('applicant.bookmarks');
    Route::post('/applicant/bookmarks/{jobPostId}/toggle', [App\Http\Controllers\Applicant\BookmarkController::class, 'toggle'])->name('applicant.bookmarks.toggle');

    // 認証申請支払い
    Route::post('/applicant/certification/payment', [App\Http\Controllers\Applicant\CertificationPaymentController::class, 'createSnap'])->name('applicant.certification.payment');
    Route::post('/applicant/certification/payment/callback', [App\Http\Controllers\Applicant\CertificationPaymentController::class, 'callback'])->name('applicant.certification.payment.callback');
    Route::get('/applicant/certification/payment/finish', [App\Http\Controllers\Applicant\CertificationPaymentController::class, 'finish'])->name('applicant.certification.payment.finish');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth')
    ->group(function () {

        // 調査チーム
        Route::prefix('investigator')
            ->name('investigator.')
            ->group(function () {
                Route::get('/', [App\Http\Controllers\InvestigatorController::class, 'index'])->name('index');
                Route::post('/{id}/save', [App\Http\Controllers\InvestigatorController::class, 'save'])->name('save');
                Route::post('/{id}/complete', [App\Http\Controllers\InvestigatorController::class, 'complete'])->name('complete');
                Route::post('/{id}/correction', [App\Http\Controllers\InvestigatorController::class, 'correction'])->name('correction');
            });

        // レビューチーム（後で実装）
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