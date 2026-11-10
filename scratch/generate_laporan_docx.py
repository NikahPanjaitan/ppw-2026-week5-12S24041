import os
import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import nsdecls, qn

def set_cell_background(cell, fill_hex):
    tcPr = cell._tc.get_or_add_tcPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_hex}"/>')
    tcPr.append(shd)

def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = OxmlElement('w:tcMar')
    for m, val in [('top', top), ('bottom', bottom), ('left', left), ('right', right)]:
        node = OxmlElement(f'w:{m}')
        node.set(qn('w:w'), str(val))
        node.set(qn('w:type'), 'dxa')
        tcMar.append(node)
    tcPr.append(tcMar)

def add_code_block(doc, code_text):
    table = doc.add_table(rows=1, cols=1)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    table.autofit = False
    
    cell = table.cell(0, 0)
    cell.width = Inches(6.5)
    set_cell_background(cell, "F3F4F6")
    set_cell_margins(cell, top=120, bottom=120, left=200, right=200)
    
    p = cell.paragraphs[0]
    p.paragraph_format.space_before = Pt(4)
    p.paragraph_format.space_after = Pt(4)
    p.paragraph_format.line_spacing = 1.05
    run = p.add_run(code_text)
    run.font.name = 'Consolas'
    run.font.size = Pt(9.5)
    run.font.color.rgb = RGBColor(0x1F, 0x29, 0x37)
    
    # spacing after table
    sp = doc.add_paragraph()
    sp.paragraph_format.space_before = Pt(0)
    sp.paragraph_format.space_after = Pt(6)

def style_table(table, col_widths, headers, data):
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    hdr_cells = table.rows[0].cells
    for i, title in enumerate(headers):
        hdr_cells[i].text = title
        set_cell_background(hdr_cells[i], "4C1D95") # Del Purple
        set_cell_margins(hdr_cells[i], top=120, bottom=120, left=150, right=150)
        p = hdr_cells[i].paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        for r in p.runs:
            r.font.name = 'Times New Roman'
            r.font.size = Pt(10)
            r.font.bold = True
            r.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)

    for row_idx, row_data in enumerate(data):
        row_cells = table.add_row().cells
        bg_color = "F9FAFB" if row_idx % 2 == 1 else "FFFFFF"
        for col_idx, text in enumerate(row_data):
            row_cells[col_idx].text = str(text)
            set_cell_background(row_cells[col_idx], bg_color)
            set_cell_margins(row_cells[col_idx], top=100, bottom=100, left=150, right=150)
            p = row_cells[col_idx].paragraphs[0]
            p.alignment = WD_ALIGN_PARAGRAPH.LEFT if col_idx != 0 else WD_ALIGN_PARAGRAPH.CENTER
            for r in p.runs:
                r.font.name = 'Times New Roman'
                r.font.size = Pt(9.5)

    for row in table.rows:
        for idx, width in enumerate(col_widths):
            row.cells[idx].width = Inches(width)

