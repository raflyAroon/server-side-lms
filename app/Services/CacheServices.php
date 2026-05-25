<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    protected int $defaultTtl = 300; // 5 menit

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return Cache::remember($key, $ttl ?? $this->defaultTtl, $callback);
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    public function forgetPattern(string $pattern): void
    {
        $redis = Cache::store('redis')->getRedis();
        $keys = $redis->keys($pattern);
        if (!empty($keys)) {
            $redis->del($keys);
        }
    }

    public function get(string $key): mixed
    {
        return Cache::get($key);
    }

    public function put(string $key, mixed $value, ?int $ttl = null): void
    {
        Cache::put($key, $value, $ttl ?? $this->defaultTtl);
    }

    public function flush(): void
    {
        Cache::flush();
    }
}