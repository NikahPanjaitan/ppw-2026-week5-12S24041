# Praktikum Minggu 09 — Web Services & REST API (SIPUS-Del API v1)
## Sistem Informasi Perpustakaan Kampus Del (SIPUS-Del Tahap 3)

---

## I. Identitas Mahasiswa
* **Nama Lengkap:** Nikah Suchia Panjaitan
* **NIM:** 12S24041
* **Program Studi:** Sarjana Sistem Informasi
* **Fakultas:** Fakultas Informatika dan Teknik Elektro
* **Kampus:** Institut Teknologi Del, Laguboti
* **Mata Kuliah:** Pemrograman dan Pengujian Aplikasi Web (12S3101)
* **Dosen Pengampu:** Chandro Pardede, S.Kom., M.Sc.

---

## II. Analisis Kebutuhan & Kelayakan Web Services (Feasibility Analysis)

### 2.1 Peran SIPUS-Del dalam Ekosistem Layanan Kampus
Dalam pengembangan tahap 3 ini, aplikasi **SIPUS-Del** berperan ganda dalam ekosistem pertukaran data antar-mesin (*Machine-to-Machine*):
1. **Sebagai Service Provider:**
   * **SOAP Legacy Provider (`/soap/sipus`):** Melayani sistem legacy Administrasi Bebas Pustaka Yudisium IT Del untuk memvalidasi kelulusan mahasiswa berdasarkan ada tidaknya buku yang masih dipinjam.
   * **RESTful API Provider (`/api/v1/...`):** Menyediakan layanan katalog buku terpaginasi, manajemen buku, dan transaksi sirkulasi peminjaman/pengembalian buku untuk aplikasi Kiosk Mandiri dan Mobile Mahasiswa.
2. **Sebagai Service Consumer:**
   * **REST Consumer OpenLibrary (`/api/v1/bukus/{id}/referensi-eksternal`):** Memanggil API publik OpenLibrary (`https://openlibrary.org`) menggunakan fasad `Http` Laravel untuk memperkaya metadata buku (penerbit asli, tahun rilis internasional, jumlah halaman, dan cover) tanpa membebani database lokal.

---

### 2.2 Matriks Komparasi Arsitektur: Web Monolitik vs SOAP vs RESTful API

| Aspek Pembanding | Web Monolitik (Minggu 5 & 6) | SOAP Web Service (Minggu 9) | RESTful Web API v1 (Minggu 9) |
| :--- | :--- | :--- | :--- |
| **Target Konsumen** | Manusia via Browser | Mesin ke Mesin (Sistem Enterprise Legacy) | Mesin ke Mesin (Mobile, SPA, Mitra Kampus) |
| **Gaya / Protokol** | SSR Monolith (HTML Rendering) | Protokol Formal W3C (XML Message Wrapper) | Gaya Arsitektur Roy Fielding (HTTP Standar) |
| **Lokasi Rute Laravel** | `routes/web.php` | `routes/web.php` (Bypass CSRF) | `routes/api.php` (Prefix `/api/v1`) |
| **Format Payload** | Dokumen HTML + CSS (~100–300 KB) | Wajib XML `<soap:Envelope>` (Verbose ~1–3 KB) | JSON Murni (`application/json` ~200–800 Byte) |
| **Manajemen State** | Stateful (Session DB + Cookie Browser) | Umumnya Stateless (bisa WS-Session) | **Wajib Stateless** (Bearer Token Sanctum) |
| **Proteksi Keamanan** | Token CSRF Form (`@csrf`) | WS-Security / Header XML | Bearer Token Header (`Authorization: Bearer`) |
| **Kontrak Layanan** | Antarmuka Visual Blade | Dokumen WSDL XML (`.wsdl`) | OpenAPI 3.0 YAML (`openapi-sipus-v1.yaml`) |
| **Standar Format Error** | Redirect Session Flash Error | Blok `<soap:Fault>` (HTTP 500) | Standar IETF **RFC 7807** (`application/problem+json`) |

---

