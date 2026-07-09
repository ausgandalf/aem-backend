<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationLog extends Model
{
    protected $fillable = [
        'application_id',
        'stage_key',
        'inspection_id',
        'document_id',
        'status',
        'updated_by',
        'description',
    ];

    /**
     * Append an audit entry for an application-related action.
     * Reused by every workflow action (submit, stage change, inspection, document).
     */
    public static function record(array $attributes): self
    {
        return self::create(array_merge([
            'inspection_id' => 0,
            'document_id'   => 0,
        ], $attributes));
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
