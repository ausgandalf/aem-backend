<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn () => self::orderBy('order')->get(),
        );
    }
}
