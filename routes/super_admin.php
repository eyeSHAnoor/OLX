<?php

use App\Http\Controllers\AdController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GiftCampaignController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\SubscriptionPermissionController;
use App\Http\Controllers\PageContentController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\UserReferralController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'super_admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Categories
    Route::resource('categories', CategoryController::class);

    // Brands
    Route::resource('brands', BrandController::class);
    Route::get('/api/brand/{brand}', [BrandController::class, 'show']);
    Route::get('/api/brands', [BrandController::class, 'getName']);
    Route::get('/api/brands/{category}', [BrandController::class, 'getByCategory']);

    // Ads Admin Routes
    Route::get('/ads', [AdController::class, 'index'])->name('ads.index');
    Route::get('admin/ads/create', [AdController::class, 'create'])->name('ads.create');
    Route::get('/ads/{ad}/edit', [AdController::class, 'edit'])->name('ads.edit');
    Route::patch('/ads/{ad}', [AdController::class, 'update']);
    Route::post('ads/{ad}/set-primary-image', [AdController::class, 'setPrimaryImage'])
        ->name('ads.set-primary-image');

    // Plans
    Route::resource('plans', PlanController::class);

    // Receipts
    Route::get('/receipts/{subscription}', [ReceiptController::class, 'show'])
        ->name('receipts.show');
    Route::get('/receipts/{subscription}/download', [ReceiptController::class, 'download'])
        ->name('receipts.download');

    // Subscription Management
    Route::post('/subscriptions/{user}/complete', [SubscriptionController::class, 'complete'])
        ->name('subscriptions.complete');
    Route::post('/subscriptions/{user}/reject', [SubscriptionController::class, 'reject'])
        ->name('subscriptions.reject');

    // Banners
    Route::resource('banners', BannerController::class)->except(['show']);
    Route::patch('banners/{banner}/toggle-status', [BannerController::class, 'toggleStatus'])
        ->name('banners.toggle-status');

    // Reports Management
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::post('/reports/{report}/respond', [ReportController::class, 'respond'])->name('reports.respond');
    Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
    Route::post('/reports/bulk-update', [ReportController::class, 'bulkUpdate'])->name('reports.bulk-update');

    // Broadcast Messages
    Route::resource('broadcast-messages', BroadcastController::class)->except(['show']);
    Route::post('broadcast-messages/{broadcast_message}/toggle-status', [BroadcastController::class, 'toggleStatus'])
        ->name('broadcast-messages.toggle-status');
    Route::post('/broadcast-message/{message}/send', [BroadcastController::class, 'broadcast'])
        ->name('broadcast-message.send');

    // Subscription Permissions
    Route::resource('subscription-permissions', SubscriptionPermissionController::class)
        ->except(['create', 'edit']);
    Route::get('subscription-permissions/all', [SubscriptionPermissionController::class, 'getAll'])
        ->name('subscription-permissions.all');

    // Page Contents
    Route::resource('page-contents', PageContentController::class)->except(['show']);
    Route::patch('page-contents/{pageContent}/toggle-status', [PageContentController::class, 'toggleStatus'])
        ->name('page-contents.toggle-status');
    Route::get('page-contents/by-key/{pageKey}', [PageContentController::class, 'getByPageKey'])
        ->name('page-contents.by-key');

    // Cities & Regions
    Route::resource('cities', CityController::class);
    Route::prefix('cities/{city}')->group(function () {
        Route::get('regions', [CityController::class, 'getRegions'])
            ->name('cities.regions');
        Route::post('regions', [CityController::class, 'storeRegions'])
            ->name('cities.regions.store');
    });
    Route::prefix('regions')->group(function () {
        Route::put('{region}', [CityController::class, 'updateRegion'])
            ->name('regions.update');
        Route::delete('{region}', [CityController::class, 'destroyRegion'])
            ->name('regions.destroy');
    });

    // Gift Campaign Routes
    Route::prefix('gift-campaigns')->name('gift-campaigns.')->group(function () {
        // Campaign CRUD
        Route::get('/', [GiftCampaignController::class, 'index'])->name('index');
        Route::get('/create', [GiftCampaignController::class, 'create'])->name('create');
        Route::post('/', [GiftCampaignController::class, 'store'])->name('store');
        Route::get('/{period}/edit', [GiftCampaignController::class, 'edit'])->name('edit');
        Route::put('/{period}', [GiftCampaignController::class, 'update'])->name('update');
        Route::delete('/{period}', [GiftCampaignController::class, 'destroy'])->name('destroy');

        // Eligible Users & Assignment
        Route::get('/{period}/eligible-users', [GiftCampaignController::class, 'showEligibleUsers'])
            ->name('eligible-users');
        Route::post('/{period}/assign-gifts', [GiftCampaignController::class, 'assignGifts'])
            ->name('assign-gifts');
        Route::post('/{period}/bulk-assign', [GiftCampaignController::class, 'bulkAssign'])
            ->name('bulk-assign');

        // Assignment Management
        Route::get('/{period}/assignments', [GiftCampaignController::class, 'assignments'])
            ->name('assignments');
        Route::patch('/assignments/{assignment}/status', [GiftCampaignController::class, 'updateAssignmentStatus'])
            ->name('update-assignment-status');
    });

    // Gifts Management
    Route::resource('gifts', GiftController::class)->except(['show']);
    Route::patch('gifts/{gift}/toggle-status', [GiftController::class, 'toggleStatus'])
        ->name('gifts.toggle-status');

    // User Referral Management
    Route::get('/referrals', [UserReferralController::class, 'index'])->name('referrals.index');
    Route::get('/users/referral/create', [UserReferralController::class, 'create'])->name('users.referral.create');
    Route::post('/users/referral', [UserReferralController::class, 'store'])->name('users.referral.store');
    Route::get('/users/{user}/referral/edit', [UserReferralController::class, 'edit'])->name('users.referral.edit');
    Route::put('/users/{user}/referral', [UserReferralController::class, 'update'])->name('users.referral.update');
    Route::delete('/users/{user}/referral', [UserReferralController::class, 'destroy'])->name('users.referral.destroy');
    Route::post('/users/{user}/generate-referral-code', [UserReferralController::class, 'generateCode'])->name('users.referral.generate-code');

    Route::post('/assign-referral-codes', [UserReferralController::class, 'assignReferralCodes'])->name("user.assign.referral");

     Route::get('/admin/withdrawals', [WithdrawalController::class, 'adminIndex'])->name('admin.withdrawals.index');
    Route::get('/admin/withdrawals/{withdrawal}', [WithdrawalController::class, 'show'])->name('admin.withdrawals.show');
    Route::post('/admin/withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->name('admin.withdrawals.approve');
    Route::post('/admin/withdrawals/{withdrawal}/complete', [WithdrawalController::class, 'complete'])->name('admin.withdrawals.complete');
    Route::post('/admin/withdrawals/{withdrawal}/reject', [WithdrawalController::class, 'reject'])->name('admin.withdrawals.reject');

    Route::resource('scheduled-notifications', App\Http\Controllers\ScheduledNotificationController::class)
        ->except(['show']);
});