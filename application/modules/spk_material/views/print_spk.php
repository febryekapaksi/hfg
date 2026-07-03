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
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
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
            height: 40px;
            width: auto;
        }

        .doc-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            padding: 14px 0 14px 80px; /* Padding kiri 80px agar teks judul bergeser dari logo */
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
            page-break-inside: avoid; /* Menjaga 1 blok produk & materialnya tetap menyatu di 1 halaman jika muat */
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
            margin-bottom: 55px; /* Ruang untuk tanda tangan fisik */
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

                <?php if (!empty($detail['materials'])): ?>
                    <table class="material-table">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="50%">Nama Material</th>
                                <th width="25%" class="text-right">Jumlah Dibutuhkan</th>
                                <th width="20%" class="text-center">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detail['materials'] as $m_idx => $material): ?>
                                <tr>
                                    <td class="text-center" style="color: #718096;"><?php echo $m_idx + 1; ?></td>
                                    <td style="font-weight: 500;"><?php echo $material['nm_material']; ?></td>
                                    <td class="text-right" style="font-weight: bold;">
                                        <?php echo number_format((float)$material['qty'] * (int)$detail['target_qty'], 4); ?>
                                    </td>
                                    <td class="text-center"><?php echo isset($material['nm_unit']) ? $material['nm_unit'] : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="padding: 10px; border: 1px dashed #cbd5e0; border-top: none; color:#718096; font-style: italic;">
                        BOM (Bill of Materials) belum tersedia untuk produk ini.
                    </div>
                <?php endif; ?>

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

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>