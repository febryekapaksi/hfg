<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Purchase Request</title>
    <style>
        @page {
            size: A4;
            margin: 5mm;
        }

        @media print {

            html,
            body {
                width: 210mm;
                height: 297mm;
                margin: 0;
                padding: 5mm;
                font-family: Arial, sans-serif;
                font-size: 16px;
            }

            table {
                border-collapse: collapse;
                width: 100%;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 4px;
            }

            .no-border td {
                border: none !important;
            }

            .text-right {
                text-align: right;
            }

            .text-center {
                text-align: center;
            }

            .bold {
                font-weight: bold;
            }

            table,
            tr,
            td,
            th {
                page-break-inside: avoid;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 15px;
            padding: 5mm;
            margin: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
        }

        .no-border td {
            border: none;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .h40 {
            height: 50px;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div style="display: flex;">
        <div class="text-left" style="width: 140px;">
            <img src="<?= base_url('assets/images/logo_sbf.png') ?>" alt="" width="100" height="60">
        </div>
        <div style="width: 60%;">
            <strong>PT Surya Bangun Fajar</strong><br>
            Jl. Kalijaga No.35 Kel. Pegambiran Kec. Lemahwungkuk<br>
            Kota Cirebon Jawa Barat 45113<br>
            Indonesia
        </div>
        <div style="width: 40%;">
            <h2 style="text-align:right; margin: 0;">PURCHASE REQUEST</h2>
            <table>
                <tr>
                    <td>Request Date : <br>
                        <?= date('d M Y', strtotime($header->tgl_so)) ?></td>
                    <td>Request Number : <br>
                        <?= $header->so_number ?></td>
                </tr>
            </table>
        </div>
    </div>
    <hr>

    <!-- Items -->
    <table class="mt-8">
        <thead>
            <tr class="text-center">
                <th style="width:10%;">Item</th>
                <th>Description</th>
                <th style="width:10%;">Qty</th>
                <th style="width:10%;">Item Unit</th>
                <th style="width:18%;">Last Purch Date</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            foreach ($detail as $det):
                $berat = ($det->berat != null) ? $det->berat . ' Kg,' : '';
                $harga = ($det->harga != null) ? '(Price Rp. ' . number_format($det->harga) . ')' : '';
            ?>
                <tr>
                    <td class="text-center"><?= $det->item ?></td>
                    <td><?= $det->product . ' ' . $berat . ' ' . $harga ?></td>
                    <td class="text-center"><?= $det->propose_purchase ?></td>
                    <td class="text-center"><?= strtoupper($det->satuan) ?></td>
                    <td class="text-center"><?= date('d M Y', strtotime($det->app_date)) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($detail)): ?>
                <tr>
                    <td colspan="6" class="text-center">No item data available</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br>
    <!-- Signature -->
    <div class="mt-8" style="display: flex;">
        <div style="
        width: 40%;
        border: 1px solid black;
        padding: 8px;
        min-height: 120px;
        box-sizing: border-box;">
            <strong>Description :</strong>
        </div>
        <div style="width: 60%;">
            <table class="no-border">
                <tr class="text-center">
                    <td>Prepared By,</td>
                    <td>Reviewed By,</td>
                    <td>Approved By,</td>
                </tr>
                <tr>
                    <td class="h40"></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="text-center">
                    <td><?= strtoupper($this->auth->user_name()) ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr class="text-left">
                    <td>Date :</td>
                    <td>Date :</td>
                    <td>Date :</td>
                </tr>
            </table>
        </div>
    </div>

</body>

</html>