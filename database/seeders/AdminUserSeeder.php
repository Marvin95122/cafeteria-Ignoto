<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
{
    User::updateOrCreate(
        ['email' => 'admin@cafeteria.com'],
        [
            'name' => 'Administrador',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]
    );
}
}
