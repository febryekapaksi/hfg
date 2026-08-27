<?php $ENABLE_MANAGE = has_permission('Request_List.Manage'); ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.material-section { border: 1px solid #e0e0e0; border-radius: 8px; padding: 16px; margin-bottom: 16px; background: #fafbfc; }
.material-section .section-title { font-weight: 600; margin-bottom: 12px; color: #495057; }
.pack-table th, .pack-table td { vertical-align: middle; font-size: 12px; }
.info-header { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 16px; margin-bottom: 20px; }
.info-header .info-label { font-size: 10px; text-transform: uppercase; font-weight: 700; color: #6c757d; display: block; }
.info-header .info-value { font-size: 15px; font-weight: 600; color: #343a40; }
</style>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-cogs me-2"></i> Manage Request Pack</h5>
        <a href="<?= site_url('request_list') ?>" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">

        <!-- Header Info -->
        <div class="info-header">
            <div class="d-flex flex-wrap align-items-center gap-4">
                <div><span class="info-label">SPK No</span><span class="info-value"><?= htmlspecialchars($spk_no) ?></span></div>
                <div class="border-start ps-4"><span class="info-label">Tanggal SPK</span><span class="info-value"><?= isset($header['tgl_spk']) ? date('d/m/Y', strtotime($header['tgl_spk'])) : '-' ?></span></div>
                <div class="border-start ps-4"><span class="info-label">Shift</span><span class="info-value"><?= htmlspecialchars($header['shift_names'] ?? '-') ?></span></div>
                <div class="border-start ps-4"><span class="info-label">Target Qty</span><span class="info-value"><?= isset($header['target_qty']) ? number_format($header['target_qty']) : '-' ?></span></div>
                <div class="border-start ps-4"><span class="info-label">Total Weight</span><span class="info-value"><?= isset($header['total_weight']) ? number_format($header['total_weight'], 2) . ' Kg' : '-' ?></span></div>
            </div>
        </div>

        <!-- Saved SPK Coils -->
        <div class="card mb-4 border-info">
            <div class="card-header bg-info text-dark d-flex justify-content-between align-items-center" style="cursor:pointer;" id="toggle-saved">
                <h6 class="mb-0 fw-bold"><i class="fa fa-list-alt me-2"></i> Daftar SPK Pack Yang Sudah Dibuat</h6>
                <span class="badge bg-light text-dark fw-bold" id="saved-count"><?= isset($saved_spk_coils) ? count($saved_spk_coils) : 0 ?> SPK Pack</span>
            </div>
            <div class="card-body p-3" id="container-saved"></div>
        </div>

        <!-- Search -->
        <div class="mb-4">
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="fa fa-search text-muted"></i></span>
                <input type="text" id="search-pack" class="form-control" placeholder="Cari pack code / material...">
            </div>
        </div>

        <!-- Material Sections with Pack Lists -->
        <div id="material-sections">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $idx => $product): ?>
                    <div class="card mb-4 border-secondary">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0 text-white fw-bold"><i class="fa fa-box me-2"></i> <?= htmlspecialchars($product['nm_produk_fg'] ?? 'Produk') ?></h6>
                        </div>
                        <div class="card-body p-3">
                            <?php if (!empty($product['materials'])): ?>
                                <?php foreach ($product['materials'] as $material): ?>
                                    <div class="material-section" data-id-material="<?= htmlspecialchars($material['id_material']) ?>">
                                        <div class="section-title d-flex justify-content-between align-items-start">
                                            <div>
                                                <i class="fa fa-cubes me-1"></i> <?= htmlspecialchars($material['nm_material']) ?>
                                                <small class="text-muted d-block mt-1">(BOM Qty: <?= number_format($material['qty'] ?? 0, 2) ?> <?= htmlspecialchars($material['nm_unit'] ?? '') ?>)</small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-info text-dark">Stock Produksi: <?= number_format($material['stock_produksi'] ?? 0, 2) ?></span>
                                            </div>
                                        </div>

                                        <!-- Pack Table WIP -->
                                        <h6 class="text-warning border-bottom pb-1 mb-2 mt-3"><i class="fa fa-industry me-1"></i> Warehouse WIP</h6>
                                        <div class="table-responsive mb-3">
                                            <table class="table table-bordered table-sm pack-table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="4%" class="text-center">
                                                            <input type="checkbox" class="form-check-input check-all-pack" data-id-material="<?= htmlspecialchars($material['id_material']) ?>" data-target="wip">
                                                        </th>
                                                        <th>Pack Code</th>
                                                        <th class="text-center" width="8%">Roll</th>
                                                        <th class="text-end" width="12%">N.W. Total</th>
                                                        <th class="text-end" width="12%">G.W. Total</th>
                                                        <th class="text-center" width="7%">Detail</th>
                                                        <th class="text-center" width="10%">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="pack-body-wip-<?= htmlspecialchars($material['id_material']) ?>">
                                                    <tr><td colspan="7" class="text-center py-2"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Pack Table Production -->
                                        <h6 class="text-info border-bottom pb-1 mb-2"><i class="fa fa-warehouse me-1"></i> Warehouse Production</h6>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm pack-table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="4%" class="text-center">
                                                            <input type="checkbox" class="form-check-input check-all-pack" data-id-material="<?= htmlspecialchars($material['id_material']) ?>" data-target="pro">
                                                        </th>
                                                        <th>Pack Code</th>
                                                        <th class="text-center" width="8%">Roll</th>
                                                        <th class="text-end" width="12%">N.W. Total</th>
                                                        <th class="text-end" width="12%">G.W. Total</th>
                                                        <th class="text-center" width="7%">Detail</th>
                                                        <th class="text-center" width="10%">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="pack-body-pro-<?= htmlspecialchars($material['id_material']) ?>">
                                                    <tr><td colspan="7" class="text-center py-2"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>
                                                </tbody>
                                            </table>
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
                <div class="text-center text-muted py-4"><i class="fa fa-info-circle fa-2x mb-2"></i><p>Tidak ada produk/material ditemukan untuk SPK ini.</p></div>
            <?php endif; ?>
        </div>

        <!-- Submit -->
        <div class="mt-4 d-flex gap-2 justify-content-end">
            <a href="<?= site_url('request_list') ?>" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i> Kembali</a>
            <?php if ($ENABLE_MANAGE): ?>
                <button type="button" class="btn btn-sm btn-primary" id="btnSaveSpkCoil"><i class="fa fa-save"></i> Save & Create SPK Pack</button>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Modal Pack Detail -->
<div class="modal fade" id="modalPackDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa fa-eye me-1"></i> Pack Detail — <span id="modalPackCode"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalPackBody" style="max-height:70vh; overflow-y:auto;"></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>

<!-- Modal Add Pack to Existing SPK Pack -->
<div class="modal fade" id="modalAddPackToSpkc" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="fa fa-plus-circle me-2"></i> Manage Pack — <span id="modal-target-spkc-no" class="fw-bold"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                    <input type="text" id="search-add-pack" class="form-control" placeholder="Cari pack code...">
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center"><input type="checkbox" class="form-check-input" id="checkAllAddPack"></th>
                                <th>Pack Code</th>
                                <th class="text-center">Roll</th>
                                <th class="text-end">N.W.</th>
                                <th class="text-end">G.W.</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="6%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-add-pack">
                            <tr><td colspan="7" class="text-center text-muted py-3">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnConfirmAddPack"><i class="fa fa-plus"></i> Tambahkan Pack</button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_URL = siteurl + active_controller;
const SPK_NO = '<?= addslashes($spk_no) ?>';

$(document).ready(function() {

    // Load saved SPK Coils
    loadSavedSpkCoils();

    // Load pack data per material
    var materialIds = [];
    $('.material-section').each(function() { materialIds.push($(this).data('id-material')); });
    materialIds.forEach(function(id) { loadPackData(id); });

    // Check All per material per gudang
    $(document).on('change', '.check-all-pack', function() {
        var idMat = $(this).data('id-material');
        var target = $(this).data('target');
        var checked = $(this).is(':checked');
        $('#pack-body-' + target + '-' + idMat + ' .pack-checkbox:not(:disabled)').prop('checked', checked);
    });

    // Sync check-all when individual checkboxes change
    $(document).on('change', '.pack-checkbox', function() {
        var tr = $(this).closest('tbody');
        var tbodyId = tr.attr('id') || '';
        // Find the matching check-all
        var total = tr.find('.pack-checkbox:not(:disabled)').length;
        var checked = tr.find('.pack-checkbox:not(:disabled):checked').length;
        // Find check-all for this tbody
        var table = tr.closest('table');
        var checkAll = table.find('.check-all-pack');
        checkAll.prop('checked', total > 0 && total === checked);
    });

    // Search
    $('#search-pack').on('keyup', function() {
        var kw = $(this).val().toLowerCase().trim();
        $('.pack-table tbody tr.pack-row').each(function() {
            $(this).toggle(($(this).data('search') || '').indexOf(kw) > -1);
        });
    });

    // Detail modal
    $(document).on('click', '.btn-pack-detail', function() {
        var idPack = $(this).data('id-pack');
        var packCode = $(this).data('pack-code');
        var idMaterial = $(this).data('id-material');
        $('#modalPackCode').text(packCode);
        $('#modalPackBody').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>');
        $('#modalPackDetail').modal('show');

        $.get(BASE_URL + '/get_pack_coils_detail/' + idPack + '?id_material=' + idMaterial, function(res) {
            if (res.status == 1 && res.data.length > 0) {
                var html = '<table class="table table-bordered table-sm table-striped" style="font-size:11px;"><thead class="table-light"><tr><th class="text-center">No</th><th>No. Coil</th><th>Internal Code</th><th class="text-end">N.W.</th><th class="text-end">G.W.</th><th class="text-end">Length</th></tr></thead><tbody>';
                var tNw = 0, tGw = 0;
                res.data.forEach(function(c, i) {
                    tNw += parseFloat(c.net_weight || 0); tGw += parseFloat(c.gross_weight || 0);
                    html += '<tr><td class="text-center">'+(i+1)+'</td><td>'+esc(c.no_coil)+'</td><td>'+esc(c.kode_internal)+'</td><td class="text-end">'+fmt(c.net_weight)+'</td><td class="text-end">'+fmt(c.gross_weight)+'</td><td class="text-end">'+fmt(c.length)+'</td></tr>';
                });
                html += '</tbody><tfoot class="table-secondary"><tr><td colspan="3" class="text-end fw-bold">Total</td><td class="text-end fw-bold">'+fmt(tNw)+'</td><td class="text-end fw-bold">'+fmt(tGw)+'</td><td></td></tr></tfoot></table>';
                $('#modalPackBody').html(html);
            } else {
                $('#modalPackBody').html('<div class="text-center text-muted py-4">No coil data.</div>');
            }
        }, 'json');
    });

    // Save
    $('#btnSaveSpkCoil').on('click', function() {
        var selectedPacks = [];
        var fromOtherSpk = [];

        $('.pack-checkbox:checked').each(function() {
            var item = {
                id_pack: $(this).data('id-pack'),
                pack_code: $(this).data('pack-code'),
                id_material: $(this).data('id-material'),
                assigned_request_id: $(this).data('assigned-req-id') || ''
            };
            selectedPacks.push(item);

            // Check if from another SPK
            if (item.assigned_request_id) {
                var tr = $(this).closest('tr');
                var statusTd = tr.find('td:last').text().trim();
                if (statusTd && statusTd !== 'Available') {
                    fromOtherSpk.push(item.pack_code);
                }
            }
        });

        if (selectedPacks.length === 0) {
            Swal.fire('Perhatian', 'Pilih minimal 1 pack.', 'warning');
            return;
        }

        var confirmMsg = selectedPacks.length + ' pack dipilih. Lanjutkan?';
        var confirmHtml = '';

        if (fromOtherSpk.length > 0) {
            confirmHtml = '<p>' + selectedPacks.length + ' pack dipilih.</p>' +
                '<p class="text-warning"><i class="fa fa-exclamation-triangle"></i> Pack berikut akan <b>dipindah</b> dari SPK Pack lain:</p>' +
                '<ul style="text-align:left;font-size:12px;">' + fromOtherSpk.map(function(p) { return '<li>' + p + '</li>'; }).join('') + '</ul>';
        }

        Swal.fire({
            title: 'Buat SPK Pack?',
            html: confirmHtml || ('<p>' + confirmMsg + '</p>'),
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Buat SPK Pack!',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            var btn = $('#btnSaveSpkCoil');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

            $.post(BASE_URL + '/save_spk_coil', { spk_no: SPK_NO, packs: selectedPacks }, function(res) {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save & Create SPK Pack');
                if (res.status == 1) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                    loadSavedSpkCoils();
                    materialIds.forEach(function(id) { loadPackData(id); });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }, 'json').fail(function() {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save & Create SPK Pack');
                Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
            });
        });
    });

    // Toggle saved section
    $('#toggle-saved').on('click', function() { $('#container-saved').slideToggle(); });

    // Delete SPK Coil
    $(document).on('click', '.btn-delete-spkc', function() {
        var reqId = $(this).data('id');
        var spkcNo = $(this).data('no');
        Swal.fire({
            title: 'Hapus ' + spkcNo + '?',
            text: 'Pack di dalamnya akan dilepas kembali.',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.post(BASE_URL + '/delete_spk_coil', { request_id: reqId }, function(res) {
                    if (res.status == 1) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                        loadSavedSpkCoils();
                        materialIds.forEach(function(id) { loadPackData(id); });
                    } else { Swal.fire('Gagal', res.message, 'error'); }
                }, 'json');
            }
        });
    });

    // Remove single pack from SPK Pack (from saved list badge ×)
    $(document).on('click', '.btn-remove-pack-item', function(e) {
        e.preventDefault();
        var reqId = $(this).data('request-id');
        var packCode = $(this).data('pack-code');

        Swal.fire({
            title: 'Hapus Pack ' + packCode + '?',
            text: 'Pack ini akan dilepas dari SPK Pack.',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            $.post(BASE_URL + '/remove_pack_from_spkc', { request_id: reqId, pack_code: packCode }, function(res) {
                if (res.status == 1) {
                    loadSavedSpkCoils();
                    materialIds.forEach(function(id) { loadPackData(id); });

                    if (res.spk_deleted) {
                        Swal.fire({ icon: 'info', title: 'SPK Pack Dihapus', text: res.message, timer: 2000, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                    }
                } else { Swal.fire('Gagal', res.message, 'error'); }
            }, 'json');
        });
    });

    // Add Pack to existing SPK Pack — open modal
    var addPackTargetReqId = null;
    $(document).on('click', '.btn-add-pack-to-spkc', function() {
        addPackTargetReqId = $(this).data('id');
        var spkcNo = $(this).data('no');
        $('#modal-target-spkc-no').text(spkcNo);
        $('#tbody-add-pack').html('<tr><td colspan="6" class="text-center py-3"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
        $('#searchAddPack').val('');
        $('#checkAllAddPack').prop('checked', false);
        $('#modalAddPackToSpkc').modal('show');

        // Load all available packs from all materials
        var allPacks = [];
        var loadCount = 0;

        if (materialIds.length === 0) {
            $('#tbody-add-pack').html('<tr><td colspan="6" class="text-center text-muted">Tidak ada material.</td></tr>');
            return;
        }

        materialIds.forEach(function(idMat) {
            $.get(BASE_URL + '/get_available_packs/' + idMat + '?spk_no=' + SPK_NO, function(res) {
                loadCount++;
                if (res.status == 1 && res.data) {
                    res.data.forEach(function(p) {
                        p._id_material = idMat;
                        // Avoid duplicates
                        if (!allPacks.find(function(x) { return x.id_pack == p.id_pack; })) {
                            allPacks.push(p);
                        }
                    });
                }
                if (loadCount === materialIds.length) {
                    renderAddPackModal(allPacks);
                }
            }, 'json');
        });
    });

    function renderAddPackModal(packs) {
        if (!packs || packs.length === 0) {
            $('#tbody-add-pack').html('<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada pack tersedia.</td></tr>');
            return;
        }
        var html = '';
        packs.forEach(function(p) {
            var isScanned = parseInt(p.scan_status) === 1;
            var assignedSPKC = p.assigned_spkc || '';
            var assignedReqId = parseInt(p.assigned_request_id) || 0;

            // Cek apakah pack ini sudah ada di SPK Pack yang sedang di-manage
            var isInThisSpk = (assignedReqId === addPackTargetReqId);
            var isDisabled = isScanned || isInThisSpk;
            var badge = '';
            var actionHtml = '';

            if (isScanned) {
                badge = '<span class="badge bg-success">Scanned</span>';
                actionHtml = '-';
            } else if (isInThisSpk) {
                badge = '<span class="badge bg-secondary">Sudah di SPK ini</span>';
                // Bisa dihapus karena belum scanned
                actionHtml = '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-from-modal" data-pack-code="' + esc(p.pack_code) + '" title="Hapus dari SPK ini"><i class="fa fa-trash"></i></button>';
            } else if (assignedSPKC) {
                badge = '<span class="badge bg-warning text-dark">di ' + esc(assignedSPKC) + '</span>';
                actionHtml = '-';
            } else {
                badge = '<span class="badge bg-light text-dark border">Available</span>';
                actionHtml = '-';
            }

            html += '<tr class="add-pack-row" data-search="' + (p.pack_code || '').toLowerCase() + '">' +
                '<td class="text-center"><input type="checkbox" class="form-check-input add-pack-check" ' + (isDisabled ? 'disabled' : '') +
                ' data-id-pack="' + p.id_pack + '" data-pack-code="' + esc(p.pack_code) + '" data-id-material="' + esc(p._id_material || '') + '"' +
                ' data-assigned-spkc="' + esc(assignedSPKC) + '" data-assigned-req-id="' + assignedReqId + '"></td>' +
                '<td><span class="badge bg-primary">' + esc(p.pack_code) + '</span></td>' +
                '<td class="text-center">' + (parseInt(p.roll_count) || 0) + '</td>' +
                '<td class="text-end">' + fmt(p.total_nw) + '</td>' +
                '<td class="text-end">' + fmt(p.total_gw) + '</td>' +
                '<td class="text-center">' + badge + '</td>' +
                '<td class="text-center">' + actionHtml + '</td></tr>';
        });
        $('#tbody-add-pack').html(html);
    }

    // Search in add-pack modal
    $('#search-add-pack').on('keyup', function() {
        var kw = $(this).val().toLowerCase().trim();
        $('.add-pack-row').each(function() { $(this).toggle(($(this).data('search') || '').indexOf(kw) > -1); });
    });

    // Remove pack from this SPK Pack (inside Manage modal)
    $(document).on('click', '.btn-remove-from-modal', function() {
        var packCode = $(this).data('pack-code');
        var $btn = $(this);

        Swal.fire({
            title: 'Hapus Pack ' + packCode + '?',
            text: 'Pack ini akan dilepas dari SPK Pack ini.',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            $.post(BASE_URL + '/remove_pack_from_spkc', { request_id: addPackTargetReqId, pack_code: packCode }, function(res) {
                if (res.status == 1) {
                    $btn.closest('tr').fadeOut(300, function() { $(this).remove(); });
                    loadSavedSpkCoils();
                    materialIds.forEach(function(id) { loadPackData(id); });

                    if (res.spk_deleted) {
                        // SPK Pack dihapus karena kosong — tutup modal
                        $('#modalAddPackToSpkc').modal('hide');
                        Swal.fire({ icon: 'info', title: 'SPK Pack Dihapus', text: res.message, timer: 2000, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1200, showConfirmButton: false });
                    }
                } else {
                    $btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                    Swal.fire('Gagal', res.message, 'error');
                }
            }, 'json');
        });
    });

    // Check all in add-pack modal
    $('#checkAllAddPack').on('change', function() {
        var checked = $(this).is(':checked');
        $('#tbody-add-pack .add-pack-check:not(:disabled)').prop('checked', checked);
    });

    // Confirm add packs to existing SPK Pack
    $('#btnConfirmAddPack').on('click', function() {
        var selected = [];
        var fromOtherSpk = [];

        $('#tbody-add-pack .add-pack-check:checked:not(:disabled)').each(function() {
            var item = {
                id_pack: $(this).data('id-pack'),
                pack_code: $(this).data('pack-code'),
                id_material: $(this).data('id-material')
            };
            selected.push(item);

            var assignedSpkc = $(this).data('assigned-spkc');
            if (assignedSpkc) {
                fromOtherSpk.push(item.pack_code + ' (dari ' + assignedSpkc + ')');
            }
        });

        if (selected.length === 0) {
            Swal.fire('Perhatian', 'Pilih minimal 1 pack.', 'warning');
            return;
        }

        // Jika ada pack dari SPK lain, tampilkan konfirmasi
        if (fromOtherSpk.length > 0) {
            Swal.fire({
                title: 'Pindahkan Pack?',
                html: '<p>Pack berikut akan <b>dipindah</b> dari SPK lain ke SPK ini:</p><ul style="text-align:left;font-size:13px;">' +
                    fromOtherSpk.map(function(p) { return '<li>' + p + '</li>'; }).join('') + '</ul>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Pindahkan!',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) doAddPacks(selected);
            });
        } else {
            doAddPacks(selected);
        }
    });

    function doAddPacks(selected) {
        var $btn = $('#btnConfirmAddPack');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

        $.post(BASE_URL + '/add_packs_to_spkc', { request_id: addPackTargetReqId, packs: selected }, function(res) {
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Tambahkan Pack');
            if (res.status == 1) {
                $('#modalAddPackToSpkc').modal('hide');
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                loadSavedSpkCoils();
                materialIds.forEach(function(id) { loadPackData(id); });
            } else { Swal.fire('Gagal', res.message, 'error'); }
        }, 'json').fail(function() {
            $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Tambahkan Pack');
            Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
        });
    }
});

function loadPackData(idMaterial) {
    var tbodyWip = $('#pack-body-wip-' + idMaterial);
    var tbodyPro = $('#pack-body-pro-' + idMaterial);
    tbodyWip.html('<tr><td colspan="7" class="text-center py-2"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
    tbodyPro.html('<tr><td colspan="7" class="text-center py-2"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');

    $.get(BASE_URL + '/get_available_packs/' + idMaterial + '?spk_no=' + SPK_NO, function(res) {
        if (res.status != 1 || !res.data || res.data.length === 0) {
            tbodyWip.html('<tr><td colspan="7" class="text-center text-muted py-2">Tidak ada pack WIP tersedia.</td></tr>');
            tbodyPro.html('<tr><td colspan="7" class="text-center text-muted py-2">Tidak ada pack Produksi tersedia.</td></tr>');
            return;
        }

        var htmlWip = '';
        var htmlPro = '';

        res.data.forEach(function(p) {
            var isScanned = parseInt(p.scan_status) === 1;
            var assignedSPKC = p.assigned_spkc || '';
            var assignedReqId = p.assigned_request_id || '';
            // Hanya disable jika SUDAH SCANNED - assigned tapi belum scan masih bisa dipilih
            var isDisabled = isScanned;
            var badge = '';

            if (isScanned) {
                badge = '<span class="badge bg-success">Scanned</span>';
            } else if (assignedSPKC) {
                badge = '<span class="badge bg-warning text-dark">di ' + esc(assignedSPKC) + '</span>';
            } else {
                badge = '<span class="badge bg-light text-dark border">Available</span>';
            }

            var row = '<tr class="pack-row" data-search="' + (p.pack_code + ' ' + idMaterial).toLowerCase() + '">' +
                '<td class="text-center"><input type="checkbox" class="form-check-input pack-checkbox" ' + (isDisabled ? 'disabled' : '') +
                ' data-id-pack="' + p.id_pack + '" data-pack-code="' + esc(p.pack_code) + '" data-id-material="' + esc(idMaterial) + '" data-assigned-req-id="' + esc(String(assignedReqId)) + '"></td>' +
                '<td><span class="badge bg-primary">' + esc(p.pack_code) + '</span></td>' +
                '<td class="text-center">' + (parseInt(p.roll_count) || 0) + '</td>' +
                '<td class="text-end">' + fmt(p.total_nw) + '</td>' +
                '<td class="text-end">' + fmt(p.total_gw) + '</td>' +
                '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-info btn-pack-detail" data-id-pack="' + p.id_pack + '" data-pack-code="' + esc(p.pack_code) + '" data-id-material="' + esc(idMaterial) + '"><i class="fa fa-eye"></i></button></td>' +
                '<td class="text-center">' + badge + '</td></tr>';

            var kd = (p.kd_gudang || '').toUpperCase();
            if (kd === 'WIP') {
                htmlWip += row;
            } else {
                htmlPro += row;
            }
        });

        tbodyWip.html(htmlWip || '<tr><td colspan="7" class="text-center text-muted py-2">Tidak ada pack WIP tersedia.</td></tr>');
        tbodyPro.html(htmlPro || '<tr><td colspan="7" class="text-center text-muted py-2">Tidak ada pack Produksi tersedia.</td></tr>');
    }, 'json').fail(function() {
        tbodyWip.html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data.</td></tr>');
        tbodyPro.html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data.</td></tr>');
    });
}

function loadSavedSpkCoils() {
    $.get(BASE_URL + '/get_saved_spk_coils/' + SPK_NO, function(res) {
        if (res.status != 1 || !res.data || res.data.length === 0) {
            $('#container-saved').html('<div class="text-center text-muted py-3">Belum ada SPK Pack.</div>');
            $('#saved-count').text('0 SPK Pack');
            return;
        }

        var html = '';
        res.data.forEach(function(spkc) {
            // Count unique packs
            var packMap = {};
            (spkc.coils || []).forEach(function(c) {
                if (c.pack_code) {
                    if (!packMap[c.pack_code]) packMap[c.pack_code] = { count: 0, scanned: true };
                    packMap[c.pack_code].count++;
                    if (parseInt(c.scan_status) === 0) packMap[c.pack_code].scanned = false;
                }
            });
            var packCodes = Object.keys(packMap);
            var packCount = packCodes.length;
            var coilCount = (spkc.coils || []).length;

            // Check if all scanned
            var allScanned = coilCount > 0;
            (spkc.coils || []).forEach(function(c) { if (parseInt(c.scan_status) === 0) allScanned = false; });

            var statusBadge = allScanned
                ? '<span class="badge bg-success">All Scanned</span>'
                : '<span class="badge bg-info">' + esc(spkc.status) + '</span>';

            // Pack list with remove button per pack
            var packListHtml = '<div class="mt-2" style="font-size:11px;">';
            packCodes.forEach(function(pc) {
                var isPackScanned = packMap[pc].scanned;
                if (!allScanned && !isPackScanned) {
                    packListHtml += '<span class="badge bg-primary me-1 mb-1">' + esc(pc) +
                        ' <a href="#" class="text-white ms-1 btn-remove-pack-item" data-request-id="' + spkc.id + '" data-pack-code="' + esc(pc) + '" title="Hapus pack ini"><i class="fa fa-times"></i></a></span>';
                } else {
                    packListHtml += '<span class="badge bg-success me-1 mb-1"><i class="fa fa-check me-1"></i>' + esc(pc) + '</span>';
                }
            });
            packListHtml += '</div>';

            // Manage buttons
            var manageHtml = '';
            if (!allScanned) {
                manageHtml += '<button type="button" class="btn btn-sm btn-outline-success btn-add-pack-to-spkc" data-id="' + spkc.id + '" data-no="' + esc(spkc.spk_coil_no) + '" title="Tambah Pack"><i class="fa fa-plus"></i></button> ';
                manageHtml += '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-spkc" data-id="' + spkc.id + '" data-no="' + esc(spkc.spk_coil_no) + '" title="Hapus SPK Pack"><i class="fa fa-trash"></i></button>';
            }

            html += '<div class="card mb-2 border">' +
                '<div class="card-body p-2">' +
                '<div class="d-flex justify-content-between align-items-center">' +
                '<div><strong>' + esc(spkc.spk_coil_no) + '</strong> ' + statusBadge +
                ' <small class="text-muted ms-2">' + packCount + ' pack, ' + coilCount + ' coils</small></div>' +
                '<div class="d-flex gap-1">' + manageHtml + '</div>' +
                '</div>' +
                packListHtml +
                '</div></div>';
        });
        $('#container-saved').html(html);
        $('#saved-count').text(res.data.length + ' SPK Pack');
    }, 'json');
}

function esc(str) { if (!str) return ''; return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmt(val) { return parseFloat(val || 0).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
</script>
