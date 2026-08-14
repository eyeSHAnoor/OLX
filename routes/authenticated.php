<?php

use App\Http\Controllers\AdController;
use App\Http\Controllers\AdFavoriteController;
use App\Http\Controllers\FavoriteAdController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CreateAdController;
use App\Http\Controllers\DownlineReferralController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserReportController;
use App\Http\Controllers\UserReferralController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\GiftDetailController;
use App\Http\Controllers\BannerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // Terms Acceptance
    Route::post('/accept-terms', function () {
        $user = auth()->user();
        $user->update([
            'terms_accepted' => true,
            'terms_accepted_at' => now(),
        ]);
        return redirect()->back();
    });

    // Subscriptions
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])
        ->name('subscriptions.index');
    Route::post('/subscriptions/manual', [SubscriptionController::class, 'submitManual'])
        ->name('subscriptions.manual');
    Route::post('/subscriptions/jazzcash/initiate', [SubscriptionController::class, 'initiateJazzCash'])
        ->name('subscriptions.jazzcash.initiate');

    // Chat Routes
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/my-ads', function () {
        $ads = auth()->user()->ads()
            ->where('status', 'active')
            ->where('is_active', true)
            ->select('id', 'ad_title', 'description', 'price')
            ->with('images:id,ad_id,path,is_primary')
            ->latest()
            ->get();

        return response()->json($ads);
    })->name('chat.my-ads');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::get('/messages/{conversation}', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/messages/{conversation}/read', [ChatController::class, 'markAsRead'])->name('chat.mark-read');
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
    Route::post('/chat/upload', [ChatController::class, 'upload'])->name('chat.upload');
    Route::post('/chat/start', [ChatController::class, 'start'])->name('chat.start');
    Route::delete('/chat/message/{message}', [ChatController::class, 'deleteMessage'])->name('chat.message.delete');
    Route::get('/chat/file/{message}', [ChatController::class, 'file'])->name('chat.file');
    Route::post('/chat/send-product', [ChatController::class, 'sendProduct'])->name('chat.send-product');
    Route::delete('/chat/{conversation}', [ChatController::class, 'destroyConversation'])->name('chat.conversation.destroy');

    // User Ads Management
    Route::get('user/ads/create', [CreateAdController::class, 'index'])->name('user.ads.create');
    Route::get('user/ads/edit/{id}', [CreateAdController::class, 'edit'])->name('user.ads.edit');
    Route::get('/ads/category-data/{category}', [CreateAdController::class, 'getCategoryData'])->name('ads.category-data');
    Route::post('user/ads', [CreateAdController::class, 'store'])->name('user.ads.store');
    Route::get('user/my/ads', [CreateAdController::class, 'Myads'])->name('user.ads');
    Route::patch('/user/ads/{ad}/status', [CreateAdController::class, 'updateStatus'])->name('user.ads.status');

    // Ad CRUD
    Route::post('/ads', [AdController::class, 'store'])->name('ads.store');
    Route::put('/ads/{ad}', [AdController::class, 'update'])->name('ads.update');
    Route::delete('/ads/{ad}', [AdController::class, 'destroy'])->name('ads.destroy');

    // Category & Brand Data
    Route::get('categories/{category}/attributes', [AdController::class, 'getAttributesByCategory']);
    Route::get('brands/{brand}/models', [AdController::class, 'getModelsByBrand']);

    // Profile Management
    Route::get('/profile/edit', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [UserProfileController::class, 'update'])->name('user.profile.update');
    Route::delete('/profile/delete', [UserProfileController::class, 'destroy'])->name('user.profile.destroy');
    Route::get('/check-username', [UserProfileController::class, 'checkUsername'])->name('user.check-username');

    // Ratings & Favorites
    Route::post('/ratings', [RatingController::class, 'store'])->name('ratings.store');
    Route::post('/ads/{ad}/favorite', [AdFavoriteController::class, 'toggle'])->name('ads.favorite');
    Route::get('/favorites', [FavoriteAdController::class, 'index'])->name('user.favorites');
    Route::post('/favorites/{ad}/toggle', [FavoriteAdController::class, 'toggle'])->name('user.favorites.toggle');
    Route::delete('/favorites/{ad}', [FavoriteAdController::class, 'destroy'])->name('user.favorites.destroy');
    Route::delete('/favorites', [FavoriteAdController::class, 'clearAll'])->name('user.favorites.clear');
    Route::get('/api/favorites', [FavoriteAdController::class, 'apiIndex'])->name('api.user.favorites');

    // Users Resource
    Route::resource('users', UserController::class);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    // Settings & Password
    Route::get('/amo/setting', [SettingController::class, 'index'])->name('amo.settings');
    Route::post('/amo/change-password', [SettingController::class, 'update'])->name('password.change');

    // Reports
    Route::get('/reports/create', [UserReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [UserReportController::class, 'store'])->name('reports.store');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::post('/order', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{order}/accept', [OrderController::class, 'accept'])->name('orders.accept');
    Route::post('/orders/{order}/reject', [OrderController::class, 'reject'])->name('orders.reject');

    // Push Notifications
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

    // Comments
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('/comments/{comment}/like', [CommentController::class, 'toggleLike'])->name('comments.like');

    // Downline Referrals
    Route::prefix('downline-referrals')->name('downline-referrals.')->group(function () {
        Route::get('/', [DownlineReferralController::class, 'index'])->name('index');
        Route::get('/create', [DownlineReferralController::class, 'create'])->name('create');
        Route::post('/', [DownlineReferralController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [DownlineReferralController::class, 'edit'])->name('edit');
        Route::put('/{user}', [DownlineReferralController::class, 'update'])->name('update');
        Route::delete('/{user}', [DownlineReferralController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/generate-code', [DownlineReferralController::class, 'generateCode'])->name('generate-code');
        Route::get('/search-by-email', [DownlineReferralController::class, 'searchByEmail'])->name('search-by-email');
    });
    Route::get('/users/{user}/referrals', [UserReferralController::class, 'userReferrals'])->name('users.referrals.show');
    Route::get('/referral-tree/{user?}', [UserReferralController::class, 'referralTree'])->name('referral.tree');

    Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('/withdrawals', [WithdrawalController::class, 'store'])->name('withdrawals.store');
    Route::post('/withdrawals/{withdrawal}/confirm', [WithdrawalController::class, 'confirm'])->name('withdrawals.confirm');
    Route::get('/withdrawals/status', [WithdrawalController::class, 'status'])->name('withdrawals.status');

    Route::get('/get/banners', [BannerController::class, 'getBanner'])->name('getbanners');

});