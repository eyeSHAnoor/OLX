<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ad;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\OrderStatusNotification;
use App\Notifications\RankUpNotification;
use App\Events\MessageSent;
use App\Notifications\NewMessageNotification;
use App\Events\OrderCreated;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $user = auth()->user();
        $status = $request->get('status', 'pending');
        $view = $request->get('view', 'buying');
        
        // Get orders with counts for statistics
        $query = Order::with(['buyer', 'seller', 'ad.images']);
        
        // Filter based on view type for main orders
        if ($view === 'buying') {
            $query->where('buyer_id', $user->id);
        } else {
            $query->where('seller_id', $user->id);
        }
        
        // Filter by status
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $orders = $query->latest()->paginate(10);
        
        // Add role to each order
        $orders->getCollection()->transform(function ($order) use ($user) {
            $order->role = $order->buyer_id === $user->id ? 'buyer' : 'seller';
            return $order;
        });
        
        // Get statistics for all statuses (buying)
        $buyingStats = [];
        $statuses = ['pending', 'accepted', 'rejected', 'completed', 'cancelled'];
        foreach ($statuses as $statStatus) {
            $buyingStats[$statStatus] = Order::where('buyer_id', $user->id)
                ->where('status', $statStatus)
                ->count();
        }
        $buyingStats['total'] = array_sum($buyingStats);
        
        // Get statistics for all statuses (selling)
        $sellingStats = [];
        foreach ($statuses as $statStatus) {
            $sellingStats[$statStatus] = Order::where('seller_id', $user->id)
                ->where('status', $statStatus)
                ->count();
        }
        $sellingStats['total'] = array_sum($sellingStats);
        
        return Inertia::render('orders/Index', [
            'orders' => $orders,
            'currentStatus' => $status,
            'currentView' => $view,
            'buyingStats' => $buyingStats,
            'sellingStats' => $sellingStats,
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'ad_id' => ['required', 'exists:ads,id'],
            'qty' => ['nullable', 'integer', 'min:1'],
        ]);

        $qty = $request->qty ?? 1;

        $ad = Ad::findOrFail($request->ad_id);

        if ($ad->user_id === auth()->id()) {
            return redirect()->back()->with('Error', 'You cannot order your own ad');
        }

        $alreadyExists = Order::where('buyer_id', auth()->id())
            ->where('ad_id', $ad->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyExists) {
            return redirect()->back()->with('Error', 'You already have a pending order for this ad');
        }

        // Create order
        $order = Order::create([
            'buyer_id' => auth()->id(),
            'seller_id' => $ad->user_id,
            'ad_id' => $ad->id,
            'price' => $ad->price,
            'qty' => $qty,

        ]);

        broadcast(new OrderCreated($order))->toOthers();

        $ad->user->notify(new NewOrderNotification($order));

        /*
        |--------------------------------------------------------------------------
        | Create / Get Conversation
        |--------------------------------------------------------------------------
        */

        $conversation = Conversation::firstOrCreate(
                            [
                                'buyer_id' => auth()->id(),
                                'seller_id' => $ad->user_id,
                            ],
                            [
                                'product_id' => $ad->id, // only used if conversation is newly created
                                'last_message_at' => now()
                            ]
                        );

        /*
        |--------------------------------------------------------------------------
        | Send System Message
        |--------------------------------------------------------------------------
        */

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'body' => "🛒 Order #{$order->id} created for {$qty} item(s).",
            'is_read' => false
        ]);

        $conversation->update([
            'last_message_at' => now()
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return redirect()
            ->route('chat.show', $conversation->id)
            ->with('Success', 'Order placed and chat opened');
    }

    // Accept order
    public function accept($orderId)
    {
        $order = Order::findOrFail($orderId);

        // Only seller can accept
        if ($order->seller_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Update status
        $order->status = 'accepted';
        $order->save();

        $seller = $order->seller; // ✅ define seller

        $newRank = $seller->calculateRank();

        if ($order->ad) {
            $order->ad->update([
                // 'is_active' => false,
                'status' => 'sold'
            ]);
        }

        if ($newRank > $seller->rank) {

            $seller->update([
                'rank' => $newRank
            ]);

            // Send notification
            $seller->notify(new RankUpNotification($newRank));
        }

        // Notify buyer
        $order->buyer->notify(new OrderStatusChangedNotification($order));

        return redirect()->back()->with('success', 'Order accepted and buyer notified.');
    }

    // Cancel order
    public function reject($orderId)
    {
        $order = Order::findOrFail($orderId);

        // Only seller can cancel
        if ($order->seller_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Update status
        $order->status = 'rejected';
        $order->save();

        // Notify buyer
        $order->buyer->notify(new OrderStatusChangedNotification($order));

        return redirect()->back()->with('success', 'Order rejected and buyer notified.');
    }

    // public function requestReview(Order $order)
    // {

    //     broadcast(new ReviewRequested($order));

    //     return redirect()->back()->with('success', 'Request send to buyer to review your order');;
    // }

    public function completed(Order $order)
    {
        $order->update([
            'status' => 'completed'
        ]);

        // notify buyer
        $order->buyer->notify(new OrderStatusNotification($order, 'completed'));

        $seller = $order->seller;

        $completedOrders = $seller->sellerOrders()
                            ->where('status', 'completed')
                            ->count();

        $newRank = $seller->calculateRank();

        if ($order->ad) {
            $order->ad->update([
                'is_active' => false,
                'status' => 'sold'
            ]);
        }

        if ($newRank > $seller->rank) {

            $seller->update([
                'rank' => $newRank
            ]);

            // Send notification
            $seller->notify(new RankUpNotification($newRank));
        }

        return back()->with('success', 'Order completed successfully');
    }

    public function cancel(Order $order)
    {
        $order->update([
            'status' => 'cancelled'
        ]);

        // notify buyer
        $order->buyer->notify(new OrderStatusNotification($order, 'cancelled'));

        return back()->with('success', 'Order cancelled');
    }

    public function review(Order $order)
    {
        $order->load(['ad','seller']);

        return Inertia::render('orders/Review', [
            'order' => $order
        ]);
    }
}