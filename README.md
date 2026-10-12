<p align="center">
  <img src="public/images/logo-del.png" alt="Logo Institut Teknologi Del" width="120">
</p>

# Tugas Praktikum Minggu 05 — PPW
## Sistem Informasi Perpustakaan Kampus Del (SIPUS-Del)

## Identitas Mahasiswa
* **Nama:** Nikah Suchia Panjaitan
* **NIM:** 12S24041
* **Program Studi:** S1 Sistem Informasi
* **Institut:** Institut Teknologi Del
* **Dosen Pengampu:** Chandro Pardede, S.Kom., M.Sc.

---

## Deskripsi Tugas
Aplikasi **SIPUS-Del** adalah sistem informasi perpustakaan berbasis web yang dibuat menggunakan framework **Laravel 11**. Tugas ini bertujuan untuk mengimplementasikan konsep arsitektur Model-View-Controller (MVC), relasi Eloquent ORM (One-to-Many), validasi form sisi server, Blade component engine, dan optimasi query kueri database (mitigasi N+1 problem menggunakan Eager Loading).

---

## Fitur yang Dikerjakan

### 1. Manajemen Koleksi Buku (CRUD Lengkap)
* **Tampilkan Daftar Buku:** Menampilkan katalog buku dengan pagination (10 buku per halaman) dan relasi kategori.
* **Tambah Buku Baru:** Formulir input buku dengan validasi sisi server:
  * ISBN unik
  * Judul minimal 5 karakter
  * Penulis, penerbit, tahun terbit (4 digit) wajib diisi
  * Kategori harus terdaftar di database
  * Stok minimal 0 (tidak boleh minus)
* **Detail Buku:** Melihat informasi lengkap buku dan sinopsis menggunakan Route Model Binding.
* **Edit Buku:** Mengubah informasi data buku menggunakan method spoofing `@method('PUT')`.
* **Hapus Buku:** Menghapus data buku menggunakan method spoofing `@method('DELETE')`.

### 2. Manajemen Kategori Buku (CRUD Lengkap)
* Menampilkan daftar kategori beserta jumlah buku di dalamnya.
* Tambah kategori baru dengan kode kategori unik dan nama kategori.
* Edit dan hapus data kategori.
* Relasi: Satu kategori memiliki banyak buku (`hasMany`), dan setiap buku berinduk ke satu kategori (`belongsTo`).

### 3. Tampilan Antarmuka (Blade UI & Bootstrap 5)
* Menggunakan master layout `<x-layout>` dengan Bootstrap 5 CDN.
* Nuansa warna identitas kampus Del: **Del Royal Purple (`#4C1D95`)** dan **Del Blue (`#0284C7`)**.
* Menampilkan pesan notifikasi flash session saat aksi berhasil (`@if(session('success'))`).
* Menggunakan direktif `@forelse` dan `@empty` untuk menangani tabel jika data masih kosong.
* Form dilengkapi token keamanan `@csrf` dan penampil pesan error `@error`.

---

## Pembuktian Mitigasi N+1 Query Problem (Eager Loading)

Pada `BukuController@index`, pengambilan data buku menerapkan Eager Loading:
```php
$bukus = Buku::with('kategori')->latest()->paginate(10);
```

Dengan Eager Loading, Laravel tidak menjalankan kueri berulang (1 + N kueri), melainkan hanya mengeksekusi **tepat 2 kueri terpadu** menggunakan klausa `WHERE IN`.

Bukti log audit kueri yang tercatat di `storage/logs/laravel.log`:
```text
[SQL AUDIT] (0.48 ms) select * from "bukus" order by "created_at" desc limit 10 offset 0
[SQL AUDIT] (0.50 ms) select * from "kategoris" where "kategoris"."id" in (1, 2, 3, 4, 5)
```

---

## Cara Menjalankan Proyek Secara Lokal

1. **Clone Repositori:**
   ```bash
   git clone https://github.com/ppw-2026-week5-12S24041.git
   cd ppw-2026-week5-12S24041
   ```

2. **Install Dependensi Composer:**
   ```bash
   composer install
   ```

3. **Setup File Environment (.env):**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Jalankan Migrasi dan Seeder:**
   ```bash
   php artisan migrate:fresh --seed
   ```
   *(Akan membuat tabel dan mengisi 6 kategori serta 21 buku menggunakan Faker)*.

5. **Jalankan Server:**
   ```bash
   php artisan serve
   ```
   Akses di browser pada: `http://127.0.0.1:8000`.

---

## Riwayat Git Conventional Commits

Commit tugas ini disusun sesuai ketentuan modul praktikum:
* `feat(migration): create kategoris and bukus tables schema`
* `feat(model): define relationships and fillable attributes`
* `feat(controller): implement restful actions and server validation`
* `feat(views): construct blade component layout and crud templates`
* `perf(eager-loading): optimize sql queries to prevent n+1 problem`
