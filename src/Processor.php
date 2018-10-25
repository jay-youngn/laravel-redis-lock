<?php

namespace RedisLock;

use Predis\Client;
use RedisLock\LuaScripts;

/**
 * Simple mutex lock.
 *
 *     __ _(_)_ __  _ __   ___ _ __ _ __   ___  __ _  ___ ___
 *    / _` | | '_ \| '_ \ / _ \ '__| '_ \ / _ \/ _` |/ __/ _ \
 *   | (_| | | | | | | | |  __/ |  | |_) |  __/ (_| | (_|  __/
 *    \__, |_|_| |_|_| |_|\___|_|  | .__/ \___|\__,_|\___\___|
 *    |___/                        |_|
 *
 * @author gjy <ginnerpeace@live.com>
 * @link https://github.com/ginnerpeace/laravel-redis-lock
 */
final class Processor
{
    // Redis key prefix
    const KEY_PREFIX = 'mutex-lock:';

    // Response string from redis cmd: set
    const LOCK_SUCCESS = 'OK';

    // Response string from redis lua script: eval
    const UNLOCK_SUCCESS = 1;

    // Params for cmd: set
    const IF_NOT_EXIST = 'NX';

    // Expire type: seconds
    const EXPIRE_TIME_SECONDS = 'EX';

    // Expire type: milliseconds
    const EXPIRE_TIME_MILLISECONDS = 'PX';

    /**
     * Predis Client.
     *
     * @var  Predis\Client
     */
    private $client;

    /**
     * Expire type for the lock key.
     *
     * @var  string
     */
    private $expireType;

    /**
     * How many times do you want to try again.
     *     (milliseconds)
     *
     * @var  integer
     */
    private $retryDelay = 200;

    /**
     * Number of retry times.
     *
     * @var  int
     */
    private $retryCount = 3;

    /**
     * This params from service provider.
     *
     * @param   Predis\Client  $client
     * @param   array  $config
     */
    public function __construct(Client $client, array $config)
    {
        $this->client = $client;

        $this->retryCount = $config['retry'];
        $this->retryDelay = $config['delay'];

        $this->setExpireType(self::EXPIRE_TIME_MILLISECONDS);
    }

    /**
     * Set key expire type.
     *
     * @param   string  $value
     * @return  self
     */
    public function setExpireType(string $value): self
    {
        $this->expireType = $value;

        return $this;
    }

    /**
     * Set retry number of times.
     *
     * @param   int  $retry
     * @return  self
     */
    public function setRetry(int $retry): self
    {
        $this->retryCount = $retry;

        return $this;
    }

    /**
     * Do it.
     *
     * @param   string  $key
     * @param   int  $expire
     * @param   string  $token
     * @return  array
     */
    protected function hit(string $key, int $expire): array
    {

        if (self::LOCK_SUCCESS === (string) $this->client->set(
            self::KEY_PREFIX . $key,
            $token = uniqid(mt_rand()),
            $this->expireType,
            $expire,
            self::IF_NOT_EXIST
        )) {
            return [
                'key' => $key,
                'token' => $token,
                'expire' => $expire,
                'expire_type' => $this->expireType,
            ];
        }

        return [];
    }

    /**
     * Get lock.
     *
     * @param   string  $key
     * @param   int  $expire
     * @param   bool  $isWaitingMode
     * @return  array
     *          - Not empty for getted lock.
     *          - Empty for lock timeout.
     */
    public function lock(string $key, int $expire, bool $isWaitingMode = true): array
    {
        $retry = $isWaitingMode ? $this->retryCount : 0;

        while (! $result = $this->hit($key, $expire)) {
            if (0 > $retry--) {
                return $result;
            }

            usleep(mt_rand(floor($this->retryDelay / 2), $this->retryDelay) * 1000);
        };

        return $result;
    }

    /**
     * Release the lock.
     *
     * @param   array  $payload
     * @return  bool
     */
    public function unlock(array $payload): bool
    {
        if (! isset($payload['key'], $payload['token'])) {
            return false;
        }

        return self::UNLOCK_SUCCESS === $this->client->eval(
            LuaScripts::del(),
            1,
            self::KEY_PREFIX . $payload['key'],
            $payload['token']
        );
    }
}
