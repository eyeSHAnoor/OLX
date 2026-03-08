<?php

namespace App\Http\Middleware;

// Remove this line: use App\Models\Notification;
use App\Models\Setting;
use App\Models\Category;
use App\Data\CategoryData;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use Illuminate\Support\Facades\Auth;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */

    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user()?->load('preferences','profile');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => [
                'message' => trim($message),
                'author' => trim($author),
            ],
            'auth' => [
                'user' => $user ? array_merge(
                    $user->toArray(),
                    ['roles' => method_exists($user, 'getRoleNames') ? $user->getRoleNames() : []],
                    ['permissions' => method_exists($user, 'getAllPermissions') ? $user->getAllPermissions()->pluck('name') : []],
                    ['subscription_status' => $user->subscriptionStatus()],
                    [
                        'image' => $user->profile
                    ]
                ) : null,
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => !$request->hasCookie('sidebar_state')
                || $request->cookie('sidebar_state') === 'true',

            'settings' => Setting::getAll(),
            'flash' => fn() => [
                'success' => session()->get('success'),
                'error' => session()->get('error'),
                'open_order_id' => session()->get('open_order_id'),
                'preview_data' => fn() => $request->session()->get('preview_data'),
                'searchResults' => session()->get('searchResults'),
                'searchedArticle' => session()->get('searchedArticle'),
            ],

            'locale' => fn() => App::getLocale(),
            'translations' => function () {
                $locale = App::getLocale();
                $path = lang_path("$locale.json");

                if (!File::exists($path))
                    return [];

                $content = File::get($path);

                // Remove BOM if present
                $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

                // Decode safely
                return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            },
            'topCategories' => function () {
            $categories = Category::whereNull('parent_id')
                ->with([
                    'childrenRecursive.files', 
                    'files',                  
                ]) 
                ->get();

            return $categories;
            },
            'selectedCity' => session('city', 'Pakistan'),
            'notifications' => fn() => $user 
                ? $user->notifications()->latest()->take(10)->get()->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'data' => $notification->data, // No need to json_decode, it's already cast to array
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at,
                        'type' => $notification->type,
                    ];
                })
                : [],

            'unreadCount' => fn() => $user
                ? $user->unreadNotifications()->count()
                : 0,
        ];
    }
}