<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        $managePermission = Permission::firstOrCreate(['name' => 'manage-applications', 'guard_name' => 'web']);

        $adminRole->givePermissionTo($managePermission);

        $admin = User::firstOrCreate(
            ['email' => 'admin@interaktifkredi.com.tr'],
            [
                'name' => 'Admin Kullanıcı',
                'phone' => '+90 555 000 0000',
                'password' => Hash::make('Password123!'),
            ]
        );

        $admin->assignRole($adminRole);
    }
}
