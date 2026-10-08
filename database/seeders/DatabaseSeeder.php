<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'password' => bcrypt('123456'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Empleado 1',
            'email' => 'empleado1@example.org',
            'password' => bcrypt('123456'),
            'role' => 'empleado',
        ]);

        User::factory()->create([
            'name' => 'Empleado 2',
            'email' => 'empleado2@example.org',
            'password' => bcrypt('123456'),
            'role' => 'empleado',
        ]);
    }
}
