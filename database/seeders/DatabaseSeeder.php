<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str; 

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuário admin ativo
        // User::create([
        //     'name' => 'Admin User',
        //     'email' => 'admin@example.com',
        //     'password' => bcrypt('password'),
        //     'role' => 'admin',
        //     'active' => true,
        //     'email_verified_at' => now(),
        // ]);

        // Usuário convidado (inativo, sem senha, com token)
        User::create([
            'name' => 'Invited User',
            'email' => 'invited@example.com',
            'password' => null,
            'role' => 'user',
            'active' => false,
            'activation_token' => Str::random(60),
        ]);
    }
}
