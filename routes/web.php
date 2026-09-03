<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EngagementController;
use App\Http\Controllers\GoogleAdsController;
use App\Http\Controllers\MetaInsightController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SuperController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

// ---------- Guest ----------
Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ---------- Authenticated ----------
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Upload gambar (canvas editor & avatar)
    Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');
    Route::post('/uploads/file', [UploadController::class, 'uploadFile'])->name('uploads.file');

    // ---------- Tool 1: Content Studio ----------
    Route::prefix('tools/content')->name('tools.content.')->group(function () {
        Route::get('/', [ContentController::class, 'index'])->name('index');
        Route::get('/create', [ContentController::class, 'create'])->name('create');
        Route::post('/', [ContentController::class, 'store'])->name('store');
        Route::get('/{content}', [ContentController::class, 'show'])->name('show');
        Route::put('/{content}', [ContentController::class, 'update'])->name('update');
        Route::post('/{content}/submit', [ContentController::class, 'submit'])->name('submit');
        Route::delete('/{content}', [ContentController::class, 'destroy'])->name('destroy');
    });

    // ---------- Tool 2: Engagement Rate ----------
    Route::prefix('tools/engagement')->name('tools.engagement.')->group(function () {
        Route::get('/', [EngagementController::class, 'index'])->name('index');
        Route::post('/calculate', [EngagementController::class, 'calculate'])->name('calculate');
        Route::delete('/{calculation}', [EngagementController::class, 'destroy'])->name('destroy');
    });

    // ---------- Tool 3: Meta Ads Insights ----------
    Route::prefix('tools/meta')->name('tools.meta.')->group(function () {
        Route::get('/', [MetaInsightController::class, 'index'])->name('index');
        Route::post('/store', [MetaInsightController::class, 'store'])->name('store');
        Route::post('/import', [MetaInsightController::class, 'importCsv'])->name('import');
        Route::post('/sync', [MetaInsightController::class, 'sync'])->name('sync');
        Route::post('/pages', [MetaInsightController::class, 'storePage'])->name('pages.store');
        Route::post('/pages/connect', [MetaInsightController::class, 'connectPages'])->name('pages.connect');
        Route::delete('/pages/{page}', [MetaInsightController::class, 'destroyPage'])->name('pages.destroy');
        Route::delete('/{insight}', [MetaInsightController::class, 'destroy'])->name('destroy');
    });

    // ---------- Tool 4: Google Ads Insights ----------
    Route::prefix('tools/google-ads')->name('tools.google-ads.')->group(function () {
        Route::get('/', [GoogleAdsController::class, 'index'])->name('index');
        Route::post('/campaigns', [GoogleAdsController::class, 'storeCampaign'])->name('campaigns.store');
        Route::post('/store', [GoogleAdsController::class, 'store'])->name('store');
        Route::post('/import', [GoogleAdsController::class, 'importCsv'])->name('import');
        Route::delete('/campaigns/{campaign}', [GoogleAdsController::class, 'destroyCampaign'])->name('campaigns.destroy');
        Route::delete('/{insight}', [GoogleAdsController::class, 'destroy'])->name('destroy');
    });

    // ---------- Tool 5: Google Analytics ----------
    Route::prefix('tools/analytics')->name('tools.analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::post('/properties', [AnalyticsController::class, 'storeProperty'])->name('properties.store');
        Route::post('/store', [AnalyticsController::class, 'store'])->name('store');
        Route::post('/import', [AnalyticsController::class, 'importCsv'])->name('import');
        Route::delete('/properties/{property}', [AnalyticsController::class, 'destroyProperty'])->name('properties.destroy');
        Route::delete('/{insight}', [AnalyticsController::class, 'destroy'])->name('destroy');
    });

    // ---------- Settings ----------
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('profile');
        Route::put('/connections', [SettingsController::class, 'updateConnections'])->name('connections');
    });

    // ---------- Super Admin ----------
    Route::prefix('super')->middleware('role:super')->name('super.')->group(function () {
        Route::get('/', [SuperController::class, 'index'])->name('index');
        Route::get('/companies', [SuperController::class, 'companies'])->name('companies');
        Route::post('/contents/{content}/approve', [SuperController::class, 'approve'])->name('contents.approve');
        Route::post('/contents/{content}/reject', [SuperController::class, 'reject'])->name('contents.reject');
    });
});