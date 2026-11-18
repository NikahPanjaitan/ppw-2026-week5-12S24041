<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SipusDelWeek6Test extends TestCase
{
    use RefreshDatabase;

    private User $librarian;
    private Kategori $kategori;
    private Buku $bukuNormal;
    private Buku $bukuStokNol;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Akun Pustakawan
        $this->librarian = User::create([
            'name'     => 'Pustakawan Del',
            'email'    => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);

        // 2. Data Kategori
        $this->kategori = Kategori::create([
            'kode_kategori' => 'KTG-RPL',
            'nama_kategori' => 'Rekayasa Perangkat Lunak',
        ]);

        // 3. Buku dengan stok normal (5 eks)
        $this->bukuNormal = Buku::create([
            'isbn'         => '978-602-001-111-1',
            'judul'        => 'Buku Rekayasa Web Modern',
            'penulis'      => 'Chandro Pardede',
            'penerbit'     => 'Del Press',
            'tahun_terbit' => 2024,
            'kategori_id'  => $this->kategori->id,
            'stok'         => 5,
            'sinopsis'     => 'Buku praktikum pemrograman web.',
        ]);

        // 4. Buku dengan stok habis (0 eks)
        $this->bukuStokNol = Buku::create([
            'isbn'         => '978-602-001-000-0',
            'judul'        => 'Buku Basis Data Stok Nol',
            'penulis'      => 'Penulis Uji',
            'penerbit'     => 'Del Press',
            'tahun_terbit' => 2024,
            'kategori_id'  => $this->kategori->id,
            'stok'         => 0,
            'sinopsis'     => 'Buku uji penolakan peminjaman saat stok 0.',
        ]);
    }

    /**
     * Test 1: Tamu (publik) dapat melihat katalog dan metrik statistik Query Builder.
     */
    public function test_guest_can_view_book_catalog_and_analytics_dashboard(): void
    {
        $response = $this->get(route('buku.index'));

        $response->assertStatus(200);
        $response->assertSee('Buku Rekayasa Web Modern');
        $response->assertSee('Stok Tersedia');
        $response->assertSee('Sedang Dipinjam');
        $response->assertSee('Total Dikembalikan');
    }

    /**
     * Test 2: Tamu tidak dapat mengakses rute mutasi sirkulasi dan diarahkan ke login.
     */
    public function test_guest_is_redirected_to_login_when_accessing_protected_routes(): void
    {
        $response = $this->get(route('peminjaman.index'));
        $response->assertRedirect(route('login'));

        $createResponse = $this->get(route('peminjaman.create'));
        $createResponse->assertRedirect(route('login'));

        $storeResponse = $this->post(route('peminjaman.store'), []);
        $storeResponse->assertRedirect(route('login'));
    }

    /**
     * Test 3: Pustakawan dapat login dengan kredensial valid, session diregenerasi, dan cookie dipasang.
     */
    public function test_librarian_can_login_with_valid_credentials_and_regenerates_session(): void
    {
        $response = $this->post(route('login.post'), [
            'email'    => 'pustakawan@del.ac.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('buku.index'));
        $response->assertSessionHas('success');
        $response->assertCookie('del_last_librarian_email', 'pustakawan@del.ac.id');
        $this->assertAuthenticatedAs($this->librarian);
    }

    /**
     * Test 4: Login gagal jika kata sandi salah.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->from(route('login'))->post(route('login.post'), [
            'email'    => 'pustakawan@del.ac.id',
            'password' => 'passwordsalah',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    /**
     * Test 5: Transaksi ACID Pinjam Buku berhasil mengurangi stok fisik secara atomik.
     */
    public function test_successful_book_borrowing_decrements_stock_atomically(): void
    {
        $this->actingAs($this->librarian);

        $initialStock = $this->bukuNormal->stok; // 5

        $response = $this->post(route('peminjaman.store'), [
            'buku_id'       => $this->bukuNormal->id,
            'nim_peminjam'  => '12S24041',
            'nama_peminjam' => 'Nikah Suchia Panjaitan',
        ]);

        $response->assertRedirect(route('peminjaman.index'));
        $response->assertSessionHas('success');

        // Verifikasi record di database
        $this->assertDatabaseHas('peminjamans', [
            'buku_id'      => $this->bukuNormal->id,
            'nim_peminjam' => '12S24041',
            'status'       => 'dipinjam',
        ]);

        // Verifikasi mutasi stok berkurang tepat 1
        $this->assertEquals($initialStock - 1, $this->bukuNormal->fresh()->stok);
    }

    /**
     * Test 6: Transaksi ditolak dan di-rollback jika stok buku bernilai 0.
     */
    public function test_borrowing_fails_and_rolls_back_when_book_stock_is_zero(): void
    {
        $this->actingAs($this->librarian);

        $response = $this->from(route('peminjaman.create'))->post(route('peminjaman.store'), [
            'buku_id'       => $this->bukuStokNol->id,
            'nim_peminjam'  => '12S24041',
            'nama_peminjam' => 'Nikah Suchia Panjaitan',
        ]);

        $response->assertRedirect(route('peminjaman.create'));
        $response->assertSessionHas('error');

        // Pastikan tidak ada data transaksi peminjaman tersimpan
        $this->assertDatabaseMissing('peminjamans', [
            'buku_id'      => $this->bukuStokNol->id,
            'nim_peminjam' => '12S24041',
        ]);

        // Stok tetap 0
        $this->assertEquals(0, $this->bukuStokNol->fresh()->stok);
    }

    /**
     * Test 7: Transaksi ditolak jika mahasiswa dengan NIM yang sama meminjam buku yang sama yang belum dikembalikan.
     */
    public function test_borrowing_fails_when_same_nim_has_not_returned_same_book(): void
    {
        $this->actingAs($this->librarian);

        // Pinjaman pertama berhasil
        $this->post(route('peminjaman.store'), [
            'buku_id'       => $this->bukuNormal->id,
            'nim_peminjam'  => '12S24041',
            'nama_peminjam' => 'Nikah Suchia Panjaitan',
        ]);

        // Pinjaman kedua untuk buku yang sama oleh NIM yang sama
        $response = $this->from(route('peminjaman.create'))->post(route('peminjaman.store'), [
            'buku_id'       => $this->bukuNormal->id,
            'nim_peminjam'  => '12S24041',
            'nama_peminjam' => 'Nikah Suchia Panjaitan',
        ]);

        $response->assertRedirect(route('peminjaman.create'));
        $response->assertSessionHas('error');

        // Total transaksi untuk NIM ini pada buku tersebut harus tetap 1
        $this->assertEquals(1, Peminjaman::where('buku_id', $this->bukuNormal->id)->where('nim_peminjam', '12S24041')->count());

        // Stok buku hanya berkurang 1 (dari 5 menjadi 4)
        $this->assertEquals(4, $this->bukuNormal->fresh()->stok);
    }

    /**
     * Test 8: Transaksi Pengembalian Buku berhasil memperbarui status dan memulihkan stok buku.
     */
    public function test_successful_book_return_increments_stock_atomically(): void
    {
        $this->actingAs($this->librarian);

        // Pinjam buku dulu (stok 5 -> 4)
        $this->post(route('peminjaman.store'), [
            'buku_id'       => $this->bukuNormal->id,
            'nim_peminjam'  => '12S24041',
            'nama_peminjam' => 'Nikah Suchia Panjaitan',
        ]);

        $peminjaman = Peminjaman::where('nim_peminjam', '12S24041')->first();
        $this->assertEquals('dipinjam', $peminjaman->status);
        $this->assertEquals(4, $this->bukuNormal->fresh()->stok);

        // Eksekusi pengembalian buku
        $response = $this->post(route('peminjaman.kembalikan', $peminjaman));

        $response->assertSessionHas('success');

        // Status berubah menjadi dikembalikan
        $freshPeminjaman = $peminjaman->fresh();
        $this->assertEquals('dikembalikan', $freshPeminjaman->status);
        $this->assertNotNull($freshPeminjaman->tanggal_kembali);

        // Stok pulih kembali menjadi 5
        $this->assertEquals(5, $this->bukuNormal->fresh()->stok);
    }

    /**
     * Test 9: Pencarian dinamis when() dan withCount('peminjamans') pada katalog.
     */
    public function test_dynamic_search_and_with_count_on_catalog(): void
    {
        $response = $this->get(route('buku.index', ['search' => 'Basis Data Stok Nol']));

        $response->assertStatus(200);
        $response->assertSee('Buku Basis Data Stok Nol');
        $response->assertDontSee('Buku Rekayasa Web Modern');
    }

    /**
     * Test 10: Logout pustakawan menginvalidasi sesi server.
     */
    public function test_librarian_logout_invalidates_session(): void
    {
        $this->actingAs($this->librarian);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
