<?php

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
