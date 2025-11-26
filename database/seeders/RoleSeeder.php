<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        $conciergeRole = Role::firstOrCreate(['name' => 'concierge']);

        // Create permissions
        $permissions = [
            'manage users',
            'manage roles',
            'manage permissions',
            'view admin dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign all permissions to admin role
        $adminRole->givePermissionTo(Permission::all());

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        // Create regular user
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@user.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('user');

        //create concierge user
        $concierge = User::updateOrCreate(
            ['email' => 'concierge@gmail.com'],
            [
                'name' => 'Concierge User',
                'number' => '0000000000',
                'password' => bcrypt('Concierge123!'),
            ]
        );

        // Assign role
        if (!$concierge->hasRole('concierge')) {
            $concierge->assignRole($conciergeRole);
        }

    }
}
