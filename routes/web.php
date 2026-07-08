<?php

use App\Http\Controllers\AdController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\GiftCampaignController;
use App\Mail\MyTestEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// City regions lookup
Route::get('/cities/{city}/regions', function (City $city) {
    return $city->regions()->pluck('name');
});

// Home & Public Pages
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/account', [\App\Http\Controllers\HomeController::class, 'account'])->name('account');
Route::get('/all-items', [\App\Http\Controllers\SearchController::class, 'allItems'])->name('all.items');
Route::get('/category/{slug?}', [CategoryController::class, 'show'])->name('category.show');
Route::post('/category/filter', [CategoryController::class, 'filter'])->name('category.filter');
Route::get('/user/{id}', [App\Http\Controllers\PublicProfileController::class, 'show'])->name('user.profile');
Route::get('/register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])->name('register');

// Ads Public Routes
Route::get('/ads/{ad}', [AdController::class, 'show'])->name('ads.show');

// Orders Public Routes
Route::get('/orders/{order}/review', [App\Http\Controllers\OrderController::class, 'review'])->name('orders.review');
Route::post('/orders/{order}/complete', [App\Http\Controllers\OrderController::class, 'completed'])->name('orders.complete');
Route::post('/orders/{order}/cancel', [App\Http\Controllers\OrderController::class, 'cancel'])->name('orders.cancel');

// Location Setting
Route::post('/set-city', function (Request $request) {
    $minutes = 525600; // Same lifetime as session

    $cityCookie   = cookie('user_city', $request->city, $minutes);
    $regionCookie = cookie('user_region', $request->region, $minutes);

    return back()
        ->with('success', 'Location updated')
        ->withCookies([$cityCookie, $regionCookie]);
})->name('set.city');

// Static Pages
Route::get('/policy/{type}', [App\Http\Controllers\PolicyController::class, 'show'])->name('policy.show');
Route::get('/aboutus', [App\Http\Controllers\AboutController::class, 'index'])->name('aboutus');
Route::get('/page/{pageKey}', [App\Http\Controllers\AboutController::class, 'show'])->name('public.page');
Route::get('/page', [App\Http\Controllers\AboutController::class, 'nav']);
Route::get('/regions/{cityName}', [App\Http\Controllers\RegionController::class, 'getByCityName']);
Route::post('/contact/send', [App\Http\Controllers\AboutController::class, 'send'])->name('contact.send')->middleware('throttle:3,1');

// Search
Route::get('/search-suggestions', [App\Http\Controllers\SearchController::class, 'suggestions']);

// Locale Setting
Route::post('/locale', function (Request $request) {
    $request->validate(['locale' => 'required|string']);
    session(['locale' => $request->input('locale')]);
    return redirect()->back();
})->name('locale.set');

Route::put('/settings/locale', [App\Http\Controllers\Settings\SetLocaleController::class, 'update'])
    ->name('settings.locale.update');

/*
|--------------------------------------------------------------------------
| Utility Routes (Artisan Commands)
|--------------------------------------------------------------------------
*/

// Scheduler trigger
Route::get('/artisan-scheduler', function () {
    Artisan::call('schedule:run');
    return 'Scheduler triggered at ' . now();
});

// Queue worker trigger (with token protection)
Route::get('/artisan-queue/{token}', function ($token) {
    if ($token !== config('app.queue_cron_token')) {
        abort(403);
    }

    Artisan::call('queue:work', [
        '--once' => true,
        '--tries' => 3,
        '--stop-when-empty' => true,
    ]);

    return 'Queue processed at ' . now();
});

// Cache clear
Route::get('/clear', function () {
    Artisan::call('optimize:clear');
    return back()->with('success', 'Cache cleared.');
})->name('cache.clear');

// Storage link
Route::get('/storagelink', function () {
    Artisan::call('storage:link');
    return back()->with('success', 'Storage Link created');
});

/*
|--------------------------------------------------------------------------
| Broadcasting
|--------------------------------------------------------------------------
*/

Broadcast::channel('notifications', function () {
    return true; // Anyone can listen
});

Broadcast::routes();

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

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
    Route::get('/subscriptions', [App\Http\Controllers\SubscriptionController::class, 'index'])
        ->name('subscriptions.index');
    Route::post('/subscriptions/manual', [App\Http\Controllers\SubscriptionController::class, 'submitManual'])
        ->name('subscriptions.manual');
    Route::post('/subscriptions/jazzcash/initiate', [App\Http\Controllers\SubscriptionController::class, 'initiateJazzCash'])
        ->name('subscriptions.jazzcash.initiate');

    // Chat Routes
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
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
    Route::get('/chat/{conversation}', [App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
    Route::get('/messages/{conversation}', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/messages/{conversation}/read', [App\Http\Controllers\ChatController::class, 'markAsRead'])->name('chat.mark-read');
    Route::post('/chat/send', [App\Http\Controllers\ChatController::class, 'send'])->name('chat.send');
    Route::post('/chat/upload', [App\Http\Controllers\ChatController::class, 'upload'])->name('chat.upload');
    Route::post('/chat/start', [App\Http\Controllers\ChatController::class, 'start'])->name('chat.start');
    Route::delete('/chat/message/{message}', [App\Http\Controllers\ChatController::class, 'deleteMessage'])->name('chat.message.delete');
    Route::get('/chat/file/{message}', [App\Http\Controllers\ChatController::class, 'file'])->name('chat.file');
    Route::post('/chat/send-product', [App\Http\Controllers\ChatController::class, 'sendProduct'])->name('chat.send-product');
    Route::delete('/chat/{conversation}', [App\Http\Controllers\ChatController::class, 'destroyConversation'])->name('chat.conversation.destroy');

    // User Ads Management
    Route::get('user/ads/create', [App\Http\Controllers\CreateAdController::class, 'index'])->name('user.ads.create');
    Route::get('user/ads/edit/{id}', [App\Http\Controllers\CreateAdController::class, 'edit'])->name('user.ads.edit');
    Route::get('/ads/category-data/{category}', [App\Http\Controllers\CreateAdController::class, 'getCategoryData'])->name('ads.category-data');
    Route::post('user/ads', [App\Http\Controllers\CreateAdController::class, 'store'])->name('user.ads.store');
    Route::get('user/my/ads', [App\Http\Controllers\CreateAdController::class, 'Myads'])->name('user.ads');
    Route::patch('/user/ads/{ad}/status', [App\Http\Controllers\CreateAdController::class, 'updateStatus'])->name('user.ads.status');

    // Ad CRUD
    Route::post('/ads', [AdController::class, 'store'])->name('ads.store');
    Route::put('/ads/{ad}', [AdController::class, 'update'])->name('ads.update');
    Route::delete('/ads/{ad}', [AdController::class, 'destroy'])->name('ads.destroy');

    // Category & Brand Data
    Route::get('categories/{category}/attributes', [AdController::class, 'getAttributesByCategory']);
    Route::get('brands/{brand}/models', [AdController::class, 'getModelsByBrand']);

    // Profile Management
    Route::get('/profile/edit', [App\Http\Controllers\UserProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [App\Http\Controllers\UserProfileController::class, 'update'])->name('user.profile.update');
    Route::delete('/profile/delete', [App\Http\Controllers\UserProfileController::class, 'destroy'])->name('user.profile.destroy');
    Route::get('/check-username', [App\Http\Controllers\UserProfileController::class, 'checkUsername'])->name('user.check-username');

    // Ratings & Favorites
    Route::post('/ratings', [App\Http\Controllers\RatingController::class, 'store'])->name('ratings.store');
    Route::post('/ads/{ad}/favorite', [App\Http\Controllers\AdFavoriteController::class, 'toggle'])->name('ads.favorite');
    Route::get('/favorites', [App\Http\Controllers\FavoriteAdController::class, 'index'])->name('user.favorites');
    Route::post('/favorites/{ad}/toggle', [App\Http\Controllers\FavoriteAdController::class, 'toggle'])->name('user.favorites.toggle');
    Route::delete('/favorites/{ad}', [App\Http\Controllers\FavoriteAdController::class, 'destroy'])->name('user.favorites.destroy');
    Route::delete('/favorites', [App\Http\Controllers\FavoriteAdController::class, 'clearAll'])->name('user.favorites.clear');
    Route::get('/api/favorites', [App\Http\Controllers\FavoriteAdController::class, 'apiIndex'])->name('api.user.favorites');

    // Users Resource
    Route::resource('users', App\Http\Controllers\UserController::class);

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/mark-as-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    // Settings & Password
    Route::get('/amo/setting', [App\Http\Controllers\SettingController::class, 'index'])->name('amo.settings');
    Route::post('/amo/change-password', [App\Http\Controllers\SettingController::class, 'update'])->name('password.change');

    // Reports
    Route::get('/reports/create', [App\Http\Controllers\UserReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [App\Http\Controllers\UserReportController::class, 'store'])->name('reports.store');

    // Orders
    Route::get('/orders', [App\Http\Controllers\OrderController::class, 'index'])->name('orders');
    Route::post('/order', [App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{order}/accept', [App\Http\Controllers\OrderController::class, 'accept'])->name('orders.accept');
    Route::post('/orders/{order}/reject', [App\Http\Controllers\OrderController::class, 'reject'])->name('orders.reject');

    // Push Notifications
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

    // Comments
    Route::post('/comments', [App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    Route::post('/comments/{comment}/like', [App\Http\Controllers\CommentController::class, 'toggleLike'])->name('comments.like');

});

/*
|--------------------------------------------------------------------------
| Admin Routes (Super Admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'super_admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Categories
    Route::resource('categories', CategoryController::class);

    // Brands
    Route::resource('brands', \App\Http\Controllers\BrandController::class);
    Route::get('/api/brand/{brand}', [\App\Http\Controllers\BrandController::class, 'show']);
    Route::get('/api/brands', [\App\Http\Controllers\BrandController::class, 'getName']);
    Route::get('/api/brands/{category}', [\App\Http\Controllers\BrandController::class, 'getByCategory']);

    // Ads Admin Routes
    Route::get('/ads', [AdController::class, 'index'])->name('ads.index');
    Route::get('admin/ads/create', [AdController::class, 'create'])->name('ads.create');
    Route::get('/ads/{ad}/edit', [AdController::class, 'edit'])->name('ads.edit');
    Route::patch('/ads/{ad}', [AdController::class, 'update']);
    Route::post('ads/{ad}/set-primary-image', [AdController::class, 'setPrimaryImage'])
        ->name('ads.set-primary-image');

    // Plans
    Route::resource('plans', App\Http\Controllers\PlanController::class);

    // Receipts
    Route::get('/receipts/{subscription}', [App\Http\Controllers\ReceiptController::class, 'show'])
        ->name('receipts.show');
    Route::get('/receipts/{subscription}/download', [App\Http\Controllers\ReceiptController::class, 'download'])
        ->name('receipts.download');

    // Subscription Management
    Route::post('/subscriptions/{user}/complete', [App\Http\Controllers\SubscriptionController::class, 'complete'])
        ->name('subscriptions.complete');
    Route::post('/subscriptions/{user}/reject', [App\Http\Controllers\SubscriptionController::class, 'reject'])
        ->name('subscriptions.reject');

    // Banners
    Route::resource('banners', App\Http\Controllers\BannerController::class)->except(['show']);
    Route::patch('banners/{banner}/toggle-status', [App\Http\Controllers\BannerController::class, 'toggleStatus'])
        ->name('banners.toggle-status');

    // Reports Management
    Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [App\Http\Controllers\ReportController::class, 'show'])->name('reports.show');
    Route::post('/reports/{report}/respond', [App\Http\Controllers\ReportController::class, 'respond'])->name('reports.respond');
    Route::delete('/reports/{report}', [App\Http\Controllers\ReportController::class, 'destroy'])->name('reports.destroy');
    Route::post('/reports/bulk-update', [App\Http\Controllers\ReportController::class, 'bulkUpdate'])->name('reports.bulk-update');

    // Broadcast Messages
    Route::resource('broadcast-messages', App\Http\Controllers\BroadcastController::class)->except(['show']);
    Route::post('broadcast-messages/{broadcast_message}/toggle-status', [App\Http\Controllers\BroadcastController::class, 'toggleStatus'])
        ->name('broadcast-messages.toggle-status');
    Route::post('/broadcast-message/{message}/send', [App\Http\Controllers\BroadcastController::class, 'broadcast'])
        ->name('broadcast-message.send');

    // Subscription Permissions
    Route::resource('subscription-permissions', App\Http\Controllers\SubscriptionPermissionController::class)
        ->except(['create', 'edit']);
    Route::get('subscription-permissions/all', [App\Http\Controllers\SubscriptionPermissionController::class, 'getAll'])
        ->name('subscription-permissions.all');

    // Page Contents
    Route::resource('page-contents', App\Http\Controllers\PageContentController::class)->except(['show']);
    Route::patch('page-contents/{pageContent}/toggle-status', [App\Http\Controllers\PageContentController::class, 'toggleStatus'])
        ->name('page-contents.toggle-status');
    Route::get('page-contents/by-key/{pageKey}', [App\Http\Controllers\PageContentController::class, 'getByPageKey'])
        ->name('page-contents.by-key');

    // Cities & Regions
    Route::resource('cities', App\Http\Controllers\CityController::class);
    Route::prefix('cities/{city}')->group(function () {
        Route::get('regions', [App\Http\Controllers\CityController::class, 'getRegions'])
            ->name('cities.regions');
        Route::post('regions', [App\Http\Controllers\CityController::class, 'storeRegions'])
            ->name('cities.regions.store');
    });
    Route::prefix('regions')->group(function () {
        Route::put('{region}', [App\Http\Controllers\CityController::class, 'updateRegion'])
            ->name('regions.update');
        Route::delete('{region}', [App\Http\Controllers\CityController::class, 'destroyRegion'])
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
    Route::resource('gifts', \App\Http\Controllers\GiftController::class)->except(['show']);
    Route::patch('gifts/{gift}/toggle-status', [\App\Http\Controllers\GiftController::class, 'toggleStatus'])
        ->name('gifts.toggle-status');
    /*
    |--------------------------------------------------------------------------
    | User Referral Management Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/referrals', [\App\Http\Controllers\UserReferralController::class, 'index'])->name('referrals.index');
    Route::get('/users/referral/create', [\App\Http\Controllers\UserReferralController::class, 'create'])->name('users.referral.create');
    Route::post('/users/referral', [\App\Http\Controllers\UserReferralController::class, 'store'])->name('users.referral.store');
    Route::get('/users/{user}/referral/edit', [\App\Http\Controllers\UserReferralController::class, 'edit'])->name('users.referral.edit');
    Route::put('/users/{user}/referral', [\App\Http\Controllers\UserReferralController::class, 'update'])->name('users.referral.update');
    Route::delete('/users/{user}/referral', [\App\Http\Controllers\UserReferralController::class, 'destroy'])->name('users.referral.destroy');
    Route::post('/users/{user}/generate-referral-code', [\App\Http\Controllers\UserReferralController::class, 'generateCode'])->name('users.referral.generate-code');
    Route::get('/users/{user}/referrals', [\App\Http\Controllers\UserReferralController::class, 'userReferrals'])->name('users.referrals.show');
    
});
    
/*
|--------------------------------------------------------------------------
| Settings & Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';