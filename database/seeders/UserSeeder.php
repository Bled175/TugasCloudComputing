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

        // Siswa sample accounts
        $students = [
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad.fauzi@eskul.test'],
            ['name' => 'Siti Nurhaliza', 'email' => 'siti.nurhaliza@eskul.test'],
            ['name' => 'Budi Santoso', 'email' => 'budi.santoso@eskul.test'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi.lestari@eskul.test'],
            ['name' => 'Eko Prasetyo', 'email' => 'eko.prasetyo@eskul.test'],
        ];

        foreach ($students as $student) {
            User::create([
                'name' => $student['name'],
                'email' => $student['email'],
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ]);
        }
    }
}
