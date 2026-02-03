<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class NotificationController extends Controller
{

    public function index()
    {
        $columns = ['title', 'message', 'type', 'created_at'];

        // Global search across multiple columns
        $globalSearch = getGlobalSearchFilter($columns);

        // Build the query
        $notifications = QueryBuilder::for(Notification::class)
            ->with(['actionByUser', 'requestedByUser']) // if you have relationships
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([$globalSearch])
            ->paginate(getPaginate())
            ->withQueryString();

        // Render Inertia page
        return Inertia::render('notification/Index', [
            'notifications' => $notifications, // or wrap in a resource/transformer
// Optionally pass related data if needed
        ]);
    }
}
