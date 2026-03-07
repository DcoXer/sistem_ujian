<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class HomeroomResultsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        protected Collection $rows
    ) {
    }

    public function headings(): array
    {
        return ['Kelas', 'NIS', 'Nama Siswa', 'Mapel', 'Ujian', 'Skor', 'Status Attempt', 'Waktu Submit'];
    }

    public function collection(): Collection
    {
        return $this->rows->map(fn ($row) => [
            $row->class_name ?? '-',
            $row->student_nis ?? '',
            $row->student_name ?? '',
            $row->subject_name ?? '-',
            $row->exam_title ?? '',
            $row->score !== null ? (string) $row->score : '',
            $row->attempt_status ?? '',
            $row->submitted_at ? $row->submitted_at->format('Y-m-d H:i:s') : '',
        ]);
    }
}
