<!DOCTYPE html>
<html>

<head>
    <title>Print QR Label - Mutation <?= $mutation['mutation_number'] ?></title>
    <style>
        @media print {
            .no-print {
                display: none;
            }

            .page-break {
                page-break-after: always;
            }

            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 10px;
        }

        .label-container {
            width: 350px;
            border: 2px solid #000;
            padding: 10px;
            margin-bottom: 20px;
            display: inline-block;
            vertical-align: top;
            margin-right: 10px;
        }

        .header-label {
            text-align: center;
            font-weight: bold;
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
            padding-bottom: 5px;
        }

        .qr-code {
            float: right;
            margin-left: 10px;
            position: relative;
            display: inline-block;
        }

        .thickness-badge {
            position: absolute;
            bottom: -7px;
            right: 0;
            background-color: #fff;
            padding: 1px 6px;
            font-size: 7px;
            font-weight: bold;
            text-align: center;
        }

        .info-table td {
            vertical-align: top;
            font-size: 11px;
            padding: 2px 4px;
        }

        .clear {
            clear: both;
        }
    </style>
</head>

<body onload="window.print();">
    <div class="no-print" style="background: #fdfd96; padding: 10px; margin-bottom: 20px;">
        <button onclick="window.print()">Print Now</button>
        <button onclick="window.close()" style="margin-left: 10px;">Close</button>
        <p><i>Make sure the label printer is ready. Click Print to print all selected labels.</i></p>
    </div>

    <?php foreach ($results as $row): ?>
        <div class="label-container">
            <div class="header-label">COIL LABEL</div>

            <div class="qr-code">
                <?php
                require_once APPPATH . 'third_party/phpqrcode/qrlib.php';

                $kode_internal = $row['kode_internal'];
                if (!empty($row['incoming_date'])) {
                    $tgl_in = date('d-m-Y', strtotime($row['incoming_date']));
                } else {
                    $tgl_in = '-';
                }
                $gudang = !empty($row['nm_gudang_tujuan']) ? $row['nm_gudang_tujuan'] : '-';
                $qr_content = $kode_internal . '/' . $tgl_in . '/' . $gudang;

                ob_start();
                QRcode::png($qr_content, null, QR_ECLEVEL_M, 4, 1);
                $imageData = ob_get_contents();
                ob_end_clean();

                $base64 = base64_encode($imageData);
                ?>
                <img src="data:image/png;base64,<?= $base64 ?>" style="width: 120px; height: 120px; display: block;">

                <?php if (!empty($row['thickness'])): ?>
                    <div class="thickness-badge">
                        <?= $row['thickness'] ?>
                    </div>
                <?php endif; ?>
            </div>

            <table class="info-table">
                <tr>
                    <td><b>ROS No.</b></td>
                    <td>: <?= $row['no_ros'] ?></td>
                </tr>
                <tr>
                    <td><b>Name</b></td>
                    <td>:
                        <?php
                        $nama_asli = $row['trade_name'];

                        $search = ['NON BORON ', 'BORON ', 'AZ70-'];
                        $nama_bersih = str_replace($search, '', $nama_asli);
                        $nama_bersih = rtrim($nama_bersih, '-');

                        echo strtoupper($nama_bersih);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><b>Internal Code</b></td>
                    <td>: <?= $row['kode_internal'] ?></td>
                </tr>
                <tr>
                    <td><b>Net Weight</b></td>
                    <td>: <?= number_format((float)$row['berat_bersih'], 2) ?> Kg</td>
                </tr>
                <tr>
                    <td><b>Gross Weight</b></td>
                    <td>: <?= number_format((float)$row['berat_kotor'], 2) ?> Kg</td>
                </tr>
                <tr>
                    <td><b>Destination Warehouse</b></td>
                    <td>
                        : <?= !empty($row['nm_gudang_tujuan']) ? $row['nm_gudang_tujuan'] . ' (' . $row['kd_gudang_ke'] . ')' : '-' ?>

                        <?php if ($row['id_gudang_ke'] == 1): ?>
                            <span style="display: inline-block; width: 12px; height: 12px; background-color: black; border-radius: 50%; margin-left: 1px; vertical-align: middle;"></span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <div class="clear"></div>
        </div>
    <?php endforeach; ?>
</body>

</html>
