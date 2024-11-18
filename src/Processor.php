<?php

namespace RedisLock;

use Predis\ClientInterface;
use RedisLock\LuaScripts;

/**
 * Simple redis mutex lock.
 *
 * @author gjy <ginnerpeace@live.com>
 * @link https://github.com/jay-youngn/laravel-redis-lock
 */
class Processor
{
    // Redis key prefix.
    const KEY_PREFIX = 'mutex-lock:';

    // Expire type is milliseconds.
    const EXPIRE_TYPE = 'PX';

    /**
     * Predis Client.
     *
     * @var \Predis\ClientInterface
     */
    private $client;

    /**
     * Number of retry times.
     *
     * @var int|null
     */
    private $retryCount = null;

    /**
     * How many times do you want to try again.
     *     (milliseconds)
     *
     * @var int
     */
    private $retryDelay = 200;

    /**
     * This params from service provider.
     *
     * @param \Predis\ClientInterface $client
     * @param int|null $retryCount
     * @param int|null $retryDelay
     */
    public function __construct(ClientInterface $client, int $retryCount = null, int $retryDelay = null)
    {
        $this->client = $client;
        $this->retryCount = $retryCount;

        if (isset($retryDelay)) {
            $this->setRetryDelay($retryDelay);
        }
    }

    /**
     * Set retry delay time.
     *
     * @param int $milliseconds
     */
    public function setRetryDelay(int $milliseconds): self
    {
        $this->retryDelay = $milliseconds;

        return $this;
    }

    /**
     * Trying to get lock.
     *
     * @param string $key
     * @param int $expire
     * @param int|null $retry
     * @return array
     *          - Not empty for getted lock.
     *          - Empty for lock timeout.
     */
    public function lock(string $key, int $expire, int $retry = null): array
    {
        $retry = $retry ?? $this->retryCount ?? 0;

        while (! $result = $this->hit($key, $expire)) {
            if ($retry-- < 1) {
                return $result;
            }

            usleep(mt_rand(floor($this->retryDelay / 2), $this->retryDelay) * 1000);
        };

        return $result;
    }

    /**
     * Release the lock.
     *
     * @param array $payload
     * @return bool
     */
    public function unlock(array $payload): bool
    {
        if (! isset($payload['key'], $payload['token'])) {
            return false;
        }

        return 1 === $this->client->eval(
            LuaScripts::del(),
            1,
            self::KEY_PREFIX . $payload['key'],
            $payload['token']
        );
    }

    /**
     * Reset a lock if it still effective.
     *
     * @param array $payload
     * @param int $expire
     * @return array
     *          - Not empty for relock success.
     *          - Empty for cant relock.
     */
    public function relock(array $payload, int $expire): array
    {
        if (
            isset($payload['key'], $payload['token'])
            && 1 === $this->client->eval(
                static::EXPIRE_TYPE === 'PX' ? LuaScripts::pexpire() : LuaScripts::expire(),
                1,
                self::KEY_PREFIX . $payload['key'],
                $payload['token'],
                $expire
            )
        ) {
            return [
                'key' => $payload['key'],
                'token' => $payload['token'],
                'expire' => $expire,
                'expire_type' => static::EXPIRE_TYPE,
            ];
        }

        return [];
    }

    /**
     * Verify lock payload.
     *
     * @param array $payload
     * @return bool
     */
    public function verify(array $payload): bool
    {
        if (! isset($payload['key'], $payload['token'])) {
            return false;
        }

        return $payload['token'] === $this->client->get(self::KEY_PREFIX . $payload['key']);
    }

    /**
     * Do it.
     *
     * @param string $key
     * @param int $expire
     * @param string $token
     * @return array
     */
    protected function hit(string $key, int $expire): array
    {
        if ('OK' === (string) $this->client->set(
            self::KEY_PREFIX . $key,
            $token = uniqid(mt_rand()),
            static::EXPIRE_TYPE,
            $expire,
            'NX'
        )) {
            return [
                'key' => $key,
                'token' => $token,
                'expire' => $expire,
                'expire_type' => static::EXPIRE_TYPE,
            ];
        }

        return [];
    }
}