def build_laporan():
    doc = docx.Document()
    
    # Page Setup Margins (Times New Roman Standard)
    sections = doc.sections
    for s in sections:
        s.top_margin = Inches(1.0)
        s.bottom_margin = Inches(1.0)
        s.left_margin = Inches(1.2)
        s.right_margin = Inches(1.0)

    # Base style
    style_normal = doc.styles['Normal']
    font = style_normal.font
    font.name = 'Times New Roman'
    font.size = Pt(11)
    font.color.rgb = RGBColor(0x11, 0x18, 0x27)

    # -------------------------------------------------------------
    # HALAMAN COVER
    # -------------------------------------------------------------
    p_inst = doc.add_paragraph()
    p_inst.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_inst1 = p_inst.add_run("INSTITUT TEKNOLOGI DEL\n")
    r_inst1.bold = True
    r_inst1.font.size = Pt(14)
    r_inst2 = p_inst.add_run("FAKULTAS INFORMATIKA DAN TEKNIK ELEKTRO\nPROGRAM STUDI SARJANA SISTEM INFORMASI\n")
    r_inst2.bold = True
    r_inst2.font.size = Pt(12)
    p_inst.paragraph_format.space_after = Pt(20)

    # Logo Del
    logo_path = r"c:\laragon\www\sipus-del\public\images\logo-del.png"
    if os.path.exists(logo_path):
        p_logo = doc.add_paragraph()
        p_logo.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p_logo.paragraph_format.space_after = Pt(24)
        run_logo = p_logo.add_run()
        run_logo.add_picture(logo_path, width=Inches(1.8))

    # Judul Laporan
    p_title = doc.add_paragraph()
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_t1 = p_title.add_run("LAPORAN PRAKTIKUM MINGGU 09\n")
    r_t1.bold = True
    r_t1.font.size = Pt(15)
    r_t1.font.color.rgb = RGBColor(0x4C, 0x1D, 0x95)

    r_t2 = p_title.add_run("ARSITEKTUR WEB SERVICES TERDISTRIBUSI:\nIMPLEMENTASI LAYANAN SOAP BEBAS PUSTAKA YUDISIUM,\nRESTFUL WEB API PROVIDER (HATEOAS, RFC 7807, TRANSAKSI ACID),\nDAN CONSUMER OPENLIBRARY PADA LARAVEL 11.X\n")
    r_t2.bold = True
    r_t2.font.size = Pt(13)

    r_t3 = p_title.add_run("Aplikasi: Sistem Informasi Perpustakaan Kampus Del (SIPUS-Del v1)\n")
    r_t3.italic = True
    r_t3.font.size = Pt(11)
    p_title.paragraph_format.space_after = Pt(36)

    # Identitas Mahasiswa
    p_id = doc.add_paragraph()
    p_id.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_id.paragraph_format.line_spacing = 1.3
    r_id_lbl = p_id.add_run("Disusun Oleh:\n")
    r_id_lbl.font.size = Pt(11)
    
    r_name = p_id.add_run("NIKAH SUCHIA PANJAITAN\n")
    r_name.bold = True
    r_name.font.size = Pt(12)
    
    r_nim = p_id.add_run("NIM: 12S24041\n\n")
    r_nim.bold = True
    r_nim.font.size = Pt(11)

    r_dos_lbl = p_id.add_run("Dosen Pengampu:\n")
    r_dos_lbl.font.size = Pt(11)
    r_dos = p_id.add_run("Chandro Pardede, S.Kom., M.Sc.\n")
    r_dos.bold = True
    r_dos.font.size = Pt(12)

    r_mk = p_id.add_run("Mata Kuliah: Pemrograman dan Pengujian Aplikasi Web (12S3101)\n")
    r_mk.font.size = Pt(11)
    p_id.paragraph_format.space_after = Pt(40)

    # Footer Kampus
    p_ft = doc.add_paragraph()
    p_ft.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_ft = p_ft.add_run("LAGUBOTI, TOBA\nTAHUN AKADEMIK 2026/2027")
    r_ft.bold = True
    r_ft.font.size = Pt(11)

    doc.add_page_break()

    # -------------------------------------------------------------
    # BAB I: PENDAHULUAN
    # -------------------------------------------------------------
    h1 = doc.add_heading("BAB I: PENDAHULUAN", level=1)
    h1.paragraph_format.space_before = Pt(12)
    h1.paragraph_format.space_after = Pt(10)
    for r in h1.runs:
        r.font.name = 'Times New Roman'
        r.font.color.rgb = RGBColor(0x4C, 0x1D, 0x95)

    doc.add_heading("1.1 Latar Belakang Masalah", level=2)
    p = doc.add_paragraph(
        "Pada paruh awal perkuliahan Pemrograman dan Pengujian Aplikasi Web (Minggu 1 hingga Minggu 6), "
        "pengembangan aplikasi terfokus pada model aplikasi monolitik berorientasi interaksi manusia "
        "(Human-to-Machine) yang mengandalkan Server-Side Rendering (SSR) via template engine Blade, "
        "di mana setiap request mengembalikan representasi visual HTML dan CSS. Namun, dalam ekosistem "
        "perguruan tinggi modern seperti Institut Teknologi Del, tidak ada satu aplikasi tunggal yang "
        "dapat menangani seluruh kebutuhan operasional secara mandiri (\"No Super-Enterprise, No Super-App\")."
    )
    p.paragraph_format.line_spacing = 1.25
    p.paragraph_format.space_after = Pt(6)

    p = doc.add_paragraph(
        "Unit Pelaksana Teknis (UPT) Perpustakaan IT Del mengelola sistem informasi inventaris buku (SIPUS-Del). "
        "Pada tahap perkembangannya saat ini, perpustakaan menghadapi kebutuhan integrasi antarsistem yang nyata: "
        "(1) Sistem Administrasi Yudisium kampus versi warisan (legacy) yang masih berbasis protokol SOAP membutuhkan "
        "layanan pengecekan tanggungan bebas pustaka mahasiswa secara otomatis; "
        "(2) Kebutuhan peluncuran aplikasi Kiosk Mandiri dan aplikasi Mobile Mahasiswa yang menuntut pertukaran data "
        "berorientasi mesin (Machine-to-Machine) yang ringan, cepat, dan stateless berbasis RESTful API; serta "
        "(3) Kebutuhan pengayaan metadata koleksi buku secara otomatis dari basis data publik internasional OpenLibrary API "
        "tanpa menduplikasi penyimpanan lokal."
    )
    p.paragraph_format.line_spacing = 1.25
    p.paragraph_format.space_after = Pt(8)

    doc.add_heading("1.2 Capaian Pembelajaran (Sub-CPMK) dan Indikator Terukur", level=2)
    p = doc.add_paragraph(
        "Praktikum Minggu ke-9 ini mengacu pada capaian pembelajaran Sub-CPMK 12-02-09-07: Mahasiswa mampu "
        "menganalisis kebutuhan dan kelayakan penerapan web service serta mengembangkan dan menggunakan "
        "web service berbasis REST dengan tepat. Indikator ketercapaian terukur meliputi:"
    )
    p.paragraph_format.line_spacing = 1.25
    p.paragraph_format.space_after = Pt(4)

    items = [
        "Kemampuan menganalisis kelayakan arsitektural (Feasibility) membedakan Web Monolitik, SOAP, dan REST API.",
        "Implementasi layanan SOAP XML ringkas (Kontrak WSDL, SOAP Envelope, dan penanganan kesalahan SOAP Fault).",
        "Implementasi RESTful API Provider terstruktur dengan mematuhi Richardson Maturity Model Level 3 (HATEOAS).",
        "Penerapan otentikasi stateless berbasis Bearer Token menggunakan Laravel Sanctum.",
        "Implementasi transaksi sirkulasi peminjaman dan pengembalian buku bergaransi ACID memanfaatkan DB::transaction() dan Pessimistic Locking lockForUpdate().",
        "Standarisasi respons kesalahan global API mengacu pada standar IETF RFC 7807 (Problem Details for HTTP APIs).",
        "Integrasi REST Service Consumer eksternal memanggil OpenLibrary API menggunakan fasad Http Laravel.",
        "Penyusunan kontrak formal OpenAPI 3.0 dan koleksi pengujian komprehensif Postman v2.1.0."
    ]
    for it in items:
        p_b = doc.add_paragraph(it, style='List Bullet')
        p_b.paragraph_format.space_after = Pt(3)
        p_b.paragraph_format.line_spacing = 1.15

    doc.add_page_break()

    # -------------------------------------------------------------
    # BAB II: LANDASAN TEORI
    # -------------------------------------------------------------
    h1 = doc.add_heading("BAB II: LANDASAN TEORI DAN KAJIAN TEKNIS", level=1)
    h1.paragraph_format.space_before = Pt(12)
    h1.paragraph_format.space_after = Pt(10)
    for r in h1.runs:
        r.font.name = 'Times New Roman'
        r.font.color.rgb = RGBColor(0x4C, 0x1D, 0x95)

    doc.add_heading("2.1 Arsitektur SOAP (Simple Object Access Protocol) & Kontrak WSDL", level=2)
    p = doc.add_paragraph(
        "SOAP adalah protokol komunikasi formal terstandarisasi W3C yang mewajibkan seluruh pesan dikemas dalam format XML. "
        "Struktur amplop SOAP terdiri dari tiga komponen utama: (1) <soap:Envelope> sebagai elemen akar; (2) <soap:Header> "
        "sebagai wadah metadata autentikasi dan transaksi opsional; serta (3) <soap:Body> yang memuat muatan data operasi. "
        "Apabila terjadi kegagalan pemrosesan, protokol SOAP secara seragam mengembalikan blok <soap:Fault> memuat <faultcode> "
        "dan <faultstring>. Kontrak layanan didefinisikan secara kaku dalam dokumen Web Services Description Language (WSDL) "
        "yang memetakan pesan input/output, antarmuka portType, aturan binding, dan alamat fisik service."
    )
    p.paragraph_format.line_spacing = 1.25

    doc.add_heading("2.2 Arsitektur REST, Segitiga REST, dan Richardson Maturity Model", level=2)
    p = doc.add_paragraph(
        "Diperkenalkan oleh Roy Fielding pada tahun 2000, Representational State Transfer (REST) adalah gaya arsitektur yang "
        "memanfaatkan semantik protokol HTTP secara murni. REST bertumpu pada Segitiga Elemen: (1) Nouns (Resources) berupa kata "
        "benda jamak seperti /bukus dan /peminjamans; (2) Verbs berupa metode HTTP terbatas yang standar (GET, POST, PUT, PATCH, DELETE); "
        "serta (3) Representations berupa format data ringkas dominan JSON (application/json). Tingkat kematangan implementasi REST "
        "dievaluasi melalui Richardson Maturity Model (RMM):"
    )
    p.paragraph_format.line_spacing = 1.25

    table_rmm = doc.add_table(rows=1, cols=4)
    rmm_headers = ["Tingkatan RMM", "Karakteristik Implementasi", "Perilaku Semantik", "Penerapan pada SIPUS-Del"]
    rmm_data = [
        ["Level 0", "The Swamp of POX", "Satu URI tunggal, metode POST untuk semua aksi", "Ditinggalkan (gaya RPC lama)"],
        ["Level 1", "Resources", "Pemecahan URI terpisah per entitas sumber daya", "URI /bukus dan /peminjamans"],
        ["Level 2", "HTTP Verbs & Status Codes", "Semantik GET, POST, PUT, PATCH, DELETE + Status 200, 201, 204, 401, 404, 409, 422", "Katalog & Transaksi Buku REST v1"],
        ["Level 3", "Hypermedia Controls (HATEOAS)", "JSON menyertakan panduan tautan dinamis (_links) sesuai state data", "BukuResource & PeminjamanResource"]
    ]
    style_table(table_rmm, [1.1, 1.8, 2.0, 1.6], rmm_headers, rmm_data)

    doc.add_heading("2.3 Semantik Mutasi Data: Perbedaan Konseptual PUT vs PATCH", level=2)
    p = doc.add_paragraph(
        "Salah satu prinsip krusial dalam semantik HTTP adalah perbedaan antara metode PUT dan PATCH:\n"
        "• PUT (Full Replacement): Bersifat idempoten. Klien wajib mengirimkan seluruh atribut representasi sumber daya secara lengkap. "
        "Jika ada field opsional yang tidak dikirimkan oleh klien, spesifikasi PUT murni mengharuskan server mereset atribut tersebut ke nilai kosong (null).\n"
        "• PATCH (Partial Update): Klien hanya mengirimkan satu atau beberapa field spesifik yang ingin diubah (misalnya hanya {\"stok\": 12}). "
        "Atribut lain yang tidak disertakan dalam body payload dijamin tetap aman dan tidak mengalami modifikasi."
    )
    p.paragraph_format.line_spacing = 1.25

    doc.add_heading("2.4 Transaksi Basis Data ACID dan Pessimistic Locking (lockForUpdate)", level=2)
    p = doc.add_paragraph(
        "Dalam sistem sirkulasi peminjaman buku perpustakaan, pengurangan stok dan pencatatan transaksi adalah dua operasi tulis yang "
        "harus dieksekusi secara atomik (Atomicity). Apabila stok buku tersisa 1 eksemplar, dan dua mahasiswa mengeksekusi peminjaman "
        "pada milidetik yang sama, ketiadaan mekanisme penguncian akan memicu Race Condition di mana stok dapat menjadi minus (-1). "
        "Laravel mengatasi hal ini melalui pembungkusan DB::transaction() yang dipadukan dengan klausa Pessimistic Locking "
        "lockForUpdate() (menghasilkan instruksi SQL SELECT ... FOR UPDATE), sehingga kueri transaksi kedua dipaksa mengantre "
        "hingga transaksi pertama selesai tuntas."
    )
    p.paragraph_format.line_spacing = 1.25

    doc.add_heading("2.5 Standar Error IETF RFC 7807 (Problem Details)", level=2)
    p = doc.add_paragraph(
        "Standar IETF RFC 7807 menetapkan format terstruktur untuk mengomunikasikan kesalahan HTTP pada API mesin-ke-mesin "
        "menggunakan media type application/problem+json. Objek JSON galat wajib memuat atribut: (1) type (URI identifikasi masalah); "
        "(2) title (ringkasan manusiawi); (3) status (kode status HTTP); (4) detail (penjelasan spesifik konteks kesalahan); "
        "serta (5) instance (URI endpoint tempat terjadinya error)."
    )
    p.paragraph_format.line_spacing = 1.25

    doc.add_page_break()

    # -------------------------------------------------------------
    # BAB III: ANALISIS KEBUTUHAN & DESAIN ARSITEKTUR
    # -------------------------------------------------------------
    h1 = doc.add_heading("BAB III: ANALISIS KEBUTUHAN & DESAIN ARSITEKTUR", level=1)
    h1.paragraph_format.space_before = Pt(12)
    h1.paragraph_format.space_after = Pt(10)
    for r in h1.runs:
        r.font.name = 'Times New Roman'
        r.font.color.rgb = RGBColor(0x4C, 0x1D, 0x95)

    doc.add_heading("3.1 Matriks Perbandingan Tiga Arsitektur Web", level=2)
    p = doc.add_paragraph(
        "Tabel berikut menyintesis perbedaan fundamental antara arsitektur web monolitik yang dibangun pada Minggu 5–6 "
        "dengan layanan web SOAP dan RESTful API pada penugasan Minggu ke-9:"
    )
    p.paragraph_format.line_spacing = 1.25

    table_comp = doc.add_table(rows=1, cols=4)
    comp_headers = ["Dimensi Arsitektur", "Web Monolitik (W5–W6)", "SOAP Web Service (W9)", "RESTful Web API v1 (W9)"]
    comp_data = [
        ["Target Pengguna", "Manusia via Web Browser", "Mesin (Sistem Enterprise Legacy)", "Mesin (Aplikasi Mobile, Kiosk, SPA)"],
        ["Protokol / Gaya", "SSR Monolith (Blade Engine)", "Protokol Formal W3C XML", "Gaya Arsitektur Roy Fielding"],
        ["Lokasi Rute Laravel", "routes/web.php", "routes/web.php (Kecualikan CSRF)", "routes/api.php (Prefix /api/v1)"],
        ["Format Payload", "HTML5 + CSS3 (~100–300 KB)", "XML <soap:Envelope> (~1–3 KB)", "JSON Murni (~200–800 Byte)"],
        ["Manajemen State", "Stateful (Session DB + Cookie)", "Stateless (opsional WS-Session)", "Stateless Murni (Bearer Token)"],
        ["Proteksi Keamanan", "Token CSRF (@csrf form)", "WS-Security / Validasi XML", "Sanctum Token (Authorization Header)"],
        ["Kontrak Layanan", "Antarmuka Visual Browser", "Dokumen WSDL XML (.wsdl)", "Dokumen OpenAPI 3.0 (.yaml)"],
        ["Format Galat", "Redirect HTTP 302 + Flash Alert", "Blok <soap:Fault> (HTTP 500)", "Standar IETF RFC 7807 (problem+json)"]
    ]
    style_table(table_comp, [1.4, 1.7, 1.7, 1.7], comp_headers, comp_data)

    doc.add_heading("3.2 Skema Relasional Basis Data (Tabel peminjamans)", level=2)
    p = doc.add_paragraph(
        "Melanjutkan skema basis data SQLite SIPUS-Del yang sebelumnya memuat tabel kategoris dan bukus, pada tahap ini "
        "diimplementasikan tabel baru peminjamans dengan struktur skema:"
    )
    p.paragraph_format.line_spacing = 1.25

    table_db = doc.add_table(rows=1, cols=4)
    db_headers = ["Nama Kolom", "Tipe Data & Batasan", "Atribut / Foreign Key", "Fungsi Bisnis"]
    db_data = [
        ["id", "BIGINT UNSIGNED", "PRIMARY KEY, AUTO INCREMENT", "Identifikator unik transaksi peminjaman"],
        ["kode_pinjam", "VARCHAR(30)", "UNIQUE, NULLABLE", "Kode bukti transaksi (misal: PINJAM-202611-001)"],
        ["buku_id", "BIGINT UNSIGNED", "FOREIGN KEY (bukus) ON DELETE CASCADE", "Relasi referensial ke entitas buku"],
        ["nim_peminjam", "VARCHAR(15)", "INDEXED, NOT NULL", "Nomor Induk Mahasiswa peminjam"],
        ["nama_peminjam", "VARCHAR(150)", "NOT NULL", "Nama lengkap mahasiswa peminjam"],
        ["tanggal_pinjam", "DATE", "NOT NULL", "Tanggal awal pencatatan sirkulasi"],
        ["tanggal_jatuh_tempo", "DATE", "NULLABLE", "Batas akhir peminjaman (default 7 hari)"],
        ["tanggal_kembali", "DATE", "NULLABLE", "Tanggal aktual buku dikembalikan"],
        ["status", "ENUM('dipinjam', 'dikembalikan')", "DEFAULT 'dipinjam'", "Status siklus hidup sirkulasi buku"],
        ["created_at, updated_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Pencatatan waktu audit persistensi data"]
    ]
    style_table(table_db, [1.5, 1.5, 1.8, 1.7], db_headers, db_data)

    doc.add_page_break()

    # -------------------------------------------------------------
    # BAB IV: IMPLEMENTASI TEKNIS KODE PROGRAM
    # -------------------------------------------------------------
    h1 = doc.add_heading("BAB IV: IMPLEMENTASI TEKNIS KODE PROGRAM", level=1)
    h1.paragraph_format.space_before = Pt(12)
    h1.paragraph_format.space_after = Pt(10)
    for r in h1.runs:
        r.font.name = 'Times New Roman'
        r.font.color.rgb = RGBColor(0x4C, 0x1D, 0x95)

    doc.add_heading("4.1 Layanan SOAP Clearance Bebas Pustaka (LibrarySoapController.php)", level=2)
    p = doc.add_paragraph(
        "Layanan SOAP dibangun secara mandiri pada berkas app/Http/Controllers/Soap/LibrarySoapController.php "
        "tanpa ketergantungan library pihak ketiga. Pengontrol ini menyediakan operasi wsdl() dan handle(). "
        "Berikut potongan implementasi ekstraksi XML dan penanganan kueri database:"
    )
    p.paragraph_format.line_spacing = 1.25

    code_soap = (
        "// Parsing dokumen XML masuk dan ambil tag nim_peminjam\n"
        "libxml_use_internal_errors(true);\n"
        "$xml = simplexml_load_string($rawXml);\n"
        "if ($xml === false) {\n"
        "    return $this->soapFault('soap:Client', 'Format XML tidak valid (Malformed XML).');\n"
        "}\n\n"
        "$nimNodes = $xml->xpath('//*[local-name()=\"CheckClearanceStatusRequest\"]/*[local-name()=\"nim_peminjam\"]');\n"
        "$nim = trim((string) $nimNodes[0]);\n\n"
        "// Cek tanggungan buku yang masih berstatus 'dipinjam' di database\n"
        "$activeLoans = DB::table('peminjamans')\n"
        "    ->where('nim_peminjam', $nim)\n"
        "    ->where('status', 'dipinjam')\n"
        "    ->count();\n\n"
        "$isCleared = ($activeLoans === 0) ? 'true' : 'false';\n"
        "// Mengembalikan amplop SOAP XML dengan status is_cleared dan active_loans"
    )
    add_code_block(doc, code_soap)

    doc.add_heading("4.2 Trait Standarisasi Respons & RFC 7807 (ApiResponse.php)", level=2)
    p = doc.add_paragraph(
        "Untuk memastikan seluruh respons REST API memiliki amplop yang konsisten dan patuh pada spesifikasi RFC 7807, "
        "dibuat trait app/Traits/ApiResponse.php yang diinjeksi ke setiap controller API:"
    )
    p.paragraph_format.line_spacing = 1.25

    code_trait = (
        "trait ApiResponse {\n"
        "    protected function successResponse($data = null, string $message = '...', int $status = 200, array $meta = []): JsonResponse {\n"
        "        $payload = ['success' => true, 'message' => $message, 'data' => $data];\n"
        "        if (!empty($meta)) { $payload['meta'] = $meta; }\n"
        "        return response()->json($payload, $status);\n"
        "    }\n\n"
        "    protected function createdResponse($data, string $locationUrl, string $message = '...'): JsonResponse {\n"
        "        return $this->successResponse($data, $message, 201)->header('Location', $locationUrl);\n"
        "    }\n\n"
        "    protected function problemResponse(string $title, string $detail, int $status, string $errorSlug = '...', array $errors = []): JsonResponse {\n"
        "        $problem = [\n"
        "            'type'     => url(\"/docs/errors/{$errorSlug}\"),\n"
        "            'title'    => $title,\n"
        "            'status'   => $status,\n"
        "            'detail'   => $detail,\n"
        "            'instance' => request()->getRequestUri(),\n"
        "        ];\n"
        "        if (!empty($errors)) { $problem['errors'] = $errors; }\n"
        "        return response()->json($problem, $status)->header('Content-Type', 'application/problem+json');\n"
        "    }\n"
        "}"
    )
    add_code_block(doc, code_trait)

    doc.add_heading("4.3 Kontrol Hypermedia HATEOAS Level 3 pada Eloquent Resource", level=2)
    p = doc.add_paragraph(
        "Puncak arsitektur REST (Level 3 RMM) diwujudkan pada BukuResource.php dan PeminjamanResource.php di mana "
        "klien dituntun oleh tautan navigasi mandiri yang berubah secara dinamis berdasarkan kondisi state data:"
    )
    p.paragraph_format.line_spacing = 1.25

    code_hateoas = (
        "// Pada BukuResource: Tautan 'pinjam_buku' HANYA muncul jika stok masih tersedia (> 0)\n"
        "if ($this->stok > 0) {\n"
        "    $links['pinjam_buku'] = [\n"
        "        'href'   => url('/api/v1/peminjamans'),\n"
        "        'method' => 'POST',\n"
        "    ];\n"
        "}\n\n"
        "// Pada PeminjamanResource: Tautan 'kembalikan_buku' HANYA muncul jika buku sedang dipinjam\n"
        "if ($this->status === 'dipinjam') {\n"
        "    $links['kembalikan_buku'] = [\n"
        "        'href'   => url(\"/api/v1/peminjamans/{$this->id}/kembalikan\"),\n"
        "        'method' => 'PATCH',\n"
        "    ];\n"
        "}"
    )
    add_code_block(doc, code_hateoas)

    doc.add_heading("4.4 Transaksi Sirkulasi ACID & Concurrency Locking (PeminjamanApiController.php)", level=2)
    p = doc.add_paragraph(
        "Logika transaksi peminjaman pada method store() dibungkus dalam transaksi database atomik dengan penguncian "
        "baris eksklusif lockForUpdate() guna meniadakan risiko Race Condition dan duplikasi transaksi:"
    )
    p.paragraph_format.line_spacing = 1.25

    code_acid = (
        "return DB::transaction(function () use ($validated) {\n"
        "    // Kunci baris buku secara eksklusif (SELECT ... FOR UPDATE)\n"
        "    $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->first();\n\n"
        "    // Cegah peminjaman aktif ganda oleh mahasiswa yang sama -> 409 Conflict\n"
        "    $cekPinjaman = Peminjaman::where('buku_id', $buku->id)\n"
        "        ->where('nim_peminjam', $nim)\n"
        "        ->where('status', 'dipinjam')\n"
        "        ->exists();\n"
        "    if ($cekPinjaman) {\n"
        "        return $this->problemResponse('Duplicate Active Loan Conflict', '...', 409, 'duplicate-loan');\n"
        "    }\n\n"
        "    // Validasi stok -> 422 Unprocessable Content jika stok habis (0)\n"
        "    if ($buku->stok < 1) {\n"
        "        return $this->problemResponse('Book Stock Exhausted', '...', 422, 'stock-exhausted');\n"
        "    }\n\n"
        "    // Kurangi stok atomik (-1) dan simpan peminjaman baru\n"
        "    $buku->decrement('stok');\n"
        "    $peminjaman = Peminjaman::create([...]);\n"
        "    return $this->createdResponse(new PeminjamanResource($peminjaman), $locationUrl);\n"
        "});"
    )
    add_code_block(doc, code_acid)

    doc.add_heading("4.5 REST Consumer Eksternal OpenLibrary dengan Graceful Fallback", level=2)
    p = doc.add_paragraph(
        "Pada BukuApiController::referensiEksternal(), fasad Http digunakan untuk memanggil API publik eksternal OpenLibrary "
        "dengan batas waktu timeout 8 detik, penanganan tanpa verifikasi SSL lokal, dan fallback yang aman:"
    )
    p.paragraph_format.line_spacing = 1.25

    code_consumer = (
        "$cleanIsbn = str_replace(['-', ' '], '', $buku->isbn);\n"
        "$openLibraryUrl = \"https://openlibrary.org/api/books?bibkeys=ISBN:{$cleanIsbn}&format=json&jscmd=data\";\n\n"
        "try {\n"
        "    $response = Http::withoutVerifying()->timeout(8)->acceptJson()->get($openLibraryUrl);\n"
        "    // Ekstrak metadata: judul_eksternal, penerbit, tahun_terbit, jumlah_halaman, cover\n"
        "} catch (\\Throwable $e) {\n"
        "    // Fallback aman jika jaringan offline / timeout tanpa melempar HTTP 500 fatal\n"
        "    return $this->successResponse([\n"
        "        'sumber' => 'OpenLibrary API (Offline/Timeout Fallback)',\n"
        "        'isbn'   => $buku->isbn,\n"
        "        'pesan'  => 'Data katalog lokal tetap aman dan dapat diakses normal.'\n"
        "    ], 'Gagal menghubungi OpenLibrary (fallback graceful aktif).');\n"
        "}"
    )
    add_code_block(doc, code_consumer)

    doc.add_page_break()

    # -------------------------------------------------------------
    # BAB V: HASIL PENGUJIAN SISTEM
    # -------------------------------------------------------------
    h1 = doc.add_heading("BAB V: HASIL PENGUJIAN SISTEM & BUKTI RESPON", level=1)
    h1.paragraph_format.space_before = Pt(12)
    h1.paragraph_format.space_after = Pt(10)
    for r in h1.runs:
        r.font.name = 'Times New Roman'
        r.font.color.rgb = RGBColor(0x4C, 0x1D, 0x95)

    p = doc.add_paragraph(
        "Pengujian komprehensif dilakukan secara bertahap melalui Terminal cURL, Postman Desktop, dan Automated Feature Testing "
        "(php artisan test). Seluruh skenario pengujian yang tercantum pada modul praktikum berhasil diverifikasi secara presisi:"
    )
    p.paragraph_format.line_spacing = 1.25

    table_test = doc.add_table(rows=1, cols=5)
    t_headers = ["No", "Skenario Pengujian", "Metode & Endpoint", "Status HTTP", "Hasil & Evaluasi Semantik"]
    t_data = [
        ["1", "WSDL Contract", "GET /soap/sipus.wsdl", "200 OK", "Content-Type: text/xml memuat dokumen WSDL resmi."],
        ["2", "SOAP Clearance (Bona)", "POST /soap/sipus", "200 OK", "active_loans: 1, is_cleared: false (ada tanggungan pinjaman)."],
        ["3", "SOAP Clearance (Nikah)", "POST /soap/sipus", "200 OK", "active_loans: 0, is_cleared: true (bebas pustaka yudisium)."],
        ["4", "SOAP Fault (NIM Kosong)", "POST /soap/sipus", "500 Error", "Respons XML <soap:Fault> memuat <faultcode>soap:Client</faultcode>."],
        ["5", "Sanctum Login Sukses", "POST /api/v1/auth/login", "200 OK", "Token Bearer berhasil diterbitkan (plainTextToken)."],
        ["6", "Sanctum Login Gagal", "POST /api/v1/auth/login", "401 Unauth", "RFC 7807 Problem Details (type: .../invalid-credentials)."],
        ["7", "Profil Pustakawan Aktif", "GET /api/v1/auth/me", "200 OK", "Otentikasi Bearer Token berhasil mengidentifikasi pustakawan."],
        ["8", "Profil Tanpa Token", "GET /api/v1/auth/me", "401 Unauth", "Ditolak otomatis dalam format RFC 7807 unauthenticated."],
        ["9", "Koleksi Buku Terpaginasi", "GET /api/v1/bukus?per_page=5", "200 OK", "Eager load kategori bebas N+1, metadata pagination lengkap."],
        ["10", "HATEOAS Buku Stok Ada", "GET /api/v1/bukus/1", "200 OK", "Tautan _links.pinjam_buku muncul karena stok > 0."],
        ["11", "HATEOAS Buku Stok Habis", "GET /api/v1/bukus/3", "200 OK", "Tautan _links.pinjam_buku otomatis hilang karena stok = 0."],
        ["12", "Buku Tidak Ditemukan", "GET /api/v1/bukus/9999", "404 Not Found", "RFC 7807 Problem Details (type: .../book-not-found)."],
        ["13", "POST Tambah Buku Baru", "POST /api/v1/bukus", "201 Created", "Header Location: /api/v1/bukus/{id} disertakan dalam respons."],
        ["14", "PUT vs PATCH Semantik", "PUT & PATCH /bukus/{id}", "200 OK", "PUT menimpa utuh representasi; PATCH hanya mengubah field stok."],
        ["15", "DELETE Buku Berpinjaman", "DELETE /api/v1/bukus/1", "409 Conflict", "Ditolak karena buku masih dipinjam aktif oleh mahasiswa."],
        ["16", "Sirkulasi Peminjaman ACID", "POST /api/v1/peminjamans", "201 Created", "Stok berkurang atomik (-1) dengan penguncian lockForUpdate."],
        ["17", "Pinjam Duplikat Ditolak", "POST /api/v1/peminjamans", "409 Conflict", "Ditolak karena NIM masih meminjam buku yang sama."],
        ["18", "Pinjam Stok 0 Ditolak", "POST /api/v1/peminjamans", "422 Unproc", "RFC 7807 (detail: 'Stok buku sedang habis')."],
        ["19", "Pengembalian Buku ACID", "PATCH /peminjamans/{id}/kembalikan", "200 OK", "Status jadi 'dikembalikan', tanggal kembali tercatat, stok (+1)."],
        ["20", "Pengembalian Ganda Ditolak", "PATCH /peminjamans/{id}/kembalikan", "409 Conflict", "Ditolak karena transaksi sudah selesai dikembalikan sebelumnya."],
        ["21", "Consumer OpenLibrary", "GET /bukus/{id}/referensi-eksternal", "200 OK", "Metadata eksternal berhasil diambil dengan fallback yang anggun."]
    ]
    style_table(table_test, [0.4, 2.0, 1.8, 1.0, 1.8], t_headers, t_data)

    doc.add_heading("5.2 Hasil Eksekusi Pengujian Otomatis (php artisan test)", level=2)
    p = doc.add_paragraph(
        "Seluruh rangkaian pengujian unit dan fitur dieksekusi secara otomatis. Hasil pengujian menunjukkan status "
        "24 passed (98 assertions) - 100% HIJAU tanpa kegagalan:"
    )
    p.paragraph_format.line_spacing = 1.25

    code_test_out = (
        "PASS  Tests\\Unit\\ExampleTest\n"
        "  ✓ that true is true\n\n"
        "PASS  Tests\\Feature\\ExampleTest\n"
        "  ✓ the application returns a successful response\n\n"
        "PASS  Tests\\Feature\\SipusDelApiTest\n"
        "  ✓ soap wsdl returns xml contract\n"
        "  ✓ soap clearance with active loans returns cleared false\n"
        "  ✓ soap clearance without loans returns cleared true\n"
        "  ✓ soap clearance with empty nim returns soap fault\n"
        "  ✓ sanctum login success and returns bearer token\n"
        "  ✓ sanctum login invalid credentials returns 401 rfc7807\n"
        "  ✓ buku index api returns paginated json\n"
        "  ✓ buku show hateoas shows pinjam when stock available and hides when zero\n"
        "  ✓ buku not found returns 404 rfc7807\n"
        "  ✓ peminjaman acid store decrements stock and returns 201\n"
        "  ✓ peminjaman fails when stock exhausted returns 422 rfc7807\n"
        "  ✓ peminjaman fails when duplicate active loan returns 409\n"
        "  ✓ peminjaman return increments stock and returns 200\n"
        "  ✓ peminjaman double return returns 409 conflict\n"
        "  ✓ openlibrary consumer returns graceful response\n\n"
        "PASS  Tests\\Feature\\SipusDelTest\n"
        "  ✓ root redirects to buku index\n"
        "  ✓ buku index renders with eager loading\n"
        "  ✓ buku store validation fails on invalid data\n"
        "  ✓ buku store success and redirects\n"
        "  ✓ buku update with route model binding\n"
        "  ✓ buku destroy\n"
        "  ✓ kategori crud\n\n"
        "Tests:    24 passed (98 assertions)\n"
        "Duration: 10.88s"
    )
    add_code_block(doc, code_test_out)

    doc.add_page_break()

    # -------------------------------------------------------------
    # BAB VI: KESIMPULAN & SARAN
    # -------------------------------------------------------------
    h1 = doc.add_heading("BAB VI: KESIMPULAN DAN SARAN", level=1)
    h1.paragraph_format.space_before = Pt(12)
    h1.paragraph_format.space_after = Pt(10)
    for r in h1.runs:
        r.font.name = 'Times New Roman'
        r.font.color.rgb = RGBColor(0x4C, 0x1D, 0x95)

    doc.add_heading("6.1 Kesimpulan", level=2)
    kesimpulan_list = [
        "Transisi dari web monolitik berorientasi visual (HTML SSR) menuju arsitektur web services berorientasi data murni (M2M) berhasil diimplementasikan secara komprehensif pada framework Laravel 11.x.",
        "Integrasi sistem warisan (legacy) berbasis SOAP berhasil dibuktikan melalui rute WSDL dan pemrosesan XML Envelope pada layanan Bebas Pustaka Yudisium, lengkap dengan penanganan galat SOAP Fault.",
        "Layanan RESTful API v1 SIPUS-Del telah memenuhi kaidah arsitektur Roy Fielding dan Richardson Maturity Model Level 3 (HATEOAS) dengan penyertaan tautan kontrol hypermedia dinamis berbasis ketersediaan stok buku dan status sirkulasi.",
        "Integritas transaksi bisnis sirkulasi peminjaman dan pengembalian buku terjamin aman dari bahaya Race Condition melalui penerapan transaksi basis data ACID yang dipadukan dengan Pessimistic Row-Level Locking (lockForUpdate).",
        "Penanganan error API telah distandarisasi secara konsisten mengacu pada IETF RFC 7807 (Problem Details), dan keamanan endpoint mutasi dilindungi secara penuh oleh token stateless Laravel Sanctum.",
        "Kontrak layanan modern OpenAPI 3.0 (docs/openapi-sipus-v1.yaml) dan koleksi pengujian Postman (docs/SIPUS-Del-API-v1.postman_collection.json) telah tersusun rapi untuk mempermudah integrasi pengembang aplikasi mobile dan pihak mitra."
    ]
    for k in kesimpulan_list:
        p_k = doc.add_paragraph(k, style='List Bullet')
        p_k.paragraph_format.space_after = Pt(4)
        p_k.paragraph_format.line_spacing = 1.2

    doc.add_heading("6.2 Saran Pengembangan Lanjutan", level=2)
    saran_list = [
        "Penerapan Rate Limiting bertingkat (misalnya pembatasan 60 request/menit untuk publik dan 1000 request/menit untuk pustakawan) untuk melindungi server dari serangan DoS/DDoS.",
        "Penerapan Caching HTTP (RFC 9111) berbasis Cache-Control dan ETag pada endpoint katalog buku publik guna menekan beban kueri database pada katalog yang jarang berubah.",
        "Penyediaan Webhook notifikasi keterlambatan pengembalian buku yang dapat diintegrasikan ke bot WhatsApp/Telegram mahasiswa."
    ]
    for s in saran_list:
        p_s = doc.add_paragraph(s, style='List Bullet')
        p_s.paragraph_format.space_after = Pt(4)
        p_s.paragraph_format.line_spacing = 1.2

    doc.add_page_break()

    # -------------------------------------------------------------
    # LAMPIRAN
    # -------------------------------------------------------------
    h1 = doc.add_heading("LAMPIRAN", level=1)
    h1.paragraph_format.space_before = Pt(12)
    h1.paragraph_format.space_after = Pt(10)
    for r in h1.runs:
        r.font.name = 'Times New Roman'
        r.font.color.rgb = RGBColor(0x4C, 0x1D, 0x95)

    doc.add_heading("Lampiran 1: Riwayat Commit Git (Conventional Commits pada week9-rest-api)", level=2)
    code_git = (
        "157e795 test(api): add comprehensive feature test suite for SOAP and REST endpoints\n"
        "ebc6b9b docs(contract): add OpenAPI 3.0 specification, Postman collection, and test report in README\n"
        "1849e51 feat(api-loans): implement ACID book borrowing and return REST endpoints with lockForUpdate\n"
        "2db8f5d feat(api-books): build BukuApiController, semantic mutations, and OpenLibrary consumer\n"
        "d03bd82 feat(api-auth): implement stateless Sanctum bearer token login and dynamic HATEOAS resources\n"
        "cab1017 feat(soap): add concise LibrarySoapController and WSDL contract for clearance check\n"
        "9da7fb7 feat(migration-seed): setup peminjamans schema and librarian seeder\n"
        "c185fdc feat(api-core): install laravel api routing, ApiResponse trait, and RFC 7807 exception handler"
    )
    add_code_block(doc, code_git)

    doc.add_heading("Lampiran 2: Informasi Akses Berkas Proyek", level=2)
    p_lamp = doc.add_paragraph(
        "1. Berkas Kontrak OpenAPI 3.0: docs/openapi-sipus-v1.yaml\n"
        "2. Berkas Koleksi Postman v2.1.0: docs/SIPUS-Del-API-v1.postman_collection.json\n"
        "3. Berkas Dokumentasi README: README.md\n"
        "4. Kredensial Akun Seeder Pustakawan: pustakawan@del.ac.id / password123\n"
        "5. Tautan Repositori GitHub: https://github.com/NikahPanjaitan/ppw-2026-week5-12S24041"
    )
    p_lamp.paragraph_format.line_spacing = 1.25

    # Simpan file docx
    output_path = r"c:\laragon\www\sipus-del\docs\Laporan_Praktikum_Week9_12S24041.docx"
    doc.save(output_path)
    print(f"Laporan berhasil digenerate di: {output_path}")

if __name__ == "__main__":
    build_laporan()
