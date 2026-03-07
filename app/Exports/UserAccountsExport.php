<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserAccountsExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected Collection $rows
    ) {
    }

    public function collection(): Collection
    {
        return $this->rows->map(function ($user) {
            return [
                'nisn' => $user->nisn,
                'name' => $user->name,
                'email' => $user->email,
                'class_name' => $user->schoolClass?->name,
                'password' => $user->role === 'student' ? ($user->nisn ?? '') : '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'NISN',
            'Nama',
            'Email',
            'Kelas',
            'Password',
        ];
    }
}
