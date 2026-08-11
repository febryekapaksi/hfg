<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<style>
    /* Styling Spreadsheet Premium - Read Only */
    .table-spreadsheet {
        table-layout: auto;
        border-collapse: separate;
        border-spacing: 0;
        width: max-content;
        min-width: 100%;
        /* PENTING: sticky mati kalau <table> sendiri kena overflow:hidden
           dari CSS global/template (mis. rule umum utk semua .table).
           Override paksa di sini supaya containing block-nya bersih. */
        overflow: visible !important;
    }

    .table-spreadsheet th {
        font-size: 10.5px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-align: center;
        vertical-align: middle;
        background-color: #f8f9fa;
        color: #333;
        padding: 6px 5px;
        border: 1px solid #dee2e6;
        white-space: normal;
        /* boleh wrap agar kolom tidak melebar mengikuti teks header */
        line-height: 1.25;
        max-width: 95px;
    }

    .table-spreadsheet td {
        padding: 5px 8px;
        border: 1px solid #dee2e6;
        vertical-align: middle;
        font-size: 12px;
        white-space: nowrap;
    }

    .product-cell {
        white-space: normal !important;
        min-width: 170px;
        max-width: 220px;
        font-size: 12px;
        font-weight: 600;
        word-wrap: break-word;
    }

    .table-container {
        position: relative;
        max-height: 600px;
        overflow: auto;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }

    /* ============================================================
       STICKY NATIVE (position: sticky) — menggantikan pendekatan
       JS-transform lama. Browser (compositor thread) yang urus
       perpindahan header/kolom saat scroll, jadi jauh lebih smooth
       dan tidak "goyang" seperti pendekatan transform via JS.

       Dua hal yang perlu dihitung sekali via JS saat load (BUKAN
       tiap event scroll):
       1) `top` untuk baris header ke-2 (karena baris ke-1 pakai rowspan)
       2) `left` untuk kolom sticky ke-2 (karena lebar kolom pertama
          bisa berubah-ubah tergantung isi data)
       ============================================================ */
    .sticky-header th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa;
        box-shadow: inset 0 -1px 0 #dee2e6, inset 0 1px 0 #dee2e6;
    }

    /* PENTING: background-color biasa di <th>/<td> sticky sering "tembus"
       (konten di belakangnya kelihatan) karena quirk rendering browser
       pada table cell + position:sticky. Solusinya kasih layer solid
       terpisah pakai ::before yang menutupi penuh area cell. */
    .sticky-header th::before {
        content: "";
        position: absolute;
        inset: 0;
        background-color: #f8f9fa;
        z-index: -1;
    }

    .col-sticky-1,
    .col-sticky-2 {
        position: sticky;
        left: 0;
        /* left col-sticky-2 di-override via JS = lebar col-sticky-1 */
        z-index: 5;
        background-color: #fff;
    }

    .col-sticky-1::before,
    .col-sticky-2::before {
        content: "";
        position: absolute;
        inset: 0;
        background-color: #fff;
        z-index: -1;
    }

    thead .col-sticky-1,
    thead .col-sticky-2 {
        /* corner cell: harus di atas header row (z-index 10) & kolom lain */
        z-index: 20;
        background-color: #f8f9fa;
    }

    thead .col-sticky-1::before,
    thead .col-sticky-2::before {
        background-color: #f8f9fa;
    }

    .cell-numeric {
        text-align: right;
        font-family: monospace;
        font-weight: 500;
        min-width: 88px;
    }

    .cell-disabled-gray {
        --bs-table-bg: #f8f9fa;
        background-color: #f8f9fa !important;
        color: #495057;
    }

    .cell-journal-green {
        --bs-table-bg: #ebfbee;
        background-color: #ebfbee !important;
        color: #2b8a3e;
        font-weight: 700 !important;
        text-align: right;
        font-family: monospace;
        min-width: 105px;
    }

    .product-code-badge {
        font-size: 10px;
        font-family: monospace;
        color: #6c757d;
    }
