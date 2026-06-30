<?php
$ENABLE_MANAGE = has_permission('Approval_mutasi.Manage');

$m       = $mutation ?? [];
$details = $m['details'] ?? [];

$status_map = [
    0 => ['Open',             'primary'],
    1 => ['Menunggu Approve', 'warning'],
    2 => ['Approved',         'success'],
    3 => ['Rejected',         'danger'],
    5 => ['Cancelled',        'secondary'],
    6 => ['Revisi',           'danger'],
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
                            <small class="text-muted d-block text-uppercase font-size-xs fw-bold">No. Mutasi</small>
                            <span class="fs-6 fw-bold text-dark"><?= $m['mutation_number'] ?></span>
                        </div>

                        <div class="px-2 flex-fill border-start-custom">
                            <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Tanggal Pengajuan</small>
                            <span class="text-dark fw-semibold">
                                <?= date('d/m/Y', strtotime($m['mutation_date'])) ?>
                            </span>
                        </div>

                        <div class="px-2 flex-fill border-start-custom">
                            <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Pengajuan Oleh</small>
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
                        <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Alasan Reject/Revisi</small>
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
                <label class="form-label fw-bold">No. Berita Acara</label>
                <p class="form-control-plaintext"><?= $m['no_berita_acara'] ?? '-' ?></p>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Gudang Asal</label>
                <p class="form-control-plaintext"><?= $m['nm_gudang_from'] ?></p>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Gudang Tujuan</label>
                <p class="form-control-plaintext"><?= $m['nm_gudang_to'] ?></p>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-bold">Keterangan / Alasan Mutasi</label>
                <p class="form-control-plaintext"><?= $m['description'] ?? '-' ?></p>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">File Berita Acara</label>
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
                    <p class="form-control-plaintext text-muted">Tidak ada file</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Approved Info (jika sudah di-approve) -->
        <?php if ($m['status'] == 2 && !empty($m['approved_by'])): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
                <i class="fa-solid fa-circle-check fs-5"></i>
                <div>
                    <strong>Diapprove oleh:</strong> <?= $m['approved_by'] ?>
                    <span class="ms-3"><strong>Tanggal:</strong> <?= date('d/m/Y H:i', strtotime($m['approved_date'])) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Detail Material & Coil -->
        <h6 class="mb-2">Detail Material & Coil</h6>
        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="tblDetailApproval">
                <thead class="table-light">
                    <tr>
                        <th>Material</th>
                        <th>Kode Internal (Coil)</th>
                        <th>No. Coil</th>
                        <th>No. IPP</th>
                        <th width="130">Net Weight (kg)</th>
                        <th width="130">Length (m)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($details)): ?>
                        <?php foreach ($details as $detail): ?>
                            <?php $coils = $detail['coils'] ?? []; ?>
                            <?php if (empty($coils)): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= $detail['nm_material'] ?></div>
                                        <?php if (!empty($detail['trade_name'])): ?>
                                            <small class="text-muted"><?= $detail['trade_name'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td colspan="5" class="text-center text-muted">Tidak ada coil</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($coils as $idx => $coil): ?>
                                    <tr>
                                        <?php if ($idx === 0): ?>
                                            <td rowspan="<?= count($coils) ?>">
                                                <div class="fw-bold"><?= $detail['nm_material'] ?></div>
                                                <?php if (!empty($detail['trade_name'])): ?>
                                                    <small class="text-muted"><?= $detail['trade_name'] ?></small>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                        <td><span class="badge bg-light text-dark border"><?= $coil['kode_internal'] ?? '-' ?></span></td>
                                        <td><strong><?= $coil['no_coil'] ?></strong></td>
                                        <td><?= $coil['no_ipp'] ?? '-' ?></td>
                                        <td><?= number_format((float)$coil['net_weight'], 2, ',', '.') ?></td>
                                        <td><?= number_format((float)$coil['length'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Tidak ada data detail</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="4" class="text-end">Total Keseluruhan</td>
                        <td>
                            <?php
                            $totalNet = 0;
                            $totalLen = 0;
                            foreach ($details as $d) {
                                foreach ($d['coils'] ?? [] as $c) {
                                    $totalNet += (float)$c['net_weight'];
                                    $totalLen += (float)$c['length'];
                                }
                            }
                            echo number_format($totalNet, 2, ',', '.');
                            ?>
                        </td>
                        <td><?= number_format($totalLen, 2, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4 d-flex gap-2 justify-content-between">
            <a href="<?= site_url('approval_mutasi') ?>" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>

            <?php if ($m['status'] == 1 && $ENABLE_MANAGE): ?>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-info text-white" onclick="doRevision(<?= $m['id'] ?>)">
                        <i class="fa-solid fa-rotate-left"></i> Revisi
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

<script>
    const BASE_URL = '<?= site_url('approval_mutasi') ?>';

    function doApprove(id) {
        Swal.fire({
            title: 'Approve Mutasi?',
            html: 'Stock akan langsung dipindahkan ke gudang tujuan.<br><strong>Aksi ini tidak dapat dibatalkan.</strong>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-check"></i> Ya, Approve',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745'
        }).then(result => {
            if (result.isConfirmed) {
                $.post(BASE_URL + '/approve/' + id, function(res) {
                    if (res.status == 1) {
                        Swal.fire({
                            title: 'Berhasil',
                            text: res.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = BASE_URL;
                        });
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                }, 'json').fail(function() {
                    Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                });
            }
        });
    }

    function doReject(id) {
        Swal.fire({
            title: 'Reject Mutasi?',
            html: '<p class="text-danger"><strong>Mutasi akan ditolak secara permanen.</strong></p>',
            input: 'textarea',
            inputPlaceholder: 'Masukkan alasan penolakan...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-times"></i> Ya, Reject',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return 'Alasan penolakan wajib diisi!';
                }
            }
        }).then(result => {
            if (result.isConfirmed && result.value) {
                $.post(BASE_URL + '/reject/' + id, {
                    reject_reason: result.value.trim()
                }, function(res) {
                    if (res.status == 1) {
                        Swal.fire({
                            title: 'Berhasil',
                            text: res.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = BASE_URL;
                        });
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                }, 'json').fail(function() {
                    Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                });
            }
        });
    }

    function doRevision(id) {
        Swal.fire({
            title: 'Kembalikan untuk Revisi?',
            html: '<p>Pengajuan akan dikembalikan ke pengaju untuk diperbaiki.</p>',
            input: 'textarea',
            inputPlaceholder: 'Masukkan catatan/poin yang perlu direvisi...',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-rotate-left"></i> Ya, Minta Revisi',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#17a2b8',
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return 'Catatan revisi wajib diisi!';
                }
            }
        }).then(result => {
            if (result.isConfirmed && result.value) {
                $.post(BASE_URL + '/revision/' + id, {
                    reject_reason: result.value.trim()
                }, function(res) {
                    if (res.status == 1) {
                        Swal.fire({
                            title: 'Berhasil',
                            text: res.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = BASE_URL;
                        });
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                }, 'json').fail(function() {
                    Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                });
            }
        });
    }
</script>