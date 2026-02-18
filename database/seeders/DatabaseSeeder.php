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
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::updateOrCreate(
            ['email' => 'operator@example.com'],
            [
                'name' => 'Operator User',
                'password' => 'password',
                'role' => User::ROLE_OPERATOR,
            ]
        );

        User::updateOrCreate(
            ['email' => 'author@example.com'],
            [
                'name' => 'Author User',
                'password' => 'password',
                'role' => User::ROLE_AUTHOR,
            ]
        );

        User::updateOrCreate(
            ['email' => 'peserta@example.com'],
            [
                'name' => 'Peserta User',
                'password' => 'password',
                'role' => User::ROLE_PESERTA,
            ]
        );

        User::where('role', 'user')->update(['role' => User::ROLE_PESERTA]);

        $this->call([
            ExamSeeder::class,
        ]);
    }
}
