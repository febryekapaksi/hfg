<?php
$ENABLE_MANAGE = has_permission('Approval_mutation.Manage');

$m       = $mutation ?? [];
$details = $m['details'] ?? [];

$status_map = [
    0 => ['Open',             'primary'],
    1 => ['Waiting Approval', 'warning'],
    2 => ['Approved',         'success'],
    3 => ['Rejected',         'danger'],
    5 => ['Cancelled',        'secondary'],
    6 => ['Revision',         'danger'],
];
$st = $status_map[$m['status']] ?? ['-', 'secondary'];
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card">
    <div class="card-body">

        <!-- Header Info -->
        <div class="p-3 bg-light border rounded mb-4 w-100">
            <div class="row align-items-center g-3 m-0">

                <div class="<?= empty($m['reject_reason']) ? 'col-12' : 'col-md-7 col-12' ?> p-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 w-100">

                        <div class="px-2 flex-fill">
                            <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Mutation No.</small>
                            <span class="fs-6 fw-bold text-dark"><?= $m['mutation_number'] ?></span>
                        </div>

                        <div class="px-2 flex-fill border-start-custom">
                            <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Request Date</small>
                            <span class="text-dark fw-semibold">
                                <?= date('d/m/Y', strtotime($m['mutation_date'])) ?>
                            </span>
                        </div>

                        <div class="px-2 flex-fill border-start-custom">
                            <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Requested By</small>
                            <span class="text-dark fw-semibold">
                                <?= !empty($m['create_by']) ? $m['create_by'] : '-' ?>
                            </span>
                        </div>

                        <div class="px-2 flex-fill border-start-custom">
                            <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Status</small>
                            <span class="badge bg-<?= $st[1] ?> px-2 py-1"><?= $st[0] ?></span>
                        </div>

                    </div>
                </div>

                <?php if (!empty($m['reject_reason'])): ?>
                    <div class="col-md-5 col-12 border-start-md ps-md-4 py-1 text-start">
                        <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Reject/Revision Reason</small>
                        <span class="text-danger fw-semibold">
                            <i class="fa-solid fa-circle-exclamation me-1"></i> <?= $m['reject_reason'] ?>
                        </span>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Detail Info -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label fw-bold">Minutes of Meeting No.</label>
                <p class="form-control-plaintext"><?= $m['no_berita_acara'] ?? '-' ?></p>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Source Warehouse</label>
                <p class="form-control-plaintext"><?= $m['nm_gudang_from'] ?></p>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Destination Warehouse</label>
                <p class="form-control-plaintext"><?= $m['nm_gudang_to'] ?></p>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-bold">Description / Mutation Reason</label>
                <p class="form-control-plaintext"><?= $m['description'] ?? '-' ?></p>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Minutes of Meeting File</label>
                <?php if (!empty($m['file_name_hash'])): ?>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-paperclip text-primary"></i>
                        <a href="<?= base_url('uploads/berita_acara_mutasi/' . $m['file_name_hash']) ?>"
                            target="_blank" class="text-truncate" style="max-width:250px;"
                            title="<?= $m['file_name_original'] ?>">
                            <?= $m['file_name_original'] ?>
                        </a>
                    </div>
                <?php else: ?>
                    <p class="form-control-plaintext text-muted">No file attached</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Approved Info (jika sudah di-approve) -->
        <?php if ($m['status'] == 2 && !empty($m['approved_by'])): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
                <i class="fa-solid fa-circle-check fs-5"></i>
                <div>
                    <strong>Approved by:</strong> <?= $m['approved_by'] ?>
                    <span class="ms-3"><strong>Date:</strong> <?= date('d/m/Y H:i', strtotime($m['approved_date'])) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Detail Pack -->
        <h6 class="mb-2"><i class="fa-solid fa-boxes-stacked me-1"></i> Pack Details</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle" id="tblDetailApproval">
                <thead class="table-light">
                    <tr>
                        <th width="3%">No</th>
                        <th class="text-center" width="12%">Pack Code</th>
                        <th>Materials</th>
                        <th class="text-center" width="5%">Roll</th>
                        <th class="text-end" width="10%">N.W. Total</th>
                        <th class="text-end" width="10%">G.W. Total</th>
                        <th class="text-center" width="6%">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($details)):
                        // Group details by pack
                        $pack_groups = [];
                        foreach ($details as $detail) {
                            $pk = $detail['pack_code'] ?: ($detail['id_warehouse_pack'] ?: 'unknown');
                            if (!isset($pack_groups[$pk])) {
                                $pack_groups[$pk] = [
                                    'pack_code' => $detail['pack_code'] ?: $pk,
                                    'id_warehouse_pack' => $detail['id_warehouse_pack'],
                                    'materials' => [],
                                    'roll_count' => 0,
                                    'total_nw' => 0,
                                    'total_gw' => 0,
                                    'coils' => [],
                                ];
                            }
                            $mat_name = $detail['trade_name'] ?: $detail['nm_material'];
                            if (!in_array($mat_name, $pack_groups[$pk]['materials'])) {
                                $pack_groups[$pk]['materials'][] = $mat_name;
                            }
                            foreach ($detail['coils'] ?? [] as $coil) {
                                $pack_groups[$pk]['roll_count']++;
                                $pack_groups[$pk]['total_nw'] += (float) $coil['net_weight'];
                                $pack_groups[$pk]['total_gw'] += (float) $coil['gross_weight'];
                                $pack_groups[$pk]['coils'][] = $coil;
                            }
                        }

                        $no = 1;
                        foreach ($pack_groups as $pg): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center"><span class="badge bg-primary"><?= htmlspecialchars($pg['pack_code']) ?></span></td>
                                <td>
                                    <?php foreach ($pg['materials'] as $mat): ?>
                                        <div style="font-size:11px;"><span class="text-primary me-1">&#9679;</span><b><?= htmlspecialchars($mat) ?></b></div>
                                    <?php endforeach; ?>
                                </td>
                                <td class="text-center"><?= $pg['roll_count'] ?></td>
                                <td class="text-end"><?= number_format($pg['total_nw'], 2, ',', '.') ?></td>
                                <td class="text-end"><?= number_format($pg['total_gw'], 2, ',', '.') ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-info btn-detail-pack" data-coils='<?= htmlspecialchars(json_encode($pg['coils'])) ?>' data-pack="<?= htmlspecialchars($pg['pack_code']) ?>">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No detail data</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="3" class="text-end">Total</td>
                        <td class="text-center">
                            <?php
                            $totalRoll = 0;
                            $totalNW = 0;
                            $totalGW = 0;
                            foreach ($pack_groups ?? [] as $pg2) {
                                $totalRoll += $pg2['roll_count'];
                                $totalNW += $pg2['total_nw'];
                                $totalGW += $pg2['total_gw'];
                            }
                            echo $totalRoll;
                            ?>
                        </td>
                        <td class="text-end"><?= number_format($totalNW, 2, ',', '.') ?></td>
                        <td class="text-end"><?= number_format($totalGW, 2, ',', '.') ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>


        <!-- Action Buttons -->
        <div class="mt-4 d-flex gap-2 justify-content-between">
            <a href="<?= site_url('approval_mutasi') ?>" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>

            <?php if ($m['status'] == 1 && $ENABLE_MANAGE): ?>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-info text-white" onclick="doRevision(<?= $m['id'] ?>)">
                        <i class="fa-solid fa-rotate-left"></i> Revision
                    </button>
                    <button type="button" class="btn btn-danger" onclick="doReject(<?= $m['id'] ?>)">
                        <i class="fa-solid fa-times"></i> Reject
                    </button>
                    <button type="button" class="btn btn-success" onclick="doApprove(<?= $m['id'] ?>)">
                        <i class="fa-solid fa-check"></i> Approve
                    </button>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Modal Detail Coil per Pack -->
