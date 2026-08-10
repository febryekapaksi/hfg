<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<style>
    /* Styling Spreadsheet Premium */
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
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-align: center;
        vertical-align: middle;
        color: #333;
        padding: 8px 4px;
        border: 1px solid #dee2e6;
    }

    .table-spreadsheet td {
        padding: 4px;
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }

    .input-disabled {
        background-color: #e9ecef !important;
        /* Soft Gray */
        color: #495057;
        font-weight: 600;
        text-align: right;
        font-size: 13px;
        padding: 4px 6px;
        border: 1px solid #ced4da !important;
    }

    .product-cell {
        font-size: 12px;
        font-weight: 600;
        min-width: 200px;
        max-width: 250px;
        white-space: normal;
        word-wrap: break-word;
        background-color: #fff;
    }

    .product-code-badge {
        font-size: 10px;
        font-family: monospace;
        color: #6c757d;
    }

    .table-container {
        position: relative;
        max-height: 600px;
        overflow: auto;
        /* x & y sekaligus, karena sekarang ada kolom sticky kiri juga */
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }

    /* ============================================================
       STICKY NATIVE (position: sticky) — menggantikan pendekatan
       JS-transform lama. Browser (compositor thread) yang urus
       perpindahan header/kolom saat scroll, jadi jauh lebih smooth
       dan tidak "goyang" seperti pendekatan transform via JS.

       Offset top (baris header ke-2, krn rowspan) dan offset left
       (kolom sticky ke-2, krn lebar kolom pertama) dihitung sekali
       via JS saat load & resize — lihat initStickyOffsets() di
       bagian script, BUKAN tiap scroll.

       ::before dipakai sebagai layer background solid terpisah,
       karena background-color biasa pada <th>/<td> sticky sering
       "tembus" (konten di belakangnya kelihatan) akibat quirk
       rendering browser pada table cell + position:sticky.
       ============================================================ */
    .sticky-header th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa;
        box-shadow: inset 0 -1px 0 #dee2e6, inset 0 1px 0 #dee2e6;
    }

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

    /* Hilangkan spinner untuk Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Hilangkan spinner untuk Firefox */
    input[type=number] {
        -moz-appearance: textfield;
        appearance: textfield;
        /* Sintaks standar modern */
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
        <div class="d-flex gap-2">
            <a href="<?= base_url('master_rate_labor/process_product') ?>" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke List
            </a>
            <button type="button" id="btn-save-all" class="btn btn-success btn-sm fw-bold">
                <i class="fa fa-save me-1"></i> Save All
            </button>
        </div>
    </div>
    <div class="card-body">
        <form id="form-process-rates">
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
                            <th width="50">Bahan Pendukung Khusus</th>
                            <th width="50">Consumable</th>
                            <th width="100">FOH</th>
                            <th width="100">Cycle Time (mnt)</th>
                            <th width="100">MP</th>
                            <th width="100">Total Man Hour</th>
                            <th width="100">Man Hour Rate</th>
                            <th width="100">Kg/Pcs</th>
                            <th width="150">Gaji Direct</th>
                            <th width="100">% Rate Indirect</th>
                            <th width="150">Gaji Indirect</th>
                            <th width="110">Std. Biaya Gaji</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-products">
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $i => $row): ?>
                                <tr class="product-row" data-code="<?= htmlspecialchars($row->code_lv4) ?>">
                                    <td class="text-center text-muted font-monospace col-sticky-1" style="font-size: 11px;"><?= $i + 1 ?></td>
                                    <td class="product-cell col-sticky-2">
                                        <div class="product-name text-dark fw-bold"><?= htmlspecialchars($row->nm_produk) ?></div>
                                        <span class="product-code-badge"><?= htmlspecialchars($row->code_lv4) ?></span>
                                        <?php if ($row->trade_name): ?>
                                            <div class="text-muted small italic">(<?= htmlspecialchars($row->trade_name) ?>)</div>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Bahan Pendukung Khusus -->
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                            name="products[<?= htmlspecialchars($row->code_lv4) ?>][bahan_pendukung_khusus]"
                                            class="form-control form-control-sm input-yellow val-bahan"
                                            value="<?= (float) $row->bahan_pendukung_khusus ?>">
                                    </td>
                                    <!-- Consumable -->
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                            name="products[<?= htmlspecialchars($row->code_lv4) ?>][consumable]"
                                            class="form-control form-control-sm input-yellow val-consumable"
                                            value="<?= (float) $row->consumable ?>">
                                    </td>
                                    <!-- FOH -->
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                            name="products[<?= htmlspecialchars($row->code_lv4) ?>][foh]"
                                            class="form-control form-control-sm input-yellow val-foh"
                                            value="<?= (float) $row->foh ?>">
                                    </td>
                                    <!-- Cycle Time -->
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                            name="products[<?= htmlspecialchars($row->code_lv4) ?>][cycle_time]"
                                            class="form-control form-control-sm input-yellow val-cycle-time"
                                            value="<?= (float) $row->cycle_time ?>">
                                    </td>
                                    <!-- MP -->
                                    <td>
                                        <input type="number" step="1" min="0"
                                            name="products[<?= htmlspecialchars($row->code_lv4) ?>][mp]"
                                            class="form-control form-control-sm input-yellow val-mp"
                                            value="<?= (int) $row->mp ?>">
                                    </td>
                                    <!-- Total Man Hour -->
                                    <td>
                                        <input type="number" readonly
                                            name="products[<?= htmlspecialchars($row->code_lv4) ?>][total_man_hour]"
                                            class="form-control form-control-sm input-disabled val-total-man-hour"
                                            value="<?= (float) $row->total_man_hour ?>">
                                    </td>
                                    <!-- Man Hour Rate -->
                                    <td>
                                        <input type="number" readonly
                                            name="products[<?= htmlspecialchars($row->code_lv4) ?>][man_hour_rate]"
                                            class="form-control form-control-sm input-disabled val-man-hour-rate"
                                            value="<?= (float) $row->man_hour_rate ?>">
                                    </td>
                                    <!-- Kg / Pcs -->
                                    <td>
                                        <input type="number" step="0.0001" min="0"
                                            name="products[<?= htmlspecialchars($row->code_lv4) ?>][kg_pcs]"
                                            class="form-control form-control-sm input-yellow val-kg-pcs"
                                            value="<?= (float) $row->kg_pcs ?>">
                                    </td>
                                    <!-- Gaji Direct -->
                                    <td>
                                        <input type="number" readonly
                                            name="products[<?= htmlspecialchars($row->code_lv4) ?>][gaji_direct]"
                                            class="form-control form-control-sm input-disabled val-gaji-direct"
                                            value="<?= (float) $row->gaji_direct ?>">
                                    </td>
                                    <!-- % Rate Indirect -->
                                    <td>
                                        <input type="number" readonly
                                            name="products[<?= htmlspecialchars($row->code_lv4) ?>][rate_indirect]"
                                            class="form-control form-control-sm input-disabled val-rate-indirect"
                                            value="<?= (float) $row->rate_indirect ?>">
                                    </td>
                                    <!-- Gaji Indirect -->
                                    <td>
                                        <input type="number" readonly
                                            name="products[<?= htmlspecialchars($row->code_lv4) ?>][gaji_indirect]"
                                            class="form-control form-control-sm input-disabled val-gaji-indirect"
                                            value="<?= (float) $row->gaji_indirect ?>">
                                    </td>
                                    <!-- Standard Biaya Gaji (Rounded Only) -->
                                    <td>
                                        <input type="hidden"
                                            name="products[<?= htmlspecialchars($row->code_lv4) ?>][standard_biaya_gaji]"
                                            class="val-standard-biaya-gaji"
                                            value="<?= (float) $row->standard_biaya_gaji ?>">
                                        <input type="number" readonly
                                            class="form-control form-control-sm input-disabled val-standard-biaya-gaji-round"
                                            value="<?= (int) $row->standard_biaya_gaji_round ?>">
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
        </form>
    </div>
