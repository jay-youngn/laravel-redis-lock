<?php

namespace RedisLock\Providers;

use RedisLock\Processor;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Redis as RedisFacade;

class RedisLockServiceProvider extends ServiceProvider
{
    /**
     * Defer load.
     *
     * @var  boolean
     */
    protected $defer = true;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../redislock.config.php' => config_path('redislock.php'),
        ], 'config');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Processor::class, function() {
            $config = config('redislock');
            return new Processor(RedisFacade::connection($config['connection']), $config);
        });
    }

    public function provides()
    {
        return [Processor::class];
    }
}
