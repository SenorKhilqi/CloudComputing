# Aplikasi Manajemen Jadwal Tugas PHP Native

Aplikasi sederhana untuk mengelola jadwal tugas mata kuliah dengan fungsionalitas CRUD.

## Struktur Database

Tabel `tasks` memiliki kolom-kolom berikut:
- `id` (INT, Primary Key, Auto Increment)
- `mata_kuliah` (VARCHAR)
- `tugas` (VARCHAR)
- `level_kesulitan` (ENUM: 'low', 'medium', 'high', 'urgent')
- `status` (ENUM: 'not_done', 'still_working_on_it')
- `tempat_pengumpulan` (ENUM: 'GCR', 'google_drive', 'hardfile', 'vcalss')
- `notes` (TEXT)

## Konfigurasi Database

Koneksi database diatur dalam `config.php` menggunakan PDO.

```php
<?php
$host = 'db'; // Nama service database di docker-compose.yml
$db   = 'task_manager';
$user = 'user';
$pass = 'password';
$charset = 'utf8mb4';

// ... (sisanya ada di file config.php)
?>