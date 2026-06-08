<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    protected int $defaultTtl = 300;

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return Cache::remember($key, $ttl ?? $this->defaultTtl, $callback);
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    // Sederhanakan forgetPattern - tidak pakai Redis dulu
    public function forgetPattern(string $pattern): void
    {
        // Untuk sementara, lewati karena tidak semua driver support
        // Jika pakai file/database, pattern matching sulit.
        // Nanti bisa dioptimalkan setelah redis berjalan.
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