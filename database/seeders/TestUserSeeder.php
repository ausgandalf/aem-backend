<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TestUserSeeder extends Seeder
{
    /**
     * One active, email-verified test user per role.
     * Email pattern: {role}@mail.com — password: 1111
     */
    public function run(): void
    {
        // Every role defined in the system (RoleSeeder is the source of truth).
        $roles = Role::pluck('label', 'name'); // ['admin' => 'Administrator', ...]

        foreach ($roles as $name => $label) {
            $user = User::firstOrCreate(
                ['email' => "{$name}@mail.com"],
                [
                    'first_name'        => $label ?: ucfirst($name),
                    'last_name'         => 'Tester',
                    'password'          => Hash::make('1111'),
                    'role'              => $name,
                    'status'            => 'active',
                    'organization_id'   => 1,
                    'email_verified_at' => now(),
                    'preferred_contact' => ['email'],
                ],
            );

            // Ensure state is correct even if the user already existed.
            $user->forceFill([
                'status'            => 'active',
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();

            $user->syncRoles([$name]);
        }
    }
}
