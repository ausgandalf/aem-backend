<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'applicant',
            'evaluator',
            'pcmu_officer',
            'eco_analyst',
            'approvals_officer',
            'board_member',
            'dcf_officer',
            'finance_officer',
            'admin',
            'marketing',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}