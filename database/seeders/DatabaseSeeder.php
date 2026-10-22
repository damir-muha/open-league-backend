<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Сначала запускаем сидер ролей (Filament Shield)
        $this->call([
            ShieldSeeder::class,
        ]);

        // Затем создаем администратора и выдаем ему права
        $admin = \App\Models\User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );
        $admin->assignRole('super_admin');
    }
}
