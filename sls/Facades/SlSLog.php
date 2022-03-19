<?php

namespace Zikix\SLS\Facades;

use Aliyun\SLS\Responses\GetHistogramsResponse;
use Aliyun\SLS\Responses\GetLogsResponse;
use Aliyun\SLS\Responses\ListLogStoresResponse;
use Aliyun\SLS\Responses\ListTopicsResponse;
use Illuminate\Support\Facades\Facade;

/**
 * Class SlSLog
 * @package Lokielse\LaravelSLS\Facades
 * @method ListLogStoresResponse listLogStores($project = null) static
 * @method bool putLogs($data, $topic = null, $source = null, $time = null) static
 * @method ListTopicsResponse listTopics() static
 * @method GetHistogramsResponse getHistograms($from = null, $to = null, $query = null, $topic = null) static
 * @method GetLogsResponse getLogs($from = null, $to = null, $query = null, $topic = null, $line = 100, $offset = null, $reverse = true) static
 * @method mixed|string getProject() static
 */
class SlSLog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sls';
    }
}