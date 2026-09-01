<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>SPK Material - <?php echo $spk['spk_no']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #111;
            padding: 15mm 15mm;
            line-height: 1.4;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: A4;
                margin: 15mm 15mm;
            }
        }

        /* Tombol Cetak Sederhana */
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999;
            padding: 10px 24px;
            background: #2b6cb0;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .print-btn:hover {
            background: #2c5282;
        }

        /* Kop Dokumen */
        .doc-header-wrapper {
            position: relative;
            margin-bottom: 20px;
        }

        .doc-logo {
            position: absolute;
            left: 5px;
            top: 50%;
            transform: translateY(-50%);
            height: 60px;
            width: auto;
        }

        .doc-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            padding: 14px 0 14px 0;
            border-top: 2px solid #111;
            border-bottom: 2px solid #111;
            letter-spacing: 0.5px;
        }

        /* Grid Informasi SPK (2 Kolom) */
        .info-container {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table {
            width: 48%;
            float: left;
            border-collapse: collapse;
        }

        .info-table.right {
            float: right;
        }

        .info-container::after {
            content: "";
            display: table;
            clear: both;
        }

        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .info-label {
            width: 90px;
            color: #555;
            font-weight: bold;
        }

        .info-separator {
            width: 15px;
            text-align: center;
            color: #555;
        }

        /* Grouping per Produk (Mencegah terpotong halaman tengah) */
        .product-group {
            page-break-inside: avoid;
            /* Menjaga 1 blok produk & materialnya tetap menyatu di 1 halaman jika muat */
            margin-bottom: 25px;
        }

        .section-header {
            background-color: #f7fafc;
            color: #1a202c;
            padding: 6px 8px;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid #cbd5e0;
            border-bottom: none;
        }

        .product-info-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e0;
            background-color: #fff;
        }

        .product-info-table td {
            padding: 6px 10px;
            font-size: 11px;
            border: 1px solid #cbd5e0;
        }

        .product-info-label {
            width: 15%;
            font-weight: bold;
            color: #4a5568;
            background-color: #edf2f7;
        }

        .product-info-value {
            width: 35%;
        }

        /* Tabel Material */
        .material-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            border: 1px solid #cbd5e0;
        }

        .material-table th {
            background-color: #edf2f7;
            color: #2d3748;
            padding: 8px 10px;
            font-size: 11px;
            font-weight: bold;
            text-align: left;
            border: 1px solid #cbd5e0;
        }

        .material-table th.text-center,
        .material-table td.text-center {
            text-align: center;
        }

        .material-table th.text-right,
        .material-table td.text-right {
            text-align: right;
        }

        .material-table td {
            padding: 7px 10px;
            font-size: 11px;
            border: 1px solid #cbd5e0;
            color: #2d3748;
        }

        .material-table tr:nth-child(even) {
            background-color: #f7fafc;
        }

        /* Footer & Tanda Tangan */
        .footer-container {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            vertical-align: bottom;
        }

        .signature-box {
            border: 1px solid #cbd5e0;
            text-align: center;
            padding: 12px;
            width: 220px;
            float: right;
            background: #fff;
        }

        .signature-title {
            font-weight: bold;
            color: #4a5568;
            margin-bottom: 55px;
            /* Ruang untuk tanda tangan fisik */
            display: block;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            display: block;
        }

        .signature-date {
            font-size: 10px;
            color: #718096;
            margin-top: 2px;
            display: block;
        }

        /* ===================================================== */
        /* HALAMAN TAMBAHAN (Form Laporan Produksi & SOP)        */
        /* ===================================================== */
        .page-break {
            page-break-before: always;
        }

        /* Judul halaman form */
        .form-doc-title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        /* Header identitas form (No SPK, Setter, dll) */
        .form-ident-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }

        .form-ident-table td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .form-ident-table .fi-label {
            font-weight: bold;
            white-space: nowrap;
            width: 80px;
        }

        .form-ident-table .fi-sep {
            width: 8px;
        }

        .form-ident-table .fi-line {
            border-bottom: 1px solid #999;
            min-width: 90px;
        }

        /* Section header berwarna (biru) */
        .form-section-header {
            background-color: #4a6fa5;
            color: #fff;
            font-weight: bold;
            font-size: 10px;
            padding: 4px 6px;
            border: 1px solid #34517a;
        }

        /* Tabel form isian manual */
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10px;
        }

        .form-table th {
            background-color: #dbe4f0;
            color: #1a202c;
            font-weight: bold;
            padding: 4px 5px;
            border: 1px solid #7a90b0;
            text-align: center;
            vertical-align: middle;
        }

        .form-table td {
            padding: 4px 5px;
            border: 1px solid #7a90b0;
            height: 22px;
            vertical-align: middle;
        }

        .form-table td.ft-no {
            text-align: center;
            width: 28px;
        }

        /* Wrapper 2 kolom untuk section 4 & 5 */
        .form-two-col {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .form-two-col > tbody > tr > td {
            vertical-align: top;
            width: 50%;
            padding: 0;
        }

        .form-two-col > tbody > tr > td.col-left {
            padding-right: 6px;
        }

        /* Tanda tangan pada form */
        .form-sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            font-size: 10px;
        }

        .form-sign-table td {
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
            width: 33.33%;
        }

        .form-sign-role {
            font-weight: bold;
            margin-bottom: 55px;
            display: block;
        }

        .form-sign-line {
            display: block;
        }

        /* Tabel SOP (Instruksi Kerja) */
        .sop-intro {
            font-size: 10px;
            font-style: italic;
            margin-bottom: 12px;
        }

        .sop-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }

        .sop-table th {
            background-color: #dbe4f0;
            color: #1a202c;
            font-weight: bold;
            padding: 5px 6px;
            border: 1px solid #7a90b0;
            text-align: center;
        }

        .sop-table td {
            padding: 5px 6px;
            border: 1px solid #7a90b0;
            vertical-align: top;
        }

        .sop-table td.sop-no {
            text-align: center;
            width: 26px;
        }

        .sop-table td.sop-bagian {
            width: 22%;
            font-weight: bold;
        }

        .sop-table td.sop-item {
            width: 20%;
        }
    </style>
