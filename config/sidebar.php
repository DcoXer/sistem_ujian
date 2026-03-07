<?php

use App\Models\User;

return [
    'default_role' => User::ROLE_STUDENT,

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
                'title' => 'Akademik',
                'items' => [
                    ['label' => 'Rombel & Siswa', 'route' => 'admin.classes.index', 'active' => ['admin.classes.*'], 'icon' => 'users'],
                    ['label' => 'Mata Pelajaran', 'route' => 'admin.subjects.index', 'active' => ['admin.subjects.*'], 'icon' => 'book'],
                    ['label' => 'Assignment Guru', 'route' => 'admin.assignments.index', 'active' => ['admin.assignments.*'], 'icon' => 'book'],
                ],
            ],
            [
                'title' => 'User',
                'items' => [
                    [
                        'label' => 'Daftar User',
                        'icon' => 'users',
                        'active' => ['admin.users.*'],
                        'children' => [
                            ['label' => 'Siswa', 'route' => 'admin.users.students.index', 'active' => ['admin.users.students.*']],
                            ['label' => 'Guru', 'route' => 'admin.users.teachers.index', 'active' => ['admin.users.teachers.*']],
                            ['label' => 'Operator', 'route' => 'admin.users.operators.index', 'active' => ['admin.users.operators.*']],
                        ],
                    ],
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
        User::ROLE_TEACHER => [
            [
                'title' => 'Utama',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => ['dashboard'], 'icon' => 'home'],
                ],
            ],
            [
                'title' => 'Teacher',
                'items' => [
                    ['label' => 'Kelola Soal Ujian', 'route' => 'teacher.exams.index', 'active' => ['teacher.exams.*'], 'icon' => 'book'],
                    ['label' => 'Data Siswa Wali', 'route' => 'teacher.homeroom.students.index', 'active' => ['teacher.homeroom.students.*'], 'icon' => 'users'],
                    ['label' => 'Hasil Wali Kelas', 'route' => 'teacher.homeroom.results.index', 'active' => ['teacher.homeroom.results.*'], 'icon' => 'pencil'],
                ],
            ],
            [
                'title' => 'Akun',
                'items' => [
                    ['label' => 'Profile', 'route' => 'profile.edit', 'active' => ['profile.*'], 'icon' => 'user'],
                ],
            ],
        ],
        User::ROLE_STUDENT => [
            [
                'title' => 'Utama',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => ['dashboard'], 'icon' => 'home'],
                ],
            ],
            [
                'title' => 'Ujian',
                'items' => [
                    ['label' => 'Daftar Ujian', 'route' => 'student.exams.index', 'active' => ['student.exams.*'], 'icon' => 'book'],
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
