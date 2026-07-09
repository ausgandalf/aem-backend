<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationLog;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserLog;
use App\Notifications\ApplicationReceived;
use App\Notifications\NewApplicationSubmitted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    // POST /api/apply - public Quick Apply
    public function quickApply(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request);

        // Guard: a public form must not silently attach to an existing account
        if (User::where('email', $validated['applicant']['email'])->exists()) {
            return response()->json([
                'message' => 'An account with this email already exists. Please log in and submit from your dashboard.',
            ], 422);
        }

        $result = DB::transaction(function () use ($validated) {
            // 1. Resolve or create the organization
            $organization = $this->resolveOrganization($validated);

            // 2. Create the applicant (random password; they set one later via reset link)
            $applicant = User::create([
                'first_name'        => $validated['applicant']['first_name'],
                'middle_name'       => $validated['applicant']['middle_name'] ?? null,
                'last_name'         => $validated['applicant']['last_name'],
                'email'             => $validated['applicant']['email'],
                'phone'             => $validated['applicant']['phone'] ?? null,
                'password'          => Hash::make(Str::random(32)),
                'role'              => 'applicant',
                'status'            => 'pending',
                'organization_id'   => $organization->id,
                'position'          => $validated['applicant']['position'] ?? null,
                'referred_from'     => $validated['applicant']['referred_from'] ?? null,
                'preferred_contact' => $validated['applicant']['preferred_contact'] ?? ['email'],
            ]);
            $applicant->assignRole('applicant');

            UserLog::create([
                'user_id' => $applicant->id,
                'action'  => 'signup',
                'details' => 'Registered via Quick Apply',
            ]);

            // 3. Create the application at the first stage
            $application = Application::create([
                'applicant_id'     => $applicant->id,
                'organization_id'  => $organization->id,
                'project_title'    => $validated['project']['project_title'],
                'project_location' => $validated['project']['project_location'],
                'requested_amount' => $validated['project']['requested_amount'] ?? null,
                'currency'         => $validated['project']['currency'] ?? 'GBP',
                'project_details'  => $validated['project']['project_details'] ?? [],
                'current_stage'    => 'submit',
                'current_status'   => ApplicationStatus::INPROGRESS,
                'updated_by'       => $applicant->id,
            ]);

            // 4. Audit log for the submission
            $applicantName = trim("{$applicant->first_name} {$applicant->middle_name} {$applicant->last_name}");
            $applicantName = preg_replace('/\s+/', ' ', $applicantName);
            ApplicationLog::record([
                'application_id' => $application->id,
                'stage_key'      => 'submit',
                'status'         => $application->current_status->value,
                'updated_by'     => $applicant->id,
                'description'    => "{$applicantName} submitted new application : {$application->project_title}",
            ]);

            return compact('applicant', 'organization', 'application');
        });

        // 4. Emails (outside the transaction — never send mail inside a DB transaction).
        // The submission has already succeeded, so email is best-effort: a mail failure
        // must not turn a successful submission into an error response.
        try {
            $result['applicant']->sendEmailVerificationNotification();
            $result['applicant']->notify(new ApplicationReceived($result['application']));

            if ($intake = config('app.application_intake_email')) {
                Notification::route('mail', $intake)->notify(new NewApplicationSubmitted(
                    $result['application'],
                    trim("{$result['applicant']->first_name} {$result['applicant']->last_name}"),
                    $result['organization']->name,
                ));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message'        => 'Application submitted successfully. Please check your email to verify your address.',
            'application_id' => $result['application']->id,
        ], 201);
    }

    // Resolve an existing organization by id, or create a new one from the payload
    private function resolveOrganization(array $validated): Organization
    {
        if (! empty($validated['organization']['organization_id'])) {
            return Organization::findOrFail($validated['organization']['organization_id']);
        }

        $org = $validated['organization'];
        unset($org['organization_id']);

        // New orgs from the public form start unverified
        $org['status'] = 'pending';

        return Organization::create($org);
    }

    private function validatePayload(Request $request): array
    {
        $existingOrg = $request->input('organization.organization_id');

        // Org fields are required only when the applicant is registering a NEW org
        $orgRequired = $existingOrg ? 'nullable' : 'required';

        return $request->validate([
            // About You
            'applicant.first_name'        => ['required', 'string', 'max:255'],
            'applicant.middle_name'       => ['nullable', 'string', 'max:255'],
            'applicant.last_name'         => ['required', 'string', 'max:255'],
            'applicant.email'             => ['required', 'email', 'max:255'],
            'applicant.phone'             => ['required', 'string', 'max:255'],
            'applicant.preferred_contact' => ['required', 'array', 'min:1'],
            'applicant.preferred_contact.*' => ['in:email,sms,scheduled_call'],
            'applicant.referred_from'     => ['required', 'string', 'max:255'],
            'applicant.position'          => ['required', 'string', 'max:255'],

            // About Organization (either pick an existing one, or register a new one)
            'organization.organization_id'           => ['nullable', 'exists:organizations,id'],
            'organization.name'                      => [$orgRequired, 'string', 'max:255'],
            'organization.registration_number'       => ['nullable', 'string', 'max:255'],
            'organization.legal_status'              => ['nullable', 'string', 'max:255'],
            'organization.type'                      => [$orgRequired, 'string', 'max:255'],
            'organization.founded_year'              => ['nullable', 'integer', 'min:1800', 'max:' . (date('Y') + 1)],
            'organization.registered_country'        => [$orgRequired, 'string', 'max:255'],
            'organization.registered_state_province' => ['nullable', 'string', 'max:255'],
            'organization.registered_city'           => [$orgRequired, 'string', 'max:255'],
            'organization.registered_address_line1'  => [$orgRequired, 'string', 'max:255'],
            'organization.registered_address_line2'  => ['nullable', 'string', 'max:255'],
            'organization.registered_postal_code'    => ['nullable', 'string', 'max:255'],
            'organization.contact_email'             => ['nullable', 'email', 'max:255'],
            'organization.contact_phone'             => ['nullable', 'string', 'max:255'],
            'organization.website_url'               => ['nullable', 'url', 'max:255'],
            'organization.currency'                  => ['nullable', 'string', 'size:3'],
            'organization.annual_income'             => ['nullable', 'numeric', 'min:0'],
            'organization.annual_expenditure'        => ['nullable', 'numeric', 'min:0'],
            'organization.reserves_policy'           => ['nullable', 'string'],

            // Project Details
            'project.project_title'    => ['required', 'string', 'max:255'],
            'project.currency'         => ['nullable', 'string', 'size:3'],
            'project.requested_amount' => ['required', 'numeric', 'min:0'],
            'project.project_location' => ['required', 'string', 'max:255'],

            // Free-text project + purpose fields, stored as jsonb
            'project.project_details'                       => ['required', 'array'],
            'project.project_details.funding_status'        => ['required', 'string'],
            'project.project_details.duration'              => ['required', 'string'],
            'project.project_details.livelihood_opportunity' => ['required', 'string'],
            'project.project_details.beneficiaries'         => ['required', 'string'],
            'project.project_details.philanthropic_call'    => ['required', 'string'],
            'project.project_details.measurable_impact'     => ['required', 'string'],
            'project.project_details.track_record'          => ['required', 'string'],
            'project.project_details.best_evidence'         => ['required', 'string'],
            'project.project_details.monitoring_evaluation' => ['required', 'string'],
            'project.project_details.exit'                  => ['required', 'string'],
            'project.project_details.collaboration'         => ['required', 'string'],
            'project.project_details.org_development_ambition' => ['required', 'string'],
        ]);
    }
}
