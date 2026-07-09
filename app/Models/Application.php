<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $fillable = [
        'applicant_id',
        'organization_id',
        'organization_details',
        'project_title',
        'project_location',
        'requested_amount',
        'currency',
        'project_details',
        'current_stage',
        'current_status',
        'prev_stage',
        'prev_status',
        'updated_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'organization_details' => 'array',
            'project_details'      => 'array',
            'metadata'             => 'array',
            'requested_amount'     => 'decimal:2',
            'current_status'       => ApplicationStatus::class,
            'prev_status'          => ApplicationStatus::class,
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'current_stage', 'key');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ApplicationLog::class);
    }
}
