<?php

namespace App\Models;

use App\Enums\InspectionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inspection extends Model
{
    protected $fillable = [
        'application_id',
        'stage_key',
        'sector_key',
        'sector_label',
        'sector_description',
        'sector_section',
        'sector_order',
        'status',
        'note',
        'metadata',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status'   => InspectionStatus::class,
            'metadata' => 'array',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    // The stage this inspection belongs to (real FK).
    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'stage_key', 'key');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Best-effort lookup of the *live* sector this inspection was copied from.
     * May be null — the sector could have been deleted or renamed since. The
     * snapshot columns (sector_label/description/section) are the source of truth.
     */
    public function liveSector(): ?Sector
    {
        return Sector::where('key', $this->sector_key)->first();
    }

    /**
     * Create an inspection for an application by COPYING a sector's data at this
     * moment. The inspection keeps its own frozen copy — later changes to (or
     * deletion of) the sector never affect it. Stage defaults to the sector's own.
     */
    public static function snapshotFromSector(
        int $applicationId,
        Sector $sector,
        ?int $userId = null,
        ?string $stageKey = null,
        InspectionStatus $status = InspectionStatus::PENDING,
    ): self {
        return self::create([
            'application_id'     => $applicationId,
            'stage_key'          => $stageKey ?? $sector->stage_key,
            'sector_key'         => $sector->key,
            'sector_label'       => $sector->label,
            'sector_description' => $sector->description,
            'sector_section'     => $sector->section,
            'sector_order'       => $sector->order,
            'status'             => $status,
            'updated_by'         => $userId,
        ]);
    }
}
