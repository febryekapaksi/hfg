<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packing List per Gudang - <?= isset($header_ros['id']) ? $header_ros['id'] : '-' ?></title>
    <style>
        /* ── Reset & Base ───────────────────────────────────────────── */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #000;
            background: #fff;
        }

        /* ── Satu halaman = satu gudang ─────────────────────────────── */
        .page-gudang+.page-gudang {
            padding: 28px 30px;
            page-break-before: always;
        }

        /* ── Header Logo + Judul ────────────────────────────────────── */
        .page-header {
            position: relative;
            display: flex;
            align-items: center;
            margin-bottom: 16px;
            min-height: 100px;
        }

        .page-header img {
            height: 90px;
            width: auto;
            flex-shrink: 0;
        }

        .page-header-title {
            position: absolute;
            left: 0;
            right: 0;
            text-align: center;
            pointer-events: none;
        }

        .page-header-title h4 {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .page-header-title p {
            font-size: 12px;
            color: #555;
        }

        /* ── Info Rows (3 kolom: kiri, tengah-kosong, kanan) ─────────── */
        .info-section {
            display: flex;
            gap: 60px;
            margin-bottom: 12px;
        }

        .info-block table {
            border-collapse: collapse;
            font-size: 13px;
        }

        .info-block td {
            padding: 2px 6px 2px 0;
            vertical-align: top;
        }

        .info-block td:first-child {
            font-weight: 600;
            white-space: nowrap;
        }

        .info-block td:nth-child(2) {
            padding-right: 8px;
        }

        /* ── Badge Gudang ────────────────────────────────────────────── */
        .badge-ros {
            display: inline-block;
            background: #2c3e50;
            color: #fff;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .badge-gudang {
            display: inline-block;
            background: #1a6e3c;
            color: #fff;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        /* ── Divider ─────────────────────────────────────────────────── */
        .divider {
            border: none;
            border-top: 2px solid #000;
            margin: 12px 0;
        }

        /* ── Tabel Detail ────────────────────────────────────────────── */
        table.detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 12px;
            page-break-inside: auto;
        }

        table.detail-table th {
            background-color: #2c3e50;
            color: #fff;
            text-align: center;
            padding: 7px 8px;
            border: 1px solid #000;
            font-size: 12px;
        }

        table.detail-table td {
            padding: 6px 8px;
            border: 1px solid #aaa;
            vertical-align: middle;
        }

        table.detail-table tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        table.detail-table tfoot td {
            border: 1px solid #000;
            background-color: #ecf0f1;
            font-weight: bold;
            padding: 7px 8px;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* ── Footer Cetak ────────────────────────────────────────────── */
        .print-footer {
            margin-top: 36px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 12px;
            color: #888;
        }

        .sign-block {
            text-align: center;
            font-size: 12px;
        }

        .sign-block .sign-line {
            width: 140px;
            border-top: 1px solid #000;
            margin: 48px auto 4px;
        }

        /* ── Tombol (non-print) ──────────────────────────────────────── */
        .no-print {
            position: fixed;
            top: 12px;
            right: 16px;
            display: flex;
            gap: 8px;
            z-index: 999;
        }

        .no-print button {
            padding: 6px 14px;
            font-size: 13px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-print {
            background: #2c3e50;
            color: #fff;
        }

        .btn-close {
            background: #aaa;
            color: #fff;
        }

        table.detail-table tbody tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        table.detail-table tfoot {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* ── Print rules ─────────────────────────────────────────────── */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 0;
            }

            table.detail-table th {
                background-color: #2c3e50 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table.detail-table tfoot td {
                background-color: #ecf0f1 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .badge-gudang {
                background-color: #1a6e3c !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table.detail-table tbody tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            table.detail-table tfoot {
                display: table-row-group;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>

<body>

    <?php
    /* ── Helper: format tanggal ── */
    function fmt_date_g($val)
    {
        if (empty($val) || $val === '0000-00-00') return '-';
        return date('d-M-Y', strtotime($val));
    }

    /* ── Ambil nilai header ── */
    $no_ros        = $header_ros['id']            ?? '-';
    $no_po         = isset($header_ros['no_po'])  ? str_replace(',', ', ', $header_ros['no_po']) : '-';
    $no_surat      = $header_ros['no_surat']      ?? '-';
    $nm_supplier   = $header_ros['nm_supplier']   ?? '-';
    $awb_number    = (!empty($header_ros['awb_bl_number'])) ? $header_ros['awb_bl_number'] : '-';
    $awb_date      = fmt_date_g($header_ros['awb_bl_date']   ?? '');
    $eta_warehouse = fmt_date_g($header_ros['eta_warehouse'] ?? '');
    $ata_pod       = fmt_date_g($header_ros['ata_pod']       ?? '');
    ?>

    <!-- Tombol Aksi (hilang saat print) -->
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">&#128438; Print</button>
        <button class="btn-close" onclick="window.close()">&times; Close</button>
    </div>

    <?php if (!empty($grouped_by_gudang)): ?>
        <?php foreach ($grouped_by_gudang as $gudang): ?>

            <?php
            $packs       = $gudang['packs'];
            $nm_gudang   = $gudang['nm_gudang'];
            $kd_gudang   = $gudang['kd_gudang'];

            // Hitung total keseluruhan gudang ini
            $ttl_kotor  = 0;
            $ttl_bersih = 0;
            foreach ($packs as $p) {
                foreach ($p['items'] as $item) {
                    $ttl_kotor  += (float) $item['berat_kotor'];
                    $ttl_bersih += (float) $item['berat_bersih'];
                }
            }
            ?>

            <div class="page-gudang">

                <!-- ── Logo + Judul ── -->
                <div class="page-header">
                    <img src="<?= base_url('assets/images/logohfg.png') ?>" alt="Logo">
                    <div class="page-header-title">
                        <h4>Packing List</h4>
                        <p>Report of Shipment</p>
                    </div>
                </div>

                <!-- ── Info Header (Kiri & Kanan) ── -->
                <div class="info-section">

                    <!-- Kiri -->
                    <div class="info-block">
                        <table>
                            <tr>
                                <td>No. ROS</td>
                                <td>:</td>
                                <td><?= htmlspecialchars($no_ros) ?></td>
                            </tr>
                            <tr>
                                <td>No. PO</td>
                                <td>:</td>
                                <td><?= htmlspecialchars($no_surat) ?></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Kanan -->
                    <div class="info-block">
                        <table>
                            <tr>
                                <td>Supplier</td>
                                <td>:</td>
                                <td><?= htmlspecialchars($nm_supplier) ?></td>
                            </tr>
                            <tr>
                                <td>Warehouse</td>
                                <td>:</td>
                                <td>
                                    <span class="badge-gudang">
                                        <?= htmlspecialchars($nm_gudang) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>

                </div><!-- /info-section -->

                <hr class="divider">

                <!-- ── Tabel per Pack ── -->
                <?php foreach ($packs as $pack): ?>
                    <?php
                    $items = $pack['items'];
                    if (empty($items)) continue;

                    // Group items by material dan hitung summary
                    $mat_summary = [];
                    foreach ($items as $row) {
                        $mat_key = $row['id_material'];
                        if (!isset($mat_summary[$mat_key])) {
                            $mat_summary[$mat_key] = [
                                'nm_material' => $row['nm_material'],
                                'trade_name'  => $row['trade_name'],
                                'total_nw'    => 0,
                                'total_gw'    => 0,
                                'coil_count'  => 0,
                            ];
                        }
                        $mat_summary[$mat_key]['total_nw'] += (float) $row['berat_bersih'];
                        $mat_summary[$mat_key]['total_gw'] += (float) $row['berat_kotor'];
                        $mat_summary[$mat_key]['coil_count']++;
                    }

                    $pack_nw = array_sum(array_column($items, 'berat_bersih'));
                    $pack_gw = array_sum(array_column($items, 'berat_kotor'));
                    ?>

                    <div style="margin-bottom: 16px;">
                        <div style="background:#eaf2f8; padding:6px 12px; border-left:4px solid #2980b9; margin-bottom:6px;">
                            <b>Pack:</b> <span class="badge-ros"><?= htmlspecialchars($pack['pack_code']) ?></span>
                        </div>

                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th style="width:30px;">No.</th>
                                    <th style="width:220px;">Item Name</th>
                                    <th style="width:220px;">Alias / Other Name</th>
                                    <th style="width:80px;">Coil Qty</th>
                                    <th style="width:110px;">Gross Weight (kg)</th>
                                    <th style="width:110px;">Net Weight (kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($mat_summary as $mat): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($mat['nm_material']) ?></td>
                                        <td><?= htmlspecialchars($mat['trade_name']) ?></td>
                                        <td class="text-center"><?= $mat['coil_count'] ?></td>
                                        <td class="text-end"><?= number_format($mat['total_gw'], 2) ?></td>
                                        <td class="text-end"><?= number_format($mat['total_nw'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end">Pack Total</td>
                                    <td class="text-end"><?= number_format($pack_gw, 2) ?></td>
                                    <td class="text-end"><?= number_format($pack_nw, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endforeach; ?>

                <!-- ── Grand Total per Gudang ── -->
                <div style="margin-top:12px; padding:8px 12px; background:#ecf0f1; border:1px solid #bdc3c7; font-weight:bold; font-size:13px; display:flex; justify-content:space-between;">
                    <span>Grand Total — <?= htmlspecialchars($nm_gudang) ?></span>
                    <span>G.W.: <?= number_format($ttl_kotor, 2) ?> Kg &nbsp;|&nbsp; N.W.: <?= number_format($ttl_bersih, 2) ?> Kg</span>
                </div>

                <!-- ── Footer ── -->
                <div class="print-footer">
                    <span>Printed On: <?= date('d-M-Y H:i') ?></span>
                    <div style="display:flex; gap:60px;">
                        <div class="sign-block">
                            <div class="sign-line"></div>
                            Prepared by
                        </div>
                        <div class="sign-block">
                            <div class="sign-line"></div>
                            Received by
                        </div>
                    </div>
                </div>

            </div><!-- /page-gudang -->

        <?php endforeach; ?>

    <?php else: ?>
        <div style="text-align:center; padding:60px; color:#999;">
            There is no coil data that has been assigned to the warehouse for ROS <b><?= htmlspecialchars($no_ros) ?></b>.
        </div>
    <?php endif; ?>

    <script>
        window.print();
    </script>

</body>

</html>