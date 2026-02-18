# Legacy Migrations Decision

## Decision
Legacy migration terkait fitur lama (`match/team`) diperlakukan sebagai **history yang dipertahankan**, bukan sampah sementara.

## Why
- Menjaga jejak evolusi schema untuk audit teknis.
- Menjaga kompatibilitas urutan migrasi pada environment yang sudah pernah hidup.
- Menghindari ambiguity antara "fitur sudah dipurge" vs "schema tidak pernah ada".

## Current Strategy
- Migration lama tetap ada sebagai sejarah.
- Final state schema exam engine dijaga oleh migration pembersih:
  - `database/migrations/2026_02_14_000012_drop_legacy_match_tables.php`

## Rule
- Jangan hapus migration legacy secara diam-diam.
- Jika strategi berubah (mis. squash total), wajib ADR baru + rencana migrasi eksplisit.
