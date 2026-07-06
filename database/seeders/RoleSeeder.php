<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'applicant'         => 'Applicant',
            'evaluator'         => 'Evaluator',
            'pcmu_officer'      => 'PCMU Officer',
            'eco_analyst'       => 'ECO Analyst',
            'approvals_officer' => 'Approvals Officer',
            'board_member'      => 'Board Member',
            'dcf_officer'       => 'DCF Officer',
            'finance_officer'   => 'Finance Officer',
            'admin'             => 'Administrator',
            'marketing'         => 'Marketing',
        ];

        foreach ($roles as $name => $label) {
            Role::updateOrCreate(
                ['name' => $name],
                ['label' => $label]
            );
        }
    }
}