<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicOrganizationController extends Controller
{
    // GET /api/organizations - public, minimal fields for the Quick Apply combobox
    public function index(Request $request): JsonResponse
    {
        $organizations = Organization::query()
            ->where('status', '<>', 'off')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('name', 'ilike', "%{$request->search}%")
                      ->orWhere('registered_city', 'ilike', "%{$request->search}%")
                      ->orWhere('registered_country', 'ilike', "%{$request->search}%");
                });
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'registered_city', 'registered_country']);

        return response()->json($organizations);
    }
}
