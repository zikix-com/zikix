<?php

namespace Zikix\Zikix;

use Illuminate\Database\Connection;
use Spatie\SqlCommenter\Comment;
use Spatie\SqlCommenter\Commenters\Commenter;

class RequestIdCommenter implements Commenter
{
    public function __construct(protected bool $includeNamespace = false) {}

    /** @return Comment|Comment[]|null */
    public function comments(string $query, Connection $connection): Comment|array|null
    {
        return [
            Comment::make('rid', Api::getRequestId()),
        ];
    }

}
