<?php

namespace Zikix\Zikix;

use Illuminate\Database\Connection;
use Spatie\SqlCommenter\Comment;
use Spatie\SqlCommenter\Commenters\Commenter;

class ZikixCommenter implements Commenter
{
    public function __construct(protected bool $includeNamespace = false) {}

    /**
     * @param string $query
     * @param Connection $connection
     *
     * @return Comment|array|Comment[]|null
     */
    public function comments(string $query, Connection $connection): Comment|array|null
    {
        return [
            Comment::make('rid', Context::getRequestId()),
        ];
    }

}
