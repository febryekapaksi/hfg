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
            <h5 class="mb-0"><i class="fa fa-clipboard-check me-2"></i> Create SPK Coil</h5>
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

            <!-- Material Sections -->
            <div id="material-sections">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $idx => $product): ?>
                        <div class="product-section mb-4">
                            <div class="product-header cursor-pointer bg-secondary p-2 rounded mb-3 d-flex justify-content-between align-items-center" data-target="product-content-<?= $idx ?>">
                                <h6 class="mb-0 text-white">
                                    <i class="fa fa-box me-2"></i>
                                    <?= htmlspecialchars(isset($product['nm_produk_fg']) ? $product['nm_produk_fg'] : 'Produk') ?>
                                </h6>
                                <i class="fa fa-chevron-up text-white"></i>
                            </div>

                            <div class="product-content" id="product-content-<?= $idx ?>">
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
                                                <h6 class="text-warning text-dark border-bottom pb-1 mb-2 mt-4">Tabel WIP</h6>
                                                <div class="table-responsive mb-4">
                                                    <table class="table table-bordered table-sm coil-table">
                                                        <thead class="table-light">
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
                                                <h6 class="text-info text-dark border-bottom pb-1 mb-2">Tabel Produksi</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-sm coil-table">
                                                        <thead class="table-light">
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
                <a href="<?= site_url('request_list') ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
                <?php if ($ENABLE_MANAGE): ?>
                    <button type="button" class="btn btn-primary" id="btnSaveSpkCoil">
                        <i class="fa fa-save"></i> Save & Create SPK
                    </button>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        const BASE_URL = siteurl + active_controller;
        const SPK_NO = '<?= addslashes($spk_no) ?>';

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
                    var disabledAttr = isScanned ? 'disabled' : '';
                    var badgeHtml = '';
                    
                    if (isScanned) {
                        badgeHtml = '<br><span class="badge bg-success">Scanned di ' + assignedSPKC + '</span>';
                    } else if (assignedSPKC) {
                        badgeHtml = '<br><span class="badge bg-warning text-dark">Terdaftar di ' + assignedSPKC + '</span>';
                    }

                    tbodyWip += '<tr>';
                    tbodyWip += '<td class="text-center">';
                    tbodyWip += '<input type="checkbox" class="form-check-input coil-checkbox" ' + disabledAttr + ' ' +
                        'data-id-material="' + escHtml(String(idMaterial)) + '" ' +
                        'data-id-coil="' + escHtml(String(coil.id)) + '" ' +
                        'data-no-coil="' + escHtml(coil.no_coil || '') + '" ' +
                        'data-kode-internal="' + escHtml(coil.kode_internal || '') + '" ' +
                        'data-id-gudang="3" ' +
                        'data-target="wip" ' +
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
                
                Swal.fire({
                    title: 'Peringatan',
                    text: 'Coil ini sudah ada di ' + assignedSpkc + ' dan belum discan. Apakah Anda ingin mengeluarkannya dan memasukannya ke SPK Coil baru ini?',
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
                            if (res.status == 1) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false,
                                }).then(function() {
                                    window.location.href = siteurl + active_controller;
                                });
                            } else {
                                btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save & Create SPK');
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