# Laporan Praktikum Minggu 05
### Pemrograman & Pengujian Web (12S3101)

* **Topik Modul:** Implementasi Pola Arsitektur Model-View-Controller (MVC), Eloquent ORM, Validasi Server-Side, Blade Component Engine, dan Asset Bundling pada Laravel 11.x  
* **Nama Studi Kasus:** SIPUS-Del (Sistem Informasi Perpustakaan Kampus Del)  
* **Dosen Pengampu:** Chandro Pardede, S.Kom., M.Sc.  
* **Tahun Akademik:** Semester Ganjil 2026/2027  

---

## Identitas Mahasiswa

* **Nama:** Suchia Panjaitan  
* **NIM:** [Isi NIM Anda, contoh: 12S23001]  
* **Program Studi:** Sarjana Sistem Informasi / Sarjana Informatika  
* **Fakultas:** Fakultas Informatika dan Teknik Elektro (FITE)  
* **Institusi:** Institut Teknologi Del, Laguboti  

---

## 1. Deskripsi Singkat Aplikasi

**SIPUS-Del** adalah sistem informasi perpustakaan berbasis web *server-side rendering* (SSR) yang dirancang untuk mengelola katalog koleksi buku dan klasifikasi bidang ilmu di Institut Teknologi Del. 

Aplikasi ini dibangun menggunakan framework **Laravel 11** dengan mematuhi prinsip *Separation of Concerns* (SoC) pada pola MVC standar:
* **Model:** Mengelola representasi entitas data (`Kategori` dan `Buku`) menggunakan Eloquent ORM.
* **View:** Menyajikan antarmuka pengguna berbasis komponen Blade (`<x-layout>`) dengan styling Bootstrap 5 CDN bertema warna **Del Royal Purple (`#4C1D95`)**.
* **Controller:** Mengorkestrasi aliran HTTP Request, validasi masukan form di sisi server, dan eksekusi query data.

---

## 2. Skema Basis Data & Relasi Eloquent

### A. Tabel `kategoris`
Menyimpan klasifikasi kategori buku.
* `id`: BigInteger (Primary Key, Auto Increment)
* `kode_kategori`: Varchar(20) — Unik, kode pengenal kategori (misal: `KTG-001`)
* `nama_kategori`: Varchar(100) — Nama bidang ilmu / kategori buku
* `created_at` & `updated_at`: Timestamps

### B. Tabel `bukus`
Menyimpan data katalog inventaris buku perpustakaan.
* `id`: BigInteger (Primary Key, Auto Increment)
* `isbn`: Varchar(30) — Unik, nomor ISBN buku
* `judul`: Varchar(255) — Judul buku
* `penulis`: Varchar(150) — Nama pengarang buku
* `penerbit`: Varchar(150) — Nama penerbit
* `tahun_terbit`: Integer — Tahun terbit buku (4 digit)
* `kategori_id`: Foreign Key ke `kategoris(id)` dengan opsi `cascadeOnDelete()`
* `stok`: Integer — Jumlah eksemplar buku yang tersedia (min: 0)
* `sinopsis`: Text (Nullable) — Ringkasan singkat isi buku
* `created_at` & `updated_at`: Timestamps

### C. Kardinalitas Relasi Eloquent
1. **Model `Kategori.php`** memiliki relasi *One-to-Many*:
   ```php
   public function bukus(): HasMany
   {
       return $this->hasMany(Buku::class, 'kategori_id');
   }
   ```
2. **Model `Buku.php`** memiliki relasi *Many-to-One*:
   ```php
   public function kategori(): BelongsTo
   {
       return $this->belongsTo(Kategori::class, 'kategori_id');
   }
   ```
Kedua model telah dilindungi dengan properti `$fillable` untuk mencegah celah keamanan *Mass Assignment*.

---

## 3. Rute RESTful & Resource Controller

Semua aksi CRUD didaftarkan secara deklaratif di `routes/web.php`:
```php
Route::get('/', fn () => redirect()->route('buku.index'));

Route::resource('kategori', KategoriController::class);
Route::resource('buku', BukuController::class);
```

