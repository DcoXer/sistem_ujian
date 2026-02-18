# Testing Strategy

## Test Suite Location
Semua guardrail domain exam ada di:
- `tests/Feature/Exam`

Struktur:
- `LifecycleTest.php`
- `ExpirationTest.php`
- `ResultVisibilityTest.php`
- `AttemptAuthorizationTest.php`
- `OperatorControlTest.php`
- `AuthorQuestionManagementTest.php`
- `MaliciousAuthorTest.php`
- `ArchitectureGuardsTest.php`
- `ThinControllersGuardTest.php`
- `SystemInvariantsTest.php`
- `ServiceDependencyDirectionGuardTest.php`

## Guardrail Wajib

### Lifecycle
- publish `draft -> running`
- expire `running -> finished`
- `finished` tidak bisa publish ulang

### Expiration
- command menutup exam yang expired
- command force expire attempt yang melewati deadline

### Authorization
- peserta tidak bisa akses/submit attempt milik user lain

### Result Visibility
- peserta tidak bisa lihat result sebelum exam `finished`
- peserta bisa lihat result setelah exam `finished`

### Operator Boundary
- operator boleh kontrol attempt sesuai aturan
- operator tidak boleh publish exam

### Architecture Guard
- mutasi `exam.status` hanya boleh terjadi di `ExamLifecycleService`
- jika `ArchitectureGuardsTest` gagal, perubahan harus ditolak meskipun fitur tampak bekerja

### Negative Invariants
- exam `finished` tidak boleh menyisakan attempt `active` setelah lifecycle sync
- attempt `submitted` tidak boleh bisa dijawab ulang atau di-submit ulang

### Service Dependency Direction
- service primitive/orchestration harus mengikuti arah dependensi yang sudah ditetapkan
- jika `ServiceDependencyDirectionGuardTest` gagal, perubahan harus ditolak

### Adversarial Role Tests
- `MaliciousAuthorTest` mengasumsikan author jahat (privilege escalation)
- jika test adversarial gagal, release harus ditahan

## Menjalankan Test
- Semua test: `php artisan test`
- Exam suite saja: `php artisan test tests/Feature/Exam`
- Satu file: `php artisan test tests/Feature/Exam/LifecycleTest.php`

## Kebijakan Hapus Test
Boleh dihapus:
- test kosmetik UI/HTML detail
- test fitur domain yang sudah dipurge

Tidak boleh dihapus:
- lifecycle, expiration, authorization, result visibility, architecture guard
