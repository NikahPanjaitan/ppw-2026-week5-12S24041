# Tugas Praktikum Minggu 06 — PPW (SIPUS-Del Tahap 2)
## Sistem Informasi Perpustakaan Kampus Del (SIPUS-Del)
**Konektivitas Basis Data Persisten, Arsitektur PDO, Fluent Query Builder, Transaksi ACID & Concurrency Locking, serta Manajemen Sesi & Autentikasi Pustakawan pada Laravel 11.x**

---

## 👩‍🎓 Identitas Mahasiswa
* **Nama:** Nikah Suchia Panjaitan
* **NIM:** 12S24041
* **Program Studi:** Sarjana Sistem Informasi
* **Fakultas:** Fakultas Informatika dan Teknik Elektro
* **Institut:** Institut Teknologi Del
* **Mata Kuliah:** Pemrograman & Pengujian Web (12S3101)
* **Dosen Pengampu:** Chandro Pardede, S.Kom., M.Sc.
* **Branch Pengembangan:** `week6-db-session`

---

## 🔑 Kredensial Akun Uji Pustakawan (Sesuai Modul)
* **URL Login:** `http://127.0.0.1:8000/login`
* **Email:** `pustakawan@del.ac.id`
* **Password:** `password123`
* **Peran:** Pustakawan Terautentikasi (Dapat melakukan mutasi katalog dan sirkulasi peminjaman/pengembalian buku)

---

## 🌟 Spesifikasi & Fitur yang Diimplementasikan (Tahap 2)

### 1. Skema Tabel Transaksi Sirkulasi (`peminjamans`) & Relasi Eloquent
* Membuat migration tabel `peminjamans`:
  * `id` (Primary Key)
  * `kode_pinjam` (string unik, format `PJM-YYYYMMDD-XXXXX`)
  * `buku_id` (Foreign Key mengacu ke `bukus.id` dengan klausa `cascadeOnDelete()`)
  * `nim_peminjam` (string 15)
  * `nama_peminjam` (string 150)
  * `tanggal_pinjam` (date)
  * `tanggal_jatuh_tempo` (date, default 7 hari)
  * `tanggal_kembali` (date, nullable)
  * `status` (`dipinjam` / `dikembalikan`)
  * `timestamps`
* Model Eloquent `Peminjaman` dengan proteksi `$fillable` dan relasi `belongsTo(Buku::class)`.
* Relasi pada model `Buku`: `hasMany(Peminjaman::class)`.

### 2. Transaksi Basis Data ACID & Concurrency Locking (`lockForUpdate`)
* **Transaksi Peminjaman Buku:**
  * Dibungkus di dalam `DB::transaction()` dengan percobaan ulang (retry 3x).
  * Penguncian baris pesimistik (*Pessimistic Locking*): `Buku::where('id', $id)->lockForUpdate()->firstOrFail()` guna mencegah bahaya *Race Condition* saat beberapa pengguna meminjam buku terakhir secara simultan.
  * **Pemeriksaan Stok Kritis:** Jika `stok < 1`, sistem melemparkan `\RuntimeException` yang memicu rollback otomatis tanpa mengurangi stok fisik menjadi minus.
  * **Pencegahan Peminjaman Ganda:** Memeriksa apakah NIM peminjam masih meminjam buku yang sama dengan status `dipinjam`.
  * Pengurangan stok fisik secara atomik via `$buku->decrement('stok')`.
* **Transaksi Pengembalian Buku:**
  * Dibungkus di dalam `DB::transaction()` + `lockForUpdate()`.
  * Mengubah status menjadi `dikembalikan` dan mengisi `tanggal_kembali`.
  * Memulihkan stok fisik buku secara atomik via `$buku->increment('stok')`.
  * Menangkap `QueryException` dan mencatatnya ke `Log::error()`.

### 3. Fluent Query Builder (`DB::table`) & Agregasi `withCount`
* **Katalog Buku:**
  * Menggunakan `Buku::with('kategori')->withCount('peminjamans')` untuk menampilkan frekuensi peminjaman tiap koleksi tanpa memicu masalah N+1.
  * Pencarian dinamis menggunakan method `when()` berdasarkan judul, penulis, ISBN, dan filter kategori.
  * Paginasi aktif dengan `withQueryString()`.
