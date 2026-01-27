<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin Eskul',
            'email' => 'admin@eskul.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Sekretaris
        User::create([
            'name' => 'Sekretaris Eskul',
            'email' => 'sekretaris@eskul.test',
            'password' => Hash::make('password'),
            'role' => 'sekretaris',
        ]);

        // Siswa
        User::create([
            'name' => 'Siswa Contoh',
            'email' => 'siswa@eskul.test',
            'password' => Hash::make('password'),
            'role' => 'siswa',
        ]);
    }
}
