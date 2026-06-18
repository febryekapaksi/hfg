<?php
$ENABLE_MANAGE = has_permission('Gl_interface.Manage');
$is_pending    = ($header['status'] === 'pending' || $header['status'] === 'error');
$memo          = !empty($header['memo']) ? json_decode($header['memo'], true) : [];

$total_debet  = 0;
$total_kredit = 0;
foreach ($details as $d) {
    $total_debet  += (float) $d['debet'];
    $total_kredit += (float) $d['kredit'];
}
$is_balance = (round($total_debet) === round($total_kredit));
?>

<style>
    .swal2-container.swal2-center {
        z-index: 9999 !important;
    }

    .badge-status {
        font-size: 13px;
        padding: 5px 12px;
        border-radius: 4px;
    }

    .badge-pending {
        background: #ffc107;
        color: #333;
    }

    .badge-posted {
        background: #28a745;
        color: #fff;
    }

    .badge-error {
        background: #dc3545;
        color: #fff;
    }

    .info-label {
        font-weight: 600;
        color: #555;
    }

    .summary-card {
        border-left: 4px solid #0d6efd;
    }

    .table-jurnal th {
        background: #f8f9fa;
    }
</style>

<div class="container-fluid mt-3">
    <!-- BACK BUTTON -->
    <div class="mb-3">
        <a href="<?= base_url('gl_interface') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
    </div>

    <!-- HEADER INFO -->
    <div class="card shadow-sm mb-3 summary-card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-file-text-o me-2"></i> GL Interface Detail — <?= !empty($header['nomor']) ? htmlspecialchars($header['nomor']) : '<span class="text-muted">Number not yet generated</span>' ?></h5>
            <span class="badge badge-status badge-<?= $header['status'] ?>"><?= strtoupper($header['status']) ?></span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <span class="info-label">Number</span><br>
                    <?= !empty($header['nomor']) ? htmlspecialchars($header['nomor']) : '<span class="text-warning"><i class="fa fa-clock-o"></i> Will be generated upon posting</span>' ?>
                </div>
                <div class="col-md-2 mb-2">
                    <span class="info-label">Date</span><br>
                    <?= htmlspecialchars($header['tgl']) ?>
                </div>
                <div class="col-md-2 mb-2">
                    <span class="info-label">Type</span><br>
                    <?= htmlspecialchars($header['jenis']) ?>
                </div>
                <div class="col-md-2 mb-2">
                    <span class="info-label">Transaction Type</span><br>
                    <?= ucfirst(htmlspecialchars($header['jenis_transaksi'])) ?>
                </div>
                <div class="col-md-3 mb-2">
                    <span class="info-label">User</span><br>
                    <?= htmlspecialchars($header['nm_lengkap'] ?? $header['user_id']) ?>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6 mb-2">
                    <span class="info-label">Description</span><br>
                    <?= htmlspecialchars($header['keterangan']) ?>
                </div>
                <?php if (!empty($memo['nama_supplier'])): ?>
                    <div class="col-md-3 mb-2">
                        <span class="info-label">Supplier</span><br>
                        <?= htmlspecialchars($memo['nama_supplier']) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($memo['no_reff'])): ?>
                    <div class="col-md-3 mb-2">
                        <span class="info-label">No Reff</span><br>
                        <?= htmlspecialchars($memo['no_reff']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($header['error_msg'])): ?>
                <div class="alert alert-danger mt-2 mb-0">
                    <i class="fa fa-exclamation-triangle"></i> <strong>Error:</strong> <?= htmlspecialchars($header['error_msg']) ?>
                </div>
            <?php endif; ?>

            <?php if ($header['status'] === 'posted' && !empty($header['posted_at'])): ?>
                <div class="alert alert-success mt-2 mb-0">
                    <i class="fa fa-check-circle"></i> Posted on: <?= htmlspecialchars($header['posted_at']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- DETAIL JURNAL TABLE -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="fa fa-list me-1"></i> Journal Detail</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-jurnal mb-0">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th>COA</th>
                            <th>COA Name</th>
                            <th>Description</th>
                            <th>No Reff</th>
                            <th>No Request</th>
                            <th class="text-end">Debet</th>
                            <th class="text-end">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($details as $d): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td style="white-space: nowrap;"><?= htmlspecialchars($d['no_perkiraan']) ?></td>
                                <td><?= htmlspecialchars($d['nama_coa'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($d['keterangan'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($d['no_reff'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($d['no_request'] ?? '-') ?></td>
                                <td class="text-end"><?= number_format($d['debet'], 0, ',', '.') ?></td>
                                <td class="text-end"><?= number_format($d['kredit'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold table-light">
                            <td colspan="6" class="text-end">TOTAL</td>
                            <td class="text-end"><?= number_format($total_debet, 0, ',', '.') ?></td>
                            <td class="text-end"><?= number_format($total_kredit, 0, ',', '.') ?></td>
                        </tr>
                        <?php if (!$is_balance): ?>
                            <tr class="text-danger">
                                <td colspan="6" class="text-end">VARIANCE</td>
                                <td colspan="2" class="text-end fw-bold"><?= number_format(abs($total_debet - $total_kredit), 0, ',', '.') ?></td>
                            </tr>
                        <?php endif; ?>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- ACTION BUTTONS -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="<?= base_url('gl_interface') ?>" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back
        </a>

        <?php if ($ENABLE_MANAGE && $is_pending): ?>
            <?php if (!$is_balance): ?>
                <div class="alert alert-warning mb-0 py-2 px-3">
                    <i class="fa fa-warning"></i> Debit and Credit are not balanced. Cannot post.
                </div>
            <?php else: ?>
                <button type="button" class="btn btn-success btn-lg" id="btnPostAccounting">
                    <i class="fa fa-paper-plane"></i> Post ke Accounting
                </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#btnPostAccounting').on('click', function() {
            var btn = $(this);
            Swal.fire({
                title: 'Post to Accounting?',
                html: 'Number: <strong><?= htmlspecialchars($header['nomor']) ?></strong><br>Make sure the journal data is correct before posting.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: '<i class="fa fa-paper-plane"></i> Yes, Post Now!',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                    Swal.fire({
                        title: 'Processing...',
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });

                    $.post('<?= base_url("gl_interface/post") ?>', {
                        id: '<?= $header['id'] ?>'
                    }, function(res) {
                        if (res.status == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: res.pesan,
                                showConfirmButton: true,
                                confirmButtonText: 'OK'
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: res.pesan
                            });
                            btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Post to Accounting');
                        }
                    }, 'json').fail(function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'A server error occurred'
                        });
                        btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Post to Accounting');
                    });
                }
            });
        });
    });
</script>