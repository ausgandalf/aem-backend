<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Document visibility by role
    |--------------------------------------------------------------------------
    |
    | Which stages' documents each role is allowed to see. Use '*' to mean
    | "all stages". Keyed by the Spatie role name; values are stage keys from
    | the `stages` table. Applicants are additionally restricted to their own
    | application's documents (enforced in the controller).
    |
    */

    'document_visibility' => [
        'applicant'         => ['submit', 'dcf_check', 'payout_processing', 'payout_complete'],
        'evaluator'         => ['submit', 'evaluation_1', 'audit', 'evaluation_2'],
        'pcmu_officer'      => ['submit', 'evaluation_1', 'audit'],
        'eco_analyst'       => ['submit', 'evaluation_1', 'audit', 'evaluation_2', 'eco_assessment'],
        'approvals_officer' => ['submit', 'evaluation_1', 'audit', 'evaluation_2', 'eco_assessment', 'legal_review'],
        'board_member'      => ['*'],
        'dcf_officer'       => ['*'],
        'finance_officer'   => ['legal_review', 'board_validation', 'dcf_check', 'payout_processing', 'payout_complete'],
        'admin'             => ['*'],
        'marketing'         => ['*'],
    ],

];
