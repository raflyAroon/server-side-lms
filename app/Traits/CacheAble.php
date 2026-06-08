<?php

namespace App\Traits;

use App\Services\CacheService;
use Illuminate\Support\Facades\App;

trait Cacheable
{
    // Sementara bypass cache untuk debugging
    protected function rememberDashboard(string $type, $userId, callable $callback, ?int $ttl = null)
    {
        return $callback();
    }

    protected function forgetDashboard(string $type, $userId)
    {
        // no-op
    }

    protected function rememberTeam(int $teamId, callable $callback, ?int $ttl = null)
    {
        return $callback();
    }

    protected function forgetTeam(int $teamId)
    {
        // no-op
    }

    protected function forgetAnnouncementsByTeam(int $teamId)
    {
        // no-op
    }
}