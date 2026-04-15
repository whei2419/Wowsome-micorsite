<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin role exists, if not create it
        if (! Role::where('name', 'admin')->exists()) {
            $adminRole = Role::create(['name' => 'admin']);
        } else {
            $adminRole = Role::where('name', 'admin')->first();
        }

        // Check if client role exists, if not create it
        if (! Role::where('name', 'client')->exists()) {
            Role::create(['name' => 'client']);
        }

        // Create permissions if they don't exist
        if (! Permission::where('name', 'full')->exists()) {
            Permission::create(['name' => 'full']);
        }

        if (! Permission::where('name', 'view')->exists()) {
            Permission::create(['name' => 'view']);
        }

        // Create a new admin user
        $adminUser = User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'], // Check by email
            [
                'fname' => 'Super Admin',
                'country' => 'Malaysia',
                'password' => Hash::make('SuperAdmin123!'),
                'marketing' => false,
                'otp_verified' => true, // If OTP field exists
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role
        if (! $adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
        }

        // Give full permissions
        if (! $adminUser->hasPermissionTo('full')) {
            $adminUser->givePermissionTo('full');
        }

        if (! $adminUser->hasPermissionTo('view')) {
            $adminUser->givePermissionTo('view');
        }

        $this->command->info('Super Admin user created successfully!');
        $this->command->info('Email: superadmin@gmail.com');
        $this->command->info('Password: SuperAdmin123!');

        // Create a normal user
        $normalUser = User::updateOrCreate(
            ['email' => 'pt@email.com'],
            [
                'fname' => 'PT User',
                'country' => 'Malaysia',
                'password' => Hash::make('armanipower@2026'),
                'marketing' => false,
                'otp_verified' => true,
                'email_verified_at' => now(),
            ]
        );

        $clientRole = Role::where('name', 'client')->first();
        if ($clientRole && ! $normalUser->hasRole('client')) {
            $normalUser->assignRole('client');
        }

        $this->command->info('Normal user created successfully!');
        $this->command->info('Email: pt@email.com');
        $this->command->info('Password: armanipower@2026');

        // Optionally create additional admin users
        $this->createAdditionalAdmins();
    }

    /**
     * Create additional admin users if needed
     */
    private function createAdditionalAdmins(): void
    {
        $additionalAdmins = [
            [
                'fname' => 'Admin Manager',
                'email' => 'manager@gmail.com',
                'country' => 'Malaysia',
                'password' => Hash::make('Manager123!'),
            ],
            [
                'fname' => 'Admin Support',
                'email' => 'support@gmail.com',
                'country' => 'Malaysia',
                'password' => Hash::make('Support123!'),
            ],
        ];

        foreach ($additionalAdmins as $adminData) {
            $user = User::updateOrCreate(
                ['email' => $adminData['email']],
                array_merge($adminData, [
                    'marketing' => false,
                    'otp_verified' => true,
                    'email_verified_at' => now(),
                ])
            );

            if (! $user->hasRole('admin')) {
                $user->assignRole('admin');
            }

            if (! $user->hasPermissionTo('full')) {
                $user->givePermissionTo('full');
            }

            if (! $user->hasPermissionTo('view')) {
                $user->givePermissionTo('view');
            }

            $plainPassword = str_replace(Hash::make(''), '', $adminData['password']);
            // Extract plain password from the array (it's already in plain text)
            $originalPassword = $adminData['email'] === 'manager@gmail.com' ? 'Manager123!' : 'Support123!';
            $this->command->info("Admin user created: {$adminData['email']} / {$originalPassword}");
        }
    }
}
