<?php

namespace RedisLock\Facades;

use RedisLock\Processor;
use Illuminate\Support\Facades\Facade;

/**
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
