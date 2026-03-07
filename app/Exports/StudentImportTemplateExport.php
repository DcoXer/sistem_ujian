<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class StudentImportTemplateExport implements FromArray
{
    public function array(): array
    {
        return [
            ['NISN', 'Nama Lengkap', 'NIK', 'Tempat Lahir', 'Tanggal Lahir', 'Rombel', 'Tingkat', 'Nama Wali'],
            ['32010001', 'Andi Pratama', '3201000101010001', 'Jakarta', '2014-01-01', '6A', '6', 'Siti Rahma'],
            ['32010002', 'Budi Santoso', '3201000202020002', 'Bandung', '2014-02-02', '6A', '6', 'Rina'],
        ];
    }
}

