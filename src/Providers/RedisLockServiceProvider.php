<?php

namespace RedisLock\Providers;

use RedisLock\Processor;
use Illuminate\Support\ServiceProvider;

class RedisLockServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../redislock.config.php' => config_path('redislock.php'),
        ], 'config');
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton(Processor::class, function($app) {
            $config = $app['config']['redislock'];
            return new Processor(
                $app['redis']->connection($config['connection']),
                $config
            );
        });
    }

    /**
     * {@inheritdoc}
     */
    public function isDeferred()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return [Processor::class];
    }
}
