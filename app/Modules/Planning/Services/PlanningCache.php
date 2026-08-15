<?php

namespace App\Modules\Planning\Services;

use App\Modules\Planning\Models\Planning;
use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;

final class PlanningCache
{
    private const INDEX_KEY_VERSION = 'v1';

    private const INDEX_TTL_SECONDS = 300;

    public function __construct(
        private readonly Repository $cache,
    ) {}

    /**
     * Return the cached Planning collection for one authenticated user.
     *
     * @param  Closure(): Collection<int, Planning>  $load
     * @return Collection<int, Planning>
     */
    public function rememberIndex(int $userId, Closure $load): Collection
    {
        return $this->cache->remember(
            $this->indexKey($userId),
            self::INDEX_TTL_SECONDS,
            $load,
        );
    }

    public function forgetIndex(int $userId): bool
    {
        return $this->cache->forget($this->indexKey($userId));
    }

    public function indexKey(int $userId): string
    {
        return sprintf(
            'planning:index:%s:user:%d',
            self::INDEX_KEY_VERSION,
            $userId,
        );
    }
}
