<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeatureRequestController;
use App\Http\Controllers\Admin\IdVerificationController;
use App\Http\Controllers\Admin\ListingController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/how-it-works', [PageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/features', [PageController::class, 'features'])->name('features');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('contact.submit');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store'])->name('login.attempt');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::patch('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');

        Route::get('/id-verifications', [IdVerificationController::class, 'index'])->name('id-verifications.index');
        Route::get('/id-verifications/{idVerification}/photo', [IdVerificationController::class, 'photo'])->name('id-verifications.photo');
        Route::patch('/id-verifications/{idVerification}', [IdVerificationController::class, 'update'])->name('id-verifications.update');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/ban', [UserController::class, 'toggleBan'])->name('users.toggle-ban');
        Route::patch('/users/{user}/admin', [UserController::class, 'toggleAdmin'])->name('users.toggle-admin');

        Route::get('/feature-requests', [FeatureRequestController::class, 'index'])->name('feature-requests.index');
        Route::patch('/feature-requests/{featureRequest}', [FeatureRequestController::class, 'update'])->name('feature-requests.update');
        Route::delete('/feature-requests/{featureRequest}', [FeatureRequestController::class, 'destroy'])->name('feature-requests.destroy');

        Route::get('/listings', [ListingController::class, 'index'])->name('listings.index');
        Route::delete('/listings/trips/{trip}', [ListingController::class, 'destroyTrip'])->name('listings.trips.destroy');
        Route::delete('/listings/goods/{good}', [ListingController::class, 'destroyGood'])->name('listings.goods.destroy');

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});