* **Dashboard Metrik Perpustakaan (4 Kartu Statistik):**
  * Total Judul Buku: `DB::table('bukus')->count()`
  * Total Stok Tersedia: `DB::table('bukus')->sum('stok')`
  * Total Buku Sedang Dipinjam: `DB::table('peminjamans')->where('status', 'dipinjam')->count()`
  * Total Buku Dikembalikan: `DB::table('peminjamans')->where('status', 'dikembalikan')->count()`

### 4. Manajemen Sesi Pengguna & Autentikasi Pustakawan
* `AuthController` mengelola siklus hidup autentikasi:
  * Login dengan `Auth::attempt()`.
  * **Regenerasi Session ID:** Wajib memanggil `$request->session()->regenerate()` saat login guna menangkal serangan *Session Fixation*.
  * State sesi server: `session(['login_timestamp' => ...])` dan `session(['librarian_name' => ...])`.
  * **HTTP Cookie Klien:** `Cookie::queue('del_last_librarian_email', $request->email, 10080)` (tersimpan 7 hari dengan proteksi `HttpOnly`).
  * Logout aman: `Auth::logout()`, `$request->session()->invalidate()`, dan `$request->session()->regenerateToken()`.
* Proteksi Rute:
  * Rute publik (Read Only): Katalog buku dan detail buku.
  * Rute terproteksi (`middleware(['auth'])`): Tambah/Edit/Hapus Buku, Kategori, Transaksi Pinjam, dan Pengembalian Buku.

### 5. Seeder Data Uji Khusus (Batas Stok 0 & 1)
* Akun Pustakawan: `pustakawan@del.ac.id` / `password123`.
* Buku Stok Habis (`stok = 0`): *"Basis Data Terdistribusi & Transaksi Konkuren ACID"* (ISBN: `978-602-001-999-0`) untuk verifikasi penolakan peminjaman.
* Buku Stok Satu (`stok = 1`): *"Teori Komputasi & Model Otomata Lanjutan"* (ISBN: `978-602-001-999-1`) untuk verifikasi peminjaman atomik batas kuota.
* Sampel riwayat peminjaman (`dipinjam` & `dikembalikan`).

---

## 🧪 Hasil Pengujian Otomatis (Automated Testing)

Seluruh fungsionalitas Minggu 5 dan Minggu 6 telah diuji dan diverifikasi menggunakan PHPUnit / Pest Feature Tests:
```powershell
php artisan test
```

**Hasil Eksekusi:**
```text
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response

   PASS  Tests\Feature\SipusDelTest
  ✓ root redirects to buku index
  ✓ buku index renders with eager loading
  ✓ buku store validation fails on invalid data
  ✓ buku store success and redirects
  ✓ buku update with route model binding
  ✓ buku destroy
  ✓ kategori crud

   PASS  Tests\Feature\SipusDelWeek6Test
  ✓ guest can view book catalog and analytics dashboard
  ✓ guest is redirected to login when accessing protected routes
  ✓ librarian can login with valid credentials and regenerates session
  ✓ login fails with invalid credentials
  ✓ successful book borrowing decrements stock atomically
  ✓ borrowing fails and rolls back when book stock is zero
  ✓ borrowing fails when same nim has not returned same book
  ✓ successful book return increments stock atomically
  ✓ dynamic search and with count on catalog
  ✓ librarian logout invalidates session

  Tests:    19 passed (85 assertions)
  Duration: 3.02s
```
**Status: 100% HIJAU (PASS).**

---

## 🚀 Panduan Menjalankan Proyek Secara Lokal

1. **Pastikan berada pada branch `week6-db-session`:**
   ```bash
   git checkout week6-db-session
   ```

2. **Setup Lingkungan (.env):**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Jalankan Migrasi & Database Seeder:**
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Jalankan Server Lokal:**
   ```bash
   php artisan serve
   ```
   Buka di peramban: `http://127.0.0.1:8000`.

---

## 📌 Riwayat Git Conventional Commits Minggu 6

1. `feat(migration): create peminjamans table with foreign key to bukus`
2. `feat(transaction): implement ACID book borrowing and return with lockForUpdate`
3. `feat(query-builder): add library analytics dashboard and dynamic search`
4. `feat(auth-session): implement librarian login, session regeneration, and cookies`
