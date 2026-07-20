<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">

<style>
    .card-report {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .card-report .card-header {
        background: linear-gradient(135deg, #4472c4 0%, #2d5aa0 100%);
        border: none;
        padding: 18px 24px;
    }

    .card-report .card-header h4 {
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    .card-body {
        padding: 24px;
    }

    .filter-box {
        background: #f8f9fb;
        border-radius: 10px;
        padding: 18px 20px;
        margin-bottom: 20px;
        border: 1px solid #ebedf0;
    }

    .filter-box label {
        font-size: 13px;
        color: #495057;
        margin-bottom: 6px;
    }

    .filter-box .form-control {
        border-radius: 8px;
    }

    .btn-tampilkan,
    .btn-download-excel {
        border-radius: 8px;
        padding: 8px 20px;
        font-weight: 500;
    }

    /* Table styling */
    .table-report {
        font-size: 13px;
        border-collapse: collapse !important;
    }

    .table-report thead th {
        vertical-align: middle;
        text-align: center;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-color: #dee2e6 !important;
    }

    .group-po {
        background-color: #eef2fb !important;
        color: #2d3f6b !important;
    }

    .group-ros {
        background-color: #eef8f0 !important;
        color: #1e5c34 !important;
    }

    .group-invoice {
        background-color: #fdf3e6 !important;
        color: #8a5a1e !important;
    }

    .group-payment {
        background-color: #fdeaea !important;
        color: #8a2020 !important;
    }

    .table-report tbody td {
        vertical-align: middle;
        white-space: nowrap;
    }

    .table-report tbody td.text-end {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .po-group-start td {
        border-top: 2px solid #4472c4 !important;
    }

    .po-badge {
        display: inline-block;
        background: #4472c4;
        color: #fff;
        font-weight: 600;
        font-size: 11.5px;
        padding: 3px 10px;
        border-radius: 20px;
    }

    .category-badge {
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 6px;
    }

    .category-dp {
        background: #fff3cd;
        color: #856404;
    }

    .category-import {
        background: #d1ecf1;
        color: #0c5460;
    }

    .category-local {
        background: #d4edda;
        color: #155724;
    }

    .category-other {
        background: #e2e3e5;
        color: #383d41;
    }

    .selisih-value {
        color: #6c757d;
        font-weight: 500;
    }

    .selisih-zero {
        color: #adb5bd;
    }

    .table-report tbody tr:nth-child(even) {
        background-color: #fafbfc;
    }

    .filter-box .form-control {
        border-radius: 6px;
        font-size: 12.5px;
        padding: 4px 10px;
        height: 30px;
        line-height: 1.3;
    }

    .filter-box label {
        font-size: 12px;
        color: #495057;
        margin-bottom: 4px;
    }
</style>

<div class="card card-report">
    <div class="card-body">
        <form action="<?= site_url('report_payment_po') ?>" method="post" id="form-filter">
            <div class="filter-box row align-items-end g-2">
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm" name="no_po" placeholder="Masukkan Nomor PO..." autocomplete="off" value="<?= $this->input->post('no_po') ?>">
                </div>
                <div class="col-md-9">
                    <button type="submit" class="btn btn-primary btn-sm btn-tampilkan me-2">
                        <i class="fa fa-search"></i> Tampilkan
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-reset" id="btn-reset">
                        <i class="fa fa-rotate-left"></i> Reset
                    </button>
                    <button type="submit" formaction="<?= site_url('report_payment_po/download_excel') ?>" class="btn btn-success btn-sm btn-download-excel me-2" id="btn-download">
                        <i class="fa fa-download"></i> Download Excel
                    </button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-report table-sm mb-0">
                <thead>
                    <tr>
                        <th colspan="5" class="group-po">PO</th>
                        <th colspan="3" class="group-ros">ROS / Incoming</th>
                        <th colspan="4" class="group-invoice">Receive Invoice</th>
                        <th colspan="6" class="group-payment">Payment</th>
                    </tr>
                    <tr>
                        <th class="group-po">No. PO</th>
                        <th class="group-po">Tipe PO</th>
                        <th class="group-po">Value PO</th>
                        <th class="group-po">Tipe TOP</th>
                        <th class="group-po">Value %</th>
                        <th class="group-ros">Total Material</th>
                        <th class="group-ros">Unbill</th>
                        <th class="group-ros">Selisih Kurs</th>
                        <th class="group-invoice">Receive Invoice</th>
                        <th class="group-invoice">Kurs</th>
                        <th class="group-invoice">Value Receive</th>
                        <th class="group-invoice">Selisih Kurs</th>
                        <th class="group-payment">Invoice Pay</th>
                        <th class="group-payment">Kurs Pay</th>
                        <th class="group-payment">Currency Pay</th>
                        <th class="group-payment">Payment IDR</th>
                        <th class="group-payment">Admin</th>
                        <th class="group-payment">Selisih Kurs</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($results)) : ?>
                        <?php foreach ($results as $po_no => $items) : ?>
                            <?php
                            $is_first  = true;
                            $row_count = count($items); // jumlah baris untuk PO ini
                            ?>
                            <?php foreach ($items as $item) : ?>
                                <?php
                                if ($item['category'] == 'dp') {
                                    $category_label = 'Uang Muka';
                                    $category_class = 'category-dp';
                                } elseif ($item['category'] == 'import') {
                                    $category_label = 'Pelunasan (After ROS)';
                                    $category_class = 'category-import';
                                } elseif ($item['category'] == 'local') {
                                    $category_label = 'Pelunasan (After Incoming)';
                                    $category_class = 'category-local';
                                } else {
                                    $category_label = ucfirst($item['category']);
                                    $category_class = 'category-other';
                                }

                                $selisih_kurs_receive_1 = abs($item['selisih_kurs_receive_1']);
                                $selisih_kurs_receive_2 = abs($item['selisih_kurs_receive_2']);
                                $selisih_kurs_admin     = abs($item['selisih_kurs_admin']);

                                $row_class = $is_first ? 'po-group-start' : '';
                                ?>
                                <tr class="<?= $row_class ?>">
                                    <?php if ($is_first) : ?>
                                        <td rowspan="<?= $row_count ?>" class="text-center">
                                            <span class="po-badge"><?= $po_no ?></span>
                                        </td>
                                    <?php endif; ?>
                                    <td><span class="category-badge <?= $category_class ?>"><?= $category_label ?></span></td>
                                    <td class="text-end"><?= number_format($item['value_po'], 2) ?></td>
                                    <td><?= $item['tipe_top'] ?></td>
                                    <td class="text-end"><?= $item['value_pct'] ?></td>
                                    <td class="text-end"><?= number_format($item['total_material'], 2) ?></td>
                                    <td class="text-end"><?= number_format($item['unbill'], 2) ?></td>
                                    <td class="text-end"><span class="selisih-value <?= $selisih_kurs_receive_1 == 0 ? 'selisih-zero' : '' ?>"><?= number_format($selisih_kurs_receive_1, 2) ?></span></td>
                                    <td class="text-end"><?= number_format($item['receive_invoice_value'], 2) ?></td>
                                    <td class="text-end"><?= number_format($item['receive_kurs'], 2) ?></td>
                                    <td class="text-end"><?= number_format($item['value_receive_idr'], 2) ?></td>
                                    <td class="text-end"><span class="selisih-value <?= $selisih_kurs_receive_2 == 0 ? 'selisih-zero' : '' ?>"><?= number_format($selisih_kurs_receive_2, 2) ?></span></td>
                                    <td class="text-end"><?= number_format($item['invoice_pay'], 2) ?></td>
                                    <td class="text-end"><?= number_format($item['kurs_pay'], 2) ?></td>
                                    <td class="text-center"><?= $item['currency_pay'] ?></td>
                                    <td class="text-end"><?= number_format($item['payment_idr'], 2) ?></td>
                                    <td class="text-end"><?= number_format($item['admin_bank'], 2) ?></td>
                                    <td class="text-end"><span class="selisih-value <?= $selisih_kurs_admin == 0 ? 'selisih-zero' : '' ?>"><?= number_format($selisih_kurs_admin, 2) ?></span></td>
                                </tr>
                                <?php $is_first = false; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="18" class="text-center py-4 text-muted">
                                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                Tidak ada data ditemukan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#btn-download').on('click', function() {
            Swal.fire({
                title: 'Downloading...',
                text: 'Harap tunggu, sistem sedang memproses laporan Excel Anda.',
                icon: 'info',
                showConfirmButton: false,
                timer: 3000
            });
        });

        $('#btn-reset').on('click', function() {
            $('input[name="no_po"]').val('');
            $('#form-filter').attr('action', '<?= site_url('report_payment_po') ?>').trigger('submit');
        });
    });
</script>