<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>SPK Coil - <?php echo $request['spk_coil_no']; ?></title>
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
                margin: 10mm;
            }
        }

        /* Tombol Cetak */
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
            padding: 14px 0 14px 80px;
            border-top: 2px solid #111;
            border-bottom: 2px solid #111;
            letter-spacing: 0.5px;
        }

        /* Grid Informasi */
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
            width: 120px;
            color: #555;
            font-weight: bold;
        }

        .info-separator {
            width: 15px;
            text-align: center;
            color: #555;
        }

        /* Tabel Coil */
        .coil-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border: 1px solid #cbd5e0;
        }

        .coil-table th {
            background-color: #edf2f7;
            color: #2d3748;
            padding: 8px 10px;
            font-size: 11px;
            font-weight: bold;
            text-align: left;
            border: 1px solid #cbd5e0;
        }

        .coil-table th.text-center,
        .coil-table td.text-center {
            text-align: center;
        }

        .coil-table th.text-right,
        .coil-table td.text-right {
            text-align: right;
        }

        .coil-table td {
            padding: 7px 10px;
            font-size: 11px;
            border: 1px solid #cbd5e0;
            color: #2d3748;
        }

        .coil-table tr:nth-child(even) {
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

<body onload="window.print()">

    <button class="print-btn no-print" onclick="window.print()">
        Print / Save PDF
    </button>

    <div class="doc-header-wrapper">
        <img src="<?php echo base_url('assets/images/logohfg.png'); ?>" alt="Logo" class="doc-logo">
        <div class="doc-title">SPK PENGAMBILAN COIL</div>
    </div>

    <div class="info-container">
        <table class="info-table">
            <tr>
                <td class="info-label">No. SPK Coil</td>
                <td class="info-separator">:</td>
                <td><strong><?php echo $request['spk_coil_no']; ?></strong></td>
            </tr>
            <tr>
                <td class="info-label">No. SPK Material</td>
                <td class="info-separator">:</td>
                <td><?php echo $request['spk_no']; ?></td>
            </tr>
        </table>

        <table class="info-table right">
            <tr>
                <td class="info-label">Tanggal</td>
                <td class="info-separator">:</td>
                <td><?php echo date('d-m-Y', strtotime($request['request_date'])); ?></td>
            </tr>
            <tr>
                <td class="info-label">Status</td>
                <td class="info-separator">:</td>
                <td><span style="text-transform: uppercase; font-weight: bold; color: #2b6cb0;"><?php echo $request['status']; ?></span></td>
            </tr>
        </table>
    </div>

    <?php if (!empty($coil_details)): ?>
        <table class="coil-table">
            <thead>
                <tr>
                    <th width="5%" class="text-center">No</th>
                    <th width="18%">Kode Internal</th>
                    <th width="15%">No Coil</th>
                    <th width="27%">Material</th>
                    <th width="15%" class="text-center">Sumber Gudang</th>
                    <th width="10%" class="text-right">Plan Use</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($coil_details as $idx => $coil): ?>
                    <tr>
                        <td class="text-center" style="color: #718096;"><?php echo $idx + 1; ?></td>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($coil['kode_internal']); ?></td>
                        <td><?php echo htmlspecialchars($coil['no_coil']); ?></td>
                        <td><?php echo htmlspecialchars($coil['nm_material']); ?></td>
                        <td class="text-center">
                            <?php
                            if ($coil['id_gudang_sumber'] == 1) {
                                echo 'Gudang Coil';
                            } elseif ($coil['id_gudang_sumber'] == 3) {
                                echo 'WIP';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td class="text-right" style="font-weight: bold;"><?php echo number_format((float)$coil['plan_use'], 4); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="padding: 10px; border: 1px dashed #cbd5e0; color:#718096; font-style: italic; margin-top: 15px;">
            Tidak ada detail coil untuk SPK Coil ini.
        </div>
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
                        <span class="signature-name">&nbsp;</span>
                        <span class="signature-date"><?php echo date('d-m-Y', strtotime($request['request_date'])); ?></span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