### Tabel Pemetaan 7 Aksi RESTful:
| Method HTTP | URL Endpoint | Action Controller | Fungsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/buku` | `BukuController@index` | Menampilkan katalog buku (+ pagination & pencarian) |
| `GET` | `/buku/create` | `BukuController@create` | Menampilkan formulir tambah buku baru |
| `POST` | `/buku` | `BukuController@store` | Menyimpan buku baru ke database |
| `GET` | `/buku/{buku}` | `BukuController@show` | Menampilkan detail buku (*Route Model Binding*) |
| `GET` | `/buku/{buku}/edit` | `BukuController@edit` | Menampilkan formulir edit buku (*Route Model Binding*) |
| `PUT` | `/buku/{buku}` | `BukuController@update` | Memperbarui data buku di database |
| `DELETE` | `/buku/{buku}` | `BukuController@destroy` | Menghapus buku dari database |

*(Entitas Kategori memiliki 7 aksi RESTful yang identik pada `KategoriController`)*.

---

## 4. Validasi Sisi Server & Keamanan Web

Validasi input dilakukan langsung di controller menggunakan method bawaan `$request->validate()`:
```php
$validated = $request->validate([
    'isbn'         => 'required|string|max:30|unique:bukus,isbn,' . ($buku->id ?? ''),
    'judul'        => 'required|string|min:5|max:255',
    'penulis'      => 'required|string|max:150',
    'penerbit'     => 'required|string|max:150',
    'tahun_terbit' => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 1),
    'kategori_id'  => 'required|exists:kategoris,id',
    'stok'         => 'required|integer|min:0',
    'sinopsis'     => 'nullable|string',
]);
```

**Fitur Keamanan:**
* **CSRF Token:** Semua formulir menyertakan token `@csrf` untuk mencegah serangan *Cross-Site Request Forgery*.
* **Method Spoofing:** Formulir update menggunakan `@method('PUT')` dan hapus menggunakan `@method('DELETE')`.
* **Pencegahan XSS:** Penampilan teks menggunakan sintaks blade `{{ ... }}` yang otomatis melakukan *HTML escaping*.
* **Feedback Galat:** Tampilan pesan galat validasi responsif menggunakan direktif `@error('nama_field')`.

---

## 5. Mitigasi N+1 Problem & Bukti Audit SQL Log

### Masalah N+1 Query (Lazy Loading)
Jika mengambil daftar buku lalu memanggil relasi kategori secara terpisah di loop Blade (`$buku->kategori->nama_kategori`), server akan mengeksekusi 1 kueri untuk tabel buku ditambah N kueri terpisah untuk setiap kategori (Total $1 + N = 11$ kueri untuk 10 data buku).

### Solusi Eager Loading (`with()`)
Di `BukuController@index`, relasi kategori dimuat lebih awal:
```php
$bukus = Buku::with('kategori')->latest()->paginate(10);
```

### Bukti Audit Kueri SQL dari `storage/logs/laravel.log`:
Audit kueri diaktifkan melalui listener `DB::listen` pada `app/Providers/AppServiceProvider.php`:
```text
[SQL AUDIT] (0.48 ms) select * from "bukus" order by "created_at" desc limit 10 offset 0
[SQL AUDIT] (0.50 ms) select * from "kategoris" where "kategoris"."id" in (1, 2, 3, 4, 5)
```
> **Pembuktian:** Laravel hanya mengeksekusi **tepat 2 kueri terpadu** berkat klausa `WHERE IN`. Hal ini membuktikan bahwa masalah $N+1$ Query Problem telah tereliminasi sepenuhnya.

---

## 6. Panduan Menjalankan Proyek Secara Lokal

1. **Clone Repositori:**
   ```bash
   git clone https://github.com/ppw-2026-week5-[NIM].git sipus-del
   cd sipus-del
   ```

2. **Pasang Dependensi:**
   ```bash
   composer install
   ```

3. **Konfigurasi Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Migrasi Database & Seeder:**
   ```bash
   php artisan migrate:fresh --seed
   ```
   *(Perintah ini akan membuat skema tabel dan menyuntikkan 6 kategori serta 21 buku akademik Del menggunakan Faker).*

5. **Jalankan Pengujian Otomatis:**
   ```bash
   php artisan test
   ```
   *(9 pengujian Unit & Feature test berstatus PASS hijau).*

6. **Jalankan Server Lokal:**
   ```bash
   php artisan serve
   ```
   Buka di peramban: `http://127.0.0.1:8000`.

---

## 7. Riwayat Git Conventional Commits

Commit repositori disusun teratur sesuai standar konvensi yang ditentukan pada modul praktikum:

| Commit Hash | Pesan Commit (Conventional Commits) |
| :--- | :--- |
| `88cf695` | `chore: initial project scaffolding with Laravel 11` |
| `d11ba0a` | `feat(migration): create kategoris and bukus tables schema` |
| `c1800f1` | `feat(model): define relationships and fillable attributes` |
| `9068d98` | `feat(controller): implement restful actions and server validation` |
| `25bbc25` | `feat(views): construct blade component layout and crud templates` |
| `4f35ee0` | `perf(eager-loading): optimize sql queries to prevent n+1 problem` |
