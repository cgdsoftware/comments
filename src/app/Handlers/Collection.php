<?php

namespace LaravelEnso\CommentsManager\app\Handlers;

use LaravelEnso\CommentsManager\app\Models\Comment;

class Collection
{
    private $request;
    private $commentable;
    private $query;

    public function __construct(array $request)
    {
        $this->commentable = (new ConfigMapper($request['type']))->class();
        $this->request = $request;
        $this->query = $this->query();
    }

    public function data()
    {
        return [
            'count' => $this->count(),
            'comments' => $this->collection(),
        ];
    }

    private function query()
    {
        return Comment::whereCommentableType($this->commentable)
            ->whereCommentableId($this->request['id'])
            ->orderBy('created_at', 'desc');
    }

    private function count()
    {
        return $this->query->count();
    }

    private function collection()
    {
        return $this->query->skip($this->request['offset'])
            ->take($this->request['paginate'])
            ->get();
    }
}