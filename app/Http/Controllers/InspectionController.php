<?php

namespace App\Http\Controllers;

use App\Enums\InspectionStatus;
use App\Models\Inspection;
use App\Models\Stage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InspectionController extends Controller
{
    // PATCH /api/inspections/{inspection}
    // Change an inspection's status + note. Requires the user's role to own the
    // inspection's stage AND the application to currently be at that stage.
    public function update(Request $request, Inspection $inspection): JsonResponse
    {
        $user  = $request->user();
        $roles = $user->getRoleNames()->all();

        $stageModel = Stage::visible()->firstWhere('key', $inspection->stage_key);
        abort_unless($stageModel && in_array($stageModel->role, $roles, true), 403, 'You are not assigned to this stage.');

        // Can only change while the application is at this stage (else read-only).
        abort_if(
            $inspection->application->current_stage !== $inspection->stage_key,
            403,
            'This stage is read-only for this application.',
        );

        $validated = $request->validate([
            'status' => ['required', Rule::in(InspectionStatus::values())],
            'note'   => ['nullable', 'string'],
        ]);

        $inspection->update([
            'status'     => $validated['status'],
            'note'       => $validated['note'] ?? null,
            'updated_by' => $user->id,
        ]);

        return response()->json([
            'id'     => $inspection->id,
            'status' => $inspection->status?->value,
            'note'   => $inspection->note,
        ]);
    }
}
