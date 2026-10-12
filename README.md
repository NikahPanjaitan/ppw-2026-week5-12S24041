# SIPUS-Del (Sistem Informasi Perpustakaan Kampus Del)

[![Laravel 11](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.3%20%7C%208.4-blue.svg)](https://php.net)
[![Bootstrap 5](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)](https://getbootstrap.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Aplikasi web berbasis server-side rendering (SSR) dengan arsitektur **Model-View-Controller (MVC)** untuk mengelola katalog inventaris perpustakaan kampus Institut Teknologi Del (IT Del). Dibuat untuk memenuhi Tugas Praktikum Minggu 05 Mata Kuliah **Pemrograman & Pengujian Web (12S3101)**.

---

## 📋 Daftar Isi
1. [Fitur Utama](#-fitur-utama)
2. [Prasyarat Sistem](#-prasyarat-sistem)
3. [Panduan Instalasi & Menjalankan](#-panduan-instalasi--menjalankan)
4. [Skema Basis Data & Relasi](#-skema-basis-data--relasi)
5. [Daftar Rute RESTful](#-daftar-rute-restful)
6. [Validasi Formulir Server-Side](#-validasi-formulir-server-side)
7. [Mitigasi N+1 Query & Bukti Audit SQL](#-mitigasi-n1-query--bukti-audit-sql)
8. [Pengujian Otomatis (Testing)](#-pengujian-otomatis-testing)
9. [Konvensi Git Commit](#-konvensi-git-commit)

---

## 🚀 Fitur Utama
- **Arsitektur Standar MVC Laravel 11**: Pemisahan yang ketat antara Model, View, dan Controller.
- **Relasi Eloquent One-to-Many**: Relasi `Kategori hasMany Buku` dan `Buku belongsTo Kategori` dengan proteksi `$fillable` dan penghapusan kaskade (`cascadeOnDelete`).
- **RESTful Resource Controller**: 7 aksi CRUD baku untuk entitas `Buku` dan `Kategori` memanfaatkan *Implicit Route Model Binding*.
- **Pencegahan N+1 Query Problem**: Menerapkan Eager Loading `Buku::with('kategori')` pada seluruh index daftar buku.
- **Validasi Sisi Server Ketat**: Validasi masukan via `$request->validate()` langsung di Controller, pesan galat presisi `@error`, dan perlindungan token `@csrf` serta method spoofing `@method('PUT')` / `@method('DELETE')`.
- **Blade Templating Modern**: Master Shell Component `<x-layout>` berbalut identitas warna **Del Royal Purple (`#4C1D95`)**, notifikasi flash message session, dan direktif aman `@forelse`.
- **Database Seeder & Factory Realistis**: Mock data pustaka buku menggunakan Faker (6 kategori dan 21 buku akademik Del).
- **Audit Listener Kueri SQL**: Pencatatan kueri SQL otomatis di `storage/logs/laravel.log`.

---

## 🛠 Prasyarat Sistem
- **PHP**: Versi 8.2, 8.3, atau 8.4 CLI dengan ekstensi aktif: `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `curl`, `fileinfo`.
- **Composer**: Versi 2.6.x atau lebih baru.
- **Basis Data**: SQLite (default bawaan).

---

## 💻 Panduan Instalasi & Menjalankan

1. **Clone Repositori**:
   ```bash
   git clone https://github.com/ppw-2026-week5-[NIM].git sipus-del
   cd sipus-del
   ```

2. **Pasang Dependensi Composer**:
   ```bash
   composer install
   ```

3. **Konfigurasi Berkas Environment**:
   Salin berkas konfigurasi `.env.example` ke `.env`:
   ```bash
   cp .env.example .env
   ```
   Pastikan pengaturan basis data pada `.env`:
   ```env
   DB_CONNECTION=sqlite
   ```

4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

5. **Eksekusi Migrasi & Suntikkan Mock Data Seeder**:
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Jalankan Server Lokal**:
   ```bash
   php artisan serve
   ```
   Buka peramban web pada alamat: `http://127.0.0.1:8000` (akan dialihkan otomatis ke `/buku`).

---

## 🗄 Skema Basis Data & Relasi

### 1. Tabel `kategoris`
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | BigInteger (PK) | Auto increment primary key |
| `kode_kategori` | Varchar(20) | Kode unik kategori (e.g. `KTG-001`) |
| `nama_kategori` | Varchar(100) | Nama klasifikasi bidang ilmu |
| `created_at` / `updated_at` | Timestamp | Pencatatan waktu rekaman data |

### 2. Tabel `bukus`
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | BigInteger (PK) | Auto increment primary key |
| `isbn` | Varchar(30) | Nomor standar buku internasional (unik) |
| `judul` | Varchar(255) | Judul buku |
| `penulis` | Varchar(150) | Nama pengarang buku |
| `penerbit` | Varchar(150) | Badan penerbit buku |
| `tahun_terbit` | Integer | Tahun cetak/terbit (4 digit) |
| `kategori_id` | BigInteger (FK) | Terhubung ke `kategoris.id` (`cascadeOnDelete`) |
| `stok` | Integer | Jumlah eksemplar tersedia (min: 0) |
| `sinopsis` | Text (Nullable) | Sinopsis / ringkasan isi buku |
| `created_at` / `updated_at` | Timestamp | Pencatatan waktu rekaman data |

---

## 🛣 Daftar Rute RESTful

| Method HTTP | URL Endpoint | Route Name | Action Controller | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/` | - | Closure redirect | Dialihkan ke `buku.index` |
| `GET` | `/buku` | `buku.index` | `BukuController@index` | Menampilkan katalog buku (+ pagination) |
| `GET` | `/buku/create` | `buku.create` | `BukuController@create` | Form input buku baru |
| `POST` | `/buku` | `buku.store` | `BukuController@store` | Menyimpan buku baru ke DB |
| `GET` | `/buku/{buku}` | `buku.show` | `BukuController@show` | Menampilkan detail spesifik buku |
| `GET` | `/buku/{buku}/edit` | `buku.edit` | `BukuController@edit` | Form edit data buku |
| `PUT/PATCH` | `/buku/{buku}` | `buku.update` | `BukuController@update` | Memperbarui data buku |
| `DELETE` | `/buku/{buku}` | `buku.destroy` | `BukuController@destroy` | Menghapus rekaman buku |
| `GET` | `/kategori` | `kategori.index` | `KategoriController@index` | Menampilkan daftar kategori |
| `GET` | `/kategori/create` | `kategori.create` | `KategoriController@create` | Form input kategori baru |
| `POST` | `/kategori` | `kategori.store` | `KategoriController@store` | Menyimpan kategori baru ke DB |
| `GET` | `/kategori/{kategori}` | `kategori.show` | `KategoriController@show` | Menampilkan kategori & buku terkait |
| `GET` | `/kategori/{kategori}/edit` | `kategori.edit` | `KategoriController@edit` | Form edit kategori |
| `PUT/PATCH` | `/kategori/{kategori}` | `kategori.update` | `KategoriController@update` | Memperbarui data kategori |
| `DELETE` | `/kategori/{kategori}` | `kategori.destroy` | `KategoriController@destroy` | Menghapus kategori & kaskade bukunya |

---

## 🛡 Validasi Formulir Server-Side
Validasi didefinisikan langsung pada method controller via `$request->validate()`:
```php
$validated = $request->validate([
    'isbn'         => 'required|string|max:30|unique:bukus,isbn,' . ($buku->id ?? 'NULL'),
    'judul'        => 'required|string|min:5|max:255',
    'penulis'      => 'required|string|max:150',
    'penerbit'     => 'required|string|max:150',
    'tahun_terbit' => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 1),
    'kategori_id'  => 'required|exists:kategoris,id',
    'stok'         => 'required|integer|min:0',
    'sinopsis'     => 'nullable|string',
]);
```

---

## ⚡ Mitigasi N+1 Query & Bukti Audit SQL

### Masalah N+1 Problem (Lazy Loading)
Jika model buku dipanggil tanpa relasi:
```php
// Buruk: Memanggil 1 kueri buku + N kueri kategori (N+1 = 11 kueri)
$bukus = Buku::latest()->paginate(10);
```

### Solusi Eager Loading (`with()`)
Pada `BukuController.php`:
```php
// Optimal: Memanggil tepat 2 kueri SQL terpadu
$bukus = Buku::with('kategori')->latest()->paginate(10);
```

### Bukti Eksekusi pada `storage/logs/laravel.log`:
```text
[2026-10-12 02:09:39] local.INFO: [SQL AUDIT] (2.33 ms) select count(*) as aggregate from "bukus"
[2026-10-12 02:09:39] local.INFO: [SQL AUDIT] (0.62 ms) select * from "bukus" order by "created_at" desc limit 10 offset 0
[2026-10-12 02:09:39] local.INFO: [SQL AUDIT] (0.44 ms) select * from "kategoris" where "kategoris"."id" in (2, 3, 4, 5, 6)
```
> **Kesimpulan**: Hanya 2 kueri data yang dieksekusi terlepas dari berapapun jumlah baris buku yang ditampilkan.

---

## 🧪 Pengujian Otomatis (Testing)
Jalankan pengujian unit dan fitur terpadu:
```bash
php artisan test
```
Hasil:
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

  Tests:    9 passed (35 assertions)
```

---

## 📌 Konvensi Git Commit
```bash
git commit -m "chore: initial project scaffolding with Laravel 11"
git commit -m "feat(migration): create kategoris and bukus tables schema"
git commit -m "feat(model): define relationships and fillable attributes"
git commit -m "feat(controller): implement restful actions and server validation"
git commit -m "feat(views): construct blade component layout and crud templates"
git commit -m "perf(eager-loading): optimize sql queries to prevent n+1 problem"
```
