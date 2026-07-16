<?php

namespace App\Models;

use App\Models\User;
use App\Support\DocumentAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'file_id',
        'application_id',
        'stage_key',
        'sector_key',
        'description',
        'flag',
        'flag_note',
        'created_by',
        'updated_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    // Who originally attached the file — set once at creation, never changed
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Limit to documents whose stage_key the given user's role is allowed to see.
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $stages = DocumentAccess::visibleStages($user);

        // null = every stage is visible for this role
        if ($stages === null) {
            return $query;
        }

        return $query->whereIn('stage_key', $stages);
    }
}
