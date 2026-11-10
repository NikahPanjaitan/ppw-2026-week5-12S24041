<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// Pengujian Otomatis RESTful API & Layanan SOAP Minggu 9 (SIPUS-Del)
class SipusDelApiTest extends TestCase
{
    use RefreshDatabase;

    private User $pustakawan;
    private Kategori $kategori;
    private Buku $bukuStokTersedia;
    private Buku $bukuStokNol;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pustakawan = User::create([
            'name'     => 'Pustakawan Del',
            'email'    => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);

        $this->kategori = Kategori::create([
            'kode_kategori' => 'KTG-TEST',
            'nama_kategori' => 'Kategori Testing',
        ]);

        $this->bukuStokTersedia = Buku::create([
            'isbn'         => '978-602-001-999-1',
            'judul'        => 'Buku Testing Stok Ada',
            'penulis'      => 'Penulis Test',
            'penerbit'     => 'Del Press',
            'tahun_terbit' => 2024,
            'kategori_id'  => $this->kategori->id,
            'stok'         => 3,
            'sinopsis'     => 'Sinopsis testing buku tersedia.',
        ]);

        $this->bukuStokNol = Buku::create([
            'isbn'         => '978-602-001-999-0',
            'judul'        => 'Buku Testing Stok Habis',
            'penulis'      => 'Penulis Test',
            'penerbit'     => 'Del Press',
            'tahun_terbit' => 2024,
            'kategori_id'  => $this->kategori->id,
            'stok'         => 0,
            'sinopsis'     => 'Sinopsis testing buku habis.',
        ]);
    }

    // 1. Uji Kontrak WSDL SOAP
    public function test_soap_wsdl_returns_xml_contract(): void
    {
        $response = $this->get('/soap/sipus.wsdl');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');
        $this->assertStringContainsString('LibraryClearanceService', $response->getContent());
        $this->assertStringContainsString('CheckClearanceStatus', $response->getContent());
    }

