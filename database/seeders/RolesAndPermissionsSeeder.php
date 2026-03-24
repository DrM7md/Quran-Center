<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // مهم: يمسح كاش الصلاحيات عشان أي تغييرات تظهر مباشرة
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        // =============================
        // 1) Define Permissions
        // =============================
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Roles & Permissions
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // Settings
            'settings.view',
            'settings.manage',

            // Reports
            'reports.view',
            'reports.export',

// Students
'students.view',
'students.create',
'students.edit',
'students.delete',

// Halaqat
'halaqat.view',
'halaqat.create',
'halaqat.edit',
'halaqat.delete',

// Memorization
'memorization.view',
'memorization.create',
'memorization.delete',

// Attendance
'attendance.view',
'attendance.edit',
'attendance.report',

        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => $guard,
            ]);
        }

        // =============================
        // 2) Define Roles
        // =============================
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => $guard,
        ]);

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => $guard,
        ]);

        $userRole = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => $guard,
        ]);

        // =============================
        // 3) Assign Permissions to Roles
        // =============================

        // Super Admin: كل الصلاحيات
        $superAdminRole->syncPermissions(Permission::where('guard_name', $guard)->get());

        // Admin: صلاحيات إدارية بدون حذف حساس (تقدر تعدلها على راحتك)
        $adminRole->syncPermissions([
            'dashboard.view',
            'users.view', 'users.create', 'users.edit',
            'roles.view',
            'settings.view',
            'reports.view', 'reports.export',
  // Students
    'students.view', 'students.create', 'students.edit',

    // Halaqat
    'halaqat.view', 'halaqat.create', 'halaqat.edit',

    // Memorization
    'memorization.view', 'memorization.create', 'memorization.delete',

    // Attendance
    'attendance.view', 'attendance.edit', 'attendance.report',

        ]);

        // User: عرض فقط
        $userRole->syncPermissions([
            'dashboard.view',

             'students.view',
    'halaqat.view',

    'memorization.view', 'memorization.create',

    'attendance.view',
    
        ]);

        // =============================
        // 4) Create Super Admin User (مرة وحدة)
        // =============================
        $email = 'admin@admin.com';
        $password = '123456789'; // غيّرها بعد أول دخول

        $superAdmin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        // لو كان موجود من قبل وما عنده تحقق بريد
        if (!$superAdmin->email_verified_at) {
            $superAdmin->email_verified_at = now();
            $superAdmin->save();
        }

        // ربط الدور (بدون تكرار)
        if (!$superAdmin->hasRole('super-admin')) {
            $superAdmin->assignRole($superAdminRole);
        }
    }
}
