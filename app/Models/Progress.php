<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Progress extends Model
{
    // "Progress" is uncountable, so Eloquent would guess "progress" — pin it
    protected $table = 'progresses';

    protected $fillable = [
        'application_id',
        'stage_key',
        'status',
        'note',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status'   => ApplicationStatus::class,
            'metadata' => 'array',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
