<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sector extends Model
{
    protected $fillable = [
        'key',
        'label',
        'description',
        'section',
        'stage_key',
        'order',
    ];

    // Sectors belong to a stage by its string key (not id) — see the FK in the
    // migration (onUpdate cascade keeps this in sync if a stage key ever changes).
    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'stage_key', 'key');
    }
}
