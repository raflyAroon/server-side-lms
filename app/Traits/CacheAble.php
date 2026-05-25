<?php

namespace App\Traits;

use App\Services\CacheService;
use Illuminate\Support\Facades\App;

trait Cacheable
{
    protected function cache(): CacheService
    {
        return App::make(CacheService::class);
    }

    // Dashboard caches
    protected function rememberDashboard(string $role, int $userId, callable $callback): mixed
    {
        $key = "dashboard:{$role}:{$userId}";
        return $this->cache()->remember($key, $callback, 60); // 1 menit
    }

    protected function forgetDashboard(string $role, int $userId): void
    {
        $this->cache()->forget("dashboard:{$role}:{$userId}");
    }

    // FAQ cache
    protected function rememberFaqs(): mixed
    {
        return $this->cache()->remember('faqs:all', function () {
            return \App\Models\Faq::orderBy('display_order')->get();
        }, 3600); // 1 jam
    }

    protected function forgetFaqs(): void
    {
        $this->cache()->forget('faqs:all');
    }

    // Announcements cache (personalisasi)
    protected function rememberAnnouncements(int $teamId, ?int $stageId): mixed
    {
        $key = "announcements:team:{$teamId}:stage:" . ($stageId ?? 0);
        return $this->cache()->remember($key, function () use ($teamId, $stageId) {
            return \App\Models\Announcement::where(function ($q) use ($teamId, $stageId) {
                $q->where('type', 'global')
                  ->orWhere(function ($q2) use ($teamId) {
                      $q2->where('type', 'team')->where('target_team_id', $teamId);
                  })
                  ->orWhere(function ($q3) use ($stageId) {
                      if ($stageId) {
                          $q3->where('type', 'stage')->where('target_stage_id', $stageId);
                      }
                  });
            })->orderBy('published_at', 'desc')->get();
        }, 300); // 5 menit
    }

    protected function forgetAnnouncementsByTeam(int $teamId): void
    {
        $this->cache()->forgetPattern("announcements:team:{$teamId}:*");
    }

    protected function forgetAllAnnouncementsCache(): void
    {
        $this->cache()->forgetPattern("announcements:*");
    }

    // Rubric cache
    protected function rememberRubric(int $stageId): mixed
    {
        $key = "rubric:stage:{$stageId}";
        return $this->cache()->remember($key, function () use ($stageId) {
            return \App\Models\Rubric::where('stage_id', $stageId)->with('criteria')->get();
        }, 3600); // 1 jam
    }

    protected function forgetRubric(int $stageId): void
    {
        $this->cache()->forget("rubric:stage:{$stageId}");
    }

    // Team cache (untuk show team)
    protected function rememberTeam(int $teamId): mixed
    {
        $key = "team:{$teamId}";
        return $this->cache()->remember($key, function () use ($teamId) {
            return \App\Models\Team::with('ketua', 'histories')->find($teamId);
        }, 600); // 10 menit
    }

    protected function forgetTeam(int $teamId): void
    {
        $this->cache()->forget("team:{$teamId}");
    }
}