<?php

namespace App\Http\Middleware;

use App\Models\Notification;
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

        $user = $request->user()?->load('preferences');

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
                ) : null,
            ],
            'notifications' => fn() => Auth::check()
                ? [
                    'received' => Notification::where('requested_by', Auth::id())
                        ->where('type', 'received')
                        ->latest('created_at')
                        ->take(10)
                        ->get(['id', 'title', 'message', 'url', 'created_at', 'type']),

                    'sent' => Notification::where('requested_by', Auth::id())
                        ->where('type', 'sent')
                        ->latest('created_at')
                        ->take(10)
                        ->get(['id', 'title', 'message', 'url', 'created_at', 'type']),
                ]
                : [],

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
        ];
    }

}
