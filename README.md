# laravel-redis-lock

[![Total Downloads](https://poser.pugx.org/ginnerpeace/laravel-redis-lock/downloads.svg)](https://packagist.org/packages/ginnerpeace/laravel-redis-lock)
[![Latest Stable Version](https://poser.pugx.org/ginnerpeace/laravel-redis-lock/v/stable.svg)](https://packagist.org/packages/ginnerpeace/laravel-redis-lock)
[![Latest Unstable Version](https://poser.pugx.org/ginnerpeace/laravel-redis-lock/v/unstable.svg)](https://packagist.org/packages/ginnerpeace/laravel-redis-lock)
[![License](https://poser.pugx.org/ginnerpeace/laravel-redis-lock/license.svg)](https://packagist.org/packages/ginnerpeace/laravel-redis-lock)

> Simple redis mutex lock in Laravel.
> Support distributed if enable `config('database.redis.cluster')`

## Getting started

### Install
> Using composer.

```bash
composer require "ginnerpeace/laravel-redis-lock:~2.3"
```

### Add service provider:
> Normally.

```php
<?php
return [
    // ....
    'providers' => [
        // ...
        RedisLock\Providers\RedisLockServiceProvider::class,
    ],
    // Its optional.
    'aliases' => [
        // ...
        'RedisLock' => RedisLock\Facades\RedisLock::class,
    ],
    // ...
];
```

> After Laravel 5.5, the package auto-discovery is supported.

```javascript
{
    "providers": [
        "RedisLock\\Providers\\RedisLockServiceProvider"
    ],
    "aliases": {
        "RedisLock": "RedisLock\\Facades\\RedisLock"
    }
}
```

> Lumen

```php
$app->register(RedisLock\Providers\LumenRedisLockServiceProvider::class);
```

### Publish resources (laravel only)
> Copied config to `config/redislock.php`.

```bash
php artisan vendor:publish --provider="RedisLock\Providers\RedisLockServiceProvider"
```

Default items:
```php
<?php

return [
    // Use app('redis')->connection('default')
    'connection' => 'default',
    'retry_count' => 3,
    'retry_delay' => 200,
];

```

### Use
```php
<?php

use RedisLock\Facades\RedisLock;

// Set the specified expire time, in milliseconds.
$millisecond = 100000;

// Try get lock.
// If has non-null property `$this->retryCount`, will retry some times with its value.
// Default value is `config('redislock.retry_count')`
$payload = RedisLock::lock('key', $millisecond);
/*
[
    "key" => "key",
    "token" => "21456004925bd1532e64616",
    "expire" => 100000,
    "expire_type" => "PX",
]
*/

// If cannot get lock, will return empty array.
$payload = RedisLock::lock('key', 100000);
/*
[]
*/

// Return bool.
RedisLock::unlock($payload);

// Determine a lock if it still effective.
RedisLock::verify($payload);

// Reset a lock if it still effective.
// The returned value is same to use RedisLock::lock()
RedisLock::relock($payload, $millisecond);

/////////////////////
// Special usages: //
/////////////////////

// Retry 5 times when missing the first time.
// Non-null `$retry` param will priority over `$this->retryCount`.
RedisLock::lock('key', 100000, 5);

// No retry (Try once only).
RedisLock::lock('key', 100000, 0);
// If value less than 0, still means try once only.
// RedisLock::lock('key', 100000, -1);
// Hmmmmmmm...Not pretty.

// Change property `$this->retryDelay` (Default value is `config('redislock.retry_delay')`).
// Retry 10 times when missing the first time.
// Every retry be apart 500 ~ 1000 milliseconds.
RedisLock::setRetryDelay(1000)->lock('key', 100000, 10);
// PS:
// RedisLock is default registered to singleton, call method `setRetryDelay()` will affects subsequent code.

// Use in business logic:
try {
    if (! $lock = RedisLock::lock('do-some-thing', 100000)) {
        throw new Exception('Resource locked.');
    }
    //////////////////////
    // Call ur methods. //
    //////////////////////
} catch (Exception $e) {
    throw $e;
} finally {
    RedisLock::unlock($lock);
}

```
