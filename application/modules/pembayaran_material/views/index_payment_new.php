<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">

<style>
    .nav-tabs .nav-link {
        color: #495057;
        font-weight: 500;
    }

    .nav-tabs .nav-link.active {
        font-weight: 600;
        border-bottom-color: #fff;
    }

    .table thead th {
        background-color: #f8f9fa !important;
        color: #333 !important;
        font-weight: 600;
        vertical-align: middle;
    }
</style>

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white pt-3 border-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-dark m-0"><?= $title; ?></h5>
        <button type="button" class="btn btn-sm btn-success choose_payment"><i class="fa fa-plus me-1"></i> Payment</button>
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs" id="paymentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="material-tab" data-bs-toggle="tab" data-bs-target="#material" type="button" role="tab" aria-controls="material" aria-selected="true">PR</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="non_material-tab" data-bs-toggle="tab" data-bs-target="#non_material" type="button" role="tab" aria-controls="non_material" aria-selected="false">Non PR</button>
            </li>
        </ul>

        <div class="tab-content pt-3" id="paymentTabsContent">

            <div class="tab-pane fade show active" id="material" role="tabpanel" aria-labelledby="material-tab">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered align-middle w-100" id="mytabledata">
                        <thead class="text-center">
                            <tr>
                                <th>No Payment</th>
                                <th>No Dokumen</th>
                                <th>Tgl Bayar</th>
                                <th>Requestor / Supplier</th>
                                <th>Nilai Bayar</th>
                                <th>Keterangan</th>
                                <th style="width: 100px;">Option</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($results)) :
                                foreach ($results as $item) : ?>
                                    <tr>
                                        <td class="text-center fw-semibold"><?= $item->id_payment; ?></td>
                                        <td class="text-center"><?= $item->no_surat; ?></td>
                                        <td class="text-center small"><?= date('d F Y', strtotime($item->tgl_bayar)); ?></td>
                                        <td><?= $item->nm_supplier; ?></td>
                                        <td class="text-end fw-semibold text-primary"><?= number_format($item->payment_bank, 2); ?></td>
                                        <td><?= $item->keterangan_pembayaran; ?></td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="<?= base_url('pembayaran_material/view_payment_new/' . $item->id_payment); ?>" target="_blank" class="btn btn-sm btn-info text-white" title="View Request Payment"><i class="fa fa-eye"></i></a>
                                                <?php if (!empty($item->link_doc) && file_exists('assets/expense/' . $item->link_doc)) : ?>
                                                    <a href="<?= base_url('assets/expense/' . $item->link_doc); ?>" class="btn btn-sm btn-primary" title="Download berkas"><i class="fa fa-download"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                            <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="non_material" role="tabpanel" aria-labelledby="non_material-tab">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered align-middle w-100" id="mytabledatanonmaterial">
                        <thead class="text-center">
                            <tr>
                                <th>No Payment</th>
                                <th>No Dokumen</th>
                                <th>Tgl Bayar</th>
                                <th>Requestor / Supplier</th>
                                <th>Nilai Bayar</th>
                                <th>Keterangan</th>
                                <th style="width: 100px;">Option</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($results2)) :
                                foreach ($results2 as $item) : ?>
                                    <tr>
                                        <td class="text-center fw-semibold"><?= $item->id_payment; ?></td>
                                        <td class="text-center"><?= $item->no_doc; ?></td>
                                        <td class="text-center small"><?= date('d F Y', strtotime($item->tgl_bayar)); ?></td>
                                        <td><?= $item->created_by; ?></td>
                                        <td class="text-end fw-semibold text-primary"><?= number_format($item->payment_bank, 2); ?></td>
                                        <td><?= $item->keterangan_pembayaran; ?></td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="<?= base_url('pembayaran_material/view_payment_new/' . $item->id_payment); ?>" target="_blank" class="btn btn-sm btn-info text-white" title="View Request Payment"><i class="fa fa-eye"></i></a>
                                                <?php if (!empty($item->link_doc) && file_exists('assets/expense/' . $item->link_doc)) : ?>
                                                    <a href="<?= base_url('assets/expense/' . $item->link_doc); ?>" class="btn btn-sm btn-primary" title="Download berkas"><i class="fa fa-download"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                            <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalLabel"><i class="fa fa-money me-2"></i>Pilih Jenis Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" data-shadow="none"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label fw-semibold small text-muted">Jenis Payment</label>
                    <select name="jenis_payment" class="form-select jenis_payment">
                        <option value="">- Silakan Pilih Jenis Payment -</option>
                        <option value="1">Pembayaran PR</option>
                        <option value="2">Pembayaran Non PR</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa fa-remove me-1"></i> Batal</button>
                <button type="button" class="btn btn-success confirm_jenis_payment"><i class="fa fa-check me-1"></i> Proses</button>
            </div>
        </div>
    </div>
</div>

<div id="form-data"></div>

<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        // Init DataTables 2.0+
        $("#mytabledata").DataTable({
            "order": [
                [0, "asc"]
            ],
            "pageLength": 10
        });

        $("#mytabledatanonmaterial").DataTable({
            "order": [
                [0, "asc"]
            ],
            "pageLength": 10
        });

        $("#form-data").hide();
    });

    // Pemicu Munculnya Pop-up Modal
    $(document).on('click', '.choose_payment', function() {
        $('#dialog-popup').modal('show');
    });

    // Eksekusi Konfirmasi Jenis Pembayaran (SweetAlert2 Terintegrasi)
    $(document).on('click', '.confirm_jenis_payment', function() {
        var jenis_payment = $('.jenis_payment').val();

        if (jenis_payment === '' || jenis_payment === null) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'Mohon pilih salah satu Jenis Payment terlebih dahulu!'
            });
        } else {
            if (jenis_payment == 1 || jenis_payment == 2) {
                window.location.href = siteurl + active_controller + 'list_request_payment/' + jenis_payment;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan sistem, silakan coba beberapa saat lagi.'
                });
            }
        }
    });
</script>