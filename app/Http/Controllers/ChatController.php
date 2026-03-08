<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use App\Notifications\NewMessageNotification;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index()
    {
        $conversations = Conversation::where('buyer_id', auth()->id())
            ->orWhere('seller_id', auth()->id())
            ->with(['buyer', 'seller', 'product', 'messages' => function($q) {
                $q->latest()->limit(1);
            }])
            ->orderBy('last_message_at', 'desc')
            ->get();

        return Inertia::render('chat/Index', [
            'conversations' => $conversations
        ]);
    }

    public function show(Conversation $conversation)
    {
        // Authorize
        if (!in_array(auth()->id(), [$conversation->buyer_id, $conversation->seller_id])) {
            abort(403);
        }

        // Get all conversations for the sidebar
        $conversations = Conversation::where('buyer_id', auth()->id())
            ->orWhere('seller_id', auth()->id())
            ->with(['buyer', 'seller', 'product', 'messages' => function($q) {
                $q->latest()->limit(1);
            }])
            ->orderBy('last_message_at', 'desc')
            ->get();

        // Load current conversation with messages
        $conversation->load(['messages.sender', 'buyer', 'seller', 'product']);

        // Mark unread as read
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // ALWAYS return Inertia render
        return Inertia::render('chat/Show', [
            'conversation' => $conversation,
            'messages' => $conversation->messages,
            'conversations' => $conversations
        ]);
    }

    public function start(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'seller_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:ads,id'
        ]);

        // Check if conversation already exists
        $conversation = Conversation::where(function($query) use ($request) {
                $query->where(function($q) use ($request) {
                    $q->where('buyer_id', auth()->id())
                    ->where('seller_id', $request->seller_id);
                })->orWhere(function($q) use ($request) {
                    $q->where('buyer_id', $request->seller_id)
                    ->where('seller_id', auth()->id());
                });
            })->first();

        // If no conversation exists, create one
        if (!$conversation) {
            $conversation = Conversation::create([
                'buyer_id' => auth()->id(),
                'seller_id' => $request->seller_id,
                // 'product_id' => $request->product_id,
                'last_message_at' => now()
            ]);
        }

        // Return the conversation ID
        return redirect()->route('chat.show', $conversation);
    }


    public function send(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'body' => 'required|string'
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);

        if (!in_array(auth()->id(), [
            $conversation->buyer_id,
            $conversation->seller_id
        ])) {
            abort(403);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'body' => $request->body,
            'is_read' => false
        ]);

        $conversation->update([
            'last_message_at' => now()
        ]);

        $message->load('sender');

        // Determine receiver
        $receiverId = auth()->id() == $conversation->buyer_id
            ? $conversation->seller_id
            : $conversation->buyer_id;

        $receiver = \App\Models\User::find($receiverId);

        // Send notification
        if ($receiver && $receiver->id !== auth()->id()) {
            $receiver->notify(
                new NewMessageNotification($conversation, $message)
            );
        }

        // Broadcast message event
        broadcast(new MessageSent($message))->toOthers();

        return redirect()->back();
    }

    public function deleteMessage(Request $request, Message $message)
    {

        $conversationId = $message->conversation_id;
        $message->delete();

        // Broadcast message deleted event
        // broadcast(new MessageSent('message is deleted'))->toOthers();

        return redirect()->back()->with('success', 'Message deleted successfully');
    }
}

    