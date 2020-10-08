<?php

namespace LaravelEnso\Comments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use LaravelEnso\Comments\Notifications\CommentTagNotification;
use LaravelEnso\Core\Models\User;
use LaravelEnso\Helpers\Traits\HasFactory;
use LaravelEnso\Helpers\Traits\UpdatesOnTouch;
use LaravelEnso\TrackWho\Traits\CreatedBy;
use LaravelEnso\TrackWho\Traits\UpdatedBy;

class Comment extends Model
{
    use CreatedBy, HasFactory, UpdatedBy, UpdatesOnTouch;

    protected $guarded = ['id'];

    protected $touches = ['commentable'];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function taggedUsers()
    {
        return $this->belongsToMany(User::class);
    }

    public function scopeFor($query, array $params)
    {
        $query->whereCommentableId($params['commentable_id'])
            ->whereCommentableType($params['commentable_type']);
    }

    public function scopeOrdered($query)
    {
        $query->orderBy('created_at', 'desc');
    }

    public function syncTags(array $taggedUsers)
    {
        $this->taggedUsers()->sync(
            (new Collection($taggedUsers))->pluck('id')
        );

        return $this;
    }

    public function notify(string $path)
    {
        $this->taggedUsers->each->notify(
            (new CommentTagNotification($this->body, $path))
                ->onQueue('notifications')
        );
    }

    public function getLoggableMorph()
    {
        return config('enso.comments.loggableMorph');
    }
}