<!DOCTYPE html>
<html>
<head>
    <title>Print QR Label - New ROS</title>
    <style>
        @media print {
            .no-print { display: none; }
            .page-break { page-break-after: always; }
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
        }
        .info-table td {
            vertical-align: top;
            font-size: 11px;
            padding: 2px 4px;
        }
        .clear { clear: both; }
    </style>
</head>
<body onload="window.print();">
    <div class="no-print" style="background: #fdfd96; padding: 10px; margin-bottom: 20px;">
        <button onclick="window.print()">Print Now</button>
        <p><i>Make sure the label printer is ready. Press Print to print all selected labels.</i></p>
    </div>

    <?php foreach ($results as $row): ?>
        <div class="label-container">
            <div class="header-label">COIL LABEL</div>

            <div class="qr-code">
                <?php
                require_once APPPATH . 'third_party/phpqrcode/qrlib.php';

                $qr_content = $row['kode_internal'];

                ob_start();
                QRcode::png($qr_content, null, QR_ECLEVEL_M, 4, 1);
                $imageData = ob_get_contents();
                ob_end_clean();

                $base64 = base64_encode($imageData);
                ?>
                <img src="data:image/png;base64,<?= $base64 ?>" style="width: 120px; height: 120px;">
            </div>

            <table class="info-table">
                <tr>
                    <td><b>No. ROS</b></td>
                    <td>: <?= $row['no_ros'] ?></td>
                </tr>
                <tr>
                    <td><b>Supplier</b></td>
                    <td>: <?= $row['nm_supplier'] ?></td>
                </tr>
                <tr>
                    <td><b>Material</b></td>
                    <td>: <?= $row['nm_erp'] ?></td>
                </tr>
                <tr>
                    <td><b>Alias Name</b></td>
                    <td>: <?= $row['nm_alias'] ?></td>
                </tr>
                <tr>
                    <td><b>Coil No.</b></td>
                    <td>: <?= $row['no_coil'] ?></td>
                </tr>
                <tr>
                    <td><b>Internal Code</b></td>
                    <td>: <?= $row['kode_internal'] ?></td>
                </tr>
                <tr>
                    <td><b>Net Weight</b></td>
                    <td>: <?= number_format($row['berat_bersih'], 2) ?> Kg</td>
                </tr>
            </table>
            <div class="clear"></div>
        </div>
    <?php endforeach; ?>
</body>
</html>
