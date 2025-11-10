<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // kullanıcı yönetimi
            'users.view', 'users.create', 'users.edit', 'users.delete',
            // rol yönetimi
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            // (ileride) örnek domain izinleri
            // 'hotels.view', 'hotels.create', ...
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $ops = Role::firstOrCreate(['name' => 'ops',    'guard_name' => 'web']);

        // admin = tüm izinler
        $admin->syncPermissions(Permission::all());

        // editor = sadece görüntüle/düzenle
        $editor->syncPermissions(Permission::whereIn('name', [
            'users.view', 'users.edit',
            'roles.view',
        ])->get());

        // ops = sadece görüntüle
        $ops->syncPermissions(Permission::whereIn('name', [
            'users.view', 'roles.view',
        ])->get());

        // admin kullanıcıya rol tak (eğer yoksa)
        if ($u = User::where('email', 'admin@icronline.com')->first()) {
            $u->syncRoles(['admin']);
        }
    }
}
