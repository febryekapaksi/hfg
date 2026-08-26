<!DOCTYPE html>
<html>

<head>
    <title>Print QR Label - Mutation <?= $mutation['mutation_number'] ?></title>
    <style>
        @media print {
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }

        body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 10px; }

        .label-container {
            width: 380px; border: 2px solid #000; padding: 12px;
            margin-bottom: 20px; display: inline-block; vertical-align: top; margin-right: 10px;
        }

        .header-label {
            text-align: center; font-weight: bold; border-bottom: 1px solid #000;
            margin-bottom: 10px; padding-bottom: 5px; font-size: 14px;
        }

        .qr-code { float: right; margin-left: 10px; position: relative; }

        .thickness-badge {
            position: absolute; bottom: -5px; right: 0; background-color: #fff;
            padding: 1px 6px; font-size: 7px; font-weight: bold; text-align: center; border: 1px solid #ccc;
        }

        .info-table td { vertical-align: top; font-size: 11px; padding: 2px 4px; }
        .info-table td.label-td { font-weight: bold; white-space: nowrap; width: 110px; }
        .material-list { margin: 0; padding-left: 12px; font-size: 10px; }
        .material-list li { margin-bottom: 1px; }
        .clear { clear: both; }
    </style>
</head>

<body onload="window.print();">
    <div class="no-print" style="background: #fdfd96; padding: 10px; margin-bottom: 20px;">
        <button onclick="window.print()">Print Now</button>
        <button onclick="window.close()" style="margin-left: 10px;">Close</button>
        <p><i>Mutation: <?= $mutation['mutation_number'] ?> | <?= $mutation['nm_gudang_from'] ?> &rarr; <?= $mutation['nm_gudang_to'] ?></i></p>
    </div>

    <?php foreach ($results as $row): ?>
        <div class="label-container">
            <div class="header-label">PACK LABEL</div>

            <div class="qr-code">
                <?php
                require_once APPPATH . 'third_party/phpqrcode/qrlib.php';

                $qr_content = $row['pack_code'];

                ob_start();
                QRcode::png($qr_content, null, QR_ECLEVEL_M, 4, 1);
                $imageData = ob_get_contents();
                ob_end_clean();

                $base64 = base64_encode($imageData);
                ?>
                <img src="data:image/png;base64,<?= $base64 ?>" style="width: 120px; height: 120px; display: block;">

                <?php if (!empty($row['thicknesses'])): ?>
                    <div class="thickness-badge">
                        <?= implode(' + ', array_map('htmlspecialchars', $row['thicknesses'])) ?>
                    </div>
                <?php endif; ?>
            </div>

            <table class="info-table">
                <tr>
                    <td class="label-td">Pack No.</td>
                    <td>: <?= htmlspecialchars($row['pack_code']) ?></td>
                </tr>
                <tr>
                    <td class="label-td">Mutation</td>
                    <td>: <?= htmlspecialchars($mutation['mutation_number']) ?></td>
                </tr>
                <tr>
                    <td class="label-td">Material</td>
                    <td>:
                        <?php if (count($row['materials']) === 1): ?>
                            <?= htmlspecialchars($row['materials'][0]) ?>
                        <?php else: ?>
                            <ul class="material-list">
                                <?php foreach ($row['materials'] as $mat): ?>
                                    <li><?= htmlspecialchars($mat) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="label-td">Total Net Weight</td>
                    <td>: <?= number_format($row['total_nw'], 2) ?> Kg</td>
                </tr>
                <tr>
                    <td class="label-td">Total Gross Weight</td>
                    <td>: <?= number_format($row['total_gw'], 2) ?> Kg</td>
                </tr>
                <tr>
                    <td class="label-td">Dest. Warehouse</td>
                    <td>
                        : <?= !empty($row['nm_gudang_tujuan']) ? htmlspecialchars($row['nm_gudang_tujuan']) . ' (' . htmlspecialchars($row['kd_gudang_ke']) . ')' : '-' ?>
                        <?php if ($row['kd_gudang_ke'] === 'PRO'): ?>
                            <span style="display:inline-block; width:12px; height:12px; background-color:#000; border-radius:50%; margin-left:4px; vertical-align:middle;"></span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <div class="clear"></div>
        </div>
    <?php endforeach; ?>
</body>

</html>
