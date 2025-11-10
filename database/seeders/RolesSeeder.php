<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Spatie cache temizle
        app()['cache']->forget('spatie.permission.cache');

        // Roller
        foreach (['admin', 'editor', 'ops', 'customer'] as $name) {
            Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Admin kullanıcıya rol ata (mevcut ise)
        $admin = User::where('email', 'admin@icronline.com')->first();
        if ($admin && ! $admin->hasRole('admin')) {
            $admin->syncRoles(['admin']);
        }
    }
}