</head>

<body>

    <button class="print-btn no-print" onclick="window.print()">
        Print / Save PDF
    </button>

    <div class="doc-header-wrapper">
        <img src="<?php echo base_url('assets/images/logohfg.png'); ?>" alt="Logo" class="doc-logo">
        <div class="doc-title">SPK PRODUKSI & MATERIAL</div>
    </div>

    <div class="info-container">
        <table class="info-table">
            <tr>
                <td class="info-label">No. SPK</td>
                <td class="info-separator">:</td>
                <td><strong><?php echo $spk['spk_no']; ?></strong></td>
            </tr>
            <tr>
                <td class="info-label">Tanggal SPK</td>
                <td class="info-separator">:</td>
                <td><?php echo date('d-m-Y', strtotime($spk['tgl_spk'])); ?></td>
            </tr>
            <?php if (!empty($spk['due_date'])): ?>
                <tr>
                    <td class="info-label">Due Date</td>
                    <td class="info-separator">:</td>
                    <td><?php echo date('d-m-Y', strtotime($spk['due_date'])); ?></td>
                </tr>
            <?php endif; ?>
        </table>

        <table class="info-table right">
            <tr>
                <td class="info-label">Shift</td>
                <td class="info-separator">:</td>
                <td><?php echo !empty($spk['shift_names']) ? $spk['shift_names'] : '-'; ?></td>
            </tr>
            <tr>
                <td class="info-label">Status</td>
                <td class="info-separator">:</td>
                <td><span style="text-transform: uppercase; font-weight: bold; color: #2b6cb0;"><?php echo $spk['status']; ?></span></td>
            </tr>
            <?php if (!empty($spk['catatan'])): ?>
                <tr>
                    <td class="info-label">Catatan</td>
                    <td class="info-separator">:</td>
                    <td><?php echo $spk['catatan']; ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <?php if (!empty($details)): ?>
        <?php foreach ($details as $idx => $detail): ?>

            <div class="product-group">

                <div class="section-header">
                    <?php echo ($idx + 1) . '. ' . $detail['nm_produk_fg']; ?>
                </div>

                <table class="product-info-table">
                    <tr>
                        <td class="product-info-label">Target Qty</td>
                        <td class="product-info-value"><strong><?php echo number_format($detail['target_qty']); ?></strong></td>
                        <td class="product-info-label">Total Weight</td>
                        <td class="product-info-value"><strong><?php echo number_format($detail['total_weight'], 2); ?> Kg</strong></td>
                    </tr>
                </table>
            </div>

        <?php endforeach; ?>
    <?php endif; ?>

    <div class="footer-container">
        <table class="footer-table">
            <tr>
                <td width="50%" style="padding-bottom: 5px;">
                    <span style="font-size:10px; color:#718096; font-style: italic;">
                        Dicetak pada: <?php echo date('d-m-Y H:i'); ?>
                    </span>
                </td>
                <td width="50%">
                    <div class="signature-box">
                        <span class="signature-title">Dibuat oleh,</span>
                        <span class="signature-name"><?php echo $created_by_name; ?></span>
                        <span class="signature-date"><?php echo date('d-m-Y', strtotime($spk['created_at'])); ?></span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ===================================================== -->
    <!-- HALAMAN 2: FORM SPK & LAPORAN PRODUKSI HARIAN         -->
    <!-- ===================================================== -->
    <div class="page-break">

        <div class="form-doc-title">FORM SURAT PERINTAH KERJA (SPK) &amp; LAPORAN PRODUKSI HARIAN</div>

        <!-- Header Identitas -->
        <table class="form-ident-table">
            <tr>
                <td class="fi-label">No. SPK</td>
                <td class="fi-sep">:</td>
                <td style="width:22%;"><strong><?php echo htmlspecialchars($spk['spk_no']); ?></strong></td>

                <td class="fi-label">Setter Name</td>
                <td class="fi-sep">:</td>
                <td class="fi-line" style="width:20%;">&nbsp;</td>

                <td class="fi-label" style="width:70px;">No. Dokumen</td>
                <td class="fi-sep">:</td>
                <td class="fi-line">&nbsp;</td>
            </tr>
            <tr>
                <td class="fi-label">Tgl</td>
                <td class="fi-sep">:</td>
                <td class="fi-line">&nbsp;</td>

                <td class="fi-label">Helper Name</td>
                <td class="fi-sep">:</td>
                <td class="fi-line">&nbsp;</td>

                <td class="fi-label">Revisi</td>
                <td class="fi-sep">:</td>
                <td class="fi-line">&nbsp;</td>
            </tr>
            <tr>
                <td class="fi-label">Mesin</td>
                <td class="fi-sep">:</td>
                <td class="fi-line">&nbsp;</td>

                <td class="fi-label">Jam Mulai s/d Selesai</td>
                <td class="fi-sep">:</td>
                <td class="fi-line">&nbsp;</td>

                <td class="fi-label">Tgl Efektif</td>
                <td class="fi-sep">:</td>
                <td class="fi-line">&nbsp;</td>
            </tr>
            <tr>
                <td class="fi-label">&nbsp;</td>
                <td class="fi-sep">&nbsp;</td>
                <td>&nbsp;</td>

                <!-- <td class="fi-label">Jam Selesai</td>
                <td class="fi-sep">:</td>
                <td class="fi-line">&nbsp;</td> -->

                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        </table>

        <!-- Section 1: Pemakaian Material -->
        <div class="form-section-header">1. PEMAKAIAN MATERIAL (BABY COIL - DARI UNPACK / WIP / HOLD)</div>
        <table class="form-table">
            <thead>
                <tr>
                    <th style="width:28px;">No</th>
                    <th style="width:14%;">Sumber Coil<br>(Unpack/WIP/Hold)</th>
                    <th style="width:12%;">No. Pack</th>
                    <th style="width:14%;">Nama Material</th>
                    <th style="width:14%;">No. Baby Coil</th>
                    <th style="width:12%;">Berat Bersih (Net Kg)</th>
                    <th>Kondisi / Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <tr>
                        <td class="ft-no"><?php echo $i; ?></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <!-- Section 2: Hasil Produksi KW 1 -->
        <div class="form-section-header">2. HASIL PRODUKSI (FINISH GOOD KW 1) - SPK &amp; STOK BEBAS</div>
        <table class="form-table">
            <thead>
                <tr>
                    <th style="width:28px;">No</th>
                    <th style="width:14%;">Jenis Produksi</th>
                    <th style="width:16%;">Nama Produk</th>
                    <th style="width:14%;">Asal Baby Coil</th>
                    <th style="width:12%;">Qty (Pcs)</th>
                    <th style="width:12%;">Berat Total (Kg)</th>
                    <th>Berat / Pcs (Kg) (Opsional)</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <tr>
                        <td class="ft-no"><?php echo $i; ?></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <!-- Section 3: Hasil Produksi KW 2 -->
        <div class="form-section-header">3. HASIL PRODUKSI NON-STANDARD (KW 2)</div>
        <table class="form-table">
            <thead>
                <tr>
                    <th style="width:28px;">No</th>
                    <th style="width:16%;">Kategori (Internal / Supplier)</th>
                    <th style="width:16%;">Nama KW 2</th>
                    <th style="width:12%;">Size (m)</th>
                    <th style="width:12%;">Qty KW 2</th>
                    <th style="width:12%;">Berat Total (Kg)</th>
                    <th>Keterangan (Contoh: White Rust)</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <tr>
                        <td class="ft-no"><?php echo $i; ?></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <!-- Section 4 & 5: Dua kolom -->
        <table class="form-two-col">
            <tr>
                <td class="col-left">
                    <div class="form-section-header">4. SISA COIL &amp; HOLD COIL</div>
                    <table class="form-table" style="margin-bottom:0;">
                        <thead>
                            <tr>
                                <th style="width:30%;">Kategori</th>
                                <th>No. Baby Coil</th>
                                <th style="width:26%;">Berat (Kg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            <?php endfor; ?>
                            <tr>
                                <td colspan="3" style="font-style:italic; font-size:9px;">*Tuliskan keterangan detail di baliknya jika perlu</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td class="col-right">
                    <div class="form-section-header">5. KOMPONEN SCRAP &amp; REJECT</div>
                    <table class="form-table" style="margin-bottom:0;">
                        <thead>
                            <tr>
                                <th>Kategori Scrap</th>
                                <th style="width:35%;">Berat (Kg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $scrap_items = [
                                'Tong Coil',
                                'Wrapping',
                                'Potongan Pisau',
                                'Reject Produk (Internal)',
                                'Reject Produk (Supplier)',
                                'Reject Material (Internal)',
                                'Reject Material (Supplier)',
                            ];
                            foreach ($scrap_items as $item):
                            ?>
                                <tr>
                                    <td><?php echo $item; ?></td>
                                    <td>&nbsp;</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Tanda Tangan -->
        <table class="form-sign-table">
            <tr>
                <td>
                    <span class="form-sign-role">Dibuat Oleh (Operator / Helper)</span>
                    <span class="form-sign-line">(_________________________)</span>
                </td>
                <td>
                    <span class="form-sign-role">Diperiksa Oleh (Leader / SPV Produksi)</span>
                    <span class="form-sign-line">(_________________________)</span>
                </td>
                <td>
                    <span class="form-sign-role">Diketahui Oleh (QC / Admin)</span>
                    <span class="form-sign-line">(_________________________)</span>
                </td>
            </tr>
        </table>

    </div>

    <!-- ===================================================== -->
    <!-- HALAMAN 3: INSTRUKSI KERJA (SOP) PENGISIAN FORM       -->
    <!-- ===================================================== -->
    <div class="page-break">

        <div class="form-doc-title">INSTRUKSI KERJA (SOP) PENGISIAN FORM SPK &amp; LAPORAN PRODUKSI</div>

        <div class="sop-intro">
            Tujuan: Memastikan Operator/Helper dapat mengisi form produksi dengan akurat sehingga data valid saat diinput Admin ke Sistem ERP.
        </div>

        <table class="sop-table">
            <thead>
                <tr>
                    <th style="width:26px;">No</th>
                    <th style="width:22%;">Bagian Form</th>
                    <th style="width:20%;">Item / Kolom</th>
                    <th>Instruksi Pengisian (Cara Pengisian &amp; Keterangan)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sop_rows = [
                    ['HEADER & IDENTITAS', 'No. SPK, Tgl Produksi, Mesin', 'Diisi sesuai dengan Surat Perintah Kerja (SPK) yang diterima dari PPIC / Admin. Harus jelas dan tidak boleh dikosongkan.'],
                    ['HEADER & IDENTITAS', 'Nama Setter & Helper, Jam', 'Diisi dengan nama petugas aktual yang menjalankan mesin. Jam mulai diisi saat mesin setup/running, jam selesai diisi saat batch produksi selesai.'],
                    ['1. PEMAKAIAN MATERIAL', 'Sumber Coil (Unpack/WIP/Hold)', 'Tulis atau beri tanda centang asal material. Pilih Unpack (Material baru buka), WIP (Sisa dari produksi sebelumnya), atau Hold (Material yang sempat ditahan QC).'],
                    ['1. PEMAKAIAN MATERIAL', 'No. Pack & No. Baby Coil', 'WAJIB diisi sesuai dengan label/barcode fisik yang menempel pada material. Sangat krusial untuk fitur traceability (lacak jejak) di program ERP.'],
                    ['1. PEMAKAIAN MATERIAL', 'Berat Bersih (Net Kg)', 'Diisi angka berat aktual (hasil timbang) material tersebut saat akan dimasukkan ke mesin.'],
                    ['2. HASIL PRODUKSI (KW 1)', 'Jenis Produksi', "Pilih 'Sesuai SPK' jika hasil produksi untuk memenuhi dokumen SPK tersebut. Pilih 'Stok Bebas' jika sisa material dijadikan produk lain (di luar SPK) untuk dijadikan stok gudang."],
                    ['2. HASIL PRODUKSI (KW 1)', 'Qty (Pcs) & Berat Total (Kg)', 'Hitung jumlah Pcs (lembar/batang) yang dihasilkan, lalu timbang berat total keseluruhannya. Sistem ERP akan otomatis menghitung persentase selisih berdasarkan data ini.'],
                    ['3. HASIL PRODUKSI (KW 2)', 'Kategori (Internal / Supplier)', 'Internal: Cacat/KW akibat proses di pabrik (kesalahan mesin/operator). Supplier: Cacat bawaan dari bahan baku supplier sebelum diproses.'],
                    ['3. HASIL PRODUKSI (KW 2)', 'Keterangan', "Wajib menuliskan alasan produk menjadi KW 2, contoh: 'White Rust', 'Penyok', 'Tergores Mesin', 'Gelombang', dll."],
                    ['4. SISA COIL & HOLD COIL', 'No. Baby Coil & Berat (Kg)', 'Lakukan penimbangan pada material sisa yang belum habis atau material yang di-Hold. Pastikan menuliskan No. Baby Coil asalnya agar sistem memotong stok secara akurat.'],
                    ['5. KOMPONEN SCRAP / REJECT', 'Tong, Wrapping, Pisau, Reject', 'Kumpulkan semua limbah (scrap) maupun material/produk reject murni (tidak bisa dijual sebagai KW 2). Timbang di akhir shift produksi dan masukkan total beratnya (Kg) pada kolom yang sesuai.'],
                    ['6. APPROVAL / PENGESAHAN', 'Tanda Tangan', 'Setelah form diisi lengkap, wajib ditandatangani oleh Operator pembuat, diverifikasi (dicek silang aktual fisiknya) oleh Leader/SPV, dan divalidasi oleh Tim QC/Admin sebelum form diserahkan ke bagian Data Entry ERP.'],
                ];
                foreach ($sop_rows as $idx => $r):
                ?>
                    <tr>
                        <td class="sop-no"><?php echo $idx + 1; ?></td>
                        <td class="sop-bagian"><?php echo htmlspecialchars($r[0]); ?></td>
                        <td class="sop-item"><?php echo htmlspecialchars($r[1]); ?></td>
                        <td><?php echo htmlspecialchars($r[2]); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>