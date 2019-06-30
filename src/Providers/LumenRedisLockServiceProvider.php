<?php

namespace RedisLock\Providers;

class LumenRedisLockServiceProvider extends RedisLockServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->app->configure('redislock');
        $this->mergeConfigFrom(__DIR__ . '/../redislock.config.php', 'redislock');
    }
}
