<?php

namespace Zikix\Zikix;

use Closure;
use Illuminate\Database\Connection;
use Laravel\SerializableClosure\Support\ReflectionClosure;
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

        [$controller, $action] = $this->getControllerAndAction();

        return [
            Comment::make('rid', Api::getRequestId()),
            Comment::make('controller', $controller),
            Comment::make('time', date('Y-m-d H:i:s')),
        ];
    }

    /**
     * @return array
     */
    protected function getControllerAndAction(): array
    {
        $action = request()->route()->getAction('uses');

        if ($action instanceof Closure) {
            $reflection = new ReflectionClosure($action);
            $controller = 'Closure';
            $action     = $reflection->getFileName();

            return [$controller, $action];
        }

        $controller = explode('@', $action)[0] ?? null;

        if (!$this->includeNamespace) {
            $controller = class_basename($controller);
        }

        $action = explode('@', $action)[1] ?? null;

        return [$controller, $action];
    }

}
