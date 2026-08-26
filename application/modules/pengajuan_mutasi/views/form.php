<?php
$ENABLE_ADD    = has_permission('Request_Mutation.Add');
$ENABLE_MANAGE = has_permission('Request_Mutation.Manage');

$is_view  = ($mode === 'view');
$is_edit  = ($mode === 'edit');
$is_add   = ($mode === 'add');
$readonly = $is_view ? 'readonly' : '';
$disabled = $is_view ? 'disabled' : '';

$m       = $mutation ?? [];
$details = $m['details'] ?? [];
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<div class="card">
    <div class="card-body">

        <?php if (!$is_add && !empty($m)): ?>
            <div class="p-3 bg-light border rounded mb-4 w-100">
                <div class="row align-items-center g-3 m-0">
                    <div class="<?= empty($m['reject_reason']) ? 'col-12' : 'col-md-7 col-12' ?> p-0">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 w-100">
                            <div class="px-2 flex-fill">
                                <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Mutation No.</small>
                                <span class="fs-6 fw-bold text-dark"><?= ($m['mutation_number']) ?></span>
                            </div>
                            <div class="px-2 flex-fill border-start-custom">
                                <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Request Date</small>
                                <span class="text-dark fw-semibold"><?= date('d/m/Y', strtotime($m['mutation_date'])) ?></span>
                            </div>
                            <div class="px-2 flex-fill border-start-custom">
                                <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Created By</small>
                                <span class="text-dark fw-semibold"><?= !empty($m['create_by']) ? $m['create_by'] : '-' ?></span>
                            </div>
                            <div class="px-2 flex-fill border-start-custom">
                                <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Status</small>
                                <?php
                                $status_map = [0 => ['Open','primary'], 1 => ['Waiting Approval','warning'], 2 => ['Approved','success'], 3 => ['Rejected','danger'], 4 => ['Done','dark'], 5 => ['Cancelled','secondary'], 6 => ['Revision','danger']];
                                $st = $status_map[$m['status']] ?? ['-','secondary'];
                                ?>
                                <span class="badge bg-<?= $st[1] ?> px-2 py-1"><?= $st[0] ?></span>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($m['reject_reason'])): ?>
                        <div class="col-md-5 col-12 border-start-md ps-md-4 py-1 text-start">
                            <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Reject/Cancel Reason</small>
                            <span class="text-danger fw-semibold"><i class="fa-solid fa-circle-exclamation me-1"></i> <?= ($m['reject_reason']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <form id="formMutasi" enctype="multipart/form-data">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Minutes of Meeting No. <span class="text-danger">*</span></label>
                    <input type="text" id="no_berita_acara" name="no_berita_acara" class="form-control" <?= $readonly ?>
                        value="<?= ($m['no_berita_acara'] ?? '') ?>" placeholder="Enter Minutes of Meeting No.">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Source Warehouse <span class="text-danger">*</span></label>
                    <select id="id_gudang_from" class="form-select" <?= ($disabled || $is_edit) ? 'disabled' : '' ?>>
                        <option value="">-- Select Source Warehouse --</option>
                        <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>" <?= (isset($m['id_gudang_from']) && $m['id_gudang_from'] == $wh['id']) ? 'selected' : '' ?>>
                                <?= ($wh['nm_gudang']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="id_gudang_from" value="<?= $m['id_gudang_from'] ?? '' ?>">
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Destination Warehouse <span class="text-danger">*</span></label>
                    <select id="id_gudang_to" class="form-select" disabled>
                        <option value="">-- Auto-selected --</option>
                        <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>" <?= (isset($m['id_gudang_to']) && $m['id_gudang_to'] == $wh['id']) ? 'selected' : '' ?>>
                                <?= ($wh['nm_gudang']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" id="id_gudang_to_hidden" name="id_gudang_to" value="<?= $m['id_gudang_to'] ?? '' ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <input type="text" id="description" name="description" class="form-control" <?= $readonly ?>
                        value="<?= ($m['description'] ?? '') ?>" placeholder="Enter mutation reason">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Attach File <small class="text-muted">(PDF/JPG/PNG, max 5MB)</small></label>
                    <?php if ($is_view): ?>
                        <?php if (!empty($m['file_name_hash'])): ?>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-paperclip text-primary"></i>
                                <a href="<?= base_url('uploads/berita_acara_mutasi/' . $m['file_name_hash']) ?>" target="_blank" class="text-truncate" style="max-width:200px;"><?= ($m['file_name_original']) ?></a>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">No file attached</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <input type="file" id="berita_acara_file" name="berita_acara_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <?php if ($is_edit && !empty($m['file_name_hash'])): ?>
                            <div class="mt-1"><small class="text-muted">Current: <a href="<?= base_url('uploads/berita_acara_mutasi/' . $m['file_name_hash']) ?>" target="_blank"><?= ($m['file_name_original']) ?></a></small></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pack List -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><i class="fa-solid fa-boxes-stacked me-1"></i> Pack Details</h6>
                <?php if (!$is_view): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddPack">
                        <i class="fa-solid fa-plus"></i> Add Pack
                    </button>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle" id="tblPack">
                    <thead class="table-light">
                        <tr>
                            <th width="3%">No</th>
                            <th class="text-center" width="12%">Pack Code</th>
                            <th>Materials</th>
                            <th class="text-center" width="5%">Roll</th>
                            <th class="text-end" width="10%">N.W. Total</th>
                            <th class="text-end" width="10%">G.W. Total</th>
                            <th class="text-center" width="6%">Detail</th>
                            <?php if (!$is_view): ?>
                                <th class="text-center" width="5%">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="packBody">
                        <tr><td colspan="<?= $is_view ? 7 : 8 ?>" class="text-center text-muted py-3">No pack selected yet.</td></tr>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="3" class="text-end">Total</td>
                            <td class="text-center" id="totalRoll">0</td>
                            <td class="text-end" id="totalNW">0.00</td>
                            <td class="text-end" id="totalGW">0.00</td>
                            <td colspan="<?= $is_view ? 1 : 2 ?>"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <input type="hidden" id="details_json" name="details_json" value="">

            <?php if (!$is_view): ?>
                <div class="mt-3 d-flex gap-2 justify-content-end">
                    <a href="<?= site_url('pengajuan_mutasi') ?>" class="btn btn-secondary">Cancel</a>
                    <button type="button" class="btn btn-primary" id="btnSave"><i class="fa-solid fa-save"></i> Save</button>
                </div>
            <?php else: ?>
                <div class="mt-3 d-flex gap-2 justify-content-end">
                    <a href="<?= site_url('pengajuan_mutasi') ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Modal Pack Picker -->
<div class="modal fade" id="modalPackPicker" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-boxes-stacked me-2"></i> Select Pack</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" id="searchPackInput" class="form-control" placeholder="Search pack code or material...">
                </div>
                <div id="packListContainer" style="max-height:400px; overflow-y:auto;">
                    <div class="text-center py-3 text-muted">Loading...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pack Detail (view coils) -->
<div class="modal fade" id="modalPackDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa fa-eye me-1"></i> Pack Detail — <span id="detailPackCode"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailPackBody" style="max-height:70vh; overflow-y:auto;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const BASE_URL = '<?= site_url('pengajuan_mutasi') ?>';
const MODE = '<?= $mode ?>';
const RECORD_ID = '<?= $id ?? '' ?>';
const IS_VIEW = MODE === 'view';
const EXISTING_DETAILS = <?= json_encode($details) ?>;

let selectedPacks = []; // Array of { id_warehouse_pack, pack_code, materials_concat, roll_count, total_nw, total_gw, coils: [...] }

$(document).ready(function() {

    // Load existing data (edit/view mode)
    if (EXISTING_DETAILS.length > 0) {
        // Group existing details by id_warehouse_pack
        var packMap = {};
        EXISTING_DETAILS.forEach(function(d) {
            var pk = String(d.id_warehouse_pack || d.pack_code || 'unknown');
            if (!packMap[pk]) {
                packMap[pk] = {
                    id_warehouse_pack: d.id_warehouse_pack || 0,
                    pack_code: d.pack_code || pk,
                    materials_concat: '',
                    roll_count: 0,
                    total_nw: 0,
                    total_gw: 0,
                    coils: []
                };
            }
            // Accumulate from coils
            if (d.coils && d.coils.length > 0) {
                d.coils.forEach(function(c) {
                    packMap[pk].roll_count++;
                    packMap[pk].total_nw += parseFloat(c.net_weight || 0);
                    packMap[pk].total_gw += parseFloat(c.gross_weight || 0);
                    // Map coil fields to expected format
                    packMap[pk].coils.push({
                        id: c.id_warehouse_stock_coil || c.id || 0,
                        id_material: d.id_material || d.code_lv4 || '',
                        nm_material: d.nm_material || '',
                        trade_name: d.trade_name || '',
                        no_coil: c.no_coil || '',
                        no_ipp: c.no_ipp || '',
                        no_po: c.no_po || '',
                        no_ros: c.no_ros || '',
                        kode_internal: c.kode_internal || '',
                        parent_coil_id: c.parent_coil_id || null,
                        is_baby_coil: c.is_baby_coil || 0,
                        gross_weight: c.gross_weight || 0,
                        net_weight: c.net_weight || 0,
                        length: c.length || 0,
                        qty_roll: c.qty_roll || 1,
                        harga_beli: c.harga_beli || 0,
                        total_nilai: c.total_nilai_mutasi || 0,
                        id_pack: d.id_warehouse_pack || 0,
                        pack_code: d.pack_code || ''
                    });
                });
            }
            // Build materials_concat
            var matEntry = (d.trade_name || '') + '||' + (d.nm_material || '') + '||' + (d.code_lv4 || '');
            if (packMap[pk].materials_concat) {
                if (packMap[pk].materials_concat.indexOf(matEntry) === -1) {
                    packMap[pk].materials_concat += ';;' + matEntry;
                }
            } else {
                packMap[pk].materials_concat = matEntry;
            }
        });
        selectedPacks = Object.values(packMap);
        renderPackTable();
    }

    // Auto-set destination warehouse
    $('#id_gudang_from').on('change', function() {
        var val = $(this).val();
        var opposite = '';
        $('#id_gudang_to option').not(':first').each(function() {
            if ($(this).val() !== val && $(this).val() !== '') opposite = $(this).val();
        });
        $('#id_gudang_to').val(opposite);
        $('#id_gudang_to_hidden').val(opposite);
        selectedPacks = [];
        renderPackTable();
    });

    if (MODE === 'edit' || MODE === 'view') {
        var currentTo = '<?= $m['id_gudang_to'] ?? '' ?>';
        if (currentTo) { $('#id_gudang_to').val(currentTo); $('#id_gudang_to_hidden').val(currentTo); }
    }

    // Add Pack button
    $('#btnAddPack').on('click', function() {
        var idGudang = $('#id_gudang_from').val();
        if (!idGudang) { Swal.fire('Attention', 'Please select a source warehouse first.', 'warning'); return; }
        showPackPicker(idGudang);
    });

    $('#btnSave').on('click', saveForm);
});

// ── Pack Picker Modal ──
function showPackPicker(idGudang) {
    $('#packListContainer').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Loading...</div>');
    $('#searchPackInput').val('');
    new bootstrap.Modal(document.getElementById('modalPackPicker')).show();

    var existingIds = selectedPacks.map(function(p) { return parseInt(p.id_warehouse_pack); });

    $.get(BASE_URL + '/get_packs?id_gudang=' + idGudang, function(res) {
        if (res.status != 1 || !res.data.length) {
            $('#packListContainer').html('<div class="text-center text-muted py-3">No packs available in this warehouse.</div>');
            return;
        }

        var html = '<table class="table table-bordered table-sm table-hover" style="font-size:12px;"><thead class="table-light"><tr><th width="3%"></th><th>Pack Code</th><th>Materials</th><th class="text-center">Roll</th><th class="text-end">N.W.</th><th class="text-end">G.W.</th></tr></thead><tbody>';

        res.data.forEach(function(p) {
            var isSelected = existingIds.includes(parseInt(p.id_warehouse_pack));
            var inMutation = parseInt(p.in_mutation) > 0;
            var isDisabled = isSelected || inMutation;
            var disabled = isDisabled ? 'disabled' : '';
            var badge = isSelected ? '<span class="badge bg-secondary">Selected</span>' : (inMutation ? '<span class="badge bg-warning text-dark">In Mutation</span>' : '');
            var matHtml = parseMaterials(p.materials_concat);

            html += '<tr class="pack-pick-row" data-search="' + (p.pack_code + ' ' + (p.materials_concat||'')).toLowerCase() + '">' +
                '<td class="text-center"><input type="checkbox" class="form-check-input pack-check" value="' + p.id_warehouse_pack + '" data-json=\'' + JSON.stringify(p) + '\' ' + disabled + '></td>' +
                '<td><span class="badge bg-primary">' + p.pack_code + '</span> ' + badge + '</td>' +
                '<td>' + matHtml + '</td>' +
                '<td class="text-center">' + (parseInt(p.roll_count)||0) + '</td>' +
                '<td class="text-end">' + fmtNum(p.total_nw) + '</td>' +
                '<td class="text-end">' + fmtNum(p.total_gw) + '</td></tr>';
        });

        html += '</tbody></table><div class="text-end mt-2"><button type="button" class="btn btn-primary btn-sm" id="btnConfirmPack"><i class="fa-solid fa-check"></i> Add Selected</button></div>';
        $('#packListContainer').html(html);

        // Search
        $('#searchPackInput').off('input').on('input', function() {
            var kw = this.value.toLowerCase().trim();
            var visible = 0;
            $('.pack-pick-row').each(function() {
                var match = ($(this).data('search')||'').indexOf(kw) > -1;
                $(this).toggle(match);
                if (match) visible++;
            });
            $('#no-result-pack-picker').remove();
            if (kw && visible === 0) {
                $('#packListContainer tbody').append('<tr id="no-result-pack-picker"><td colspan="6" class="text-center text-muted py-3"><i class="fa fa-search"></i> No results for "<b>' + kw + '</b>"</td></tr>');
            }
        });

        // Confirm
        $('#btnConfirmPack').off('click').on('click', function() {
            $('.pack-check:checked:not(:disabled)').each(function() {
                var p = JSON.parse($(this).attr('data-json'));
                selectedPacks.push({
                    id_warehouse_pack: p.id_warehouse_pack,
                    pack_code: p.pack_code,
                    materials_concat: p.materials_concat,
                    roll_count: parseInt(p.roll_count) || 0,
                    total_nw: parseFloat(p.total_nw) || 0,
                    total_gw: parseFloat(p.total_gw) || 0,
                    coils: [] // Will be loaded on save
                });
            });
            bootstrap.Modal.getInstance(document.getElementById('modalPackPicker')).hide();
            renderPackTable();
        });
    }, 'json');
}

// ── Render Pack Table ──
function renderPackTable() {
    var tbody = $('#packBody');
    var colCount = IS_VIEW ? 7 : 8;

    if (!selectedPacks.length) {
        tbody.html('<tr><td colspan="' + colCount + '" class="text-center text-muted py-3">No pack selected yet.</td></tr>');
        recalcTotals();
        return;
    }

    var html = '';
    selectedPacks.forEach(function(p, i) {
        var matHtml = parseMaterials(p.materials_concat);
        var detBtn = '<button type="button" class="btn btn-sm btn-outline-info btn-view-detail" data-idx="' + i + '"><i class="fa fa-eye"></i></button>';
        var delBtn = IS_VIEW ? '' : '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-pack" data-idx="' + i + '"><i class="fa-solid fa-trash"></i></button></td>';

        html += '<tr>' +
            '<td class="text-center">' + (i + 1) + '</td>' +
            '<td class="text-center"><span class="badge bg-primary">' + p.pack_code + '</span></td>' +
            '<td>' + matHtml + '</td>' +
            '<td class="text-center">' + p.roll_count + '</td>' +
            '<td class="text-end">' + fmtNum(p.total_nw) + '</td>' +
            '<td class="text-end">' + fmtNum(p.total_gw) + '</td>' +
            '<td class="text-center">' + detBtn + '</td>' +
            delBtn + '</tr>';
    });
    tbody.html(html);
    recalcTotals();
}

function recalcTotals() {
    var tRoll = 0, tNW = 0, tGW = 0;
    selectedPacks.forEach(function(p) { tRoll += p.roll_count; tNW += p.total_nw; tGW += p.total_gw; });
    $('#totalRoll').text(tRoll);
    $('#totalNW').text(fmtNum(tNW));
    $('#totalGW').text(fmtNum(tGW));
}

// Remove pack
$(document).on('click', '.btn-remove-pack', function() { selectedPacks.splice($(this).data('idx'), 1); renderPackTable(); });

// View detail
$(document).on('click', '.btn-view-detail', function() {
    var idx = $(this).data('idx');
    var p = selectedPacks[idx];
    if (!p) return;

    $('#detailPackCode').text(p.pack_code);
    $('#detailPackBody').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>');
    new bootstrap.Modal(document.getElementById('modalPackDetail')).show();

    // If coils already loaded
    if (p.coils && p.coils.length > 0) {
        renderDetailModal(p.coils);
        return;
    }

    // Load from server
    $.get(BASE_URL + '/get_pack_coils?id_pack=' + p.id_warehouse_pack, function(res) {
        if (res.status == 1 && res.data.length > 0) {
            p.coils = res.data;
            renderDetailModal(res.data);
        } else {
            $('#detailPackBody').html('<div class="text-center text-muted py-4">No coil data found.</div>');
        }
    }, 'json');
});

function renderDetailModal(coils) {
    var html = '<table class="table table-bordered table-sm table-striped" style="font-size:11px;"><thead class="table-light"><tr><th class="text-center">No</th><th>Material</th><th>No. Coil</th><th>Internal Code</th><th class="text-end">N.W.</th><th class="text-end">G.W.</th><th class="text-end">Length</th></tr></thead><tbody>';
    var tNw = 0, tGw = 0;
    coils.forEach(function(c, i) {
        tNw += parseFloat(c.net_weight) || 0;
        tGw += parseFloat(c.gross_weight) || 0;
        html += '<tr><td class="text-center">' + (i+1) + '</td><td>' + (c.trade_name || c.nm_material || '-') + '</td><td>' + (c.no_coil||'-') + '</td><td>' + (c.kode_internal||'-') + '</td><td class="text-end">' + fmtNum(c.net_weight) + '</td><td class="text-end">' + fmtNum(c.gross_weight) + '</td><td class="text-end">' + fmtNum(c.length) + '</td></tr>';
    });
    html += '</tbody><tfoot class="table-secondary"><tr><td colspan="4" class="text-end fw-bold">Total</td><td class="text-end fw-bold">' + fmtNum(tNw) + '</td><td class="text-end fw-bold">' + fmtNum(tGw) + '</td><td></td></tr></tfoot></table>';
    $('#detailPackBody').html(html);
}

// ── Save ──
function saveForm() {
    var no_berita_acara = $('#no_berita_acara').val().trim();
    var id_gudang_from = $('#id_gudang_from').val();
    var id_gudang_to = $('#id_gudang_to_hidden').val();
    var description = $('#description').val().trim();

    if (!no_berita_acara) { Swal.fire('Attention', 'Minutes of Meeting No. is required.', 'warning'); return; }
    if (!id_gudang_from) { Swal.fire('Attention', 'Source warehouse must be selected.', 'warning'); return; }
    if (!id_gudang_to) { Swal.fire('Attention', 'Destination warehouse not set.', 'warning'); return; }
    if (!selectedPacks.length) { Swal.fire('Attention', 'At least one pack must be selected.', 'warning'); return; }
    if (!description) { Swal.fire('Attention', 'Description is required.', 'warning'); return; }

    // Check all packs have coils loaded
    var needLoad = selectedPacks.filter(function(p) { return !p.coils || p.coils.length === 0; });

    if (needLoad.length > 0) {
        // Load coils for packs that don't have them yet
        Swal.fire({ title: 'Loading coil data...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });

        var promises = needLoad.map(function(p) {
            return $.get(BASE_URL + '/get_pack_coils?id_pack=' + p.id_warehouse_pack);
        });

        Promise.all(promises).then(function(results) {
            results.forEach(function(res, i) {
                if (res.status == 1) needLoad[i].coils = res.data;
            });
            Swal.close();
            doSave(no_berita_acara, id_gudang_from, id_gudang_to, description);
        });
    } else {
        doSave(no_berita_acara, id_gudang_from, id_gudang_to, description);
    }
}

function doSave(no_berita_acara, id_gudang_from, id_gudang_to, description) {
    Swal.fire({
        title: 'Are you sure?', text: 'Save this mutation request?', icon: 'question',
        showCancelButton: true, confirmButtonText: 'Yes, save!', cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (!result.isConfirmed) return;

        // Build details payload: group coils by material within each pack
        var detailsPayload = [];
        selectedPacks.forEach(function(p) {
            // Group coils by material
            var matGroup = {};
            (p.coils || []).forEach(function(c) {
                var mk = c.id_material || c.code_lv4 || 'unknown';
                if (!matGroup[mk]) {
                    matGroup[mk] = {
                        id_warehouse_stock: 0,
                        id_warehouse_pack: p.id_warehouse_pack,
                        pack_code: p.pack_code,
                        id_material: mk,
                        nm_material: c.nm_material || '',
                        trade_name: c.trade_name || '',
                        code_lv4: c.id_material || mk,
                        id_unit: c.id_unit || null,
                        harga_beli: c.harga_beli || 0,
                        coils: []
                    };
                }
                matGroup[mk].coils.push({
                    id_warehouse_stock_coil: c.id_warehouse_stock_coil || c.id || 0,
                    id_warehouse_pack: p.id_warehouse_pack,
                    pack_code: p.pack_code,
                    no_coil: c.no_coil || '',
                    no_ipp: c.no_ipp || '',
                    no_po: c.no_po || '',
                    no_ros: c.no_ros || '',
                    kode_internal: c.kode_internal || '',
                    parent_coil_id: c.parent_coil_id || null,
                    is_baby_coil: c.is_baby_coil || 0,
                    gross_weight: c.gross_weight || 0,
                    net_weight: c.net_weight || 0,
                    length: c.length || 0,
                    qty_roll: c.qty_roll || 1,
                    harga_beli: c.harga_beli || 0,
                    total_nilai_mutasi: parseFloat(c.total_nilai_mutasi || c.total_nilai || 0)
                });
            });
            Object.values(matGroup).forEach(function(mg) { detailsPayload.push(mg); });
        });

        var formData = new FormData(document.getElementById('formMutasi'));
        formData.set('no_berita_acara', no_berita_acara);
        formData.set('id_gudang_from', id_gudang_from);
        formData.set('id_gudang_to', id_gudang_to);
        formData.set('description', description);
        formData.set('details_json', JSON.stringify(detailsPayload));

        var url = MODE === 'edit' ? BASE_URL + '/update/' + RECORD_ID : BASE_URL + '/save';
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: url, type: 'POST', data: formData, processData: false, contentType: false, dataType: 'json',
            success: function(res) {
                $('#btnSave').prop('disabled', false).html('<i class="fa-solid fa-save"></i> Save');
                if (res.status == 1) {
                    Swal.fire({ title: 'Success', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false }).then(function() {
                        window.location.href = '<?= site_url('pengajuan_mutasi') ?>';
                    });
                } else {
                    Swal.fire('Failed', res.message, 'error');
                }
            },
            error: function() {
                $('#btnSave').prop('disabled', false).html('<i class="fa-solid fa-save"></i> Save');
                Swal.fire('Error', 'A server error occurred.', 'error');
            }
        });
    });
}

// ── Utilities ──
function parseMaterials(str) {
    if (!str) return '-';
    var mats = [...new Set(str.split(';;'))];
    var html = '';
    mats.forEach(function(m) {
        var parts = m.split('||');
        html += '<div style="font-size:11px;"><span class="text-primary me-1">&#9679;</span><b>' + (parts[0]||'') + '</b> <small class="text-muted">(' + (parts[1]||'') + ')</small></div>';
    });
    return html;
}

function fmtNum(val) {
    return parseFloat(val || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
