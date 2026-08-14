<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use App\Models\Stage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SectorController extends Controller
{
    // POST /api/admin/stages/{stage}/sectors
    // stage_key is taken from the parent stage — never supplied by the client.
    public function store(Request $request, Stage $stage): JsonResponse
    {
        $validated = $request->validate([
            'key'         => ['required', 'string', 'max:255', 'alpha_dash', 'unique:sectors,key'],
            'label'       => ['required', 'string', 'max:255'],
            'section'     => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['stage_key'] = $stage->key;
        $validated['order']     = (int) Sector::where('stage_key', $stage->key)->max('order') + 1;

        $sector = Sector::create($validated);

        return response()->json($this->present($sector), 201);
    }

    // PATCH /api/admin/sectors/{sector}
    public function update(Request $request, Sector $sector): JsonResponse
    {
        $validated = $request->validate([
            'key'         => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('sectors', 'key')->ignore($sector->id)],
            'label'       => ['required', 'string', 'max:255'],
            'section'     => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $sector->update($validated);

        return response()->json($this->present($sector));
    }

    // DELETE /api/admin/sectors/{sector}
    public function destroy(Sector $sector): JsonResponse
    {
        $sector->delete();

        return response()->json(['message' => 'Sector deleted']);
    }

    // POST /api/admin/stages/{stage}/sectors/reorder  { ids: [...] }
    // Reorders sectors within a single stage.
    public function reorder(Request $request, Stage $stage): JsonResponse
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', Rule::exists('sectors', 'id')->where('stage_key', $stage->key)],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $index => $id) {
                Sector::where('id', $id)->update(['order' => $index]);
            }
        });

        return response()->json(['message' => 'Order saved']);
    }

    private function present(Sector $s): array
    {
        return [
            'id'          => $s->id,
            'key'         => $s->key,
            'label'       => $s->label,
            'section'     => $s->section,
            'description' => $s->description,
            'stage_key'   => $s->stage_key,
            'order'       => $s->order,
        ];
    }
}
