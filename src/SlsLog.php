<?php

namespace Zikix\Zikix;

use Aliyun\SLS\Client;
use Aliyun\SLS\Exception;
use Aliyun\SLS\Models\LogItem;
use Aliyun\SLS\Requests\GetHistogramsRequest;
use Aliyun\SLS\Requests\GetLogsRequest;
use Aliyun\SLS\Requests\ListLogStoresRequest;
use Aliyun\SLS\Requests\ListTopicsRequest;
use Aliyun\SLS\Requests\PutLogsRequest;
use Aliyun\SLS\Responses\GetHistogramsResponse;
use Aliyun\SLS\Responses\GetLogsResponse;
use Aliyun\SLS\Responses\ListLogStoresResponse;
use Aliyun\SLS\Responses\ListTopicsResponse;
use Illuminate\Support\Arr;

class SlsLog
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $project;

    /**
     * @var string
     */
    protected $logStore;


    public function __construct(Client $client)
    {
        $this->client = $client;
    }


    /**
     * List log stores in project
     *
     * @param null $project
     *
     * @return ListLogStoresResponse
     * @throws Exception
     */
    public function listLogStores($project = null)
    {
        $project = $project ?: $this->project;
        $request = new ListLogStoresRequest($project);
        return $this->client->listLogStores($request);
    }


    /**
     * Write logs to store
     *
     * @param array $data
     * @param null $topic
     * @param null $source
     * @param null $time
     *
     * @return bool
     * @throws Exception
     */
    public function putLogs($data, $topic = null, $source = null, $time = null): bool
    {
        $logItem  = new LogItem($data, $time);
        $request  = new PutLogsRequest($this->project, $this->logStore, $topic, $source, [$logItem]);
        $response = $this->client->putLogs($request);

        return Arr::get($response->getAllHeaders(), '_info.http_code') === 200;
    }


    /**
     * List topics in store
     *
     * @return ListTopicsResponse
     * @throws Exception
     */
    public function listTopics()
    {
        $request = new ListTopicsRequest($this->project, $this->logStore);
        return $this->client->listTopics($request);
    }


    /**
     * Get history logs
     *
     * @param null $from
     * @param null $to
     * @param null $query
     * @param null $topic
     *
     * @return GetHistogramsResponse
     * @throws Exception
     */
    public function getHistograms($from = null, $to = null, $query = null, $topic = null)
    {
        $request = new GetHistogramsRequest($this->project, $this->logStore, $from, $to, $topic, $query);
        return $this->client->getHistograms($request);
    }


    /**
     * Get logs in store
     *
     * @param null $from
     * @param null $to
     * @param null $query
     * @param null $topic
     * @param int $line
     * @param null $offset
     * @param boolean $reverse
     *
     * @return GetLogsResponse
     * @throws Exception
     */
    public function getLogs(
        $from = null,
        $to = null,
        $query = null,
        $topic = null,
        $line = 100,
        $offset = null,
        $reverse = true
    )
    {
        $request = new GetLogsRequest($this->project, $this->logStore, $from, $to, $topic, $query, $line, $offset,
                                      $reverse);
        return $this->client->getLogs($request);
    }


    /**
     * @return mixed|string
     */
    public function getProject()
    {
        return $this->project;
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setProject($value)
    {
        $this->project = $value;

        return $this;
    }


    /**
     * @return mixed|string
     */
    public function getLogStore()
    {
        return $this->logStore;
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setLogStore($value)
    {
        $this->logStore = $value;

        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
