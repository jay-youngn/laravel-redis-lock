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
            __DIR__.'/../redislock.config.php' => config_path('redislock.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../redislock.config.php', 'redislock');
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton(Processor::class, function($app) {
            $config = $app['config']['redislock'];

            if ($this->versionCompare('5.4', '>=')) {
                $predisClient = $app['redis']->connection($config['connection'])->client();
            } else {
                $predisClient = $app['redis']->connection($config['connection']);
            }

            return new Processor(
                $predisClient,
                $config['retry_count'],
                $config['retry_delay']
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

    /**
     * Compare illuminate component version.
     *     - illuminate/redis 5.4 has a big upgrade.
     *
     * @param   string  $compareVersion
     * @param   string  $operator
     * @return  bool|null
     */
    protected function versionCompare(string $compareVersion, string $operator)
    {
        return version_compare($this->app->version(), $compareVersion, $operator);
    }
}
