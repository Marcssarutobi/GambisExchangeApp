<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Utilisateur Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'), // change le mot de passe après
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Utilisateur Caissier
        User::create([
            'name' => 'Caissier Test',
            'email' => 'caissier@example.com',
            'password' => Hash::make('password123'), // change le mot de passe après
            'role' => 'caissier',
            'status' => 'active',
        ]);
    }
}
