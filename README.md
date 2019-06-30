# laravel-redis-lock

[![Total Downloads](https://poser.pugx.org/ginnerpeace/laravel-redis-lock/downloads.svg)](https://packagist.org/packages/ginnerpeace/laravel-redis-lock)
[![Latest Stable Version](https://poser.pugx.org/ginnerpeace/laravel-redis-lock/v/stable.svg)](https://packagist.org/packages/ginnerpeace/laravel-redis-lock)
[![Latest Unstable Version](https://poser.pugx.org/ginnerpeace/laravel-redis-lock/v/unstable.svg)](https://packagist.org/packages/ginnerpeace/laravel-redis-lock)
[![License](https://poser.pugx.org/ginnerpeace/laravel-redis-lock/license.svg)](https://packagist.org/packages/ginnerpeace/laravel-redis-lock)

> Simple mutex lock, redis edition.

## Getting started

### Install
> Using composer.
```bash
# Laravel/Lumen < 5.4
composer require "ginnerpeace/laravel-redis-lock:~1.1"

# Laravel/Lumen >= 5.4
composer require "ginnerpeace/laravel-redis-lock:~2.1"
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

### Use
```php
<?php

use RedisLock\Facades\RedisLock;

// Ex. 1
$millisecond = 100000;

$payload = RedisLock::lock('key', $millisecond);
/*
[
    "key" => "key",
    "token" => "21456004925bd1532e64616",
    "expire" => 100000,
    "expire_type" => "PX",
]
*/

// Ex. 2
$second = 100;

/**
 * Its singleton instance, same as the following:
 * RedisLock::setExpireType('EX');
 * RedisLock::lock('key', $second);
 */
$payload = RedisLock::setExpireType('EX')->lock('key', $second);
/*
[
    "key" => "key",
    "token" => "5069829505bd154c8bb865",
    "expire" => 100,
    "expire_type" => "EX",
]
*/

// Is locking.
$payload = RedisLock::lock('key', 100000);
/*
[]
*/

// Return bool.
RedisLock::unlock($payload);

// Try 5 times if can't get lock at first hit.
// Default value: 3
RedisLock::setRetry(5)->lock('key', 100000);

// Try once only.
RedisLock::lock('key', 100000, false);

```
