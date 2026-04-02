<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use App\Notifications\NewMessageNotification;
use App\Events\OrderRequestSent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
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

    public function sendProduct(Request $request)
    {
        // dd($request->all());
        // Validate request data
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            // 'body' => 'required|string',
            'is_order_request' => 'nullable|boolean', // optional flag
            'order_data' => 'nullable|array'         // optional order info
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);

        // Ensure sender is part of the conversation
        if (!in_array(Auth::id(), [$conversation->buyer_id, $conversation->seller_id])) {
            abort(403);
        }

        // Create chat message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'body' => $request->body,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);
        $message->load('sender');

        // Determine receiver
        $receiverId = Auth::id() == $conversation->buyer_id
            ? $conversation->seller_id
            : $conversation->buyer_id;

        $receiver = \App\Models\User::find($receiverId);

        // Notify receiver (if not self)
        if ($receiver && $receiver->id !== Auth::id()) {
            $receiver->notify(new NewMessageNotification($conversation, $message));
        }

        // Broadcast normal chat message to others
        broadcast(new MessageSent($message))->toOthers();

        // -------------------------------
        // Trigger Order Request Popup (if applicable)
        // -------------------------------
        if ($request->is_order_request && $request->order_data) {

            // Merge conversation_id and sender_id into order data
            $orderPayload = array_merge($request->order_data, [
                'conversation_id' => $conversation->id,
                'sender_id' => Auth::id(),
                'buyer_id' => $conversation->buyer_id,
                'status' => 'requested'
            ]);

            // Broadcast order request to buyer and seller channels
            broadcast(new OrderRequestSent($orderPayload));
        }

        return redirect()->back();
    }

    public function deleteMessage(Request $request, Message $message)
    {
        // If message is a file, delete the stored file
        if ($message->type === 'file' && $message->body) {

            if (Storage::disk('public')->exists($message->body)) {
                Storage::disk('public')->delete($message->body);
            }
        }

        $message->delete();

        return back()->with('success', 'Message deleted successfully');
    }

    public function upload(Request $request)
    {
        $request->validate([
             'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm,pdf,doc,docx,xls,xlsx,ppt,pptx,txt|max:10240',
            'conversation_id' => 'required|exists:conversations,id'
        ]);
        
        $path = $request->file('file')->store('chat/' . $request->conversation_id);
        
        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'sender_id' => auth()->id(),
            'body' => $path,
            'type' => 'file',
            'is_read' => false
        ]);
        
        broadcast(new MessageSent($message))->toOthers();
        
        return response()->json(['success' => true]);
    }

    public function file(Message $message)
    {
        $conversation = $message->conversation;

        if (!in_array(auth()->id(), [
            $conversation->buyer_id,
            $conversation->seller_id
        ])) {
            abort(403);
        }

        if (!Storage::exists($message->body)) {
            abort(404);
        }

        return Storage::response($message->body);
    }

    public function destroyConversation(Conversation $conversation)
    {
        // Only buyer or seller can clear conversation
        if (!in_array(Auth::id(), [$conversation->buyer_id, $conversation->seller_id])) {
            abort(403, 'Unauthorized');
        }

        // Delete files inside messages
        foreach ($conversation->messages as $message) {

            if ($message->type === 'file' && $message->body) {

                if (Storage::disk('public')->exists($message->body)) {
                    Storage::disk('public')->delete($message->body);
                }
            }
        }

        // Delete all messages
        $conversation->messages()->delete();

        // Reset last message timestamp (optional but recommended)
        $conversation->update([
            'last_message_at' => null
        ]);

        return back()->with('success', 'Chat cleared successfully');
    }
}

    