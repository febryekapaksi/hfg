<?php
// Group saved coils by id_material for easier rendering
$coils_by_material = [];
foreach ($saved_coils as $coil) {
    $coils_by_material[$coil['id_material']][] = $coil;
}
?>

<style>
    .material-section {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        background: #fafbfc;
    }

    .material-section .section-title {
        font-weight: 600;
        margin-bottom: 12px;
        color: #495057;
    }

    .coil-table th,
    .coil-table td {
        vertical-align: middle;
        font-size: 13px;
    }

    .info-header {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .info-header .info-item {
        display: inline-block;
        margin-right: 30px;
    }

    .info-header .info-label {
        font-size: 10px;
        text-transform: uppercase;
        font-weight: 700;
        color: #6c757d;
        display: block;
    }

    .info-header .info-value {
        font-size: 15px;
        font-weight: 600;
        color: #343a40;
    }
</style>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-clipboard-list me-2"></i> View SPK Coil Detail</h5>
        <a href="<?= site_url('request_list') ?>" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">

        <!-- Header Info -->
        <div class="info-header">
            <div class="d-flex flex-wrap align-items-center gap-4">
                <div class="info-item">
                    <span class="info-label">SPK No</span>
                    <span class="info-value"><?= htmlspecialchars($spk_no) ?></span>
                </div>
                <div class="info-item border-start ps-4">
                    <span class="info-label">Tanggal SPK</span>
                    <span class="info-value"><?= isset($header['tgl_spk']) ? date('d/m/Y', strtotime($header['tgl_spk'])) : '-' ?></span>
                </div>
                <div class="info-item border-start ps-4">
                    <span class="info-label">Shift</span>
                    <span class="info-value"><?= htmlspecialchars(isset($header['shift_names']) ? $header['shift_names'] : '-') ?></span>
                </div>
                <div class="info-item border-start ps-4">
                    <span class="info-label">Target Qty</span>
                    <span class="info-value"><?= isset($header['target_qty']) ? number_format($header['target_qty']) : '-' ?></span>
                </div>
                <div class="info-item border-start ps-4">
                    <span class="info-label">Total Weight</span>
                    <span class="info-value"><?= isset($header['total_weight']) ? number_format($header['total_weight'], 2) . ' Kg' : '-' ?></span>
                </div>
                <div class="info-item border-start ps-4">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <?php 
                        $badge_class = 'bg-secondary';
                        if ($header['status'] == 'Material Requested') $badge_class = 'bg-primary';
                        elseif ($header['status'] == 'Material On Load') $badge_class = 'bg-info text-dark';
                        elseif ($header['status'] == 'Material Confirmed') $badge_class = 'bg-success';
                        elseif ($header['status'] == 'Cancelled') $badge_class = 'bg-danger';
                        ?>
                        <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($header['status']) ?></span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Material Sections -->
        <div id="material-sections">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $idx => $product): ?>
                    <div class="card mb-4 border-secondary product-section">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center product-header cursor-pointer" data-target="product-content-<?= $idx ?>" style="cursor: pointer;">
                            <h6 class="mb-0 text-white fw-bold">
                                <i class="fa fa-box me-2"></i>
                                <?= htmlspecialchars(isset($product['nm_produk_fg']) ? $product['nm_produk_fg'] : 'Produk') ?>
                            </h6>
                            <i class="fa fa-chevron-up text-white"></i>
                        </div>

                        <div class="card-body product-content p-3" id="product-content-<?= $idx ?>">
                            <?php if (!empty($product['materials'])): ?>
                                <?php foreach ($product['materials'] as $material): ?>
                                    <div class="material-section">
                                        <div class="section-title">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <i class="fa fa-cubes me-1"></i>
                                                    <?= htmlspecialchars($material['nm_material']) ?>
                                                    <small class="text-muted ms-2 d-block mt-1">(Qty BOM: <?= isset($material['qty']) ? number_format($material['qty'], 2) : '0' ?> <?= htmlspecialchars(isset($material['nm_unit']) ? $material['nm_unit'] : '') ?>)</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="coil-content">
                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <div>
                                                        <small class="text-muted d-block">Target Qty (Produk):</small>
                                                        <strong><?= isset($product['target_qty']) ? number_format($product['target_qty']) : '0' ?></strong>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div>
                                                        <small class="text-muted d-block">Berat Per Unit (Produk):</small>
                                                        <strong><?= isset($product['berat_per_unit']) ? number_format($product['berat_per_unit'], 2) : '0' ?> Kg</strong>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div>
                                                        <small class="text-muted d-block">Total Weight (Produk):</small>
                                                        <strong><?= isset($product['total_weight']) ? number_format($product['total_weight'], 2) : '0' ?> Kg</strong>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php
                                            $mat_coils = isset($coils_by_material[$material['id_material']]) ? $coils_by_material[$material['id_material']] : [];
                                            $wip_coils = array_filter($mat_coils, function($c) { return $c['id_gudang_sumber'] == 3; });
                                            $pro_coils = array_filter($mat_coils, function($c) { return $c['id_gudang_sumber'] == 1; });
                                            ?>

                                            <!-- Table WIP -->
                                            <h6 class="text-warning text-dark border-bottom pb-1 mb-2 mt-4">Coil WIP Terpilih</h6>
                                            <div class="table-responsive mb-4">
                                                <table class="table table-bordered table-sm coil-table">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="5%" class="text-center">No</th>
                                                            <th>No Coil</th>
                                                            <th>Kode Internal</th>
                                                            <th>SPK Coil No</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (!empty($wip_coils)): ?>
                                                            <?php $no=1; foreach ($wip_coils as $coil): ?>
                                                                <tr>
                                                                    <td class="text-center"><?= $no++ ?></td>
                                                                    <td><?= htmlspecialchars($coil['no_coil']) ?></td>
                                                                    <td><?= htmlspecialchars($coil['kode_internal']) ?></td>
                                                                    <td><?= htmlspecialchars($coil['spk_coil_no']) ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="4" class="text-center text-muted">Tidak ada coil WIP yang dipilih.</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Table Produksi -->
                                            <h6 class="text-info text-dark border-bottom pb-1 mb-2">Coil Produksi Terpilih</h6>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm coil-table">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="5%" class="text-center">No</th>
                                                            <th>No Coil</th>
                                                            <th>Kode Internal</th>
                                                            <th>SPK Coil No</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (!empty($pro_coils)): ?>
                                                            <?php $no=1; foreach ($pro_coils as $coil): ?>
                                                                <tr>
                                                                    <td class="text-center"><?= $no++ ?></td>
                                                                    <td><?= htmlspecialchars($coil['no_coil']) ?></td>
                                                                    <td><?= htmlspecialchars($coil['kode_internal']) ?></td>
                                                                    <td><?= htmlspecialchars($coil['spk_coil_no']) ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="4" class="text-center text-muted">Tidak ada coil Produksi yang dipilih.</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-warning">Tidak ada data BOM material untuk produk ini.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fa fa-info-circle fa-2x mb-2"></i>
                    <p>Tidak ada produk/material ditemukan untuk SPK ini.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
    $(document).ready(function() {
        /**
         * Toggle product sections
         */
        $('.product-header').click(function() {
            var target = $(this).data('target');
            var $content = $('#' + target);
            var $icon = $(this).find('i.fa.fa-chevron-up, i.fa.fa-chevron-down');

            if ($content.is(':visible')) {
                $content.slideUp(300);
                $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            } else {
                $content.slideDown(300);
                $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            }
        });
    });
</script>
