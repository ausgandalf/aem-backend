<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

use App\Models\Organization;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $wrblo = Organization::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'WRBLO',
                'country'  => 'UK',
            ]
        );
    }
}