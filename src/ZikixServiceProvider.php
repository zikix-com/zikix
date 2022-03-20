<?php

namespace Zikix\Component;

use Aliyun\SLS\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Zikix\Component\Sls\SlsLog;

class ZikixServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function boot()
    {
        $this->publishes([realpath(__DIR__ . '/../config/zikix.php') => config_path('zikix.php')]);
    }

    /**
     * Add the connector to the queue drivers.
     *
     * @return void
     */
    public function register()
    {
        Route::get('/health', [HealthController::class, 'action']);

        $this->app->singleton('sls', function ($app) {
            $config = $app['config']['zikix'];

            $accessKeyId     = Arr::get($config, 'access_key_id');
            $accessKeySecret = Arr::get($config, 'access_key_secret');
            $endpoint        = Arr::get($config, 'endpoint');
            $project         = Arr::get($config, 'project');
            $store           = Arr::get($config, 'store');

            $client = new Client($endpoint, $accessKeyId, $accessKeySecret);

            $log = new SlsLog($client);
            $log->setProject($project);
            $log->setLogStore($store);

            return $log;
        });

    }
}
