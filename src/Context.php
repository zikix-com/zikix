<?php

namespace Zikix\Zikix;

use JsonException;

class Context
{

    /**
     * @return string
     * @throws JsonException
     */
    public static function serialize(): string
    {
        return app('zikix.context')->serialize();
    }

    /**
     * @param string $string
     */
    public static function unserialize(string $string)
    {
        app('zikix.context')->unserialize($string);
    }

    /**
     * @param array $context
     *
     * @return void
     */
    public static function set(array $context): void
    {
        app('zikix.context')->set($context);
    }

    /**
     * @param string $key
     * @param $value
     *
     * @return void
     */
    public static function append(string $key, $value): void
    {
        app('zikix.context')->append($key, $value);
    }

    /**
     * @param string $key
     * @param $item
     *
     * @return void
     */
    public static function push(string $key, $item): void
    {
        app('zikix.context')->push($key, $item);
    }

    /**
     * @return array
     */
    public static function get(): array
    {
        return app('zikix.context')->get();
    }

    /**
     * @param string $requestId
     *
     * @return void
     */
    public static function setRequestId(string $requestId): void
    {
        app('zikix.context')->setRequestId($requestId);

    }

    /**
     * @return string
     */
    public static function getRequestId(): string
    {
        return app('zikix.context')->getRequestId();
    }

}
