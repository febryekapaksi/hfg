<?php
$ENABLE_MANAGE = has_permission('Request_List.Manage');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .skeleton {
        border-radius: 4px;
        animation: shimmer 1.5s infinite linear;
        background: linear-gradient(90deg, #f2f2f2 25%, #e0e0e0 50%, #f2f2f2 75%);
        background-size: 200% 100%;
    }

    .skeleton-line {
        height: 20px;
        margin: 8px 0;
        border-radius: 4px;
    }

    .skeleton-line.short {
        width: 60%;
    }

    .skeleton-line.medium {
        width: 80%;
    }

    .skeleton-line.tall {
        height: 40px;
    }

    .skeleton-block {
        height: 150px;
        margin: 12px 0;
        border-radius: 6px;
    }

    @keyframes shimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

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

    .badge-gudang-coil {
        background-color: #556ee6;
        color: #fff;
    }

    .badge-wip {
        background-color: #f1b44c;
        color: #fff;
    }

    .coil-skeleton {
        padding: 20px;
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

<!-- Skeleton Loading -->
<div id="skeleton-content">
    <div class="card">
        <div class="card-body">
            <div class="skeleton skeleton-line medium"></div>
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="skeleton skeleton-line tall"></div>
                </div>
                <div class="col-md-3">
                    <div class="skeleton skeleton-line tall"></div>
                </div>
                <div class="col-md-3">
                    <div class="skeleton skeleton-line tall"></div>
                </div>
                <div class="col-md-3">
                    <div class="skeleton skeleton-line tall"></div>
                </div>
            </div>
            <div class="skeleton skeleton-block"></div>
            <div class="skeleton skeleton-block"></div>
            <div class="skeleton skeleton-line short"></div>
        </div>
    </div>
</div>

<!-- Actual Content (hidden until ready) -->
<div id="actual-content" style="display:none">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-cogs me-2"></i> Manage Request Coil</h5>
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
                </div>
            </div>

            <!-- Card List SPK Coil Existing -->
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-dark d-flex justify-content-between align-items-center cursor-pointer" id="toggle-saved-spk-coils" style="cursor: pointer;">
                    <h6 class="mb-0 fw-bold"><i class="fa fa-list-alt me-2"></i> Daftar SPK Coil Yang Sudah Dibuat</h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark fw-bold" id="saved-spkc-count"><?= isset($saved_spk_coils) ? count($saved_spk_coils) : 0 ?> SPK Coil</span>
                        <i class="fa fa-chevron-up text-dark ms-2" id="icon-toggle-saved-spkc"></i>
                    </div>
                </div>
                <div class="card-body p-3" id="container-saved-spk-coils">
                    <!-- Data SPK Coil dimuat via JavaScript -->
                </div>
            </div>

            <!-- Live Search Input for Main Form -->
            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa fa-search text-muted"></i></span>
                    <input type="text" id="form-search-coil" class="form-control" placeholder="Cari No Coil / Kode Internal / Nama Material di tabel ketersediaan...">
                </div>
                <div id="form-search-no-match-alert" style="display:none;" class="mt-2"></div>
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
                                        <div class="material-section" data-id-material="<?= htmlspecialchars($material['id_material']) ?>">
                                            <div class="section-title">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <i class="fa fa-cubes me-1"></i>
                                                        <?= htmlspecialchars($material['nm_material']) ?>
                                                        <small class="text-muted ms-2 d-block mt-1">(Qty BOM: <?= isset($material['qty']) ? number_format($material['qty'], 2) : '0' ?> <?= htmlspecialchars(isset($material['nm_unit']) ? $material['nm_unit'] : '') ?>)</small>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-warning text-dark mb-1 d-inline-block" style="min-width: 140px;">Stock WIP: <?= number_format(isset($material['stock_wip']) ? $material['stock_wip'] : 0, 2) ?></span>
                                                        <br>
                                                        <span class="badge bg-info text-dark d-inline-block" style="min-width: 140px;">Stock Produksi: <?= number_format(isset($material['stock_produksi']) ? $material['stock_produksi'] : 0, 2) ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Coil Skeleton (shown while loading) -->
                                            <div class="coil-skeleton" id="skeleton-<?= htmlspecialchars($material['id_material']) ?>">
                                                <div class="skeleton skeleton-line medium"></div>
                                                <div class="skeleton skeleton-line"></div>
                                                <div class="skeleton skeleton-line short"></div>
                                                <div class="skeleton skeleton-line"></div>
                                            </div>

                                            <!-- Coil Content (hidden until AJAX loads) -->
                                            <div class="coil-content" id="content-<?= htmlspecialchars($material['id_material']) ?>" style="display:none">
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

                                                <!-- Table WIP -->
                                                <h6 class="text-warning text-dark border-bottom pb-1 mb-2 mt-4">Warehouse WIP</h6>
                                                <div class="table-responsive mb-4">
                                                    <table class="table table-bordered table-sm coil-table">
                                                        <thead>
                                                            <tr>
                                                                <th width="5%" class="text-center">
                                                                    <input type="checkbox" class="form-check-input check-all"
                                                                        data-id-material="<?= htmlspecialchars($material['id_material']) ?>" data-target="wip">
                                                                </th>
                                                                <th>No Coil</th>
                                                                <th>Kode Internal</th>
                                                                <th class="text-end">Net Weight</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="coil-body-wip-<?= htmlspecialchars($material['id_material']) ?>">
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!-- Table Produksi -->
                                                <h6 class="text-info text-dark border-bottom pb-1 mb-2">Warehouse Production 1</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-sm coil-table">
                                                        <thead>
                                                            <tr>
                                                                <th width="5%" class="text-center">
                                                                    <input type="checkbox" class="form-check-input check-all"
                                                                        data-id-material="<?= htmlspecialchars($material['id_material']) ?>" data-target="pro">
                                                                </th>
                                                                <th>No Coil</th>
                                                                <th>Kode Internal</th>
                                                                <th class="text-end">Net Weight</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="coil-body-pro-<?= htmlspecialchars($material['id_material']) ?>">
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

            <!-- Submit Button -->
            <div class="mt-4 d-flex gap-2 justify-content-end">
                <a href="<?= site_url('request_list') ?>" class="btn btn-sm btn-secondary">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
                <?php if ($ENABLE_MANAGE): ?>
                    <button type="button" class="btn btn-sm btn-primary" id="btnSaveSpkCoil">
                        <i class="fa fa-save"></i> Save & Create SPK
                    </button>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<!-- Modal Tambah Coil ke SPK Coil Existing -->
<div class="modal fade" id="modal-add-coil-to-spkc" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalAddCoilLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white" id="modalAddCoilLabel"><i class="fa fa-plus-circle me-2"></i> Tambah Coil ke <span id="modal-target-spkc-no" class="fw-bold"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow: auto;">
                <input type="hidden" id="modal-target-request-id" value="">

                <!-- Modal Live Search Input -->
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" id="modal-search-coil" class="form-control form-control-sm" placeholder="Cari No Coil / Kode Internal di modal...">
                    </div>
                    <div id="modal-search-no-match-alert" style="display:none;" class="mt-2"></div>
                </div>

                <p class="text-muted small mb-3"><i class="fa fa-info-circle me-1"></i> Pilih coil ketersediaan berikut untuk dimasukkan ke dalam SPK Coil ini:</p>
                <div id="modal-available-coils-container">
                    <div class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Memuat coil ketersediaan...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btn-submit-add-coils-to-spkc"><i class="fa fa-plus me-1"></i> Tambahkan Coil</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        const BASE_URL = siteurl + active_controller;
        const SPK_NO = '<?= addslashes($spk_no) ?>';

        // Toggle Card Daftar SPK Coil Yang Sudah Dibuat
        $('#toggle-saved-spk-coils').click(function() {
            var $body = $('#container-saved-spk-coils');
            var $icon = $('#icon-toggle-saved-spkc');

            if ($body.is(':visible')) {
                $body.slideUp(300);
                $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            } else {
                $body.slideDown(300);
                $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            }
        });

        // Load Saved SPK Coils Card
        loadSavedSpkCoils();

        function loadSavedSpkCoils() {
            $.ajax({
                url: BASE_URL + '/get_saved_spk_coils/' + encodeURIComponent(SPK_NO),
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status == 1) {
                        renderSavedSpkCoilsCard(res.data);
                    } else {
                        $('#container-saved-spk-coils').html('<div class="text-center text-muted py-2">Gagal memuat SPK Coil.</div>');
                    }
                },
                error: function() {
                    $('#container-saved-spk-coils').html('<div class="text-center text-danger py-2">Terjadi kesalahan jaringan saat memuat SPK Coil.</div>');
                }
            });
        }

        function renderSavedSpkCoilsCard(list) {
            var $container = $('#container-saved-spk-coils');
            $('#saved-spkc-count').text((list ? list.length : 0) + ' SPK Coil');

            if (!list || list.length === 0) {
                $container.html('<div class="text-center text-muted py-3"><i class="fa fa-info-circle me-1"></i> Belum ada SPK Coil yang dibuat untuk SPK Material ini.</div>');
                return;
            }

            var html = '';
            $.each(list, function(i, spkc) {
                var statusBadge = '';
                if (spkc.status === 'Material On Load') {
                    statusBadge = '<span class="badge bg-info text-dark">Material On Load</span>';
                } else if (spkc.status === 'Material Confirmed') {
                    statusBadge = '<span class="badge bg-success">Material Confirmed</span>';
                } else {
                    statusBadge = '<span class="badge bg-secondary">' + escHtml(spkc.status) + '</span>';
                }

                var isDeletable = (spkc.status === 'Material Requested' || spkc.status === 'Material On Load');

                html += '<div class="border rounded p-3 mb-3 bg-white shadow-sm">';
                html += '<div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">';
                html += '<div>';
                html += '<strong class="fs-6 me-2 text-primary"><i class="fa fa-layer-group me-1"></i>' + escHtml(spkc.spk_coil_no) + '</strong> ';
                html += statusBadge;
                html += '<small class="text-muted ms-3"><i class="fa fa-clock me-1"></i> Date: ' + (spkc.request_date || '-') + '</small>';
                html += '</div>';

                var hasAnyScannedCoil = false;
                if (spkc.coils && spkc.coils.length > 0) {
                    $.each(spkc.coils, function(idx, c) {
                        // Hanya coil non-WIP yang sudah discan manual yang dianggap "scanned" (tidak bisa dihapus)
                        var isWip = (c.id_gudang_sumber == 4 || c.id_gudang_sumber == '4');
                        if ((c.scan_status == 1 || c.scan_status == '1') && !isWip) {
                            hasAnyScannedCoil = true;
                        }
                    });
                }

                html += '<div>';
                if (isDeletable) {
                    html += '<button type="button" class="btn btn-sm btn-outline-success me-2 btn-open-add-coil-modal" data-id="' + spkc.id + '" data-no="' + escHtml(spkc.spk_coil_no) + '" title="Tambah Coil ke ' + escHtml(spkc.spk_coil_no) + '">';
                    html += '<i class="fa fa-plus me-1"></i> Tambah Coil';
                    html += '</button>';

                    if (!hasAnyScannedCoil) {
                        html += '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-spkc" data-id="' + spkc.id + '" data-no="' + escHtml(spkc.spk_coil_no) + '" title="Hapus Seluruh SPK Coil">';
                        html += '<i class="fa fa-trash me-1"></i> Hapus SPK Coil';
                        html += '</button>';
                    }
                }
                html += '</div>';
                html += '</div>';

                // Table of coils
                html += '<div class="table-responsive">';
                html += '<table class="table table-bordered table-sm mb-0 bg-light" style="font-size: 13px;">';
                html += '<thead>';
                html += '<tr>';
                html += '<th width="5%" class="text-center">No</th>';
                html += '<th>No Coil</th>';
                html += '<th>Kode Internal</th>';
                html += '<th>Nama Material</th>';
                html += '<th>Gudang Sumber</th>';
                html += '<th width="10%" class="text-center">Aksi</th>';
                html += '</tr>';
                html += '</thead>';
                html += '<tbody>';

                if (spkc.coils && spkc.coils.length > 0) {
                    $.each(spkc.coils, function(idx, c) {
                        var isCoilScanned = (c.scan_status == 1 || c.scan_status == '1');
                        var isWip = (c.id_gudang_sumber == 4 || c.id_gudang_sumber == '4');
                        var actionHtml = '';

                        if (isCoilScanned && !isWip) {
                            // Coil non-WIP yang sudah discan manual — tidak bisa dihapus
                            actionHtml = '<span class="badge bg-success py-1 px-2"><i class="fa fa-check-circle me-1"></i> Scanned</span>';
                        } else if (isWip && isCoilScanned) {
                            // Coil WIP auto-scan — tetap bisa dihapus
                            if (isDeletable) {
                                actionHtml = '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-coil-item" data-id="' + c.id + '" data-no="' + escHtml(c.no_coil || c.kode_internal) + '" title="Hapus Coil Ini"><i class="fa fa-times"></i> Hapus</button>';
                            } else {
                                actionHtml = '<span class="text-muted">-</span>';
                            }
                        } else if (isDeletable) {
                            actionHtml = '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-coil-item" data-id="' + c.id + '" data-no="' + escHtml(c.no_coil || c.kode_internal) + '" title="Hapus Coil Ini Dari ' + escHtml(spkc.spk_coil_no) + '"><i class="fa fa-times me-1"></i> Hapus</button>';
                        } else {
                            actionHtml = '<span class="text-muted">-</span>';
                        }

                        html += '<tr>';
                        html += '<td class="text-center">' + (idx + 1) + '</td>';
                        html += '<td>' + escHtml(c.no_coil || '-') + '</td>';
                        html += '<td>' + escHtml(c.kode_internal || '-') + '</td>';
                        html += '<td>' + escHtml(c.nm_material || '-') + '</td>';
                        html += '<td>' + escHtml(c.nm_gudang || (c.id_gudang_sumber == 4 ? 'WIP' : 'Produksi')) + '</td>';
                        html += '<td class="text-center">' + actionHtml + '</td>';
                        html += '</tr>';
                    });
                } else {
                    html += '<tr><td colspan="6" class="text-center text-muted">Tidak ada detail coil.</td></tr>';
                }

                html += '</tbody>';
                html += '</table>';
                html += '</div>';
                html += '</div>';
            });

            $container.html(html);
        }

        // Hapus 1 Item Coil event
        $(document).on('click', '.btn-delete-coil-item', function() {
            var detailId = $(this).data('id');
            var coilLabel = $(this).data('no');

            Swal.fire({
                title: 'Keluarkan Coil?',
                text: 'Apakah Anda yakin ingin mengeluarkan Coil ' + coilLabel + ' dari SPK Coil ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Keluarkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: BASE_URL + '/delete_spk_coil_item',
                        type: 'POST',
                        data: {
                            detail_id: detailId
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.status == 1) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                // Dynamic reload
                                loadSavedSpkCoils();
                                materialIds.forEach(function(idMaterial) {
                                    loadCoilData(idMaterial);
                                });
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                        }
                    });
                }
            });
        });

        // Open Modal Tambah Coil ke SPK Coil Existing
        $(document).on('click', '.btn-open-add-coil-modal', function() {
            var reqId = $(this).data('id');
            var spkcNo = $(this).data('no');

            $('#modal-target-request-id').val(reqId);
            $('#modal-target-spkc-no').text(spkcNo);
            $('#modal-available-coils-container').html('<div class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2">Memuat coil ketersediaan...</p></div>');
            $('#modal-add-coil-to-spkc').modal('show');

            // Collect available unassigned coils from DOM/AJAX
            loadModalAvailableCoils(reqId);
        });

        function loadModalAvailableCoils(targetReqId) {
            var html = '';
            var hasAnyCoil = false;

            $('.material-section').each(function() {
                var $matSec = $(this);
                var idMaterial = $matSec.data('id-material');
                var nmMaterial = $matSec.find('.section-title').text().trim().split('\n')[0];

                var wipCoils = [];
                var proCoils = [];

                $('#content-' + idMaterial + ' .coil-checkbox').each(function() {
                    var $cb = $(this);
                    var isScanned = ($cb.data('is-scanned') == 1 || $cb.data('is-scanned') == '1');
                    var coilObj = {
                        id_coil: $cb.data('id-coil'),
                        no_coil: $cb.data('no-coil'),
                        kode_internal: $cb.data('kode-internal'),
                        id_gudang: $cb.data('id-gudang'),
                        assigned_spkc: $cb.data('assigned-spkc') || '',
                        assigned_req_id: $cb.data('assigned-req-id') || '',
                        is_scanned: isScanned,
                        id_material: idMaterial,
                        nm_material: $cb.data('nm-material') || nmMaterial
                    };

                    if (coilObj.id_gudang == 4) {
                        wipCoils.push(coilObj);
                    } else {
                        proCoils.push(coilObj);
                    }
                });

                if (wipCoils.length > 0 || proCoils.length > 0) {
                    hasAnyCoil = true;
                    html += '<div class="card mb-3 border material-modal-block">';
                    html += '<div class="card-header bg-light fw-bold py-2"><i class="fa fa-cubes me-1"></i> ' + escHtml(nmMaterial) + '</div>';
                    html += '<div class="card-body p-2">';

                    // Warehouse WIP
                    html += '<h6 class="text-warning text-dark border-bottom pb-1 mb-2 fw-bold" style="font-size:13px;"><i class="fa fa-warehouse me-1"></i> Warehouse WIP</h6>';
                    if (wipCoils.length > 0) {
                        html += '<div class="table-responsive mb-3"><table class="table table-bordered table-sm mb-0" style="font-size:13px;">';
                        html += '<thead><tr><th width="5%" class="text-center">#</th><th>No Coil</th><th>Kode Internal</th></tr></thead><tbody>';

                        wipCoils.forEach(function(coil) {
                            var isAssignedToThis = (String(coil.assigned_req_id) === String(targetReqId));
                            // Coil WIP: meskipun is_scanned (auto), tetap bisa dipindah
                            var disabledAttr = isAssignedToThis ? 'disabled' : '';
                            var badgeInfo = '';
                            if (coil.is_scanned && coil.assigned_spkc) {
                                var scannedLoc = ' di ' + escHtml(coil.assigned_spkc);
                                badgeInfo = ' <span class="badge bg-warning text-dark">Terdaftar' + scannedLoc + '</span>';
                            } else if (isAssignedToThis) {
                                badgeInfo = ' <span class="badge bg-secondary">Sudah ada di SPK Coil ini</span>';
                            } else if (coil.assigned_spkc) {
                                badgeInfo = ' <span class="badge bg-warning text-dark">Terdaftar di ' + escHtml(coil.assigned_spkc) + '</span>';
                            }

                            html += '<tr class="modal-coil-row">';
                            html += '<td class="text-center"><input type="checkbox" class="form-check-input modal-add-coil-cb" ' + disabledAttr + ' ' +
                                'data-id-coil="' + escHtml(String(coil.id_coil)) + '" ' +
                                'data-id-material="' + escHtml(String(idMaterial)) + '" ' +
                                'data-nm-material="' + escHtml(coil.nm_material) + '" ' +
                                'data-no-coil="' + escHtml(coil.no_coil) + '" ' +
                                'data-kode-internal="' + escHtml(coil.kode_internal) + '" ' +
                                'data-id-gudang="4" ' +
                                'data-assigned-spkc="' + escHtml(coil.assigned_spkc) + '" ' +
                                'data-assigned-req-id="' + escHtml(String(coil.assigned_req_id)) + '"></td>';
                            html += '<td>' + escHtml(coil.no_coil || '-') + badgeInfo + '</td>';
                            html += '<td>' + escHtml(coil.kode_internal || '-') + '</td>';
                            html += '</tr>';
                        });
                        html += '</tbody></table></div>';
                    } else {
                        html += '<div class="text-muted small mb-3">Tidak ada coil WIP ketersediaan.</div>';
                    }

                    // Warehouse Production 1
                    html += '<h6 class="text-info text-dark border-bottom pb-1 mb-2 fw-bold" style="font-size:13px;"><i class="fa fa-industry me-1"></i> Warehouse Production 1</h6>';
                    if (proCoils.length > 0) {
                        html += '<div class="table-responsive"><table class="table table-bordered table-sm mb-0" style="font-size:13px;">';
                        html += '<thead><tr><th width="5%" class="text-center">#</th><th>No Coil</th><th>Kode Internal</th></tr></thead><tbody>';

                        proCoils.forEach(function(coil) {
                            var isAssignedToThis = (String(coil.assigned_req_id) === String(targetReqId));
                            var disabledAttr = (isAssignedToThis || coil.is_scanned) ? 'disabled' : '';
                            var badgeInfo = '';
                            if (coil.is_scanned) {
                                var scannedLoc = coil.assigned_spkc ? ' di ' + escHtml(coil.assigned_spkc) : '';
                                badgeInfo = ' <span class="badge bg-success"><i class="fa fa-check-circle me-1"></i> Scanned' + scannedLoc + '</span>';
                            } else if (isAssignedToThis) {
                                badgeInfo = ' <span class="badge bg-secondary">Sudah ada di SPK Coil ini</span>';
                            } else if (coil.assigned_spkc) {
                                badgeInfo = ' <span class="badge bg-warning text-dark">Terdaftar di ' + escHtml(coil.assigned_spkc) + '</span>';
                            }

                            html += '<tr class="modal-coil-row">';
                            html += '<td class="text-center"><input type="checkbox" class="form-check-input modal-add-coil-cb" ' + disabledAttr + ' ' +
                                'data-id-coil="' + escHtml(String(coil.id_coil)) + '" ' +
                                'data-id-material="' + escHtml(String(idMaterial)) + '" ' +
                                'data-nm-material="' + escHtml(coil.nm_material) + '" ' +
                                'data-no-coil="' + escHtml(coil.no_coil) + '" ' +
                                'data-kode-internal="' + escHtml(coil.kode_internal) + '" ' +
                                'data-id-gudang="1" ' +
                                'data-assigned-spkc="' + escHtml(coil.assigned_spkc) + '" ' +
                                'data-assigned-req-id="' + escHtml(String(coil.assigned_req_id)) + '"></td>';
                            html += '<td>' + escHtml(coil.no_coil || '-') + badgeInfo + '</td>';
                            html += '<td>' + escHtml(coil.kode_internal || '-') + '</td>';
                            html += '</tr>';
                        });
                        html += '</tbody></table></div>';
                    } else {
                        html += '<div class="text-muted small">Tidak ada coil Produksi ketersediaan.</div>';
                    }

                    html += '</div></div>';
                }
            });

            if (!hasAnyCoil) {
                html = '<div class="text-center text-muted py-4"><i class="fa fa-info-circle fa-2x mb-2 d-block"></i>Tidak ada coil ketersediaan yang bisa ditambahkan.</div>';
            }

            $('#modal-available-coils-container').html(html);
        }

        // Live Search Filter di Form Utama
        $('#form-search-coil').on('keyup', function() {
            var val = $(this).val().toLowerCase().trim();
            var globalMatchCount = 0;

            $('.material-section').each(function() {
                var $section = $(this);
                var matName = $section.find('.section-title').text().toLowerCase();
                var sectionMatchCount = 0;

                $section.find('.coil-table tbody tr').each(function() {
                    var $tr = $(this);
                    if ($tr.hasClass('search-no-match-row')) return;

                    var rowText = $tr.text().toLowerCase();
                    if (val === '' || rowText.indexOf(val) > -1 || matName.indexOf(val) > -1) {
                        $tr.show();
                        sectionMatchCount++;
                    } else {
                        $tr.hide();
                    }
                });

                if (val !== '' && sectionMatchCount === 0 && matName.indexOf(val) === -1) {
                    $section.hide();
                } else {
                    $section.show();
                    globalMatchCount += sectionMatchCount;
                }
            });

            if (val !== '' && globalMatchCount === 0) {
                $('#form-search-no-match-alert').html(
                    '<div class="alert alert-warning text-center my-3"><i class="fa fa-info-circle me-2"></i>Tidak ada coil atau material yang cocok dengan kata kunci "<strong>' + escHtml(val) + '</strong>".</div>'
                ).show();
            } else {
                $('#form-search-no-match-alert').hide();
            }
        });

        // Live Search Filter di Modal
        $(document).on('keyup', '#modal-search-coil', function() {
            var val = $(this).val().toLowerCase().trim();
            var globalModalMatch = 0;

            $('.material-modal-block').each(function() {
                var $block = $(this);
                var blockMatName = $block.find('.card-header').text().toLowerCase();
                var blockMatchCount = 0;

                $block.find('.modal-coil-row').each(function() {
                    var $tr = $(this);
                    var rowText = $tr.text().toLowerCase();
                    if (val === '' || rowText.indexOf(val) > -1 || blockMatName.indexOf(val) > -1) {
                        $tr.show();
                        blockMatchCount++;
                    } else {
                        $tr.hide();
                    }
                });

                if (val !== '' && blockMatchCount === 0 && blockMatName.indexOf(val) === -1) {
                    $block.hide();
                } else {
                    $block.show();
                    globalModalMatch += blockMatchCount;
                }
            });

            if (val !== '' && globalModalMatch === 0) {
                $('#modal-search-no-match-alert').html(
                    '<div class="alert alert-warning text-center my-3"><i class="fa fa-info-circle me-2"></i>Tidak ada coil yang cocok dengan kata kunci "<strong>' + escHtml(val) + '</strong>".</div>'
                ).show();
            } else {
                $('#modal-search-no-match-alert').hide();
            }
        });

        // Swal Warning saat mencentang coil di Modal yang sudah terdaftar di SPK Coil lain
        $(document).on('change', '.modal-add-coil-cb', function(e) {
            var $checkbox = $(this);
            var isChecked = $checkbox.is(':checked');
            var assignedSpkc = $checkbox.data('assigned-spkc');

            if (isChecked && assignedSpkc) {
                $checkbox.prop('checked', false);

                var idGudang = $checkbox.data('id-gudang');
                var isWip = (idGudang == 4 || idGudang == '4');
                var alertText = isWip
                    ? 'Coil ini sudah ada di ' + assignedSpkc + '. Apakah Anda ingin memindahkannya ke SPK Coil ini?'
                    : 'Coil ini sudah ada di ' + assignedSpkc + ' dan belum discan. Apakah Anda ingin mengeluarkannya dan memasukannya ke SPK Coil ini?';

                Swal.fire({
                    title: 'Peringatan',
                    text: alertText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, pindahkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $checkbox.prop('checked', true);
                    }
                });
            }
        });

        // Submit Tambah Coil ke SPK Coil Existing
        $('#btn-submit-add-coils-to-spkc').click(function() {
            var targetReqId = $('#modal-target-request-id').val();
            if (!targetReqId) return;

            var selectedCoils = [];
            $('.modal-add-coil-cb:checked').each(function() {
                selectedCoils.push({
                    id_coil: $(this).data('id-coil'),
                    id_material: $(this).data('id-material'),
                    nm_material: $(this).data('nm-material'),
                    kode_internal: $(this).data('kode-internal'),
                    no_coil: $(this).data('no-coil'),
                    id_gudang_sumber: $(this).data('id-gudang'),
                    assigned_request_id: $(this).data('assigned-req-id')
                });
            });

            if (selectedCoils.length === 0) {
                Swal.fire('Peringatan', 'Pilih minimal 1 coil untuk ditambahkan.', 'warning');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

            $.ajax({
                url: BASE_URL + '/add_coils_to_spkc',
                type: 'POST',
                data: {
                    request_id: targetReqId,
                    coils: selectedCoils
                },
                dataType: 'json',
                success: function(res) {
                    $btn.prop('disabled', false).html('<i class="fa fa-plus me-1"></i> Tambahkan Coil');
                    if (res.status == 1) {
                        $('#modal-add-coil-to-spkc').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // Dynamic reload
                        loadSavedSpkCoils();
                        materialIds.forEach(function(idMaterial) {
                            loadCoilData(idMaterial);
                        });
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html('<i class="fa fa-plus me-1"></i> Tambahkan Coil');
                    Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                }
            });
        });

        // Hapus SPK Coil event
        $(document).on('click', '.btn-delete-spkc', function() {
            var reqId = $(this).data('id');
            var spkcNo = $(this).data('no');

            Swal.fire({
                title: 'Hapus SPK Coil?',
                text: 'Apakah Anda yakin ingin menghapus ' + spkcNo + '? Coil di dalamnya akan dilepas kembali ke stok ketersediaan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: BASE_URL + '/delete_spk_coil',
                        type: 'POST',
                        data: {
                            request_id: reqId
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.status == 1) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                // Dynamic reload
                                loadSavedSpkCoils();
                                materialIds.forEach(function(idMaterial) {
                                    loadCoilData(idMaterial);
                                });
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                        }
                    });
                }
            });
        });

        // Track materials for AJAX loading
        var materialIds = [];
        $('.material-section').each(function() {
            materialIds.push($(this).data('id-material'));
        });

        // Load coil data for each material
        var loadedCount = 0;
        if (materialIds.length === 0) {
            // No materials — just show content
            $('#skeleton-content').hide();
            $('#actual-content').fadeIn();
        } else {
            // Show actual content frame immediately, coil sections have their own skeletons
            $('#skeleton-content').hide();
            $('#actual-content').fadeIn();

            materialIds.forEach(function(idMaterial) {
                loadCoilData(idMaterial);
            });
        }

        /**
         * Load available coils for a material via AJAX
         */
        function loadCoilData(idMaterial) {
            $.ajax({
                url: BASE_URL + '/get_available_coils/' + idMaterial + '?spk_no=' + SPK_NO,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    $('#skeleton-' + idMaterial).hide();
                    $('#content-' + idMaterial).show();

                    if (res.status == 1) {
                        renderCoilTable(idMaterial, res.data, res.total_gudang_coil, res.total_wip);
                    } else {
                        $('#coil-body-wip-' + idMaterial).html('<tr><td colspan="4" class="text-center text-muted">Gagal memuat data</td></tr>');
                        $('#coil-body-pro-' + idMaterial).html('<tr><td colspan="4" class="text-center text-muted">Gagal memuat data</td></tr>');
                    }
                },
                error: function() {
                    $('#skeleton-' + idMaterial).hide();
                    $('#content-' + idMaterial).show();
                    $('#coil-body-wip-' + idMaterial).html('<tr><td colspan="4" class="text-center text-danger">Terjadi kesalahan jaringan.</td></tr>');
                    $('#coil-body-pro-' + idMaterial).html('<tr><td colspan="4" class="text-center text-danger">Terjadi kesalahan jaringan.</td></tr>');
                }
            });
        }

        /**
         * Render coil table rows
         */
        function renderCoilTable(idMaterial, data, totalGudang, totalWip) {
            var tbodyWip = '';
            var tbodyPro = '';

            if (data.wip && data.wip.length > 0) {
                data.wip.forEach(function(coil) {
                    var isScanned = (coil.scan_status == 1) ? true : false;
                    var assignedSPKC = coil.assigned_spkc || '';
                    var assignedReqId = coil.assigned_request_id || '';
                    // Coil WIP: meskipun scan_status=1 (auto), tetap bisa dipindah (tidak disabled)
                    var disabledAttr = '';
                    var badgeHtml = '';

                    if (isScanned && assignedSPKC) {
                        badgeHtml = '<br><span class="badge bg-warning text-dark">Terdaftar di ' + escHtml(assignedSPKC) + '</span>';
                    } else if (assignedSPKC) {
                        badgeHtml = '<br><span class="badge bg-warning text-dark">Terdaftar di ' + escHtml(assignedSPKC) + '</span>';
                    }

                    tbodyWip += '<tr>';
                    tbodyWip += '<td class="text-center">';
                    tbodyWip += '<input type="checkbox" class="form-check-input coil-checkbox" ' + disabledAttr + ' ' +
                        'data-id-material="' + escHtml(String(idMaterial)) + '" ' +
                        'data-id-coil="' + escHtml(String(coil.id)) + '" ' +
                        'data-no-coil="' + escHtml(coil.no_coil || '') + '" ' +
                        'data-kode-internal="' + escHtml(coil.kode_internal || '') + '" ' +
                        'data-id-gudang="4" ' +
                        'data-target="wip" ' +
                        'data-is-scanned="' + (isScanned ? '1' : '0') + '" ' +
                        'data-assigned-spkc="' + escHtml(assignedSPKC) + '" ' +
                        'data-assigned-req-id="' + escHtml(String(assignedReqId)) + '" ' +
                        'data-nm-material="' + escHtml(coil.nm_material || '') + '">';
                    tbodyWip += '</td>';
                    tbodyWip += '<td>' + escHtml(coil.no_coil || '-') + badgeHtml + '</td>';
                    tbodyWip += '<td>' + escHtml(coil.kode_internal || '-') + '</td>';
                    tbodyWip += '<td class="text-end">' + parseFloat(coil.net_weight || 0).toFixed(2) + '</td>';
                    tbodyWip += '</tr>';
                });
            } else {
                tbodyWip = '<tr><td colspan="4" class="text-center text-muted">Tidak ada coil WIP tersedia.</td></tr>';
            }

            if (data.gudang_coil && data.gudang_coil.length > 0) {
                data.gudang_coil.forEach(function(coil) {
                    var isScanned = (coil.scan_status == 1) ? true : false;
                    var assignedSPKC = coil.assigned_spkc || '';
                    var assignedReqId = coil.assigned_request_id || '';
                    var disabledAttr = isScanned ? 'disabled' : '';
                    var badgeHtml = '';

                    if (isScanned) {
                        badgeHtml = '<br><span class="badge bg-success">Scanned di ' + assignedSPKC + '</span>';
                    } else if (assignedSPKC) {
                        badgeHtml = '<br><span class="badge bg-warning text-dark">Terdaftar di ' + assignedSPKC + '</span>';
                    }

                    tbodyPro += '<tr>';
                    tbodyPro += '<td class="text-center">';
                    tbodyPro += '<input type="checkbox" class="form-check-input coil-checkbox" ' + disabledAttr + ' ' +
                        'data-id-material="' + escHtml(String(idMaterial)) + '" ' +
                        'data-id-coil="' + escHtml(String(coil.id)) + '" ' +
                        'data-no-coil="' + escHtml(coil.no_coil || '') + '" ' +
                        'data-kode-internal="' + escHtml(coil.kode_internal || '') + '" ' +
                        'data-id-gudang="1" ' +
                        'data-target="pro" ' +
                        'data-is-scanned="' + (isScanned ? '1' : '0') + '" ' +
                        'data-assigned-spkc="' + escHtml(assignedSPKC) + '" ' +
                        'data-assigned-req-id="' + escHtml(String(assignedReqId)) + '" ' +
                        'data-nm-material="' + escHtml(coil.nm_material || '') + '">';
                    tbodyPro += '</td>';
                    tbodyPro += '<td>' + escHtml(coil.no_coil || '-') + badgeHtml + '</td>';
                    tbodyPro += '<td>' + escHtml(coil.kode_internal || '-') + '</td>';
                    tbodyPro += '<td class="text-end">' + parseFloat(coil.net_weight || 0).toFixed(2) + '</td>';
                    tbodyPro += '</tr>';
                });
            } else {
                tbodyPro = '<tr><td colspan="4" class="text-center text-muted">Tidak ada coil Produksi tersedia.</td></tr>';
            }

            $('#coil-body-wip-' + idMaterial).html(tbodyWip);
            $('#coil-body-pro-' + idMaterial).html(tbodyPro);
        }

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

        /**
         * Check All toggle per material
         */
        $(document).on('change', '.check-all', function() {
            var idMaterial = $(this).data('id-material');
            var target = $(this).data('target');
            var isChecked = $(this).is(':checked');
            $('#coil-body-' + target + '-' + idMaterial + ' .coil-checkbox').prop('checked', isChecked);
            updateCheckedCount(idMaterial);
        });

        /**
         * Individual checkbox change
         */
        $(document).on('change', '.coil-checkbox', function(e) {
            var $checkbox = $(this);
            var isChecked = $checkbox.is(':checked');
            var assignedSpkc = $checkbox.data('assigned-spkc');

            if (isChecked && assignedSpkc) {
                // Prevent default/temporary uncheck
                $checkbox.prop('checked', false);

                var idGudang = $checkbox.data('id-gudang');
                var isWip = (idGudang == 4 || idGudang == '4');
                var alertText = isWip
                    ? 'Coil ini sudah ada di ' + assignedSpkc + '. Apakah Anda ingin memindahkannya ke SPK Coil baru ini?'
                    : 'Coil ini sudah ada di ' + assignedSpkc + ' dan belum discan. Apakah Anda ingin mengeluarkannya dan memasukannya ke SPK Coil baru ini?';

                Swal.fire({
                    title: 'Peringatan',
                    text: alertText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, pindahkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $checkbox.prop('checked', true);
                        updateCheckAllState($checkbox);
                    }
                });
            } else {
                updateCheckAllState($checkbox);
            }
        });

        function updateCheckAllState($checkbox) {
            var idMaterial = $checkbox.data('id-material');
            var target = $checkbox.data('target');

            // Update check-all state
            var total = $('#coil-body-' + target + '-' + idMaterial + ' .coil-checkbox').length;
            var checked = $('#coil-body-' + target + '-' + idMaterial + ' .coil-checkbox:checked').length;
            $('.check-all[data-id-material="' + idMaterial + '"][data-target="' + target + '"]').prop('checked', total > 0 && checked === total);
        }

        // Checked count display removed
        function updateCheckedCount(idMaterial) {
            // Do nothing since element is removed
        }

        /**
         * Save & Create SPK Coil
         */
        $('#btnSaveSpkCoil').on('click', function() {
            // Client-side validation
            var isValid = true;
            var errorMessages = [];


            if (!isValid) {
                Swal.fire('Validasi Gagal', errorMessages.join('<br>'), 'error');
                return;
            }

            // Collect data
            var coils = [];
            $('.coil-checkbox:checked').each(function() {
                var idMaterial = $(this).data('id-material');

                coils.push({
                    id_coil: $(this).data('id-coil'),
                    id_material: idMaterial,
                    nm_material: $(this).data('nm-material'),
                    kode_internal: $(this).data('kode-internal'),
                    no_coil: $(this).data('no-coil'),
                    id_gudang_sumber: $(this).data('id-gudang'),
                    assigned_request_id: $(this).data('assigned-req-id')
                });
            });

            // Show confirmation before saving
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Pastikan coil yang dipilih sudah sesuai untuk semua material.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Buat SPK!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit via AJAX
                    var btn = $('#btnSaveSpkCoil');
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

                    $.ajax({
                        url: siteurl + active_controller + 'save_spk_coil',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            spk_no: '<?= $header['spk_no'] ?>',
                            coils: coils
                        },
                        success: function(res) {
                            btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save & Create SPK');
                            if (res.status == 1) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false,
                                });
                                // Update card list & coil tables dynamically
                                loadSavedSpkCoils();
                                materialIds.forEach(function(idMaterial) {
                                    loadCoilData(idMaterial);
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: res.message
                                });
                            }
                        },
                        error: function() {
                            btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save & Create SPK');
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Terjadi kesalahan pada server.'
                            });
                        }
                    });
                }
            });
        });

        /**
         * HTML escape helper
         */
        function escHtml(str) {
            if (!str) return '';
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(String(str)));
            return div.innerHTML;
        }

    });
</script>