### 2.3 Alasan Teknis Pemilihan REST untuk Aplikasi Mobile / Kiosk Dibanding SOAP
1. **Efisiensi Bandwidth & Ukuran Payload:** Payload JSON pada REST 5–8 kali lebih ringan dibanding amplop XML SOAP, sangat krusial untuk perangkat mobile mahasiswa yang menggunakan koneksi seluler.
2. **Kemudahan Parsing di Sisi Klien:** JSON adalah format native pada ekosistem Flutter, React Native, Kotlin, maupun Swift tanpa perlu library parsing XML yang rumit.
3. **Stateless Scalability:** Autentikasi token Sanctum tidak membebani memori sesi di server, sehingga server backend SIPUS-Del dapat diskalakan horizontal di balik Load Balancer tanpa khawatir sesi pengguna terputus.
4. **Semantik HTTP Alami:** Operasi CRUD langsung memanfaatkan kata kerja resmi protokol HTTP (GET, POST, PUT, PATCH, DELETE) dengan kode status yang jujur (200, 201, 204, 401, 404, 409, 422).

---

## III. Tabel Katalog Endpoint Web Services (SOAP & REST API v1)

| No | Protokol | Method | Endpoint URL / Path | Proteksi Auth | Deskripsi Fungsional | Status Sukses | Status Error |
| :---: | :---: | :---: | :--- | :---: | :--- | :---: | :---: |
| 1 | SOAP | `GET` | `/soap/sipus.wsdl` | Publik | Dokumen kontrak WSDL XML operasi `CheckClearanceStatus` | 200 OK | 500 |
| 2 | SOAP | `POST` | `/soap/sipus` | Publik | Cek tanggungan peminjaman aktif yudisium via XML envelope | 200 OK | 500 (`<soap:Fault>`) |
| 3 | REST | `POST` | `/api/v1/auth/login` | Publik | Verifikasi email pustakawan & terbitkan token Bearer Sanctum | 200 OK | 401, 422 |
| 4 | REST | `GET` | `/api/v1/auth/me` | Bearer Token | Detail profil pustakawan pemilik token aktif | 200 OK | 401 |
| 5 | REST | `POST` | `/api/v1/auth/logout` | Bearer Token | Pencabutan (revoke) token aktif dan pengakhiran sesi API | 200 OK | 401 |
| 6 | REST | `GET` | `/api/v1/bukus` | Publik | Katalog buku terpaginasi + filter pencarian, kategori, ketersediaan, & sorting | 200 OK | - |
| 7 | REST | `GET` | `/api/v1/bukus/{id}` | Publik | Detail buku lengkap beserta kategori, riwayat peminjaman, & HATEOAS level 3 | 200 OK | 404 |
| 8 | REST | `GET` | `/api/v1/bukus/{id}/referensi-eksternal` | Publik | REST Consumer ke OpenLibrary API dengan timeout 8s & graceful fallback | 200 OK | 404, 502 |
| 9 | REST | `POST` | `/api/v1/bukus` | Bearer Token | Tambah buku baru ke katalog (mereturn header `Location`) | 201 Created | 401, 422 |
| 10 | REST | `PUT` | `/api/v1/bukus/{id}` | Bearer Token | Penggantian representasi buku secara utuh (Full Replacement) | 200 OK | 401, 404, 422 |
| 11 | REST | `PATCH` | `/api/v1/bukus/{id}` | Bearer Token | Modifikasi sebagian atribut buku (Partial Update `sometimes`) | 200 OK | 400, 401, 404, 422 |
| 12 | REST | `DELETE`| `/api/v1/bukus/{id}` | Bearer Token | Hapus buku (ditolak 409 jika memiliki pinjaman aktif) | 204 No Content | 401, 404, 409 |
| 13 | REST | `GET` | `/api/v1/peminjamans` | Bearer Token | Riwayat peminjaman buku terpaginasi dengan filter `status` dan `nim` | 200 OK | 401 |
| 14 | REST | `POST` | `/api/v1/peminjamans` | Bearer Token | Transaksi peminjaman buku atomik (`DB::transaction` + `lockForUpdate`) | 201 Created | 401, 409, 422 |
| 15 | REST | `PATCH`| `/api/v1/peminjamans/{id}/kembalikan`| Bearer Token | Pengembalian buku atomik (`DB::transaction` + `lockForUpdate`), stok +1 | 200 OK | 401, 404, 409 |

