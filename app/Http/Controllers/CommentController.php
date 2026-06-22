<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\CommentReplyNotification;
use App\Notifications\CommentLikedNotification;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'ad_id'     => 'required|exists:ads,id',
            'message'   => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
            'type'      => 'nullable|string',
        ]);

        $comment = Comment::create([
            'user_id'   => Auth::id(),
            'ad_id'     => $request->ad_id,
            'parent_id' => $request->parent_id,
            'type'      => $request->type ?? 'text',
            'message'   => $request->message,
        ]);

        $comment->load(['user', 'likes', 'replies.user']);

        // 🔔 Notify the parent comment owner (if this is a reply)
        if ($comment->parent_id) {
            $parentComment = Comment::with('user')->find($comment->parent_id);
            if ($parentComment && $parentComment->user && $parentComment->user->id !== Auth::id()) {
                $parentComment->user->notify(new CommentReplyNotification($comment, $parentComment));
            }
        }
        // Optional: notify ad owner on any new top-level comment?
        // If you want, uncomment the block below
        // else {
        //     $ad = Ad::with('user')->find($comment->ad_id);
        //     if ($ad && $ad->user && $ad->user->id !== Auth::id()) {
        //         $ad->user->notify(new \App\Notifications\NewCommentOnAdNotification($comment, $ad));
        //     }
        // }

        return back()->with('success', 'Comment posted!');
    }

    public function toggleLike(Comment $comment)
    {
        $userId = Auth::id();
        $like = $comment->likes()->where('user_id', $userId)->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            $comment->likes()->create(['user_id' => $userId]);
            $liked = true;

            // 🔔 Notify the comment owner (if not the liker)
            if ($comment->user_id !== $userId) {
                $comment->user->notify(new CommentLikedNotification($comment, $userId));
            }

            // 🔔 Also notify the ad owner (if different from comment owner and liker)
            $ad = Ad::with('user')->find($comment->ad_id);
            if ($ad && $ad->user && $ad->user->id !== $userId && $ad->user->id !== $comment->user_id) {
                $ad->user->notify(new CommentLikedNotification($comment, $userId));
            }
        }

        return back()->with('liked', $liked);
    }
}