<?php
$ENABLE_MANAGE = has_permission('Spk_Material.Manage');

// Status badge classes
$status_badges = [
    'Material Requested' => 'bg-warning text-dark',
    'Material Confirmed' => 'bg-info text-dark',
    'Released'           => 'bg-success',
    'Cancelled'          => 'bg-danger',
];
$badge_class = isset($status_badges[$spk['status']]) ? $status_badges[$spk['status']] : 'bg-secondary';

// Determine editability
$editable_statuses = ['Material Requested', 'Material Confirmed'];
$is_editable = in_array($spk['status'], $editable_statuses);
?>

<style>
    .skeleton-line {
        height: 14px;
        border-radius: 4px;
        background: linear-gradient(90deg, #e2e2e2 25%, #f0f0f0 37%, #e2e2e2 63%);
        background-size: 400% 100%;
        animation: skeleton-shimmer 1.4s ease infinite;
        margin-bottom: 10px;
    }
    .skeleton-line.w-25 { width: 25%; }
    .skeleton-line.w-50 { width: 50%; }
    .skeleton-line.w-75 { width: 75%; }
    .skeleton-line.w-100 { width: 100%; }
    .skeleton-line.h-20 { height: 20px; }
    .skeleton-line.h-30 { height: 30px; }

    @keyframes skeleton-shimmer {
        0% { background-position: 100% 50%; }
        100% { background-position: 0 50%; }
    }
</style>

<!-- Skeleton Loading -->
<div id="skeleton-view">
    <div class="card">
        <div class="card-body">
            <div class="skeleton-line h-30 w-50 mb-3"></div>
            <div class="skeleton-line w-75 mb-2"></div>
            <div class="skeleton-line w-50 mb-2"></div>
            <div class="skeleton-line w-25 mb-2"></div>
            <div class="skeleton-line w-75 mb-4"></div>
            <div class="skeleton-line h-20 w-100 mb-2"></div>
            <div class="skeleton-line w-100 mb-2"></div>
            <div class="skeleton-line w-100 mb-2"></div>
            <div class="skeleton-line w-100 mb-2"></div>
            <div class="skeleton-line w-75 mb-2"></div>
        </div>
    </div>
</div>

<!-- Actual Content (hidden until ready) -->
<div id="content-view" style="display:none">
    <!-- Header Card -->
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-clipboard-list"></i> Detail SPK Material</h5>
            <a href="<?= site_url('spk_material') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="35%" class="fw-bold">No. SPK</td>
                            <td>: <?= htmlspecialchars($spk['spk_no']) ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Tanggal SPK</td>
                            <td>: <?= date('d/m/Y', strtotime($spk['tgl_spk'])) ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Due Date</td>
                            <td>: <?= !empty($spk['due_date']) ? date('d/m/Y', strtotime($spk['due_date'])) : '-' ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Shift</td>
                            <td>: <?= !empty($spk['shift_names']) ? htmlspecialchars($spk['shift_names']) : '-' ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Status</td>
                            <td>: <span class="badge rounded-pill <?= $badge_class ?>"><?= htmlspecialchars($spk['status']) ?></span></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="35%" class="fw-bold">Catatan</td>
                            <td>: <?= !empty($spk['catatan']) ? htmlspecialchars($spk['catatan']) : '-' ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Created By</td>
                            <td>: <?= htmlspecialchars($spk['created_by']) ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Created At</td>
                            <td>: <?= date('d/m/Y H:i', strtotime($spk['created_at'])) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Produk Table -->
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-boxes-stacked"></i> Detail Produk</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="5%">Urut</th>
                            <th>Nama Produk</th>
                            <th class="text-center" width="12%">Target Qty</th>
                            <th class="text-center" width="14%">Total Weight (Kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($details)): ?>
                            <?php foreach ($details as $row): ?>
                            <tr>
                                <td class="text-center"><?= $row['urut'] ?></td>
                                <td><?= htmlspecialchars($row['nm_produk_fg']) ?></td>
                                <td class="text-end"><?= number_format($row['target_qty'], 0, ',', '.') ?></td>
                                <td class="text-end"><?= number_format($row['total_weight'], 2, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">Tidak ada detail produk.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <!-- Print PDF always available -->
                <a href="<?= site_url('spk_material/print_pdf/' . $spk['spk_no']) ?>" target="_blank" class="btn btn-secondary">
                    <i class="fa fa-file-pdf"></i> Print PDF
                </a>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Show content after short delay
    setTimeout(function() {
        $('#skeleton-view').hide();
        $('#content-view').fadeIn(200);
    }, 300);
});
</script>
