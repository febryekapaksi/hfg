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
.skeleton-line.short { width: 60%; }
.skeleton-line.medium { width: 80%; }
.skeleton-line.tall { height: 40px; }
.skeleton-block {
    height: 150px;
    margin: 12px 0;
    border-radius: 6px;
}
@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
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
.coil-table th, .coil-table td {
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
                <div class="col-md-3"><div class="skeleton skeleton-line tall"></div></div>
                <div class="col-md-3"><div class="skeleton skeleton-line tall"></div></div>
                <div class="col-md-3"><div class="skeleton skeleton-line tall"></div></div>
                <div class="col-md-3"><div class="skeleton skeleton-line tall"></div></div>
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
                    <?php foreach ($products as $product): ?>
                        <h6 class="mt-4 mb-3">
                            <i class="fa fa-box me-1"></i>
                            <?= htmlspecialchars(isset($product['nm_produk_fg']) ? $product['nm_produk_fg'] : 'Produk') ?>
                        </h6>

                        <?php if (!empty($product['materials'])): ?>
                            <?php foreach ($product['materials'] as $material): ?>
                                <div class="material-section" data-id-material="<?= htmlspecialchars($material['id_material']) ?>">
                                    <div class="section-title">
                                        <i class="fa fa-cubes me-1"></i>
                                        <?= htmlspecialchars($material['nm_material']) ?>
                                        <small class="text-muted ms-2">(Qty BOM: <?= isset($material['qty']) ? number_format($material['qty'], 2) : '0' ?> <?= htmlspecialchars(isset($material['nm_unit']) ? $material['nm_unit'] : '') ?>)</small>
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
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Plan Use <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control input-plan-use"
                                                       id="plan-use-<?= htmlspecialchars($material['id_material']) ?>"
                                                       data-id-material="<?= htmlspecialchars($material['id_material']) ?>"
                                                       min="0" step="1" placeholder="0" value="0">
                                            </div>
                                            <div class="col-md-3 d-flex align-items-end">
                                                <div>
                                                    <small class="text-muted d-block">Total Coil Gudang Coil:</small>
                                                    <strong id="total-gudang-<?= htmlspecialchars($material['id_material']) ?>">0</strong>
                                                </div>
                                            </div>
                                            <div class="col-md-3 d-flex align-items-end">
                                                <div>
                                                    <small class="text-muted d-block">Total Coil WIP:</small>
                                                    <strong id="total-wip-<?= htmlspecialchars($material['id_material']) ?>">0</strong>
                                                </div>
                                            </div>
                                            <div class="col-md-3 d-flex align-items-end">
                                                <div>
                                                    <small class="text-muted d-block">Checked:</small>
                                                    <strong id="checked-count-<?= htmlspecialchars($material['id_material']) ?>">0</strong>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm coil-table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="5%" class="text-center">
                                                            <input type="checkbox" class="form-check-input check-all"
                                                                   data-id-material="<?= htmlspecialchars($material['id_material']) ?>">
                                                        </th>
                                                        <th>No Coil</th>
                                                        <th>Kode Internal</th>
                                                        <th class="text-end">Net Weight</th>
                                                        <th class="text-center">Sumber</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="coil-body-<?= htmlspecialchars($material['id_material']) ?>">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
    const SPK_NO  = '<?= addslashes($spk_no) ?>';

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
                    $('#coil-body-' + idMaterial).html(
                        '<tr><td colspan="5" class="text-center text-muted">Gagal memuat data: ' + escHtml(res.message || '') + '</td></tr>'
                    );
                }
            },
            error: function() {
                $('#skeleton-' + idMaterial).hide();
                $('#content-' + idMaterial).show();
                $('#coil-body-' + idMaterial).html(
                    '<tr><td colspan="5" class="text-center text-danger">Terjadi kesalahan jaringan.</td></tr>'
                );
            }
        });
    }

    /**
     * Render coil table rows
     */
    function renderCoilTable(idMaterial, data, totalGudang, totalWip) {
        var tbody = '';
        var allCoils = [];

        // Combine gudang_coil and wip
        if (data.gudang_coil && data.gudang_coil.length > 0) {
            data.gudang_coil.forEach(function(coil) {
                coil._source = 'gudang_coil';
                allCoils.push(coil);
            });
        }
        if (data.wip && data.wip.length > 0) {
            data.wip.forEach(function(coil) {
                coil._source = 'wip';
                allCoils.push(coil);
            });
        }

        if (allCoils.length === 0) {
            tbody = '<tr><td colspan="5" class="text-center text-muted">Tidak ada coil tersedia untuk material ini.</td></tr>';
        } else {
            allCoils.forEach(function(coil) {
                var badgeClass = coil._source === 'gudang_coil' ? 'badge-gudang-coil' : 'badge-wip';
                var badgeText  = coil._source === 'gudang_coil' ? 'Gudang Coil' : 'WIP';
                var idGudang   = coil._source === 'gudang_coil' ? 1 : 3;

                tbody += '<tr>';
                tbody += '<td class="text-center">';
                tbody += '<input type="checkbox" class="form-check-input coil-checkbox" '
                       + 'data-id-material="' + escHtml(String(idMaterial)) + '" '
                       + 'data-id-coil="' + escHtml(String(coil.id)) + '" '
                       + 'data-no-coil="' + escHtml(coil.no_coil || '') + '" '
                       + 'data-kode-internal="' + escHtml(coil.kode_internal || '') + '" '
                       + 'data-id-gudang="' + idGudang + '" '
                       + 'data-nm-material="' + escHtml(coil.nm_material || '') + '">';
                tbody += '</td>';
                tbody += '<td>' + escHtml(coil.no_coil || '-') + '</td>';
                tbody += '<td>' + escHtml(coil.kode_internal || '-') + '</td>';
                tbody += '<td class="text-end">' + parseFloat(coil.net_weight || 0).toFixed(2) + '</td>';
                tbody += '<td class="text-center"><span class="badge ' + badgeClass + '">' + badgeText + '</span></td>';
                tbody += '</tr>';
            });
        }

        $('#coil-body-' + idMaterial).html(tbody);
        $('#total-gudang-' + idMaterial).text(totalGudang || 0);
        $('#total-wip-' + idMaterial).text(totalWip || 0);
    }

    /**
     * Check All toggle per material
     */
    $(document).on('change', '.check-all', function() {
        var idMaterial = $(this).data('id-material');
        var isChecked  = $(this).is(':checked');
        $('#coil-body-' + idMaterial + ' .coil-checkbox').prop('checked', isChecked);
        updateCheckedCount(idMaterial);
    });

    /**
     * Individual checkbox change
     */
    $(document).on('change', '.coil-checkbox', function() {
        var idMaterial = $(this).data('id-material');
        updateCheckedCount(idMaterial);

        // Update check-all state
        var total   = $('#coil-body-' + idMaterial + ' .coil-checkbox').length;
        var checked = $('#coil-body-' + idMaterial + ' .coil-checkbox:checked').length;
        $('.check-all[data-id-material="' + idMaterial + '"]').prop('checked', total > 0 && checked === total);
    });

    /**
     * Update checked count display
     */
    function updateCheckedCount(idMaterial) {
        var checked = $('#coil-body-' + idMaterial + ' .coil-checkbox:checked').length;
        $('#checked-count-' + idMaterial).text(checked);
    }

    /**
     * Save & Create SPK Coil
     */
    $('#btnSaveSpkCoil').on('click', function() {
        // Client-side validation
        var isValid = true;
        var errorMessages = [];

        $('.material-section').each(function() {
            var idMaterial = $(this).data('id-material');
            var planUse    = parseInt($('#plan-use-' + idMaterial).val()) || 0;
            var checked    = $('#coil-body-' + idMaterial + ' .coil-checkbox:checked').length;
            var totalGudang = parseInt($('#total-gudang-' + idMaterial).text()) || 0;
            var totalWip    = parseInt($('#total-wip-' + idMaterial).text()) || 0;
            var totalCoil   = totalGudang + totalWip;

            // Skip material with 0 plan use and 0 checked (user didn't interact)
            if (planUse === 0 && checked === 0) {
                return; // continue to next material
            }

            // Validate Plan Use does not exceed Total Coil
            if (planUse > totalCoil) {
                isValid = false;
                var matName = $(this).find('.section-title').text().trim();
                errorMessages.push('Plan Use untuk "' + matName + '" melebihi Total Coil yang tersedia (' + totalCoil + ').');
            }

            // Validate number of checked coils = Plan Use
            if (checked !== planUse) {
                isValid = false;
                var matName = $(this).find('.section-title').text().trim();
                errorMessages.push('Jumlah coil yang dicentang (' + checked + ') tidak sesuai dengan Plan Use (' + planUse + ') untuk "' + matName + '".');
            }
        });

        // Check at least one coil is selected
        var totalChecked = $('.coil-checkbox:checked').length;
        if (totalChecked === 0) {
            isValid = false;
            errorMessages.push('Minimal 1 coil harus dipilih.');
        }

        if (!isValid) {
            Swal.fire('Validasi Gagal', errorMessages.join('<br>'), 'error');
            return;
        }

        // Collect data
        var coils = [];
        $('.coil-checkbox:checked').each(function() {
            var idMaterial = $(this).data('id-material');
            var planUse    = parseInt($('#plan-use-' + idMaterial).val()) || 0;

            coils.push({
                id_coil:         $(this).data('id-coil'),
                id_material:     idMaterial,
                nm_material:     $(this).data('nm-material'),
                kode_internal:   $(this).data('kode-internal'),
                no_coil:         $(this).data('no-coil'),
                id_gudang_sumber: $(this).data('id-gudang'),
                plan_use:        planUse
            });
        });

        var postData = {
            spk_no: SPK_NO,
            coils: coils
        };

        // Disable button
        var $btn = $('#btnSaveSpkCoil');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: BASE_URL + '/save_spk_coil',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save & Create SPK');

                if (res.status == 1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message || 'SPK Coil berhasil dibuat.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        window.location.href = siteurl + 'request_list';
                    });
                } else {
                    Swal.fire('Error', res.message || 'Gagal menyimpan SPK Coil.', 'error');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save & Create SPK');
                Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
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
