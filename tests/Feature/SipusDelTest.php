<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SipusDelTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_buku_index(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('buku.index'));
    }

    public function test_buku_index_renders_with_eager_loading(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'KTG-TEST',
            'nama_kategori' => 'Kategori Testing',
        ]);

        Buku::create([
            'isbn' => '978-000-111-222-3',
            'judul' => 'Judul Buku Testing 1',
            'penulis' => 'Penulis Testing',
            'penerbit' => 'Penerbit Testing',
            'tahun_terbit' => 2024,
            'kategori_id' => $kategori->id,
            'stok' => 10,
            'sinopsis' => 'Sinopsis testing buku.',
        ]);

        $response = $this->get(route('buku.index'));
        $response->assertStatus(200);
        $response->assertSee('Judul Buku Testing 1');
        $response->assertSee('Kategori Testing');
    }

    public function test_buku_store_validation_fails_on_invalid_data(): void
    {
        $response = $this->post(route('buku.store'), [
            'isbn' => '',
            'judul' => 'Abc', // min 5 failed
            'penulis' => '',
            'penerbit' => '',
            'tahun_terbit' => 1800, // min 1900 failed
            'kategori_id' => 9999, // exists failed
            'stok' => -5, // min 0 failed
        ]);

        $response->assertSessionHasErrors(['isbn', 'judul', 'penulis', 'penerbit', 'tahun_terbit', 'kategori_id', 'stok']);
    }

    public function test_buku_store_success_and_redirects(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'KTG-RPL',
            'nama_kategori' => 'Rekayasa Perangkat Lunak',
        ]);

        $response = $this->post(route('buku.store'), [
            'isbn' => '978-123-456-789-0',
            'judul' => 'Buku Baru Pemrograman Web',
            'penulis' => 'Dosen Del',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2024,
            'kategori_id' => $kategori->id,
            'stok' => 15,
            'sinopsis' => 'Sinopsis buku baru.',
        ]);

        $response->assertRedirect(route('buku.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bukus', [
            'isbn' => '978-123-456-789-0',
            'judul' => 'Buku Baru Pemrograman Web',
        ]);
    }

    public function test_buku_update_with_route_model_binding(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'KTG-01',
            'nama_kategori' => 'Sains Komputer',
        ]);

        $buku = Buku::create([
            'isbn' => '978-999-888-777-6',
            'judul' => 'Buku Sebelum Update',
            'penulis' => 'Penulis Lama',
            'penerbit' => 'Penerbit Lama',
            'tahun_terbit' => 2020,
            'kategori_id' => $kategori->id,
            'stok' => 5,
        ]);

        $response = $this->put(route('buku.update', $buku), [
            'isbn' => '978-999-888-777-6', // same ISBN should pass unique ignore
            'judul' => 'Buku Setelah Diupdate',
            'penulis' => 'Penulis Baru',
            'penerbit' => 'Penerbit Baru',
            'tahun_terbit' => 2023,
            'kategori_id' => $kategori->id,
            'stok' => 20,
            'sinopsis' => 'Deskripsi diperbarui',
        ]);

        $response->assertRedirect(route('buku.index'));
        $this->assertDatabaseHas('bukus', [
            'id' => $buku->id,
            'judul' => 'Buku Setelah Diupdate',
            'stok' => 20,
        ]);
    }

    public function test_buku_destroy(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'KTG-DEL',
            'nama_kategori' => 'Kategori Hapus',
        ]);

        $buku = Buku::create([
            'isbn' => '978-000-000-000-1',
            'judul' => 'Buku Yang Akan Dihapus',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2022,
            'kategori_id' => $kategori->id,
            'stok' => 2,
        ]);

        $response = $this->delete(route('buku.destroy', $buku));
        $response->assertRedirect(route('buku.index'));
        $this->assertDatabaseMissing('bukus', ['id' => $buku->id]);
    }

    public function test_kategori_crud(): void
    {
        $response = $this->post(route('kategori.store'), [
            'kode_kategori' => 'KTG-BARU',
            'nama_kategori' => 'Kategori Buku Baru',
        ]);
        $response->assertRedirect(route('kategori.index'));
        $this->assertDatabaseHas('kategoris', ['kode_kategori' => 'KTG-BARU']);

        $kategori = Kategori::where('kode_kategori', 'KTG-BARU')->first();

        $updateResponse = $this->put(route('kategori.update', $kategori), [
            'kode_kategori' => 'KTG-BARU',
            'nama_kategori' => 'Kategori Buku Baru Diubah',
        ]);
        $updateResponse->assertRedirect(route('kategori.index'));
        $this->assertDatabaseHas('kategoris', ['nama_kategori' => 'Kategori Buku Baru Diubah']);

        $deleteResponse = $this->delete(route('kategori.destroy', $kategori));
        $deleteResponse->assertRedirect(route('kategori.index'));
        $this->assertDatabaseMissing('kategoris', ['id' => $kategori->id]);
    }
}