</div>

<!-- Load SweetAlert2 dynamically -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {

        // Simple Search Filter (keeping all items in DOM)
        $('#search-produk').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#tbody-products tr.product-row').filter(function() {
                var name = $(this).find('.product-name').text().toLowerCase();
                var code = $(this).find('.product-code-badge').text().toLowerCase();
                $(this).toggle(name.indexOf(value) > -1 || code.indexOf(value) > -1);
            });
        });

        // Recalculate row on input changes
        $(document).on('keyup change', '.val-cycle-time, .val-mp, .val-kg-pcs', function() {
            var row = $(this).closest('tr');
            calculateRow(row);
        });

        // Helper calculate logic
        function calculateRow(row) {
            var cycleTime = parseFloat(row.find('.val-cycle-time').val()) || 0;
            var mp = parseFloat(row.find('.val-mp').val()) || 0;
            var manHourRate = parseFloat(row.find('.val-man-hour-rate').val()) || 0;
            var kgPcs = parseFloat(row.find('.val-kg-pcs').val()) || 0;
            var rateIndirect = parseFloat(row.find('.val-rate-indirect').val()) || 0;

            // Total Man Hour = (cycle_time * MP) / 60
            var totalManHour = (cycleTime * mp) / 60.0;
            row.find('.val-total-man-hour').val(totalManHour.toFixed(6));

            // Gaji Direct = (total_man_hour * man_hour_rate) / kg_pcs
            var gajiDirect = 0;
            if (kgPcs > 0) {
                gajiDirect = (totalManHour * manHourRate) / kgPcs;
            }
            row.find('.val-gaji-direct').val(gajiDirect.toFixed(6));

            // Gaji Indirect = gaji_direct * (rate_indirect / 100)
            var gajiIndirect = gajiDirect * (rateIndirect / 100.0);
            row.find('.val-gaji-indirect').val(gajiIndirect.toFixed(6));

            // Std. Biaya Gaji = gaji_direct + gaji_indirect
            var stdBiayaGaji = gajiDirect + gajiIndirect;
            row.find('.val-standard-biaya-gaji').val(stdBiayaGaji.toFixed(6));

            // Std. Biaya Gaji Rounded = Math.round(stdBiayaGaji)
            var stdBiayaGajiRound = Math.round(stdBiayaGaji);
            row.find('.val-standard-biaya-gaji-round').val(stdBiayaGajiRound);
        }

        // Run initial calculations for all rows on page load
        $('#tbody-products tr.product-row').each(function() {
            calculateRow($(this));
        });

        // ============================================================
        // STICKY OFFSET INIT
        // Pakai position: sticky asli (CSS), jadi JS di sini HANYA
        // menghitung offset top/left sekali saat load & resize —
        // TIDAK ada lagi apa pun yang jalan pada event scroll (beda
        // dari versi lama yang pakai transform via JS tiap scroll,
        // yang bikin efek "goyang").
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

        var container = document.getElementById('table-container');
        if (container) {
            initStickyOffsets();
            window.addEventListener('resize', initStickyOffsets);
        }

        // Submit all data via AJAX
        $('#btn-save-all').on('click', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Simpan Perubahan?',
                text: 'Seluruh data tarif proses produk dalam tabel akan disimpan.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                // Form data serialization
                var formData = $('#form-process-rates').serialize();

                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Harap tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: siteurl + 'master_rate_labor/save_rate_process_product',
                    type: 'POST',
                    dataType: 'json',
                    data: formData,
                    success: function(res) {
                        if (res.status == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.pesan,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.href = siteurl + 'master_rate_labor/process_product';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res.pesan
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Request gagal: ' + xhr.status
                        });
                    }
                });
            });
        });
    });
</script>