<?php

// use App\Http\Controllers\StudentController;
use App\Mail\MyTestEmail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\CategoryController;

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
/*
 * cron job is not running on Hostinger due php8.3 version
 * So creating these routes for temporary solution
 */
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


// Route::get('/dashboard', function () {
//     return redirect()->route('dashboard');
// })->name('dashboard');



Route::middleware([
    'auth',
])->group(
        function () {

            Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

            // Category
            Route::resource('categories', CategoryController::class);
            // Brands
            Route::resource('brands', \App\Http\Controllers\BrandController::class);
            // Ads
            Route::resource('ads', \App\Http\Controllers\AdController::class);
            Route::post('ads/{ad}/set-primary-image', [\App\Http\Controllers\AdController::class, 'setPrimaryImage'])
                ->name('ads.set-primary-image');
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
