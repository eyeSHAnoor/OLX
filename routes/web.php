<?php

use App\Http\Controllers\AdController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\RegionController;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Broadcast;
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
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/account', [HomeController::class, 'account'])->name('account');
Route::get('/all-items', [SearchController::class, 'allItems'])->name('all.items');
Route::get('/category/{slug?}', [CategoryController::class, 'show'])->name('category.show');
Route::post('/category/filter', [CategoryController::class, 'filter'])->name('category.filter');
Route::get('/user/{id}', [PublicProfileController::class, 'show'])->name('user.profile');
Route::get('/register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])->name('register');

// Ads Public Routes
Route::get('/ads/{ad}', [AdController::class, 'show'])->name('ads.show');

// Orders Public Routes
Route::get('/orders/{order}/review', [OrderController::class, 'review'])->name('orders.review');
Route::post('/orders/{order}/complete', [OrderController::class, 'completed'])->name('orders.complete');
Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

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
Route::get('/policy/{type}', [PolicyController::class, 'show'])->name('policy.show');
Route::get('/aboutus', [AboutController::class, 'index'])->name('aboutus');
Route::get('/page/{pageKey}', [AboutController::class, 'show'])->name('public.page');
Route::get('/page', [AboutController::class, 'nav']);
Route::get('/regions/{cityName}', [RegionController::class, 'getByCityName']);
Route::post('/contact/send', [AboutController::class, 'send'])->name('contact.send')->middleware('throttle:3,1');

// Search
Route::get('/search-suggestions', [SearchController::class, 'suggestions']);

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
require __DIR__ . '/authenticated.php';

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/super_admin.php';

/*
|--------------------------------------------------------------------------
| Settings & Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';