---

## IV. Fitur Unggulan Arsitektur API

### 4.1 Hypermedia as the Engine of Application State (HATEOAS Level 3)
Pada `BukuResource` dan `PeminjamanResource`, setiap objek JSON menyertakan blok kontrol hypermedia `_links` yang bersifat kontekstual:
* **Pada Koleksi Buku:** Tautan aksi `_links.pinjam_buku` (`POST /api/v1/peminjamans`) **hanya muncul jika stok buku > 0**. Jika stok habis (0), tautan tersebut otomatis hilang sehingga aplikasi Mobile/Kiosk dapat langsung menonaktifkan tombol pinjam tanpa perlu menduplikasi logika bisnis di sisi klien.
* **Pada Riwayat Peminjaman:** Tautan aksi `_links.kembalikan_buku` (`PATCH /api/v1/peminjamans/{id}/kembalikan`) **hanya muncul jika status transaksi masih `'dipinjam'`**. Jika buku sudah dikembalikan, tautan tersebut otomatis hilang.

### 4.2 Standar Penanganan Error IETF RFC 7807 (Problem Details)
Seluruh respons kesalahan pada rute `/api/*` (400, 401, 404, 409, 422, 502) ditangani secara seragam melalui `bootstrap/app.php` dan `ApiResponse` trait dengan header `Content-Type: application/problem+json`:
```json
{
  "type": "http://localhost:8000/docs/errors/validation-failed",
  "title": "Unprocessable Content",
  "status": 422,
  "detail": "Satu atau lebih atribut input yang dikirim tidak memenuhi aturan validasi.",
  "instance": "/api/v1/bukus",
  "errors": {
    "isbn": ["The isbn field is required."],
    "judul": ["The judul field must be at least 3 characters."]
  }
}
```

### 4.3 Jaminan Transaksi Sirkulasi ACID & Pessimistic Locking
Transaksi sirkulasi peminjaman dan pengembalian dibungkus di dalam `DB::transaction()` dengan `Buku::where('id', $id)->lockForUpdate()->first()`:
* Menghindarkan kondisi *Race Condition* pada stok buku bernilai 1 jika dua peminjam memanggil API pada milidetik yang sama.
* Mencegah peminjaman ganda (*duplicate active loan*) oleh NIM yang sama untuk buku yang belum dikembalikan (merespons `409 Conflict`).
* Membatalkan transaksi dan merespons `422 Unprocessable Content` jika stok buku telah habis (0).

---

## V. Panduan Instalasi & Pengujian Cepat

### 5.1 Menjalankan Server Lokal
1. **Clone & Masuk ke Folder Proyek:**
   ```bash
   git clone https://github.com/NikahPanjaitan/ppw-2026-week5-12S24041.git sipus-del
   cd sipus-del
   git checkout week9-rest-api
   ```
2. **Install Dependensi & Siapkan Database:**
   ```bash
   composer install
   php artisan migrate:fresh --seed
   ```
3. **Jalankan Server Laravel:**
   ```bash
   php artisan serve
   ```
   Server aktif pada `http://127.0.0.1:8000`.

### 5.2 Akun Uji Pustakawan (Seeder)
* **Email:** `pustakawan@del.ac.id`
* **Password:** `password123`

### 5.3 Cara Mengimpor Koleksi Postman
1. Buka aplikasi **Postman Desktop**.
2. Klik tombol **Import** di pojok kiri atas.
3. Pilih berkas `docs/SIPUS-Del-API-v1.postman_collection.json`.
4. Koleksi akan memuat 5 folder pengujian lengkap dengan variabel `{{base_url}}` dan `{{token}}`. Jalankan request `2.1 POST Login Pustakawan Sukses` terlebih dahulu; script pengujian akan otomatis menyimpan Bearer Token ke variabel koleksi!

