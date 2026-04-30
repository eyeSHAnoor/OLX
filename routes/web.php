<?php

// use App\Http\Controllers\StudentController;
use App\Mail\MyTestEmail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdController;
use Illuminate\Support\Facades\Mail;

use App\Http\Controllers\PushSubscriptionController;


Route::get('/cities/{city}/regions', function (City $city) {
    return $city->regions()->pluck('name');
});

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/account', [\App\Http\Controllers\HomeController::class, 'account'])->name('account');
Route::get('/all-items', [App\Http\Controllers\SearchController::class, 'allItems'])->name('all.items');
Route::get('/category/{slug?}', [CategoryController::class, 'show'])->name('category.show');
Route::post('/category/filter', [CategoryController::class, 'filter'])->name('category.filter');
Route::get('/user/{id}', [App\Http\Controllers\PublicProfileController::class, 'show'])->name('user.profile');
Route::get('/ads/{ad}', [App\Http\Controllers\AdController::class, 'show'])->name('ads.show');
Route::get('/orders/{order}/review', [App\Http\Controllers\OrderController::class, 'review'])
    // ->middleware('signed')
    ->name('orders.review');
Route::post('/orders/{order}/complete', [App\Http\Controllers\OrderController::class,'completed'])->name('orders.complete');
Route::post('/orders/{order}/cancel', [App\Http\Controllers\OrderController::class,'cancel'])->name('orders.cancel');
Route::post('/set-city', function (\Illuminate\Http\Request $request) {

Log::info('Set-city session write', ['city' => $request->city, 'region' => $request->region]);
    session(['city' => $request->city]);
    session(['region' => $request->region]); // Clear region if city is Pakistan
    return back();
})->name('set.city');

// Route::post('/set-city', function (\Illuminate\Http\Request $request) {
//     session(['city' => $request->city]);
    
//     if ($request->city === 'Pakistan' || empty($request->region)) {
//         session()->forget('region');
//     } else {
//         session(['region' => $request->region]);
//     }
    
//     return back();
// })->name('set.city');

Route::get('/policy/{type}', [App\Http\Controllers\PolicyController::class, 'show'])->name('policy.show');
Route::get('/aboutus', [App\Http\Controllers\AboutController::class, 'index'])->name('aboutus');
Route::get('/page/{pageKey}', [App\Http\Controllers\AboutController::class, 'show'])->name('public.page');
Route::get('/page', [App\Http\Controllers\AboutController::class, 'nav']);
Route::get('/regions/{cityName}', [App\Http\Controllers\RegionController::class, 'getByCityName']);
Route::post('/contact/send', [App\Http\Controllers\AboutController::class, 'send'])->name('contact.send');
Route::get('/search-suggestions', [App\Http\Controllers\SearchController::class, 'suggestions']);





Route::get('/artisan-scheduler', function () {
    \Illuminate\Support\Facades\Artisan::call('schedule:run');
    return 'Scheduler triggered at ' . now();
}); // Scheduler needed when we schedule something in root/console.php
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

Broadcast::channel('notifications', function () {
    return true; // anyone can listen
});
Broadcast::routes();
Route::get('/clear', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return back()->with('success', 'Cache cleared.');
})->name('cache.clear');


Route::get('/storagelink', function () {
    \Illuminate\Support\Facades\Artisan::call('storage:link');
    return back()->with('success', 'Storage Link created');
});

