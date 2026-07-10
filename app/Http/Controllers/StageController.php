<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use Illuminate\Http\JsonResponse;

class StageController extends Controller
{
    // GET /api/stages - ordered workflow stages, served from cache
    public function index(): JsonResponse
    {
        $stages = Stage::cached()->map(fn (Stage $s) => [
            'key'   => $s->key,
            'label' => $s->label,
            'role'  => $s->role,
            'order' => $s->order,
        ]);

        return response()->json($stages);
    }
}
