<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Station;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Station::create([
            'name' => 'Weekday',
            'description' => 'Weekday Exclusive Gifts',
        ]);

        Station::create([
            'name' => 'Weekend',
            'description' => 'Weekend Exclusive Gifts',
        ]);

        Station::create([
            'name' => 'Referral Tier 1',
            'description' => 'Referral Tier 1 Gift (1 successful referral)',
        ]);

        Station::create([
            'name' => 'Referral Tier 2',
            'description' => 'Referral Tier 2 Gift (5 successful referrals)',
        ]);



        $role = Role::create(['name' => 'client']);

        $user = User::create([
            'name' => 'admin',
            'number' => '0123456789',
            'email' => 'admin@gmail.com',
            'country' => 'Malaysia',
            'password' => Hash::make('Wowsome4s2025'),
        ]);

        $user->assignRole('admin');

    }
}
