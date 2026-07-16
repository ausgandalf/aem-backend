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

    public function progresses(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * The workflow engine: move prev/current stage+status, write an audit log,
     * and upsert the per-stage progress snapshot(s). Every stage move goes through here.
     */
    public function recordTransition(
        ApplicationStatus $outgoingStatus,
        string $toStage,
        ApplicationStatus $toStatus,
        int $userId,
        string $description,
        ?string $note = null,
    ): void {
        $fromStage = $this->current_stage;

        $this->update([
            'prev_stage'     => $fromStage,
            'prev_status'    => $outgoingStatus,
            'current_stage'  => $toStage,
            'current_status' => $toStatus,
            'updated_by'     => $userId,
        ]);

        ApplicationLog::record([
            'application_id' => $this->id,
            'stage_key'      => $fromStage,
            'status'         => $toStatus->value,
            'updated_by'     => $userId,
            'description'    => $description,
            'note'           => $note,
        ]);

        // Snapshot the outgoing stage's status, then the incoming stage's (if different)
        $this->upsertProgress($fromStage, $outgoingStatus, $note);
        if ($toStage !== $fromStage) {
            $this->upsertProgress($toStage, $toStatus);
        }
    }

    public function upsertProgress(
        string $stageKey,
        ApplicationStatus $status,
        ?string $note = null,
        ?array $metadata = null,
    ): void {
        Progress::updateOrCreate(
            ['application_id' => $this->id, 'stage_key' => $stageKey],
            ['status' => $status, 'note' => $note, 'metadata' => $metadata],
        );
    }

    // Applicant "Save": stays at submit, prev + current both submit/pending
    public function saveDraft(int $userId, string $applicantName): void
    {
        $this->recordTransition(
            ApplicationStatus::PENDING,
            'submit',
            ApplicationStatus::PENDING,
            $userId,
            "{$applicantName} saved application draft : {$this->project_title}",
        );
    }

    // Applicant "Submit": submit passed → moves to evaluation_1/pending
    public function submitForReview(int $userId, string $applicantName): void
    {
        $this->recordTransition(
            ApplicationStatus::PASSED,
            'evaluation_1',
            ApplicationStatus::PENDING,
            $userId,
            "{$applicantName} submitted application for review : {$this->project_title}",
        );
    }
}
