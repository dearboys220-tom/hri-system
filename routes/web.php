<?php

use App\Http\Controllers\Auth\ApplicantAuthController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

Route::get('/register/company', function () {
    return Inertia::render('Auth/RegisterCompany');
})->name('register.company');

Route::prefix('auth/applicant')->group(function () {
    Route::get('/google', [ApplicantAuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [ApplicantAuthController::class, 'handleGoogleCallback']);
});
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

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
    

require __DIR__.'/auth.php';