<div class="modal fade" id="modalDetailPackApproval" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa fa-eye me-1"></i> Pack Detail — <span id="approvalPackCode"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="approvalPackBody" style="max-height: 70vh; overflow: auto;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    const BASE_URL = '<?= site_url('approval_mutasi') ?>';

    function doApprove(id) {
        Swal.fire({
            title: 'Approve Mutation?',
            html: 'Stock will be transferred to the destination warehouse immediately.<br><strong>This action cannot be undone.</strong>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-check"></i> Yes, Approve',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#28a745'
        }).then(result => {
            if (result.isConfirmed) {
                $.post(BASE_URL + '/approve/' + id, function(res) {
                    if (res.status == 1) {
                        Swal.fire({
                            title: 'Success',
                            text: res.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = BASE_URL;
                        });
                    } else {
                        Swal.fire('Failed', res.message, 'error');
                    }
                }, 'json').fail(function() {
                    Swal.fire('Error', 'A server error occurred.', 'error');
                });
            }
        });
    }

    function doReject(id) {
        Swal.fire({
            title: 'Reject Mutation?',
            html: '<p class="text-danger"><strong>This mutation will be permanently rejected.</strong></p>',
            input: 'textarea',
            inputPlaceholder: 'Enter rejection reason...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-times"></i> Yes, Reject',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545',
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return 'Rejection reason is required!';
                }
            }
        }).then(result => {
            if (result.isConfirmed && result.value) {
                $.post(BASE_URL + '/reject/' + id, {
                    reject_reason: result.value.trim()
                }, function(res) {
                    if (res.status == 1) {
                        Swal.fire({
                            title: 'Success',
                            text: res.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = BASE_URL;
                        });
                    } else {
                        Swal.fire('Failed', res.message, 'error');
                    }
                }, 'json').fail(function() {
                    Swal.fire('Error', 'A server error occurred.', 'error');
                });
            }
        });
    }

    function doRevision(id) {
        Swal.fire({
            title: 'Return for Revision?',
            html: '<p>The request will be returned to the requester for correction.</p>',
            input: 'textarea',
            inputPlaceholder: 'Enter revision notes/points...',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-rotate-left"></i> Yes, Request Revision',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#17a2b8',
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return 'Revision notes are required!';
                }
            }
        }).then(result => {
            if (result.isConfirmed && result.value) {
                $.post(BASE_URL + '/revision/' + id, {
                    reject_reason: result.value.trim()
                }, function(res) {
                    if (res.status == 1) {
                        Swal.fire({
                            title: 'Success',
                            text: res.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = BASE_URL;
                        });
                    } else {
                        Swal.fire('Failed', res.message, 'error');
                    }
                }, 'json').fail(function() {
                    Swal.fire('Error', 'A server error occurred.', 'error');
                });
            }
        });
    }

    // Detail pack modal — gunakan data-bs-toggle approach
    $(document).on('click', '.btn-detail-pack', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var coils = JSON.parse($(this).attr('data-coils'));
        var packCode = $(this).data('pack');
        $('#approvalPackCode').text(packCode);

        if (!coils || coils.length === 0) {
            $('#approvalPackBody').html('<div class="text-center text-muted py-4">No coil data.</div>');
        } else {
            var html = '<table class="table table-bordered table-sm table-striped" style="font-size:11px;"><thead class="table-light"><tr><th class="text-center">No</th><th>No. Coil</th><th>Internal Code</th><th class="text-end">N.W. (Kg)</th><th class="text-end">G.W. (Kg)</th><th class="text-end">Length (M)</th></tr></thead><tbody>';
            var tNw = 0,
                tGw = 0;
            coils.forEach(function(c, i) {
                tNw += parseFloat(c.net_weight || 0);
                tGw += parseFloat(c.gross_weight || 0);
                html += '<tr><td class="text-center">' + (i + 1) + '</td><td>' + (c.no_coil || '-') + '</td><td>' + (c.kode_internal || '-') + '</td><td class="text-end">' + parseFloat(c.net_weight || 0).toLocaleString('id-ID', {
                    minimumFractionDigits: 2
                }) + '</td><td class="text-end">' + parseFloat(c.gross_weight || 0).toLocaleString('id-ID', {
                    minimumFractionDigits: 2
                }) + '</td><td class="text-end">' + parseFloat(c.length || 0).toLocaleString('id-ID', {
                    minimumFractionDigits: 2
                }) + '</td></tr>';
            });
            html += '</tbody><tfoot class="table-secondary"><tr><td colspan="3" class="text-end fw-bold">Total</td><td class="text-end fw-bold">' + tNw.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            }) + '</td><td class="text-end fw-bold">' + tGw.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            }) + '</td><td></td></tr></tfoot></table>';
            $('#approvalPackBody').html(html);
        }

        $('#modalDetailPackApproval').modal('show');
    });
</script>