</style>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <div class="d-flex align-items-center">
            <div class="input-group input-group-sm" style="width: 400px;">
                <span class="input-group-text bg-light text-muted"><i class="fa fa-search"></i></span>
                <input type="text" id="search-produk" class="form-control" placeholder="Cari produk...">
            </div>
        </div>
        <div class="d-flex">
            <a href="<?= base_url('master_rate_labor/process_product_form') ?>" class="btn btn-warning text-white btn-sm fw-bold">
                <i class="fa fa-edit me-1"></i> Update Data
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-container" id="table-container">
            <table class="table table-bordered table-spreadsheet mb-0 align-middle">
                <thead class="sticky-header">
                    <tr>
                        <th rowspan="2" width="40" class="col-sticky-1">No</th>
                        <th rowspan="2" class="col-sticky-2">Produk</th>
                        <th colspan="3">Standard Biaya (Rp/Kg)</th>
                        <th colspan="9">Standard Biaya Gaji (Rp/Kg)</th>
                    </tr>
                    <tr>
                        <th>Bahan Pendukung Khusus</th>
                        <th style="white-space:nowrap;">Consumable</th>
                        <th>FOH</th>
                        <th>Cycle Time (mnt)</th>
                        <th>MP</th>
                        <th>Total Man Hour</th>
                        <th>Man Hour Rate</th>
                        <th>Kg/Pcs</th>
                        <th>Gaji Direct</th>
                        <th>% Rate Indirect</th>
                        <th>Gaji Indirect</th>
                        <th>Std. Biaya Gaji</th>
                    </tr>
                </thead>
                <tbody id="tbody-products">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $i => $row): ?>
                            <tr class="product-row">
                                <td class="text-center text-muted font-monospace col-sticky-1" style="font-size: 11px;"><?= $i + 1 ?></td>
                                <td class="product-cell col-sticky-2">
                                    <div class="product-name text-dark fw-bold"><?= htmlspecialchars($row->nm_produk) ?></div>
                                    <span class="product-code-badge"><?= htmlspecialchars($row->code_lv4) ?></span>
                                    <?php if ($row->trade_name): ?>
                                        <div class="text-muted small italic">(<?= htmlspecialchars($row->trade_name) ?>)</div>
                                    <?php endif; ?>
                                </td>
                                <!-- Bahan Pendukung -->
                                <td class="cell-numeric">
                                    Rp <?= number_format($row->bahan_pendukung_khusus, 2, ',', '.') ?>
                                </td>
                                <!-- Consumable -->
                                <td class="cell-numeric">
                                    Rp <?= number_format($row->consumable, 2, ',', '.') ?>
                                </td>
                                <!-- FOH -->
                                <td class="cell-numeric">
                                    Rp <?= number_format($row->foh, 2, ',', '.') ?>
                                </td>
                                <!-- Cycle Time -->
                                <td class="cell-numeric">
                                    <?= number_format($row->cycle_time, 2, ',', '.') ?>
                                </td>
                                <!-- MP -->
                                <td class="text-center font-monospace">
                                    <?= number_format($row->mp, 0, ',', '.') ?>
                                </td>
                                <!-- Total Man Hour -->
                                <td class="cell-numeric cell-disabled-gray">
                                    <?= number_format($row->total_man_hour, 4, ',', '.') ?>
                                </td>
                                <!-- Man Hour Rate -->
                                <td class="cell-numeric cell-disabled-gray">
                                    Rp <?= number_format($row->man_hour_rate, 0, ',', '.') ?>
                                </td>
                                <!-- Kg / Pcs -->
                                <td class="cell-numeric cell-disabled-gray">
                                    <?php
                                    $val_kg = (float)($row->kg_pcs ?? 0);
                                    $has_val = $val_kg > 0;
                                    ?>

                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <span class="<?= !$has_val ? 'text-danger fw-bold' : '' ?>">
                                            <?= number_format($val_kg, 4, ',', '.') ?>
                                        </span>

                                        <?php if (!$has_val): ?>
                                            <span class="text-danger"
                                                style="cursor: pointer; font-size: 12px;"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="Weight 0. Silakan isi weight di Master Product!">
                                                <i class="fas fa-solid fa-exclamation"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <!-- Gaji Direct -->
                                <td class="cell-numeric cell-disabled-gray">
                                    Rp <?= number_format($row->gaji_direct, 4, ',', '.') ?>
                                </td>
                                <!-- % Rate Indirect -->
                                <td class="text-center font-monospace cell-disabled-gray">
                                    <?= number_format($row->rate_indirect, 2, ',', '.') ?>%
                                </td>
                                <!-- Gaji Indirect -->
                                <td class="cell-numeric cell-disabled-gray">
                                    Rp <?= number_format($row->gaji_indirect, 4, ',', '.') ?>
                                </td>
                                <!-- Standard Biaya Gaji (Rounded) -->
                                <td class="cell-journal-green">
                                    Rp <?= number_format($row->standard_biaya_gaji_round, 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="14" class="text-center text-muted py-3">Produk tidak ditemukan atau belum aktif.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Simple Search Filter
        $('#search-produk').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#tbody-products tr.product-row').filter(function() {
                var name = $(this).find('.product-name').text().toLowerCase();
                var code = $(this).find('.product-code-badge').text().toLowerCase();
                $(this).toggle(name.indexOf(value) > -1 || code.indexOf(value) > -1);
            });
        });

        // ============================================================
        // STICKY OFFSET INIT
        // Pakai position: sticky asli (CSS), jadi JS di sini HANYA
        // menghitung offset top/left sekali saat load & resize —
        // TIDAK ada lagi apa pun yang jalan pada event scroll.
        // Ini yang bikin sticky-nya smooth (dikerjakan browser
        // compositor thread), beda dari versi lama yang pakai
        // transform via JS pada tiap scroll event.
        // ============================================================
        function initStickyOffsets() {
            var container = document.getElementById('table-container');
            var table = container && container.querySelector('table');
            if (!table) return;

            var thead = table.querySelector('thead');
            var row1 = thead.querySelector('tr:first-child');
            var row2 = thead.querySelector('tr:last-child');

            // Offset top untuk baris header ke-2 (krn baris ke-1 pakai rowspan,
            // jadi baris ke-2 secara visual mulai di bawah baris ke-1)
            if (row1 && row2 && row1 !== row2) {
                var offsetTop = row2.getBoundingClientRect().top - row1.getBoundingClientRect().top;
                row2.querySelectorAll('th').forEach(function(th) {
                    th.style.top = offsetTop + 'px';
                });
            }

            // Offset left untuk kolom sticky ke-2 (Produk), supaya nempel
            // persis di sebelah kanan kolom sticky ke-1 (No) yang lebarnya
            // bisa berubah tergantung konten / breakpoint.
            var col1 = table.querySelector('.col-sticky-1');
            if (col1) {
                var col1Width = col1.getBoundingClientRect().width;
                table.querySelectorAll('.col-sticky-2').forEach(function(el) {
                    el.style.left = col1Width + 'px';
                });
            }
        }

        initStickyOffsets();
        window.addEventListener('resize', initStickyOffsets);
    });
</script>