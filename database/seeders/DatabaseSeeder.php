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
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);

        User::query()->updateOrCreate(
            ['email' => 'admin@localgo.local'],
            [
                'name' => 'Administrador localgo',
                'password' => 'password',
                'is_admin' => true,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'repartidor@localgo.local'],
            [
                'name' => 'Repartidor localgo',
                'password' => 'password',
                'is_repartidor' => true,
            ],
        );
    }
}
