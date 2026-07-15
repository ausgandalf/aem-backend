<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationLog;
use App\Models\Organization;
use App\Models\Stage;
use App\Models\User;
use App\Models\UserLog;
use App\Notifications\ApplicationReceived;
use App\Notifications\NewApplicationSubmitted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    // POST /api/apply - public Quick Apply
    public function quickApply(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request);

        // Attach the draft to an existing account if the email is known, otherwise
        // create a fresh applicant. Either way the draft lands under that account and
        // the account owner is emailed — nobody can submit without signing in.
        $existingUser = User::where('email', $validated['applicant']['email'])->first();

        $result = DB::transaction(function () use ($validated, $existingUser) {
            // 1. Resolve or create the organization
            $organization = $this->resolveOrganization($validated);

            // 2. Reuse the existing account, or create a new applicant
            if ($existingUser) {
                $applicant = $existingUser;
                $isNew = false;
            } else {
                // Random password; the applicant sets a real one via the reset link
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
                $isNew = true;
            }

            // 3. Create the application as a DRAFT at the submit stage.
            //    The applicant must return to ARM and hit Submit to send it to review.
            $application = Application::create([
                'applicant_id'     => $applicant->id,
                'organization_id'  => $organization->id,
                'project_title'    => $validated['project']['project_title'],
                'project_location' => $validated['project']['project_location'],
                'requested_amount' => $validated['project']['requested_amount'] ?? null,
                'currency'         => $validated['project']['currency'] ?? 'GBP',
                'project_details'  => $validated['project']['project_details'] ?? [],
                'current_stage'    => 'submit',
                'current_status'   => ApplicationStatus::PENDING,
                'updated_by'       => $applicant->id,
            ]);

            // 4. Audit log + initial progress snapshot for the draft
            $applicantName = preg_replace('/\s+/', ' ', trim(
                "{$applicant->first_name} {$applicant->middle_name} {$applicant->last_name}"
            ));
            ApplicationLog::record([
                'application_id' => $application->id,
                'stage_key'      => 'submit',
                'status'         => $application->current_status->value,
                'updated_by'     => $applicant->id,
                'description'    => "{$applicantName} started a new application : {$application->project_title}",
            ]);
            $application->upsertProgress('submit', ApplicationStatus::PENDING);

            return compact('applicant', 'organization', 'application', 'isNew');
        });

        // Emails (outside the transaction — never send mail inside a DB transaction).
        // Best-effort so a mail hiccup never fails a successful submission.
        try {
            // New accounts additionally need to verify their email and set a password
            if ($result['isNew']) {
                $result['applicant']->sendEmailVerificationNotification();
                PasswordBroker::sendResetLink(['email' => $result['applicant']->email]);
            }
            // Common to both: "a new application draft was created, come and submit it"
            $result['applicant']->notify(new ApplicationReceived($result['application']));
        } catch (\Throwable $e) {
            report($e);
        }

        $message = $result['isNew']
            ? 'Your application has been saved as a draft. Check your email to verify your address and set a password, then sign in to review and submit it.'
            : 'Your application has been saved as a draft under your existing account. Sign in to review and submit it — we\'ve emailed you the details.';

        return response()->json([
            'message'        => $message,
            'is_new_user'    => $result['isNew'],
            'application_id' => $result['application']->id,
        ], 201);
    }

    // Notify the intake inbox that an application has entered review (best-effort)
    private function notifyStaffOfSubmission(Application $application, User $applicant): void
    {
        if (! $intake = config('app.application_intake_email')) {
            return;
        }
        try {
            Notification::route('mail', $intake)->notify(new NewApplicationSubmitted(
                $application,
                $this->fullName($applicant),
                $application->organization?->name ?? '',
            ));
        } catch (\Throwable $e) {
            report($e);
        }
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

    // Public Quick Apply: applicant + organization + project
    private function validatePayload(Request $request): array
    {
        $orgRequired = $request->input('organization.organization_id') ? 'nullable' : 'required';

        return $request->validate(array_merge(
            [
                'applicant.first_name'          => ['required', 'string', 'max:255'],
                'applicant.middle_name'         => ['nullable', 'string', 'max:255'],
                'applicant.last_name'           => ['required', 'string', 'max:255'],
                'applicant.email'               => ['required', 'email', 'max:255'],
                'applicant.phone'               => ['required', 'string', 'max:255'],
                'applicant.preferred_contact'   => ['required', 'array', 'min:1'],
                'applicant.preferred_contact.*' => ['in:email,sms,scheduled_call'],
                'applicant.referred_from'       => ['required', 'string', 'max:255'],
                'applicant.position'            => ['required', 'string', 'max:255'],
            ],
            $this->organizationRules($orgRequired),
            $this->projectRules(),
        ));
    }

    // Authenticated create/edit: organization + project + save|submit action
    private function validateAuthenticatedInput(Request $request): array
    {
        $orgRequired = $request->input('organization.organization_id') ? 'nullable' : 'required';

        return $request->validate(array_merge(
            ['action' => ['required', 'in:save,submit']],
            $this->organizationRules($orgRequired),
            $this->projectRules(),
        ));
    }

    private function organizationRules(string $orgRequired): array
    {
        return [
            // Either pick an existing org, or register a new one (all fields required when new)
            'organization.organization_id'           => ['nullable', 'exists:organizations,id'],
            'organization.name'                      => [$orgRequired, 'string', 'max:255'],
            'organization.registration_number'       => [$orgRequired, 'string', 'max:255'],
            'organization.legal_status'              => [$orgRequired, 'string', 'max:255'],
            'organization.type'                      => [$orgRequired, 'string', 'max:255'],
            'organization.founded_year'              => [$orgRequired, 'integer', 'min:1800', 'max:' . (date('Y') + 1)],
            'organization.registered_country'        => [$orgRequired, 'string', 'max:255'],
            'organization.registered_state_province' => [$orgRequired, 'string', 'max:255'],
            'organization.registered_city'           => [$orgRequired, 'string', 'max:255'],
            'organization.registered_address_line1'  => [$orgRequired, 'string', 'max:255'],
            'organization.registered_address_line2'  => ['nullable', 'string', 'max:255'],
            'organization.registered_postal_code'    => [$orgRequired, 'string', 'max:255'],
            'organization.contact_email'             => [$orgRequired, 'email', 'max:255'],
            'organization.contact_phone'             => [$orgRequired, 'string', 'max:255'],
            'organization.website_url'               => [$orgRequired, 'url', 'max:255'],
            'organization.currency'                  => [$orgRequired, 'string', 'size:3'],
            'organization.annual_income'             => [$orgRequired, 'numeric', 'min:0'],
            'organization.annual_expenditure'        => [$orgRequired, 'numeric', 'min:0'],
            'organization.reserves_policy'           => [$orgRequired, 'string'],
        ];
    }

    private function projectRules(): array
    {
        return [
            'project.project_title'    => ['required', 'string', 'max:255'],
            'project.currency'         => ['required', 'string', 'size:3'],
            'project.requested_amount' => ['required', 'numeric', 'min:0'],
            'project.project_location' => ['required', 'string', 'max:255'],

            // Free-text project + purpose fields, stored as jsonb
            'project.project_details'                          => ['required', 'array'],
            'project.project_details.funding_status'           => ['required', 'string'],
            'project.project_details.duration'                 => ['required', 'string'],
            'project.project_details.livelihood_opportunity'   => ['required', 'string'],
            'project.project_details.beneficiaries'            => ['required', 'string'],
            'project.project_details.philanthropic_call'       => ['required', 'string'],
            'project.project_details.measurable_impact'        => ['required', 'string'],
            'project.project_details.track_record'             => ['required', 'string'],
            'project.project_details.best_evidence'            => ['required', 'string'],
            'project.project_details.monitoring_evaluation'    => ['required', 'string'],
            'project.project_details.exit'                     => ['required', 'string'],
            'project.project_details.collaboration'            => ['required', 'string'],
            'project.project_details.org_development_ambition' => ['required', 'string'],
        ];
    }

    private function applicationAttributes(array $validated, int $userId): array
    {
        return [
            'project_title'    => $validated['project']['project_title'],
            'project_location' => $validated['project']['project_location'],
            'requested_amount' => $validated['project']['requested_amount'] ?? null,
            'currency'         => $validated['project']['currency'] ?? 'GBP',
            'project_details'  => $validated['project']['project_details'] ?? [],
            'updated_by'       => $userId,
        ];
    }

    private function fullName(User $user): string
    {
        return preg_replace('/\s+/', ' ', trim("{$user->first_name} {$user->middle_name} {$user->last_name}"));
    }

    private function authorizeOwner(Request $request, Application $application): void
    {
        abort_if($application->applicant_id !== $request->user()->id, 403, 'This is not your application.');
    }

    // GET /api/applications - the authenticated applicant's own applications (cards)
    public function index(Request $request): JsonResponse
    {
        $applications = Application::query()
            ->where('applicant_id', $request->user()->id)
            ->latest()
            ->get([
                'id', 'project_title', 'requested_amount', 'currency',
                'current_stage', 'current_status', 'prev_stage', 'prev_status', 'created_at',
            ]);

        return response()->json($applications);
    }

    // GET /api/applications/{application} - full details + ordered progress (own only)
    public function show(Request $request, Application $application): JsonResponse
    {
        $this->authorizeOwner($request, $application);
        $application->load('organization');

        $progressMap = $application->progresses()->get()->keyBy('stage_key');
        $progress = Stage::cached()->map(fn (Stage $s) => [
            'key'    => $s->key,
            'label'  => $s->label,
            'order'  => $s->order,
            'status' => optional($progressMap->get($s->key))->status?->value,
            'note'   => optional($progressMap->get($s->key))->note,
        ])->values();

        return response()->json([
            'application' => $application,
            'progress'    => $progress,
        ]);
    }

    // POST /api/applications - authenticated applicant creates a new application
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateAuthenticatedInput($request);
        $user = $request->user();
        $name = $this->fullName($user);

        $application = DB::transaction(function () use ($validated, $user, $name) {
            $organization = $this->resolveOrganization($validated);

            $application = Application::create(array_merge(
                $this->applicationAttributes($validated, $user->id),
                [
                    'applicant_id'    => $user->id,
                    'organization_id' => $organization->id,
                    'current_stage'   => 'submit',
                    'current_status'  => ApplicationStatus::PENDING,
                ],
            ));

            $application->upsertProgress('submit', ApplicationStatus::PENDING);
            ApplicationLog::record([
                'application_id' => $application->id,
                'stage_key'      => 'submit',
                'status'         => ApplicationStatus::PENDING->value,
                'updated_by'     => $user->id,
                'description'    => "{$name} created new application : {$application->project_title}",
            ]);

            if ($validated['action'] === 'submit') {
                $application->submitForReview($user->id, $name);
            }

            return $application;
        });

        if ($validated['action'] === 'submit') {
            $this->notifyStaffOfSubmission($application->load('organization'), $request->user());
        }

        return response()->json([
            'message'        => $validated['action'] === 'submit'
                ? 'Application submitted for review.'
                : 'Application saved.',
            'application_id' => $application->id,
        ], 201);
    }

    // PATCH /api/applications/{application} - edit at submit stage, then save or submit
    public function update(Request $request, Application $application): JsonResponse
    {
        $this->authorizeOwner($request, $application);

        if ($application->current_stage !== 'submit') {
            return response()->json([
                'message' => 'This application has moved past submission and can no longer be edited.',
            ], 422);
        }

        $validated = $this->validateAuthenticatedInput($request);
        $user = $request->user();
        $name = $this->fullName($user);

        DB::transaction(function () use ($validated, $user, $name, $application) {
            $organization = $this->resolveOrganization($validated);

            $application->update(array_merge(
                $this->applicationAttributes($validated, $user->id),
                ['organization_id' => $organization->id],
            ));

            if ($validated['action'] === 'submit') {
                $application->submitForReview($user->id, $name);
            } else {
                $application->saveDraft($user->id, $name);
            }
        });

        if ($validated['action'] === 'submit') {
            $this->notifyStaffOfSubmission($application->load('organization'), $request->user());
        }

        return response()->json([
            'message'        => $validated['action'] === 'submit'
                ? 'Application submitted for review.'
                : 'Application saved.',
            'application_id' => $application->id,
        ]);
    }
}
