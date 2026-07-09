<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    // GET /api/admin/organizations
    public function index(Request $request): JsonResponse
    {
        $organizations = Organization::query()
            ->withCount('users')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('name', 'ilike', "%{$request->search}%")
                      ->orWhere('registration_number', 'ilike', "%{$request->search}%")
                      ->orWhere('type', 'ilike', "%{$request->search}%")
                      ->orWhere('registered_country', 'ilike', "%{$request->search}%")
                      ->orWhere('contact_email', 'ilike', "%{$request->search}%");
                });
            })
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('name')
            ->paginate($request->per_page ?? 20);

        return response()->json($organizations);
    }

    // POST /api/admin/organizations
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateOrganization($request);
        $validated['created_by'] = $request->user()->id;
        $validated['updated_by'] = $request->user()->id;

        $organization = Organization::create($validated);

        return response()->json([
            'message'      => 'Organization created successfully',
            'organization' => $organization,
        ], 201);
    }

    // GET /api/admin/organizations/{organization}
    public function show(Organization $organization): JsonResponse
    {
        $organization->loadCount('users');

        return response()->json(['organization' => $organization]);
    }

    // PATCH /api/admin/organizations/{organization}
    public function update(Request $request, Organization $organization): JsonResponse
    {
        $validated = $this->validateOrganization($request);
        $validated['updated_by'] = $request->user()->id;

        $organization->update($validated);

        return response()->json([
            'message'      => 'Organization updated successfully',
            'organization' => $organization->fresh(),
        ]);
    }

    // DELETE /api/admin/organizations/{organization}
    public function destroy(Organization $organization): JsonResponse
    {
        if ($organization->users()->exists()) {
            return response()->json([
                'message' => 'Cannot delete an organization that has users assigned',
            ], 422);
        }

        $organization->delete();

        return response()->json([
            'message' => 'Organization deleted successfully',
        ]);
    }

    private function validateOrganization(Request $request): array
    {
        $validated = $request->validate([
            // Core identity
            'name'                => ['required', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'legal_status'        => ['nullable', 'string', 'max:255'],
            'type'                => ['nullable', 'string', 'max:255'],
            'founded_year'        => ['nullable', 'integer', 'min:1800', 'max:' . (date('Y') + 1)],

            // Registered (legal) address
            'registered_country'        => ['nullable', 'string', 'max:255'],
            'registered_state_province' => ['nullable', 'string', 'max:255'],
            'registered_city'           => ['nullable', 'string', 'max:255'],
            'registered_address_line1'  => ['nullable', 'string', 'max:255'],
            'registered_address_line2'  => ['nullable', 'string', 'max:255'],
            'registered_postal_code'    => ['nullable', 'string', 'max:255'],

            // Operating / correspondence address
            'current_same_as_registered' => ['boolean'],
            'current_country'            => ['nullable', 'string', 'max:255'],
            'current_state_province'     => ['nullable', 'string', 'max:255'],
            'current_city'               => ['nullable', 'string', 'max:255'],
            'current_address_line1'      => ['nullable', 'string', 'max:255'],
            'current_address_line2'      => ['nullable', 'string', 'max:255'],
            'current_postal_code'        => ['nullable', 'string', 'max:255'],

            // Contact
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'website_url'   => ['nullable', 'url', 'max:255'],

            // Social
            'social_facebook'  => ['nullable', 'string', 'max:255'],
            'social_linkedin'  => ['nullable', 'string', 'max:255'],
            'social_twitter'   => ['nullable', 'string', 'max:255'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_youtube'   => ['nullable', 'string', 'max:255'],
            'social_whatsapp'  => ['nullable', 'string', 'max:255'],

            // Financials
            'currency'           => ['nullable', 'string', 'size:3'],
            'annual_income'      => ['nullable', 'numeric', 'min:0'],
            'annual_expenditure' => ['nullable', 'numeric', 'min:0'],
            'reserves_policy'    => ['nullable', 'string'],

            // Metadata
            'status' => ['nullable', 'in:pending,verified,off'],
            'note'   => ['nullable', 'string'],
        ]);

        // When the operating address mirrors the registered one, don't store a stale copy
        if (! empty($validated['current_same_as_registered'])) {
            foreach ([
                'current_country',
                'current_state_province',
                'current_city',
                'current_address_line1',
                'current_address_line2',
                'current_postal_code',
            ] as $field) {
                $validated[$field] = null;
            }
        }

        return $validated;
    }
}
