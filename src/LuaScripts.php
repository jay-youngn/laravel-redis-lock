<?php

namespace RedisLock;

class LuaScripts
{
    /**
     * Get the Lua script for delete a lock.
     *
     * KEYS[1] - The lock key, for example: mutex-lock:foo
     * ARGV[1] - The token string for lock.
     *
     * @return string
     */
    public static function del()
    {
        return <<<'LUA'
if redis.call('get', KEYS[1]) == ARGV[1] then return redis.call('del', KEYS[1]) else return 0 end
LUA;
    }

    /**
     * Get the Lua script for delay a lock.
     *     - Use expire cmd.
     *
     * KEYS[1] - The lock key, for example: mutex-lock:foo
     * ARGV[1] - The token string for lock.
     * ARGV[2] - The milliseconds which the lock should be expire.
     *
     * @return string
     */
    public static function expire()
    {
        return <<<'LUA'
if redis.call('get', KEYS[1]) == ARGV[1] then return redis.call('expire', KEYS[1], ARGV[2]) else return 0 end
LUA;
    }

    /**
     * Get the Lua script for delay a lock.
     *     - Use pexpire cmd.
     *
     * KEYS[1] - The lock key, for example: mutex-lock:foo
     * ARGV[1] - The token string for lock.
     * ARGV[2] - The milliseconds which the lock should be expire.
     *
     * @return string
     */
    public static function pexpire()
    {
        return <<<'LUA'
if redis.call('get', KEYS[1]) == ARGV[1] then return redis.call('pexpire', KEYS[1], ARGV[2]) else return 0 end
LUA;
    }
}
