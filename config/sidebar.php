<?php

use App\Models\User;

return [
    'default_role' => User::ROLE_PESERTA,

    'roles' => [
        User::ROLE_ADMIN => [
            [
                'title' => 'Utama',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => ['dashboard'], 'icon' => 'home'],
                ],
            ],
            [
                'title' => 'Ujian',
                'items' => [
                    ['label' => 'Daftar Ujian', 'route' => 'admin.exams.index', 'active' => ['admin.exams.*'], 'icon' => 'list'],
                ],
            ],
            [
                'title' => 'User',
                'items' => [
                    ['label' => 'Daftar User', 'route' => 'admin.users.index', 'active' => ['admin.users.*'], 'icon' => 'users'],
                ],
            ],
            [
                'title' => 'Akun',
                'items' => [
                    ['label' => 'Profile', 'route' => 'profile.edit', 'active' => ['profile.*'], 'icon' => 'user'],
                ],
            ],
        ],
        User::ROLE_OPERATOR => [
            [
                'title' => 'Utama',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => ['dashboard'], 'icon' => 'home'],
                ],
            ],
            [
                'title' => 'Ujian',
                'items' => [
                    ['label' => 'Monitoring Ujian', 'route' => 'operator.exams.index', 'active' => ['operator.exams.*'], 'icon' => 'monitor'],
                ],
            ],
            [
                'title' => 'Akun',
                'items' => [
                    ['label' => 'Profile', 'route' => 'profile.edit', 'active' => ['profile.*'], 'icon' => 'user'],
                ],
            ],
        ],
        User::ROLE_AUTHOR => [
            [
                'title' => 'Utama',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => ['dashboard'], 'icon' => 'home'],
                ],
            ],
            [
                'title' => 'Authoring',
                'items' => [
                    ['label' => 'Kelola Soal Ujian', 'route' => 'author.exams.index', 'active' => ['author.exams.*'], 'icon' => 'book'],
                ],
            ],
            [
                'title' => 'Akun',
                'items' => [
                    ['label' => 'Profile', 'route' => 'profile.edit', 'active' => ['profile.*'], 'icon' => 'user'],
                ],
            ],
        ],
        User::ROLE_PESERTA => [
            [
                'title' => 'Utama',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => ['dashboard'], 'icon' => 'home'],
                ],
            ],
            [
                'title' => 'Ujian',
                'items' => [
                    ['label' => 'Daftar Ujian', 'route' => 'peserta.exams.index', 'active' => ['peserta.exams.*'], 'icon' => 'book'],
                ],
            ],
            [
                'title' => 'Akun',
                'items' => [
                    ['label' => 'Profile', 'route' => 'profile.edit', 'active' => ['profile.*'], 'icon' => 'user'],
                ],
            ],
        ],
    ],
];
