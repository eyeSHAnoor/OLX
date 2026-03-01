<?php

// use App\Http\Controllers\StudentController;
use App\Mail\MyTestEmail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdController;

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/all-items', [App\Http\Controllers\SearchController::class, 'allItems'])->name('all.items');
Route::get('/category/{slug?}', [CategoryController::class, 'show'])->name('category.show');
Route::post('/category/filter', [CategoryController::class, 'filter'])->name('category.filter');
Route::get('/user/{id}', [App\Http\Controllers\PublicProfileController::class, 'show'])->name('user.profile');
Route::get('/ads/{ad}', [App\Http\Controllers\AdController::class, 'show'])->name('ads.show');
Route::post('/set-city', function (\Illuminate\Http\Request $request) {
    session(['city' => $request->city]);

    return back();
})->name('set.city');

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

    Route::get('/subscriptions',
        [App\Http\Controllers\SubscriptionController::class,'index'])
        ->name('subscriptions.index');

    Route::post('/subscriptions/manual',
        [App\Http\Controllers\SubscriptionController::class,'submitManual'])
        ->name('subscriptions.manual');

    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/send', [App\Http\Controllers\ChatController::class, 'send'])->name('chat.send');
    Route::post('/chat/start', [App\Http\Controllers\ChatController::class, 'start'])->name('chat.start');

    Route::get('user/ads/create', [App\Http\Controllers\CreateAdController::class, 'index'])->name('user.ads.create');
    Route::get('user/ads/edit/{id}', [App\Http\Controllers\CreateAdController::class, 'edit'])->name('user.ads.edit');
    Route::get('/ads/category-data/{category}', [App\Http\Controllers\CreateAdController::class, 'getCategoryData'])->name('ads.category-data');
    Route::post('user/ads', [App\Http\Controllers\CreateAdController::class, 'store'])->name('user.ads.store');
    Route::get('my/ads', [App\Http\Controllers\CreateAdController::class, 'Myads'])->name('my.ads');
    Route::post('/ads', [AdController::class, 'store'])->name('ads.store');
    Route::put('/ads/{ad}', [AdController::class, 'update'])->name('ads.update');

    

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
            Route::delete('/ads/{ad}', [AdController::class, 'destroy'])->name('ads.destroy');
            Route::post('ads/{ad}/set-primary-image', [\App\Http\Controllers\AdController::class, 'setPrimaryImage'])
                ->name('ads.set-primary-image');
            Route::get('/api/brands/{category}', [App\Http\Controllers\BrandController::class, 'getByCategory']);

            //PLANS
            Route::resource('plans', App\Http\Controllers\PlanController::class);

            // Users
            Route::resource('users', App\Http\Controllers\UserController::class);
            Route::get('/receipts/{subscription}', [App\Http\Controllers\ReceiptController::class, 'show'])
                ->name('receipts.show');
            Route::get('/receipts/{subscription}/download', [App\Http\Controllers\ReceiptController::class, 'download'])
                ->name('receipts.download');

            
            Route::post('/subscriptions/{user}/complete', [App\Http\Controllers\SubscriptionController::class, 'complete'])->name('subscriptions.complete');
            Route::post('/subscriptions/{user}/reject', [App\Http\Controllers\SubscriptionController::class, 'reject'])->name('subscriptions.reject');

            Route::resource('banners', App\Http\Controllers\BannerController::class)->except(['show']);
            Route::patch('banners/{banner}/toggle-status', [App\Http\Controllers\BannerController::class, 'toggleStatus'])->name('banners.toggle-status');


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
