<?php

namespace App\Http\Controllers\Officer;

use App\Enums\ApplicationStatus;
use App\Enums\InspectionStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Document;
use App\Models\Inspection;
use App\Models\Sector;
use App\Models\Stage;
use App\Models\User;
use App\Notifications\ApplicationStageChanged;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OfficerApplicationController extends Controller
{
    // GET /api/officer/applications
    // The officer's review queue: applications grouped under each stage their
    // role(s) handle. A role may own more than one stage (e.g. evaluator).
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $roles = $user->getRoleNames()->all();
        $stages = Stage::forRoles($roles);

        $groups = $stages->map(fn (Stage $stage) => [
            'stage' => [
                'key'   => $stage->key,
                'label' => $stage->label,
                'order' => $stage->order,
            ],
            'applications' => Application::query()
                ->select([
                    'id', 'project_title', 'requested_amount', 'currency',
                    'current_stage', 'current_status', 'prev_stage', 'prev_status', 'created_at',
                ])
                ->withCount(['documents as documents_count' => fn ($q) => $q->visibleTo($user)])
                ->where('current_stage', $stage->key)
                ->latest()
                ->get(),
        ])->values();

        return response()->json($groups);
    }

    // GET /api/applications/{application}/process/{stage}
    // Process-page data for one stage. Requires the user's role to own the stage.
    // If the application is not currently at this stage, everything is read-only.
    public function process(Request $request, Application $application, string $stage): JsonResponse
    {
        $user  = $request->user();
        $roles = $user->getRoleNames()->all();

        $stageModel = Stage::visible()->firstWhere('key', $stage);
        abort_unless($stageModel, 404, 'Stage not found.');
        abort_unless(in_array($stageModel->role, $roles, true), 403, 'You are not assigned to this stage.');

        // Read-only unless the application is actually sitting at this stage.
        $readOnly = $application->current_stage !== $stage;

        // In edit mode, make sure an inspection exists for every current sector of
        // the stage — but only create the MISSING ones; never touch existing rows.
        if (! $readOnly) {
            $this->ensureInspections($application, $stage);
        }

        $inspections = Inspection::where('application_id', $application->id)
            ->where('stage_key', $stage)
            ->orderBy('sector_section')   // NULLs sort last in Postgres ASC
            ->orderBy('sector_order')
            ->get();

        // Documents for this application + stage, grouped by sector_key so each
        // inspection can show its own file list.
        $docsBySector = Document::with(['file', 'creator:id,first_name,last_name', 'updater:id,first_name,last_name'])
            ->where('application_id', $application->id)
            ->where('stage_key', $stage)
            ->latest()
            ->get()
            ->groupBy('sector_key');

        $items = $inspections->map(function (Inspection $insp) use ($docsBySector, $readOnly, $user) {
            $docs = $docsBySector->get($insp->sector_key, collect());

            return [
                'id'                 => $insp->id,
                'sector_key'         => $insp->sector_key,
                'sector_label'       => $insp->sector_label,
                'sector_description' => $insp->sector_description,
                'sector_section'     => $insp->sector_section,
                'sector_order'       => $insp->sector_order,
                'status'             => $insp->status?->value,
                'note'               => $insp->note,
                'documents'          => $docs->map(fn (Document $d) => $this->presentDoc($d, $readOnly, $user))->values(),
                'documents_count'    => $docs->count(),
            ];
        });

        return response()->json([
            'stage'       => ['key' => $stageModel->key, 'label' => $stageModel->label],
            'read_only'   => $readOnly,
            'application' => ['id' => $application->id, 'project_title' => $application->project_title],
            'inspections' => $items->values(),
        ]);
    }

    // POST /api/applications/{application}/process/{stage}/complete
    // Record a Pass/Reject decision for the stage the application currently sits at.
    // Pass → advances to the next stage; Reject → sends it back to a chosen earlier
    // stage. Either way we move the workflow, snapshot progress, and (optionally)
    // seed extra "Additional Inquiries" inspections at the target stage.
    public function complete(Request $request, Application $application, string $stage): JsonResponse
    {
        $user  = $request->user();
        $roles = $user->getRoleNames()->all();

        $current = Stage::visible()->firstWhere('key', $stage);
        abort_unless($current, 404, 'Stage not found.');
        abort_unless(in_array($current->role, $roles, true), 403, 'You are not assigned to this stage.');
        abort_unless($application->current_stage === $stage, 422, 'This application is not currently at this stage.');

        $validated = $request->validate([
            'decision'                  => ['required', 'in:pass,reject'],
            'note'                      => ['nullable', 'string'],
            'target_stage'              => ['nullable', 'string'],
            'inspections'               => ['array'],
            'inspections.*.label'       => ['required', 'string', 'max:255'],
            'inspections.*.description' => ['nullable', 'string'],
        ]);

        $visible = Stage::visible();

        if ($validated['decision'] === 'pass') {
            $target   = $visible->where('order', '>', $current->order)->sortBy('order')->first();
            $outgoing = ApplicationStatus::PASSED;
        } else {
            $target = $visible->firstWhere('key', $validated['target_stage'] ?? '');
            abort_unless(
                $target && $target->order < $current->order,
                422,
                'Choose an earlier stage to send this application back to.',
            );
            $outgoing = ApplicationStatus::REJECTED;
        }

        $name = preg_replace('/\s+/', ' ', trim("{$user->first_name} {$user->last_name}"));
        $note = $validated['note'] ?? null;

        DB::transaction(function () use ($application, $current, $target, $outgoing, $user, $name, $note, $validated) {
            if ($target) {
                $verb = $outgoing === ApplicationStatus::PASSED ? 'passed' : 'sent back';
                $desc = "{$name} {$verb}: {$current->label} → {$target->label}";
                $application->recordTransition($outgoing, $target->key, ApplicationStatus::PENDING, $user->id, $desc, $note);

                if (! empty($validated['inspections'])) {
                    $section = "Additional Inquiries from {$current->label}";
                    foreach ($validated['inspections'] as $i => $item) {
                        Inspection::create([
                            'application_id'     => $application->id,
                            'stage_key'          => $target->key,
                            'sector_key'         => 'addl__' . (Str::slug($item['label']) ?: 'item') . '__' . Str::lower(Str::random(6)),
                            'sector_label'       => $item['label'],
                            'sector_description' => $item['description'] ?? null,
                            'sector_section'     => $section,
                            'sector_order'       => $i,
                            'status'             => InspectionStatus::PENDING,
                            'created_by'         => $user->id, // manually added → attribute it
                            'updated_by'         => $user->id,
                        ]);
                    }
                }
            } else {
                // Passing the final stage — mark complete in place (no next stage).
                $desc = "{$name} completed the final stage: {$current->label}";
                $application->recordTransition($outgoing, $current->key, ApplicationStatus::PASSED, $user->id, $desc, $note);
            }
        });

        // Alert the users assigned to the stage the application just arrived at.
        // No-ops unless the master switch (config app.enable_email_notification) is on.
        if ($target) {
            $this->notifyStageRole(
                $application,
                $target,
                $current->label,
                $outgoing === ApplicationStatus::PASSED ? 'passed' : 'rejected',
                $note,
            );
        }

        return response()->json([
            'message'       => 'Decision recorded.',
            'current_stage' => $application->fresh()->current_stage,
        ]);
    }

    // Email the active users whose role owns the destination stage that an
    // application has been passed/rejected onto their desk. Fully wired but
    // gated behind the ENABLE_EMAIL_NOTIFICATION master switch (default off).
    private function notifyStageRole(
        Application $application,
        Stage $toStage,
        string $fromStageLabel,
        string $decision,
        ?string $note,
    ): void {
        if (! config('app.enable_email_notification')) {
            return; // logic is in place; it activates when the flag is turned on
        }

        $recipients = User::role($toStage->role)->where('status', 'active')->get();
        if ($recipients->isEmpty()) {
            return;
        }

        try {
            Notification::send($recipients, new ApplicationStageChanged(
                $application->id,
                $application->project_title,
                $decision,
                $fromStageLabel,
                $toStage->label,
                $note,
            ));
        } catch (\Throwable $e) {
            report($e); // best-effort; never fail a recorded decision over email
        }
    }

    // Create an inspection (sector snapshot) for each of the stage's current
    // sectors that doesn't already have one for this application. Idempotent.
    private function ensureInspections(Application $application, string $stageKey): void
    {
        $existing = Inspection::where('application_id', $application->id)
            ->where('stage_key', $stageKey)
            ->pluck('sector_key')
            ->all();

        Sector::where('stage_key', $stageKey)
            ->orderBy('order')
            ->get()
            ->reject(fn (Sector $s) => in_array($s->key, $existing, true))
            ->each(fn (Sector $s) => Inspection::snapshotFromSector($application->id, $s, null, $stageKey));
    }

    private function presentDoc(Document $d, bool $readOnly, User $user): array
    {
        $disk = Storage::disk('s3');

        return [
            'id'          => $d->id,
            'description' => $d->description,
            'flag'        => $d->flag,
            'flag_note'   => $d->flag_note,
            'stage_key'   => $d->stage_key,
            'sector_key'  => $d->sector_key,
            'created_at'  => $d->created_at,
            'updated_at'  => $d->updated_at,
            'submitted_by' => $d->creator
                ? preg_replace('/\s+/', ' ', trim("{$d->creator->first_name} {$d->creator->last_name}"))
                : null,
            'updated_by' => $d->updater
                ? preg_replace('/\s+/', ' ', trim("{$d->updater->first_name} {$d->updater->last_name}"))
                : null,
            'file' => [
                'original_name' => $d->file->original_name,
                'mime_type'     => $d->file->mime_type,
                'size'          => $d->file->size,
                'extension'     => $d->file->extension,
                'about'         => $d->file->about,
                'tags'          => $d->file->tags,
            ],
            'view_url'   => $disk->temporaryUrl($d->file->object_key, now()->addMinutes(30)),
            // Only the original uploader may delete, and never in read-only mode.
            'can_delete' => ! $readOnly && $d->created_by === $user->id,
        ];
    }
}
