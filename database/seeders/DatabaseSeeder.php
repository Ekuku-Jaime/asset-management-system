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
        User::create([
            'name' => 'Saíde Jailane',
            'email' => 'jailane@example.com',
            'password' => bcrypt('SJalane26#'),
            'role' => 'admin',
            'active' => true,
            'email_verified_at' => now(),
        ]);

     

       
    }
}
