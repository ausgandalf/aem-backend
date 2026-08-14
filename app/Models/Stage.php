<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Stage extends Model
{
    public const CACHE_KEY = 'stages.ordered';

    protected $fillable = [
        'key',
        'label',
        'role',
        'order',
        'status',
    ];

    // A stage's sectors, linked by the stage's string key, kept in display order.
    public function sectors(): HasMany
    {
        return $this->hasMany(Sector::class, 'stage_key', 'key')->orderBy('order');
    }

    // Flush the cache whenever a stage is created/updated/deleted
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * All stages, ordered, served from cache (stages rarely change).
     *
     * @return Collection<int, Stage>
     */
    public static function cached(): Collection
    {
        // Cache plain arrays (not Eloquent models) to avoid fragile object
        // serialization, then rehydrate into Stage models on read.
        $rows = Cache::rememberForever(
            self::CACHE_KEY,
            fn () => self::orderBy('order')->get()->toArray(),
        );

        return self::hydrate($rows);
    }

    /**
     * Only stages that are switched on, in order — used for anything applicant-
     * facing (e.g. the progress diagram). Retired stages have status 'off'.
     *
     * @return Collection<int, Stage>
     */
    public static function visible(): Collection
    {
        return self::cached()->where('status', 'on')->values();
    }

    /**
     * On-stages handled by any of the given role names, in order. Used to build
     * an officer's queue (a role may own more than one stage).
     *
     * @param  array<int, string>  $roles
     * @return Collection<int, Stage>
     */
    public static function forRoles(array $roles): Collection
    {
        return self::visible()->whereIn('role', $roles)->values();
    }
}
