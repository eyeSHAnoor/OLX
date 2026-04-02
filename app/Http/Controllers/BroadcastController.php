<?php

namespace App\Http\Controllers;

use App\Models\AdminBroadcastMessage;
use App\Events\BroadcastAdminMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BroadcastController extends Controller
{
    /**
     * Display a listing of broadcast messages.
     */
    public function index()
    {
        $columns = [
            'title',
            'body',
            'is_active',
            'created_at',
        ];

        // Global search helper (searches title and body)
        $globalSearch = getGlobalSearchFilter($columns, ['title', 'body']);

        $messages = QueryBuilder::for(AdminBroadcastMessage::class)
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([
                $globalSearch,
                AllowedFilter::exact('is_active'),
            ])
            ->paginate(getPaginate())
            ->withQueryString();

        return Inertia::render('broadcast/Index', [
            'messages' => $messages,
        ]);
    }

    /**
     * Show a specific broadcast message.
     */
    public function show(AdminBroadcastMessage $broadcastMessage)
    {
        return response()->json($broadcastMessage);
    }

    /**
     * Store a new broadcast message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'body'      => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        DB::transaction(function () use ($request) {
            AdminBroadcastMessage::create($request->only([
                'title',
                'body',
                'is_active',
            ]));
        });

        return redirect()->back()->with('success', 'Broadcast message created successfully.');
    }

    /**
     * Update an existing broadcast message.
     */
    public function update(Request $request, AdminBroadcastMessage $broadcastMessage)
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'body'      => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        DB::transaction(function () use ($request, $broadcastMessage) {
            $broadcastMessage->update($request->only([
                'title',
                'body',
                'is_active',
            ]));
        });

        return redirect()->back()->with('success', 'Broadcast message updated successfully.');
    }

    /**
     * Delete a broadcast message.
     */
    public function destroy(AdminBroadcastMessage $broadcastMessage)
    {
        DB::transaction(function () use ($broadcastMessage) {
            $broadcastMessage->delete();
        });

        return redirect()->back()->with('success', 'Broadcast message deleted successfully.');
    }

    /**
     * Toggle the active status of a broadcast message.
     */
    public function toggleStatus(AdminBroadcastMessage $broadcastMessage)
    {
        $broadcastMessage->update([
            'is_active' => !$broadcastMessage->is_active,
        ]);

        return redirect()->back()->with('success', 'Broadcast message status updated successfully.');
    }

    /**
     * API endpoint: return active broadcast messages.
     */
    public function getActive()
    {
        $messages = AdminBroadcastMessage::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($messages);
    }

    public function broadcast(AdminBroadcastMessage $message)
    {
        broadcast(new BroadcastAdminMessage([
            'id' => $message->id,
            'title' => $message->title,
            'body' => $message->body,
        ]));

        return redirect()->back()->with('success', 'Message broadcasted successfully.');
    }
}