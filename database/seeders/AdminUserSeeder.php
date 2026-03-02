<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::query()->updateOrCreate(
            ['email' => 'admin@conservicos.com.br'],
            [
                'name' => 'Administrador',
                'password' => '123456',
            ]
        );

        $permissions = Permission::query()->pluck('name');
        if ($permissions->isNotEmpty()) {
            $user->syncPermissions($permissions);
        }

        $roles = Role::query()->pluck('name');
        if ($roles->isNotEmpty()) {
            $user->syncRoles($roles);
        }
    }
}
