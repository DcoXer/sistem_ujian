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
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Teacher User',
                'password' => 'password',
                'role' => User::ROLE_TEACHER,
            ]
        );

        User::updateOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Student User',
                'password' => 'password',
                'role' => User::ROLE_STUDENT,
            ]
        );

        User::where('role', 'user')->update(['role' => User::ROLE_STUDENT]);

        $this->call([
            ExamSeeder::class,
        ]);
    }
}
