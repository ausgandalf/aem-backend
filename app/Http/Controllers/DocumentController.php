<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationLog;
use App\Models\Document;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    // POST /api/applications/{application}/documents/presign
    // Returns a short-lived S3 PUT URL so the browser uploads directly to the
    // bucket under documents/{application_id}/... — the bytes never touch the API.
    public function presign(Request $request, Application $application): JsonResponse
    {
        $this->authorizeAccess($request->user(), $application);

        $validated = $request->validate([
            'filename'  => ['required', 'string', 'max:255'],
            'mime_type' => ['nullable', 'string', 'max:255'],
        ]);

        $ext = strtolower(pathinfo($validated['filename'], PATHINFO_EXTENSION));
        $key = "documents/{$application->id}/" . Str::uuid()->toString() . ($ext ? ".{$ext}" : '');

        ['url' => $url, 'headers' => $headers] = Storage::disk('s3')->temporaryUploadUrl(
            $key,
            now()->addMinutes(10),
        );

        return response()->json([
            'url'        => $url,
            'headers'    => $headers,
            'object_key' => $key,
            'bucket'     => config('filesystems.disks.s3.bucket'),
            'disk'       => 's3',
        ]);
    }

    // GET /api/applications/{application}/documents
    public function index(Request $request, Application $application): JsonResponse
    {
        $user = $request->user();
        $this->authorizeAccess($user, $application);

        $documents = $application->documents()
            ->with(['file', 'creator:id,first_name,last_name', 'updater:id,first_name,last_name'])
            ->visibleTo($user)
            ->latest()
            ->get()
            ->map(fn (Document $d) => $this->present($d));

        return response()->json($documents);
    }

    // POST /api/applications/{application}/documents
    // Called AFTER the browser has uploaded the object to S3 via the presigned URL.
    public function store(Request $request, Application $application): JsonResponse
    {
        $user = $request->user();
        $this->authorizeAccess($user, $application);

        $validated = $request->validate([
            'object_key'    => ['required', 'string', 'max:1024'],
            'original_name' => ['required', 'string', 'max:255'],
            'mime_type'     => ['nullable', 'string', 'max:255'],
            'about'         => ['nullable', 'string'],
            'tags'          => ['nullable', 'string', 'max:1000'],
            'description'   => ['nullable', 'string'],
        ]);

        $disk = Storage::disk('s3');
        abort_unless(
            $disk->exists($validated['object_key']),
            422,
            'The uploaded file was not found in storage.',
        );

        $document = DB::transaction(function () use ($validated, $user, $application, $disk) {
            $file = File::create([
                'uploaded_by'   => $user->id,
                'disk'          => 's3',
                'bucket'        => config('filesystems.disks.s3.bucket'),
                'object_key'    => $validated['object_key'],
                'original_name' => $validated['original_name'],
                'extension'     => strtolower(pathinfo($validated['original_name'], PATHINFO_EXTENSION)) ?: null,
                'mime_type'     => $validated['mime_type'] ?? null,
                'size'          => $disk->size($validated['object_key']), // authoritative
                'about'         => $validated['about'] ?? null,
                'tags'          => $validated['tags'] ?? null,
            ]);

            $document = Document::create([
                'file_id'        => $file->id,
                'application_id' => $application->id,
                'stage_key'      => $application->current_stage,
                'description'    => $validated['description'] ?? null,
                'flag'           => 'ok',
                'created_by'     => $user->id, // original attacher — never touched again
                'updated_by'     => $user->id,
            ]);

            $name = preg_replace('/\s+/', ' ', trim("{$user->first_name} {$user->last_name}"));
            ApplicationLog::record([
                'application_id' => $application->id,
                'stage_key'      => $application->current_stage,
                'document_id'    => $document->id,
                'updated_by'     => $user->id,
                'description'    => "{$name} uploaded document: {$file->original_name}",
            ]);

            return $document->load(['file', 'creator', 'updater']);
        });

        return response()->json($this->present($document), 201);
    }

    // PATCH /api/applications/{application}/documents/{document}
    // Update the reviewable metadata: "why put this document" + flag/flag note.
    public function update(Request $request, Application $application, Document $document): JsonResponse
    {
        $user = $request->user();
        $this->authorizeAccess($user, $application);
        abort_unless($document->application_id === $application->id, 404);

        $validated = $request->validate([
            'description' => ['nullable', 'string'],
            'flag'        => ['required', 'in:ok,warning,invalid,ignore'],
            'flag_note'   => ['nullable', 'string'],
        ]);

        $document->update([...$validated, 'updated_by' => $user->id]);
        $document->load(['file', 'creator', 'updater']);

        $name = preg_replace('/\s+/', ' ', trim("{$user->first_name} {$user->last_name}"));
        ApplicationLog::record([
            'application_id' => $application->id,
            'stage_key'      => $document->stage_key,
            'document_id'    => $document->id,
            'updated_by'     => $user->id,
            'description'    => "{$name} updated document: {$document->file->original_name}",
        ]);

        return response()->json($this->present($document));
    }

    // Applicants may only touch their own application; staff may touch any.
    private function authorizeAccess(User $user, Application $application): void
    {
        $isOwner = $application->applicant_id === $user->id;
        $isStaff = $user->getRoleNames()->contains(fn ($r) => $r !== 'applicant');

        abort_unless($isOwner || $isStaff, 403, 'You cannot access this application.');
    }

    private function present(Document $d): array
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
            'submitted_by' => $d->creator
                ? preg_replace('/\s+/', ' ', trim("{$d->creator->first_name} {$d->creator->last_name}"))
                : null,
            'updated_by' => $d->updater
                ? preg_replace('/\s+/', ' ', trim("{$d->updater->first_name} {$d->updater->last_name}"))
                : null,
            'updated_at' => $d->updated_at,
            'file'        => [
                'original_name' => $d->file->original_name,
                'mime_type'     => $d->file->mime_type,
                'size'          => $d->file->size,
                'extension'     => $d->file->extension,
                'about'         => $d->file->about,
                'tags'          => $d->file->tags,
            ],
            'view_url'      => $disk->temporaryUrl($d->file->object_key, now()->addMinutes(30)),
            'thumbnail_url' => $d->file->thumbnail
                ? $disk->temporaryUrl($d->file->thumbnail, now()->addMinutes(30))
                : null,
        ];
    }
}