Route::middleware(['auth'])->group(function () {


    Route::post('/accept-terms', function () {
            $user = auth()->user();
            $user->update([
                'terms_accepted' => true,
                'terms_accepted_at' => now()
            ]);
            return redirect()->back();
        });

    Route::get('/subscriptions',
        [App\Http\Controllers\SubscriptionController::class,'index'])
        ->name('subscriptions.index');

    Route::post('/subscriptions/manual',
        [App\Http\Controllers\SubscriptionController::class,'submitManual'])
        ->name('subscriptions.manual');

    Route::post('/subscriptions/jazzcash/initiate', [App\Http\Controllers\SubscriptionController::class, 'initiateJazzCash'])
    ->name('subscriptions.jazzcash.initiate')
    ->middleware(['auth']); 

    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
      Route::get('/chat/my-ads', function () {
        $ads = auth()->user()->ads()
            ->where('status', 'active')
            ->where('is_active', true) 
            ->select('id','ad_title','description','price')
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
   
    Route::get('user/ads/create', [App\Http\Controllers\CreateAdController::class, 'index'])->name('user.ads.create');
    Route::get('user/ads/edit/{id}', [App\Http\Controllers\CreateAdController::class, 'edit'])->name('user.ads.edit');
    Route::get('/ads/category-data/{category}', [App\Http\Controllers\CreateAdController::class, 'getCategoryData'])->name('ads.category-data');
    Route::post('user/ads', [App\Http\Controllers\CreateAdController::class, 'store'])->name('user.ads.store');
    Route::get('user/my/ads', [App\Http\Controllers\CreateAdController::class, 'Myads'])->name('user.ads');
    Route::post('/ads', [AdController::class, 'store'])->name('ads.store');
    Route::put('/ads/{ad}', [AdController::class, 'update'])->name('ads.update');
    Route::delete('/ads/{ad}', [AdController::class, 'destroy'])->name('ads.destroy');
    Route::patch('/user/ads/{ad}/status', [App\Http\Controllers\CreateAdController::class, 'updateStatus'])->name('user.ads.status');
    Route::get('categories/{category}/attributes', [AdController::class, 'getAttributesByCategory']);
    Route::get('brands/{brand}/models', [AdController::class, 'getModelsByBrand']);

    Route::get('/profile/edit', [App\Http\Controllers\UserProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [App\Http\Controllers\UserProfileController::class, 'update'])->name('user.profile.update');
    Route::delete('/profile/delete', [App\Http\Controllers\UserProfileController::class, 'destroy'])->name('user.profile.destroy');
    Route::get('/check-username', [App\Http\Controllers\UserProfileController::class, 'checkUsername'])->name('user.check-username');

    Route::post('/ratings', [App\Http\Controllers\RatingController::class, 'store'])->name('ratings.store');
    Route::post('/ads/{ad}/favorite', [App\Http\Controllers\AdFavoriteController::class, 'toggle'])->name('ads.favorite');
      Route::get('/favorites', [App\Http\Controllers\FavoriteAdController::class, 'index'])->name('user.favorites');
    Route::post('/favorites/{ad}/toggle', [App\Http\Controllers\FavoriteAdController::class, 'toggle'])->name('user.favorites.toggle');
    Route::delete('/favorites/{ad}', [App\Http\Controllers\FavoriteAdController::class, 'destroy'])->name('user.favorites.destroy');
    Route::delete('/favorites', [App\Http\Controllers\FavoriteAdController::class, 'clearAll'])->name('user.favorites.clear');
    
    // API endpoint for AJAX filtering
    Route::get('/api/favorites', [App\Http\Controllers\FavoriteAdController::class, 'apiIndex'])->name('api.user.favorites');
    // Users
    Route::resource('users', App\Http\Controllers\UserController::class);

    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/mark-as-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    Route::get('/amo/setting', [App\Http\Controllers\SettingController::class, 'index'])->name('amo.settings');
    Route::post('/amo/change-password', [App\Http\Controllers\SettingController::class, 'update'])->name('password.change');

    Route::get('/reports/create', [App\Http\Controllers\UserReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [App\Http\Controllers\UserReportController::class, 'store'])->name('reports.store');

    Route::get('/orders', [App\Http\Controllers\OrderController::class, 'index'])->name('orders');
    Route::post('/order', [App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{order}/accept', [App\Http\Controllers\OrderController::class, 'accept'])->name('orders.accept');
    Route::post('/orders/{order}/reject', [App\Http\Controllers\OrderController::class, 'reject'])->name('orders.reject');
    // Route::post('/orders/{order}/request-review', [App\Http\Controllers\OrderController::class, 'requestReview'])->name('orders.requestReview');
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');
    
});

Route::middleware([
    'auth',
    'super_admin'
]) 
->group(
        function () {

            Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

            // Category
            Route::resource('categories', CategoryController::class);
            // Brands
            Route::resource('brands', \App\Http\Controllers\BrandController::class);
            Route::get('/api/brand/{brand}', [\App\Http\Controllers\BrandController::class, 'show']);
            Route::get('/api/brands', [\App\Http\Controllers\BrandController::class, 'getName']);
            // Ads
            Route::get('/ads', [AdController::class, 'index'])->name('ads.index');
            Route::get('admin/ads/create', [AdController::class, 'create'])->name('ads.create');
            Route::get('/ads/{ad}/edit', [AdController::class, 'edit'])->name('ads.edit');
            Route::patch('/ads/{ad}', [AdController::class, 'update']); // optional, Laravel accepts both PUT/PATCH
            Route::post('ads/{ad}/set-primary-image', [\App\Http\Controllers\AdController::class, 'setPrimaryImage'])
                ->name('ads.set-primary-image');
            Route::get('/api/brands/{category}', [App\Http\Controllers\BrandController::class, 'getByCategory']);

            //PLANS
            Route::resource('plans', App\Http\Controllers\PlanController::class);

            Route::get('/receipts/{subscription}', [App\Http\Controllers\ReceiptController::class, 'show'])
                ->name('receipts.show');
            Route::get('/receipts/{subscription}/download', [App\Http\Controllers\ReceiptController::class, 'download'])
                ->name('receipts.download');

            
            Route::post('/subscriptions/{user}/complete', [App\Http\Controllers\SubscriptionController::class, 'complete'])->name('subscriptions.complete');
            Route::post('/subscriptions/{user}/reject', [App\Http\Controllers\SubscriptionController::class, 'reject'])->name('subscriptions.reject');

            Route::resource('banners', App\Http\Controllers\BannerController::class)->except(['show']);
            Route::patch('banners/{banner}/toggle-status', [App\Http\Controllers\BannerController::class, 'toggleStatus'])->name('banners.toggle-status');

            Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/{report}', [App\Http\Controllers\ReportController::class, 'show'])->name('reports.show');
            Route::post('/reports/{report}/respond', [App\Http\Controllers\ReportController::class, 'respond'])->name('reports.respond');
            Route::delete('/reports/{report}', [App\Http\Controllers\ReportController::class, 'destroy'])->name('reports.destroy');
            Route::post('/reports/bulk-update', [App\Http\Controllers\ReportController::class, 'bulkUpdate'])->name('reports.bulk-update');

            //  Route::post('/admin/broadcast-message', [App\Http\Controllers\BroadcastController::class, 'store']);
            Route::resource('broadcast-messages', App\Http\Controllers\BroadcastController::class)->except(['show']);
            Route::post('broadcast-messages/{broadcast_message}/toggle-status', [App\Http\Controllers\BroadcastController::class, 'toggleStatus'])
                ->name('broadcast-messages.toggle-status');
            Route::post('/broadcast-message/{message}/send', [App\Http\Controllers\BroadcastController::class, 'broadcast'])
                ->name('broadcast-message.send');

            // // API endpoint (could be in api.php if used externally)
            // Route::get('api/broadcast-messages/active', [App\Http\Controllers\BroadcastController::class, 'getActive'])
            //     ->name('api.broadcast-messages.active');

            Route::resource('page-contents', App\Http\Controllers\PageContentController::class)->except(['show']);
            Route::patch('page-contents/{pageContent}/toggle-status', [App\Http\Controllers\PageContentController::class, 'toggleStatus'])->name('page-contents.toggle-status');
            // Optional: API endpoint for frontend retrieval
            Route::get('page-contents/by-key/{pageKey}', [App\Http\Controllers\PageContentController::class, 'getByPageKey'])->name('page-contents.by-key');

            // City resource routes (index, create, store, show, edit, update, destroy)
            Route::resource('cities', App\Http\Controllers\CityController::class);

            // Additional routes for managing regions under a city
            Route::prefix('cities/{city}')->group(function () {
                Route::get('regions', [App\Http\Controllers\CityController::class, 'getRegions'])->name('cities.regions');
                Route::post('regions', [App\Http\Controllers\CityController::class, 'storeRegions'])->name('cities.regions.store');
            });

            // Routes for direct region management (update/delete)
            Route::prefix('regions')->group(function () {
                Route::put('{region}', [App\Http\Controllers\CityController::class, 'updateRegion'])->name('regions.update');
                Route::delete('{region}', [App\Http\Controllers\CityController::class, 'destroyRegion'])->name('regions.destroy');
            });
                }
    );




    require __DIR__ . '/settings.php';


    require __DIR__ . '/auth.php';

Route::post('/locale', function (Request $request) {
    $request->validate(['locale' => 'required|string']);
    session(['locale' => $request->input('locale')]);
    // return response()->json(['ok' => true]);
    return redirect()->back();
})->name('locale.set');

Route::put('/settings/locale', [App\Http\Controllers\Settings\SetLocaleController::class, 'update'])
    ->name('settings.locale.update');

// Route::get('/.well-known/{any}', function () {
//     abort(404);
// })->where('any', '.*');