    // 2. Uji SOAP Mahasiswa Ada Pinjaman Aktif (is_cleared = false)
    public function test_soap_clearance_with_active_loans_returns_cleared_false(): void
    {
        Peminjaman::create([
            'buku_id'        => $this->bukuStokTersedia->id,
            'nim_peminjam'   => '12S22001',
            'nama_peminjam'  => 'Bona Pandiangan',
            'tanggal_pinjam' => now()->toDateString(),
            'status'         => 'dipinjam',
        ]);

        $xmlReq = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:m="http://sipus.del.ac.id/soap/clearance">
    <soap:Body>
        <m:CheckClearanceStatusRequest>
            <m:nim_peminjam>12S22001</m:nim_peminjam>
        </m:CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], $xmlReq);

        $response->assertStatus(200);
        $this->assertStringContainsString('<m:is_cleared>false</m:is_cleared>', $response->getContent());
        $this->assertStringContainsString('<m:active_loans>1</m:active_loans>', $response->getContent());
    }

    // 3. Uji SOAP Mahasiswa Bebas Pinjaman (is_cleared = true)
    public function test_soap_clearance_without_loans_returns_cleared_true(): void
    {
        $xmlReq = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:m="http://sipus.del.ac.id/soap/clearance">
    <soap:Body>
        <m:CheckClearanceStatusRequest>
            <m:nim_peminjam>12S24041</m:nim_peminjam>
        </m:CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], $xmlReq);

        $response->assertStatus(200);
        $this->assertStringContainsString('<m:is_cleared>true</m:is_cleared>', $response->getContent());
        $this->assertStringContainsString('<m:active_loans>0</m:active_loans>', $response->getContent());
    }

    // 4. Uji SOAP Fault saat NIM kosong (500 soap:Fault)
    public function test_soap_clearance_with_empty_nim_returns_soap_fault(): void
    {
        $xmlReq = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <m:CheckClearanceStatusRequest xmlns:m="http://sipus.del.ac.id/soap/clearance">
            <m:nim_peminjam></m:nim_peminjam>
        </m:CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], $xmlReq);

        $response->assertStatus(500);
        $this->assertStringContainsString('<soap:Fault>', $response->getContent());
        $this->assertStringContainsString('soap:Client', $response->getContent());
    }

    // 5. Uji Login Berhasil Menerbitkan Sanctum Token
    public function test_sanctum_login_success_and_returns_bearer_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'pustakawan@del.ac.id',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['token_type', 'access_token', 'user' => ['id', 'email']],
            ]);
    }

    // 6. Uji Login Kredensial Salah Mengembalikan 401 RFC 7807
    public function test_sanctum_login_invalid_credentials_returns_401_rfc7807(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'pustakawan@del.ac.id',
            'password' => 'passwordsalah',
        ]);

        $response->assertStatus(401)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('title', 'Invalid Credentials');
    }

    // 7. Uji Katalog Buku Terpaginasi
    public function test_buku_index_api_returns_paginated_json(): void
    {
        $response = $this->getJson('/api/v1/bukus?per_page=1');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'total_items', 'links'],
            ]);
    }

    // 8. Uji HATEOAS Level 3: pinjam_buku muncul saat stok > 0, hilang saat stok = 0
    public function test_buku_show_hateoas_shows_pinjam_when_stock_available_and_hides_when_zero(): void
    {
        // Buku stok > 0
        $resAda = $this->getJson("/api/v1/bukus/{$this->bukuStokTersedia->id}");
        $resAda->assertStatus(200)
            ->assertJsonPath('data.status_ketersediaan', 'TERSEDIA');
        $this->assertArrayHasKey('pinjam_buku', $resAda->json('data._links'));

        // Buku stok = 0
        $resNol = $this->getJson("/api/v1/bukus/{$this->bukuStokNol->id}");
        $resNol->assertStatus(200)
            ->assertJsonPath('data.status_ketersediaan', 'HABIS');
        $this->assertArrayNotHasKey('pinjam_buku', $resNol->json('data._links'));
    }

    // 9. Uji Detail Buku 404 RFC 7807
    public function test_buku_not_found_returns_404_rfc7807(): void
    {
        $response = $this->getJson('/api/v1/bukus/99999');

        $response->assertStatus(404)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('title', 'Resource Not Found');
    }

    // 10. Uji Transaksi ACID Peminjaman Berhasil (-1 stok, 201 Created + Location)
    public function test_peminjaman_acid_store_decrements_stock_and_returns_201(): void
    {
        $token = $this->pustakawan->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id'       => $this->bukuStokTersedia->id,
                'nim_peminjam'  => '12S24041',
                'nama_peminjam' => 'Nikah Suchia',
            ]);

        $response->assertStatus(201)
            ->assertHeader('Location');

        // Verifikasi atomik stok berkurang dari 3 menjadi 2
        $this->assertEquals(2, $this->bukuStokTersedia->fresh()->stok);
    }

    // 11. Uji Peminjaman Ditolak saat Stok Habis (422 RFC 7807)
    public function test_peminjaman_fails_when_stock_exhausted_returns_422_rfc7807(): void
    {
        $token = $this->pustakawan->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id'       => $this->bukuStokNol->id,
                'nim_peminjam'  => '12S24041',
                'nama_peminjam' => 'Nikah Suchia',
            ]);

        $response->assertStatus(422)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('title', 'Book Stock Exhausted');
    }

    // 12. Uji Peminjaman Ditolak saat Masih Meminjam Buku yang Sama (409 Conflict)
    public function test_peminjaman_fails_when_duplicate_active_loan_returns_409(): void
    {
        $token = $this->pustakawan->createToken('test')->plainTextToken;

        Peminjaman::create([
            'buku_id'        => $this->bukuStokTersedia->id,
            'nim_peminjam'   => '12S24041',
            'nama_peminjam'  => 'Nikah Suchia',
            'tanggal_pinjam' => now()->toDateString(),
            'status'         => 'dipinjam',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id'       => $this->bukuStokTersedia->id,
                'nim_peminjam'  => '12S24041',
                'nama_peminjam' => 'Nikah Suchia',
            ]);

        $response->assertStatus(409)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('title', 'Duplicate Active Loan Conflict');
    }

    // 13. Uji Pengembalian Buku Atomik (+1 stok, 200 OK)
    public function test_peminjaman_return_increments_stock_and_returns_200(): void
    {
        $token = $this->pustakawan->createToken('test')->plainTextToken;

        $pinjam = Peminjaman::create([
            'buku_id'        => $this->bukuStokTersedia->id,
            'nim_peminjam'   => '12S24041',
            'nama_peminjam'  => 'Nikah Suchia',
            'tanggal_pinjam' => now()->toDateString(),
            'status'         => 'dipinjam',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/peminjamans/{$pinjam->id}/kembalikan");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'dikembalikan');

        // Stok bertambah kembali dari 3 menjadi 4
        $this->assertEquals(4, $this->bukuStokTersedia->fresh()->stok);
    }

    // 14. Uji Pengembalian Berulang Ditolak (409 Conflict)
    public function test_peminjaman_double_return_returns_409_conflict(): void
    {
        $token = $this->pustakawan->createToken('test')->plainTextToken;

        $pinjam = Peminjaman::create([
            'buku_id'         => $this->bukuStokTersedia->id,
            'nim_peminjam'    => '12S24041',
            'nama_peminjam'   => 'Nikah Suchia',
            'tanggal_pinjam'  => now()->toDateString(),
            'tanggal_kembali' => now()->toDateString(),
            'status'          => 'dikembalikan',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/peminjamans/{$pinjam->id}/kembalikan");

        $response->assertStatus(409)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('title', 'Conflict — Book Already Returned');
    }

    // 15. Uji Consumer OpenLibrary Fallback Aman
    public function test_openlibrary_consumer_returns_graceful_response(): void
    {
        $response = $this->getJson("/api/v1/bukus/{$this->bukuStokTersedia->id}/referensi-eksternal");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
