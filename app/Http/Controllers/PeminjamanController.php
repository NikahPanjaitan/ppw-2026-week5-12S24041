<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PeminjamanController extends Controller
{
    /**
     * Tampilkan daftar riwayat sirkulasi peminjaman buku.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $peminjamans = Peminjaman::with('buku')
            ->when($status, function ($query, $status) {
                if (in_array($status, ['dipinjam', 'dikembalikan'])) {
                    $query->where('status', $status);
                }
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_pinjam', 'like', "%{$search}%")
                      ->orWhere('nim_peminjam', 'like', "%{$search}%")
                      ->orWhere('nama_peminjam', 'like', "%{$search}%")
                      ->orWhereHas('buku', function ($bukuQuery) use ($search) {
                          $bukuQuery->where('judul', 'like', "%{$search}%");
                      });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('peminjaman.index', compact('peminjamans', 'status', 'search'));
    }

    /**
     * Form Peminjaman Buku Baru
     */
    public function create(Request $request)
    {
        $bukuId = $request->query('buku_id');
        $selectedBuku = $bukuId ? Buku::find($bukuId) : null;
        $bukus = Buku::where('stok', '>', 0)->orderBy('judul')->get();

        return view('peminjaman.create', compact('bukus', 'selectedBuku'));
    }

    /**
     * Transaksi ACID Peminjaman Buku (DB::transaction & lockForUpdate).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'buku_id'       => 'required|exists:bukus,id',
            'nim_peminjam'  => 'required|string|min:6|max:15',
            'nama_peminjam' => 'required|string|min:3|max:150',
        ]);

        try {
            DB::transaction(function () use ($validated, &$peminjaman) {
                // 1. Pessimistic Locking: Kunci baris buku secara eksklusif (SELECT ... FOR UPDATE)
                $buku = Buku::where('id', $validated['buku_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // 2. Pemeriksaan Stok Fisik: Batalkan & rollback jika stok habis (stok = 0)
                if ($buku->stok < 1) {
                    throw new \RuntimeException("Buku '{$buku->judul}' tidak dapat dipinjam karena stok fisik saat ini habis (0)!");
                }

                // 3. Cegah Peminjaman Ganda: Satu NIM tidak boleh meminjam buku yang sama jika belum dikembalikan
                $activeLoan = Peminjaman::where('buku_id', $buku->id)
                    ->where('nim_peminjam', strtoupper(trim($validated['nim_peminjam'])))
                    ->where('status', 'dipinjam')
                    ->lockForUpdate()
                    ->exists();

                if ($activeLoan) {
                    throw new \RuntimeException("Mahasiswa dengan NIM {$validated['nim_peminjam']} masih meminjam buku ini dan belum mengembalikannya!");
                }

                // 4. Kurangi stok fisik buku secara atomik
                $buku->decrement('stok');

                // 5. Catat transaksi peminjaman ke tabel peminjamans
                $kodePinjam = 'PJM-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                $peminjaman = Peminjaman::create([
                    'kode_pinjam'         => $kodePinjam,
                    'buku_id'             => $buku->id,
                    'nim_peminjam'        => strtoupper(trim($validated['nim_peminjam'])),
                    'nama_peminjam'       => $validated['nama_peminjam'],
                    'tanggal_pinjam'      => now()->toDateString(),
                    'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
                    'status'              => 'dipinjam',
                ]);
            }, 3);

            return redirect()->route('peminjaman.index')
                ->with('success', "Transaksi peminjaman buku berhasil dicatat dengan kode: {$peminjaman->kode_pinjam}!");
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB TRANSACTION EXCEPTION - PINJAM]: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem database saat memproses transaksi peminjaman.');
        }
    }

    /**
     * Transaksi ACID Pengembalian Buku (DB::transaction & lockForUpdate).
     */
    public function kembalikan(Peminjaman $peminjaman)
    {
        try {
            DB::transaction(function () use ($peminjaman) {
                // 1. Kunci baris transaksi peminjaman (Pessimistic Locking)
                $lockedPeminjaman = Peminjaman::where('id', $peminjaman->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 2. Pastikan buku belum berstatus dikembalikan
                if ($lockedPeminjaman->status === 'dikembalikan') {
                    throw new \RuntimeException("Buku dengan kode {$lockedPeminjaman->kode_pinjam} sudah dikembalikan sebelumnya!");
                }

                // 3. Ubah status menjadi 'dikembalikan' dan isi tanggal_kembali
                $lockedPeminjaman->update([
                    'status'          => 'dikembalikan',
                    'tanggal_kembali' => now()->toDateString(),
                ]);

                // 4. Kunci dan kembalikan stok fisik buku (atomic increment)
                $buku = Buku::where('id', $lockedPeminjaman->buku_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $buku->increment('stok');
            }, 3);

            return redirect()->back()
                ->with('success', "Buku pada transaksi {$peminjaman->kode_pinjam} berhasil dikembalikan dan stok buku telah dipulihkan!");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB TRANSACTION EXCEPTION - KEMBALI]: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem basis data saat memproses pengembalian buku.');
        }
    }
}
