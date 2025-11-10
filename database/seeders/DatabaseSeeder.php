<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Örnek test user (opsiyonel)
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Roller ve admin rolü
        $this->call(RolesSeeder::class);

        // İzinler
        $this->call(PermissionsSeeder::class);
    }
}