### 5.4 Spesifikasi Kontrak OpenAPI 3.0
Berkas kontrak OpenAPI 3.0 tersimpan di `docs/openapi-sipus-v1.yaml` dan dapat langsung diimpor ke [Swagger Editor](https://editor.swagger.io) untuk melihat dokumentasi interaktif.

---

## VI. Ringkasan Bukti Eksekusi Pengujian (Terminal & Postman)

### 1. Bukti Pengujian SOAP Clearance Yudisium
* **GET `/soap/sipus.wsdl`:** Berhasil menyajikan dokumen kontrak WSDL XML dengan header `Content-Type: text/xml; charset=utf-8` (HTTP 200 OK).
* **POST `/soap/sipus` (NIM `12S22001` - Bona Pandiangan):** Mengembalikan XML `<is_cleared>false</is_cleared>` dan `<active_loans>1</active_loans>` karena mahasiswa masih meminjam buku.
* **POST `/soap/sipus` (NIM `12S24041` - Nikah Suchia):** Mengembalikan XML `<is_cleared>true</is_cleared>` dan `<active_loans>0</active_loans>` karena bebas pinjaman buku.
* **POST `/soap/sipus` (NIM Kosong):** Mengembalikan status HTTP 500 dengan blok `<soap:Fault>` berisi `<faultcode>soap:Client</faultcode>`.

### 2. Bukti Pengujian Otentikasi Sanctum & RFC 7807
* **POST `/api/v1/auth/login`:** Berhasil menerbitkan `access_token` Bearer (`1|...`).
* **GET `/api/v1/auth/me` (Dengan Token):** Mengembalikan HTTP 200 OK data profil pustakawan.
* **GET `/api/v1/auth/me` (Tanpa Token):** Ditolak dengan HTTP 401 Unauthorized dalam format RFC 7807 (`type: ".../errors/unauthenticated"`).

### 3. Bukti Pengujian Katalog & Semantik Mutasi
* **GET `/api/v1/bukus?per_page=5`:** Mengembalikan koleksi terpaginasi lengkap dengan metadata `current_page`, `total_items`, dan tautan navigasi.
* **HATEOAS Buku Stok Habis (ID 3):** Tautan `pinjam_buku` otomatis tersembunyi pada respons JSON.
* **POST `/api/v1/bukus`:** Mengembalikan HTTP 201 Created dan header `Location: http://127.0.0.1:8000/api/v1/bukus/{id}`.
* **PUT vs PATCH `/api/v1/bukus/{id}`:** PUT menuntut seluruh kolom dikirim ulang; PATCH mengizinkan pengubahan parsial hanya pada field `{"stok": 15}`.

### 4. Bukti Transaksi Sirkulasi ACID & Pessimistic Locking
* **POST `/api/v1/peminjamans`:** Peminjaman buku berhasil dicatat atomik dengan pemotongan stok (-1), merespons HTTP 201 Created dan header Location.
* **POST `/api/v1/peminjamans` (Stok 0):** Ditolak dengan status HTTP 422 Unprocessable Content (`"detail": "Maaf, stok untuk buku '...' sedang habis (0 tersisa)."`).
* **POST `/api/v1/peminjamans` (Pinjaman Duplikat):** Ditolak dengan status HTTP 409 Conflict.
* **PATCH `/api/v1/peminjamans/{id}/kembalikan`:** Mengembalikan buku secara atomik, mengubah status transaksi menjadi `'dikembalikan'`, dan menambah stok buku (+1).
* **PATCH Pengembalian Berulang:** Ditolak dengan status HTTP 409 Conflict (`"title": "Conflict — Book Already Returned"`).

### 5. Bukti Konsumen REST OpenLibrary
* **GET `/api/v1/bukus/1/referensi-eksternal`:** Berhasil memanggil OpenLibrary API dengan timeout 8 detik dan menyediakan penanganan fallback yang anggun tanpa mengganggu katalog lokal.

---
*Proyek diselesaikan untuk memenuhi kriteria evaluasi resmi Modul Praktikum Minggu 09 (12S3101 - Pemrograman dan Pengujian Aplikasi Web).*
