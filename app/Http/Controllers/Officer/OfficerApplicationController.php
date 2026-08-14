<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Document;
use App\Models\Inspection;
use App\Models\Sector;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
                ->withCount('documents')
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
        $docsBySector = Document::with(['file', 'creator:id,first_name,last_name'])
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
            'created_at'  => $d->created_at,
            'submitted_by' => $d->creator
                ? preg_replace('/\s+/', ' ', trim("{$d->creator->first_name} {$d->creator->last_name}"))
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
