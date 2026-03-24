<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = '12345678';

        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $adminRole      = Role::firstOrCreate(['name' => 'admin']);
                          Role::firstOrCreate(['name' => 'muhafidh']);
        $guardianRole   = Role::firstOrCreate(['name' => 'guardian']);

        // ولي أمر تجريبي
        $guardian = User::firstOrCreate(
            ['email' => 'guardian@quran.local'],
            ['name' => 'ولي أمر تجريبي', 'password' => Hash::make($defaultPassword)]
        );
        $guardian->syncRoles([$guardianRole]);

        // super-admin افتراضي (بدون center_id)
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@quran.local'],
            ['name' => 'Super Admin', 'password' => Hash::make($defaultPassword)]
        );
        $superAdmin->syncRoles([$superAdminRole]);

        // أدمن افتراضي (center_id يُعيَّن في CenterSeeder)
        $admin = User::firstOrCreate(
            ['email' => 'admin@quran.local'],
            ['name' => 'Admin', 'password' => Hash::make($defaultPassword)]
        );
        $admin->syncRoles([$adminRole]);
    }
}
