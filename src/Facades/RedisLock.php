<?php

namespace RedisLock\Facades;

use RedisLock\Processor;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array lock(string $key, int $expire, int $retry = null)
 * @method static bool unlock(array $payload)
 * @method static array relock(array $payload, int $expire)
 * @method static bool verify(array $payload)
 * @method static $this setExpireType(string $value)
 * @method static $this setRetryDelay(int $milliseconds)
 *
 * @see \RedisLock\Processor
 */
class RedisLock extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Processor::class;
    }
}
