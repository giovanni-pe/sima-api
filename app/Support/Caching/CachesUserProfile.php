<?php

namespace App\Support\Caching;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

trait CachesUserProfile
{
    /** TTL del perfil (segundos) */
    protected int $profileTtl = 600;   // 10 min

    protected function profileCacheKey(int $userId): string
    {
        return "user_profile:{$userId}";
    }

    /**
     * Devuelve el perfil desde caché o lo genera y guarda si no existe.
     */
    protected function rememberProfile(Model $user): array
    {
        return Cache::remember(
            $this->profileCacheKey($user->id),
            $this->profileTtl,
            fn () => $this->formatUserData(
                $user->load(['roles.permissions'])
            )
        );
    }

    /** Invalida la caché del perfil para el usuario indicado. */
    protected function bustProfileCache(int $userId): void
    {
        Cache::forget($this->profileCacheKey($userId));
    }
}
