<?php

namespace Database\Seeders;

use App\Models\Stage;
use Illuminate\Database\Seeder;

class StageSeeder extends Seeder
{
    public function run(): void
    {
        // Ordered workflow stages. `role` mirrors the Spatie role that acts at that stage.
        $stages = [
            ['key' => 'submit',            'label' => 'Submit',            'role' => 'applicant',         'order' => 0],
            ['key' => 'evaluation_1',      'label' => 'Evaluation 1',      'role' => 'evaluator',         'order' => 1],
            ['key' => 'audit',             'label' => 'Audit',             'role' => 'pcmu_officer',      'order' => 2],
            ['key' => 'evaluation_2',      'label' => 'Evaluation 2',      'role' => 'evaluator',         'order' => 3],
            ['key' => 'eco_assessment',    'label' => 'ECO Assessment',    'role' => 'eco_analyst',       'order' => 4],
            ['key' => 'legal_review',      'label' => 'Legal Review',      'role' => 'approvals_officer', 'order' => 5],
            ['key' => 'board_validation',  'label' => 'Board Validation',  'role' => 'board_member',      'order' => 6],
            ['key' => 'dcf_check',         'label' => 'DCF Check',         'role' => 'dcf_officer',       'order' => 7],
            ['key' => 'payout_processing', 'label' => 'Payout in process', 'role' => 'finance_officer',   'order' => 8],
            ['key' => 'payout_complete',   'label' => 'Payout Complete',   'role' => 'finance_officer',   'order' => 9],
        ];

        foreach ($stages as $stage) {
            Stage::updateOrCreate(['key' => $stage['key']], $stage);
        }
    }
}
