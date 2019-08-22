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

    /**
     * {@inheritdoc}
     */
    protected function versionCompare(string $compareVersion, string $operator)
    {
        // Lumen (5.8.12) (Laravel Components 5.8.*)
        $lumenVersion = $this->app->version();

        if (preg_match('/Lumen \((\d\.\d\.\d{1,2})\)( \(Laravel Components (\d\.\d\.\*)\))?/', $lumenVersion, $matches)) {
            // Prefer Laravel Components version.
            $lumenVersion = isset($matches[3]) ? $matches[3] : $matches[1];
        }

        return version_compare($lumenVersion, $compareVersion, $operator);
    }
}
