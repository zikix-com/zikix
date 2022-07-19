<?php

namespace Zikix\Zikix;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use Aliyun\SLS\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ZikixServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function boot()
    {
        $this->publishes([dirname(__DIR__) . '/config/zikix.php' => config_path('zikix.php')]);
    }

    /**
     * Add the connector to the queue drivers.
     *
     * @return void
     * @throws ClientException
     */
    public function register()
    {
        Route::get('/health', HealthController::class);

        if (config('zikix.access_key_id')) {
            AlibabaCloud::accessKeyClient(
                config('zikix.access_key_id'),
                config('zikix.access_key_secret')
            )->asDefaultClient()->regionId('cn-hangzhou');
        }

        $this->app->singleton('sls', function ($app) {

            $config = $app['config']['zikix'];

            $accessKeyId     = Arr::get($config, 'sls_access_key');
            $accessKeySecret = Arr::get($config, 'sls_access_secret');
            $endpoint        = Arr::get($config, 'sls_endpoint');
            $project         = Arr::get($config, 'sls_project');
            $store           = Arr::get($config, 'sls_store');

            $client = new Client($endpoint, $accessKeyId, $accessKeySecret);

            $log = new SlsLog($client);
            $log->setProject($project);
            $log->setLogStore($store);

            return $log;
        });

        $this->app->singleton('zikix.context', function ($app) {
            return new ContextManager($app);
        });

    }
}
