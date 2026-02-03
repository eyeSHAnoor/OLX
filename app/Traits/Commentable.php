<?php

namespace App\Traits;

use App\Models\Comment;

trait Commentable
{
    protected static function bootCommentable()
    {
        static::deleting(function ($model) {
            $model->comments->each->delete();
        });
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }


    public function addComments($title, $details, $userId = null)
    {
        $newComment = new Comment([
            'user_id' => $userId ?? auth()->id(),
            'title' => $title,
            'details' => $details,
        ]);

        $comment = $this->comments()->save($newComment);

//        $comment->addFiles('attachments');

    }
}
