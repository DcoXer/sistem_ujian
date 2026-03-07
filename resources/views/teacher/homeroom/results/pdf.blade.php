<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Wali Kelas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 16px; margin: 0 0 8px; }
        .meta { margin-bottom: 12px; color: #374151; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .small { color: #6b7280; font-size: 10px; }
    </style>
</head>
<body>
    <h1>Hasil Ujian Wali Kelas</h1>
    <p class="meta">
        Kelas Wali: {{ $assignedClasses->pluck('name')->implode(', ') }}<br>
        Dicetak: {{ $printedAt->format('d M Y H:i') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Kelas</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Mapel</th>
                <th>Ujian</th>
                <th>Skor</th>
                <th>Status</th>
                <th>Submit</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->class_name ?? '-' }}</td>
                    <td>{{ $row->student_nis ?? '-' }}</td>
                    <td>{{ $row->student_name }}</td>
                    <td>{{ $row->subject_name ?? '-' }}</td>
                    <td>{{ $row->exam_title }}</td>
                    <td>{{ $row->score !== null ? $row->score : '-' }}</td>
                    <td>{{ $row->attempt_status }}</td>
                    <td>{{ $row->submitted_at?->format('d M Y H:i') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="small">Belum ada data hasil ujian untuk kelas wali Anda.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
