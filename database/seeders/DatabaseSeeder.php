<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Generate minimal 5 Kategori (dibuat 6 kategori relevan Kampus Del)
        $categoriesData = [
            ['kode_kategori' => 'KTG-001', 'nama_kategori' => 'Rekayasa Perangkat Lunak'],
            ['kode_kategori' => 'KTG-002', 'nama_kategori' => 'Sistem Informasi'],
            ['kode_kategori' => 'KTG-003', 'nama_kategori' => 'Kecerdasan Buatan & Data Science'],
            ['kode_kategori' => 'KTG-004', 'nama_kategori' => 'Jaringan & Keamanan Siber'],
            ['kode_kategori' => 'KTG-005', 'nama_kategori' => 'Matematika & Algoritma'],
            ['kode_kategori' => 'KTG-006', 'nama_kategori' => 'Basis Data & Komputasi Awan'],
        ];

        $kategoris = [];
        foreach ($categoriesData as $cat) {
            $kategoris[] = Kategori::firstOrCreate(
                ['kode_kategori' => $cat['kode_kategori']],
                $cat
            );
        }

        // 2. Generate minimal 20 Buku realistis (total 21 judul)
        $daftarBuku = [
            [
                'isbn' => '978-602-001-101-1',
                'judul' => 'Clean Code: Panduan Praktis Menulis Kode Berkualitas',
                'penulis' => 'Robert C. Martin',
                'penerbit' => 'Prentice Hall',
                'tahun_terbit' => 2018,
                'kategori_id' => $kategoris[0]->id,
                'stok' => 12,
                'sinopsis' => 'Panduan komprehensif tentang teknik penulisan kode yang bersih, mudah dirawat, dan terstruktur sesuai standar industri perangkat lunak modern.',
            ],
            [
                'isbn' => '978-602-001-102-2',
                'judul' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
                'penulis' => 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides',
                'penerbit' => 'Addison-Wesley',
                'tahun_terbit' => 2017,
                'kategori_id' => $kategoris[0]->id,
                'stok' => 8,
                'sinopsis' => 'Katalog pola desain berorientasi objek yang menjadi acuan klasik bagi insinyur perangkat lunak dalam memecahkan permasalahan arsitektur umum.',
            ],
            [
                'isbn' => '978-602-001-103-3',
                'judul' => 'Pemrograman Web Modern dengan Laravel 11',
                'penulis' => 'Chandro Pardede',
                'penerbit' => 'Del Press',
                'tahun_terbit' => 2024,
                'kategori_id' => $kategoris[0]->id,
                'stok' => 25,
                'sinopsis' => 'Buku ajar komprehensif mengenai arsitektur MVC, Eloquent ORM, validasi server-side, serta implementasi RESTful API menggunakan Laravel 11.',
            ],
            [
                'isbn' => '978-602-001-104-4',
                'judul' => 'Analisis dan Perancangan Sistem Informasi Berorientasi Objek',
                'penulis' => 'Alan Dennis, Barbara Haley Wixom, David Tegarden',
                'penerbit' => 'Wiley',
                'tahun_terbit' => 2020,
                'kategori_id' => $kategoris[1]->id,
                'stok' => 15,
                'sinopsis' => 'Metodologi analisis kebutuhan dan perancangan sistem informasi enterprise menggunakan Unified Modeling Language (UML).',
            ],
            [
                'isbn' => '978-602-001-105-5',
                'judul' => 'Enterprise Architecture Planning & Frameworks',
                'penulis' => 'Steven H. Spewak',
                'penerbit' => 'John Wiley & Sons',
                'tahun_terbit' => 2019,
                'kategori_id' => $kategoris[1]->id,
                'stok' => 7,
                'sinopsis' => 'Strategi pembangunan arsitektur data, aplikasi, dan infrastruktur teknologi organisasi untuk mendukung efektivitas bisnis jangka panjang.',
            ],
            [
                'isbn' => '978-602-001-106-6',
                'judul' => 'Hands-On Machine Learning dengan Scikit-Learn dan TensorFlow',
                'penulis' => 'Aurélien Géron',
                'penerbit' => "O'Reilly Media",
                'tahun_terbit' => 2022,
                'kategori_id' => $kategoris[2]->id,
                'stok' => 10,
                'sinopsis' => 'Konsep praktis machine learning mulai dari regresi linier, decision trees, ensemble methods, hingga deep neural network.',
            ],
            [
                'isbn' => '978-602-001-107-7',
                'judul' => 'Kecerdasan Buatan: Pendekatan Modern Edisi 4',
                'penulis' => 'Stuart Russell & Peter Norvig',
                'penerbit' => 'Pearson Education',
                'tahun_terbit' => 2021,
                'kategori_id' => $kategoris[2]->id,
                'stok' => 14,
                'sinopsis' => 'Referensi standar universitas dunia yang mengupas teori agen cerdas, algoritma pencarian, representasi pengetahuan, dan penalaran probabilistik.',
            ],
            [
                'isbn' => '978-602-001-108-8',
                'judul' => 'Computer Networking: A Top-Down Approach',
                'penulis' => 'James F. Kurose & Keith W. Ross',
                'penerbit' => 'Pearson',
                'tahun_terbit' => 2021,
                'kategori_id' => $kategoris[3]->id,
                'stok' => 18,
                'sinopsis' => 'Pendekatan top-down dalam mempelajari lapisan protokol jaringan internet: aplikasi, transport, network, link, dan physical layer.',
            ],
            [
                'isbn' => '978-602-001-109-9',
                'judul' => 'Kriptografi dan Keamanan Jaringan: Prinsip & Praktik',
                'penulis' => 'William Stallings',
                'penerbit' => 'Prentice Hall',
                'tahun_terbit' => 2020,
                'kategori_id' => $kategoris[3]->id,
                'stok' => 9,
                'sinopsis' => 'Eksplorasi algoritma enkripsi simetris dan asimetris, hash message, tanda tangan digital, protokol TLS/SSL, dan pencegahan ancaman keamanan.',
            ],
            [
                'isbn' => '978-602-001-110-0',
                'judul' => 'Introduction to Algorithms (CLRS)',
                'penulis' => 'Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest, Clifford Stein',
                'penerbit' => 'MIT Press',
                'tahun_terbit' => 2022,
                'kategori_id' => $kategoris[4]->id,
                'stok' => 20,
                'sinopsis' => 'Buku teks ensiklopedis mengenai analisis kompleksitas asimtotik algoritma, struktur data canggih, dan teknik optimasi dynamic programming.',
            ],
            [
                'isbn' => '978-602-001-111-1',
                'judul' => 'Matematika Diskrit dan Aplikasinya pada Ilmu Komputer',
                'penulis' => 'Kenneth H. Rosen',
                'penerbit' => 'McGraw-Hill',
                'tahun_terbit' => 2019,
                'kategori_id' => $kategoris[4]->id,
                'stok' => 16,
                'sinopsis' => 'Fondasi logika matematika, pembuktian formal, teori graf, relasi rekurensi, dan aljabar boolean untuk sains komputasi.',
            ],
            [
                'isbn' => '978-602-001-112-2',
                'judul' => 'Database System Concepts (Silberschatz)',
                'penulis' => 'Abraham Silberschatz, Henry F. Korth, S. Sudarshan',
                'penerbit' => 'McGraw-Hill',
                'tahun_terbit' => 2020,
                'kategori_id' => $kategoris[5]->id,
                'stok' => 11,
                'sinopsis' => 'Teori pemodelan basis data relasional, aljabar relasional, normalisasi 1NF-3NF/BCNF, manajemen transaksi ACID, dan indexing b-tree.',
            ],
            [
                'isbn' => '978-602-001-113-3',
                'judul' => 'High Performance MySQL: Optimization and Tuning',
                'penulis' => 'Silvia Botros & Jeremy Tinley',
                'penerbit' => "O'Reilly Media",
                'tahun_terbit' => 2021,
                'kategori_id' => $kategoris[5]->id,
                'stok' => 6,
                'sinopsis' => 'Panduan tingkat lanjut untuk penyetelan performa database relasional, desain skema efektif, dan mitigasi bottleneck kueri.',
            ],
            [
                'isbn' => '978-602-001-114-4',
                'judul' => 'Refactoring: Improving the Design of Existing Code',
                'penulis' => 'Martin Fowler',
                'penerbit' => 'Addison-Wesley',
                'tahun_terbit' => 2019,
                'kategori_id' => $kategoris[0]->id,
                'stok' => 14,
                'sinopsis' => 'Prinsip dan katalog restrukturisasi kode program yang mengalami code smells guna meningkatkan readability dan maintainability.',
            ],
            [
                'isbn' => '978-602-001-115-5',
                'judul' => 'Domain-Driven Design: Tackling Complexity in Software',
                'penulis' => 'Eric Evans',
                'penerbit' => 'Addison-Wesley',
                'tahun_terbit' => 2018,
                'kategori_id' => $kategoris[0]->id,
                'stok' => 5,
                'sinopsis' => 'Pendekatan pengembangan sistem berbasis model domain mendalam, ubiquitous language, serta pemisahan bounded contexts.',
            ],
            [
                'isbn' => '978-602-001-116-6',
                'judul' => 'Manajemen Proyek Teknologi Informasi Modern',
                'penulis' => 'Kathy Schwalbe',
                'penerbit' => 'Cengage Learning',
                'tahun_terbit' => 2021,
                'kategori_id' => $kategoris[1]->id,
                'stok' => 13,
                'sinopsis' => 'Metodologi pengelolaan proyek TI mulai dari inisiasi, estimasi waktu, manajemen risiko, hingga metodologi tangkas Scrum.',
            ],
            [
                'isbn' => '978-602-001-117-7',
                'judul' => 'Deep Learning dengan Python',
                'penulis' => 'François Chollet',
                'penerbit' => 'Manning Publications',
                'tahun_terbit' => 2021,
                'kategori_id' => $kategoris[2]->id,
                'stok' => 9,
                'sinopsis' => 'Eksplorasi arsitektur jaringan saraf tiruan convolutional, recurrent, dan transformers langsung dari perancang pustaka Keras.',
            ],
            [
                'isbn' => '978-602-001-118-8',
                'judul' => 'The Web Application Hacker\'s Handbook: Finding and Exploiting Security Flaws',
                'penulis' => 'Dafydd Stuttard & Marcus Pinto',
                'penerbit' => 'Wiley',
                'tahun_terbit' => 2019,
                'kategori_id' => $kategoris[3]->id,
                'stok' => 8,
                'sinopsis' => 'Teknik eksplorasi dan audit keamanan aplikasi web modern, pencegahan SQL Injection, Cross-Site Scripting, dan celah autentikasi.',
            ],
            [
                'isbn' => '978-602-001-119-9',
                'judul' => 'Data Structures and Algorithms in Java',
                'penulis' => 'Robert Lafore',
                'penerbit' => 'Sams Publishing',
                'tahun_terbit' => 2017,
                'kategori_id' => $kategoris[4]->id,
                'stok' => 15,
                'sinopsis' => 'Visualisasi dan implementasi array, linked list, stack, queue, binary search tree, hash tables, dan heap sorting.',
            ],
            [
                'isbn' => '978-602-001-120-0',
                'judul' => 'Cloud Native Architecture & Microservices with Kubernetes',
                'penulis' => 'Brendan Burns',
                'penerbit' => "O'Reilly Media",
                'tahun_terbit' => 2023,
                'kategori_id' => $kategoris[5]->id,
                'stok' => 10,
                'sinopsis' => 'Konsep perancangan sistem terdistribusi berskala besar berbasis kontainer Docker dan orkestrasi microservices menggunakan Kubernetes.',
            ],
            [
                'isbn' => '978-602-001-121-1',
                'judul' => 'User Story Mapping: Membangun Backlog Produk Agile',
                'penulis' => 'Jeff Patton',
                'penerbit' => "O'Reilly Media",
                'tahun_terbit' => 2020,
                'kategori_id' => $kategoris[1]->id,
                'stok' => 7,
                'sinopsis' => 'Panduan praktis perancangan backlog produk dan pemetaan pengalaman pengguna pada tim rekayasa perangkat lunak tangkas.',
            ],
        ];

        foreach ($daftarBuku as $bukuData) {
            Buku::firstOrCreate(
                ['isbn' => $bukuData['isbn']],
                $bukuData
            );
        }
    }
}
