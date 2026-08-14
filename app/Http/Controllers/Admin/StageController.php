<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StageController extends Controller
{
    // GET /api/admin/stages
    // Full stage list (with sectors) for the management screen — fresh, not cached.
    public function index(): JsonResponse
    {
        $stages = Stage::with('sectors')
            ->orderBy('order')
            ->get()
            ->map(fn (Stage $s) => $this->present($s));

        return response()->json($stages);
    }

    // POST /api/admin/stages
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key'    => ['required', 'string', 'max:255', 'alpha_dash', 'unique:stages,key'],
            'label'  => ['required', 'string', 'max:255'],
            'role'   => ['required', 'string', Rule::exists('roles', 'name')],
            'status' => ['nullable', 'in:on,off'],
        ]);

        // New stages go to the end of the order.
        $validated['order'] = (int) Stage::max('order') + 1;
        $validated['status'] ??= 'on';

        $stage = Stage::create($validated);

        return response()->json($this->present($stage->load('sectors')), 201);
    }

    // PATCH /api/admin/stages/{stage}
    // NOTE: `key` is intentionally immutable — applications, progresses, documents
    // and config/workflow.php reference it as a plain string (no FK cascade), so
    // renaming it here would silently orphan live data.
    public function update(Request $request, Stage $stage): JsonResponse
    {
        $validated = $request->validate([
            'label'  => ['required', 'string', 'max:255'],
            'role'   => ['required', 'string', Rule::exists('roles', 'name')],
            'status' => ['required', 'in:on,off'],
        ]);

        $stage->update($validated);

        return response()->json($this->present($stage->load('sectors')));
    }

    // POST /api/admin/stages/reorder  { ids: [3, 1, 2, ...] }
    // Persists the new stage order in one shot.
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:stages,id'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $index => $id) {
                Stage::where('id', $id)->update(['order' => $index]);
            }
        });

        // Mass query-builder updates skip model events, so flush the cache manually.
        Cache::forget(Stage::CACHE_KEY);

        return response()->json(['message' => 'Order saved']);
    }

    private function present(Stage $s): array
    {
        return [
            'id'      => $s->id,
            'key'     => $s->key,
            'label'   => $s->label,
            'role'    => $s->role,
            'order'   => $s->order,
            'status'  => $s->status,
            'sectors' => $s->sectors->map(fn ($sec) => [
                'id'          => $sec->id,
                'key'         => $sec->key,
                'label'       => $sec->label,
                'section'     => $sec->section,
                'description' => $sec->description,
                'stage_key'   => $sec->stage_key,
                'order'       => $sec->order,
            ])->values(),
        ];
    }
}
