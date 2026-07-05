<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'damon@wrblo.org'],
            [
                'first_name' => 'Damon',
                'last_name'  => 'Simpson',
                'password'   => Hash::make('Admin123!'),
                'role'       => 'admin',
                'status'     => 'active',
                'organization_id' => 1,
                'email_verified_at' => now(),
                'preferred_contact' => ['email'],
            ]
        );

        $admin->assignRole('admin');
    }
}