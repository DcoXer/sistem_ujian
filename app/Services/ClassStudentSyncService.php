<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClassStudentSyncService
{
    public function syncFromRawRows(SchoolClass $class, string $studentsRaw): array
    {
        $rows = preg_split('/\r\n|\r|\n/', $studentsRaw);
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        DB::transaction(function () use ($rows, $class, &$created, &$updated, &$skipped, &$errors): void {
            foreach ($rows as $index => $row) {
                $line = trim((string) $row);
                if ($line === '') {
                    continue;
                }

                $parts = array_map('trim', explode('|', $line));

                if (count($parts) < 2 || $parts[0] === '' || $parts[1] === '') {
                    $skipped++;
                    $errors[] = 'Baris '.($index + 1).' format invalid. Minimal NIS/NISN|Nama Lengkap.';
                    continue;
                }

                $nisn = $parts[0];
                $name = $parts[1];
                $nik = $parts[2] ?? null;
                $birthPlace = $parts[3] ?? null;
                $birthDate = $this->parseBirthDate($parts[4] ?? null);
                $rombelName = $parts[5] ?? null;
                $gradeLevel = $this->parseGradeLevel($parts[6] ?? null);
                $guardianName = $parts[7] ?? null;

                $targetClass = $class;
                if ($rombelName !== null && $rombelName !== '') {
                    $resolvedClass = SchoolClass::query()
                        ->when($gradeLevel !== null, fn ($query) => $query->where('grade_level', $gradeLevel))
                        ->where('name', $rombelName)
                        ->orderByDesc('school_year_id')
                        ->first();

                    if (! $resolvedClass) {
                        $skipped++;
                        $errors[] = 'Baris '.($index + 1).' rombel "'.$rombelName.'" tidak ditemukan.';
                        continue;
                    }

                    $targetClass = $resolvedClass;
                }

                $nis = $nisn;
                $email = 's'.Str::lower($nisn).'@student.local';

                $existingByNis = User::query()->where('nis', $nis)->first();
                $existingByNisn = User::query()->where('nisn', $nisn)->first();
                $existing = $existingByNis ?: $existingByNisn;
                if ($existing) {
                    $existing->update([
                        'name' => $name,
                        'class_id' => $targetClass->id,
                        'role' => User::ROLE_STUDENT,
                        'nis' => $nis,
                        'nisn' => $nisn,
                        'nik' => $nik ?: null,
                        'birth_place' => $birthPlace ?: null,
                        'birth_date' => $birthDate,
                        'guardian_name' => $guardianName ?: null,
                    ]);
                    $updated++;
                    continue;
                }

                if (User::query()->where('email', $email)->exists()) {
                    $skipped++;
                    $errors[] = 'Baris '.($index + 1).' email bentrok untuk NIS '.$nis.'.';
                    continue;
                }

                User::query()->create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($nis),
                    'role' => User::ROLE_STUDENT,
                    'nis' => $nis,
                    'nisn' => $nisn,
                    'nik' => $nik ?: null,
                    'birth_place' => $birthPlace ?: null,
                    'birth_date' => $birthDate,
                    'guardian_name' => $guardianName ?: null,
                    'class_id' => $targetClass->id,
                ]);
                $created++;
            }
        });

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    private function parseBirthDate(?string $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $raw)->format('Y-m-d');
            } catch (\Throwable) {
                // try next format
            }
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseGradeLevel(?string $value): ?int
    {
        $raw = trim((string) $value);
        if ($raw === '' || ! ctype_digit($raw)) {
            return null;
        }

        $grade = (int) $raw;
        if ($grade < 1 || $grade > 12) {
            return null;
        }

        return $grade;
    }
}
