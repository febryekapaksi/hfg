<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-end py-3">
        <a href="<?= base_url('master_rate_labor/process_product') ?>" class="btn btn-primary btn-sm">
            <i class="fa fa-cogs me-1"></i> Master Rate Process Product
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tbl-rates" width="100%">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>Item</th>
                        <th class="text-end">Rate / Tarif</th>
                        <th>Remark</th>
                        <th>Update By</th>
                        <th class="text-center">Update Date</th>
                        <th class="text-center" width="120">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rates)): ?>
                        <?php foreach ($rates as $i => $row): ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($row->item) ?></td>
                                <td class="text-end fw-bold text-success">
                                    <?php if ($row->type === 'direct'): ?>
                                        Rp <?= number_format($row->rate, 0, ',', '.') ?> / Jam
                                    <?php else: ?>
                                        <?= number_format($row->rate, 2, ',', '.') ?> %
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row->remark ?: '-') ?></td>
                                <td><?= htmlspecialchars($row->updated_by ?: '-') ?></td>
                                <td class="text-center">
                                    <?= $row->updated_date ? date('d-M-y H:i', strtotime($row->updated_date)) : '-' ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-warning edit-rate me-1" data-id="<?= $row->id ?>" title="Edit Tarif">
                                        <i class="fa fa-edit me-1"></i>Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Belum ada data tarif labor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Dialog -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="editLaborRateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-edit-rate" autocomplete="off">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title fw-bold" id="head_title">
                        <i class="fa fa-edit me-2"></i>Edit Labor Rate
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="ModalView">
                    <!-- Loaded via AJAX -->
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times me-1"></i> Batal
                    </button>
                    <button type="button" id="btn-save-rate" class="btn btn-warning text-white fw-bold">
                        <i class="fa fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Load SweetAlert2 and Styles dynamically -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // Open Edit Modal
        $(document).on('click', '.edit-rate', function() {
            var id = $(this).data('id');
            $.ajax({
                url: siteurl + 'master_rate_labor/edit_rate/' + id,
                type: 'GET',
                success: function(data) {
                    $('#ModalView').html(data);
                    $('#dialog-popup').modal('show');
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal memuat form edit'
                    });
                }
            });
        });

        // Save Rate
        $(document).on('click', '#btn-save-rate', function(e) {
            e.preventDefault();
            var id = $('#rate-id').val();
            var rate = $('#rate-val').val();
            var remark = $('#remark-val').val();

            if (rate === '' || isNaN(rate)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Tarif/Nilai rate harus berupa angka!'
                });
                return;
            }

            $.ajax({
                url: siteurl + 'master_rate_labor/save_rate',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: id,
                    rate: rate,
                    remark: remark
                },
                success: function(res) {
                    if (res.status == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.pesan,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            $('#dialog-popup').modal('hide');
                            window.location.reload();
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
</script>