<?php

use App\Http\Controllers\Auth\ApplicantAuthController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterCompanyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'storeUser']);
    Route::get('/login-staff', [AuthController::class, 'loginStaff'])->name('login-staff');
    Route::post('/login-staff', [AuthController::class, 'storeStaff']);
    Route::post('/login', [AuthController::class, 'store']);
    Route::get('/register/company', [RegisterCompanyController::class, 'create'])->name('register.company');
    Route::post('/register/company', [RegisterCompanyController::class, 'store']);
});
Route::prefix('auth/applicant')->middleware('guest')->group(function () {
    Route::get('/google', [ApplicantAuthController::class, 'redirectToGoogle'])->name('applicant.google');
    Route::get('/google/callback', [ApplicantAuthController::class, 'handleGoogleCallback'])->name('applicant.google.callback');
});
Route::middleware(['auth:staff', 'role:admin_user'])->prefix('admin')->group(function () {
    Route::get('/dashboard', fn () =>
        Inertia::render('Admin/AdminMain')
    )->name('admin.dashboard');
});
Route::middleware(['auth:staff', 'role:investigator_user'])->prefix('investigator')->group(function () {
    Route::get('/dashboard', fn () =>
        Inertia::render('Admin/Investigator/InvestigatorMain')
    )->name('investigator.dashboard');
});
Route::middleware(['auth:staff', 'role:reviewer_user'])->prefix('reviewer')->group(function () {
    Route::get('/dashboard', fn () =>
        Inertia::render('Admin/Reviewer/ReviewerMain')
    )->name('reviewer.dashboard');
});
Route::prefix('company')->middleware(['auth:web', 'role:company'])->group(function () {
    Route::middleware('company.verified')->group(function () {
        Route::get('/dashboard', fn () =>
            Inertia::render('User/Company/CompanyMain')
        )->name('company.dashboard');

    });
    Route::get('/pending', fn () =>
        Inertia::render('User/Company/CompanyMain')
    )->name('company.pending');

});
Route::middleware(['auth', 'role:applicant'])->prefix('applicant')->group(function () {
    Route::get('/dashboard', fn () =>
        Inertia::render('User/Company/CompanyMain')
    )->name('applicant.dashboard');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:web')
    ->name('logout');

Route::post('/logout-staff', [AuthController::class, 'logoutStaff'])
    ->middleware('auth:staff')
    ->name('logout-staff');

Route::prefix('auth/applicant')->group(function () {
    Route::get('/google', [ApplicantAuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [ApplicantAuthController::class, 'handleGoogleCallback']);
});

Route::prefix('investigator')
    ->name('investigator.')
    ->group(function () {

        Route::get('/', function () {
            return Inertia::render('Admin/Investigator/InvestigatorMain');
        })->name('index');

    });
Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::prefix('reviewer')
            ->name('reviewer.')
            ->group(function () {

                Route::get('/', function () {
                    return Inertia::render('Admin/Reviewer/ReviewerMain');
                })->name('index');

            });

    });

