<?php
$ENABLE_ADD    = has_permission('Spk_Material.Add');
$ENABLE_MANAGE = has_permission('Spk_Material.Manage');

$is_edit = ($mode === 'edit');
$is_add  = ($mode === 'add');

$spk_no     = $is_edit ? $spk['spk_no'] : '';
$tgl_spk    = $is_edit ? $spk['tgl_spk'] : date('Y-m-d');
$due_date   = $is_edit ? (isset($spk['due_date']) ? $spk['due_date'] : '') : '';
$shift_ids  = $is_edit ? (isset($spk['shift_ids']) ? $spk['shift_ids'] : '') : '';
$shift_names = $is_edit ? (isset($spk['shift_names']) ? $spk['shift_names'] : '') : '';
$catatan    = $is_edit ? $spk['catatan'] : '';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.skeleton { border-radius:4px; animation:shimmer 1.5s infinite linear; background:linear-gradient(90deg,#f2f2f2 25%,#e0e0e0 50%,#f2f2f2 75%); background-size:200% 100%; }
.skeleton-line { height:20px; margin:8px 0; border-radius:4px; }
.skeleton-line.short { width:60%; }
.skeleton-line.medium { width:80%; }
.skeleton-line.tall { height:40px; }
.skeleton-block { height:120px; margin:12px 0; border-radius:6px; }
@keyframes shimmer { 0%{background-position:200% 0;} 100%{background-position:-200% 0;} }
.product-line { border:1px solid #e0e0e0; border-radius:8px; padding:16px; margin-bottom:12px; background:#fafbfc; position:relative; }
.product-line .line-number { position:absolute; top:-10px; left:12px; background:#556ee6; color:#fff; border-radius:50%; width:24px; height:24px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:bold; }
.product-line .btn-remove-line { position:absolute; top:8px; right:8px; }
.product-line .select2-container { width:100% !important; }
.is-invalid + .select2-container .select2-selection { border-color:#f46a6a !important; }
.weight-warning { font-size:11px; color:#f1b44c; margin-top:2px; }
</style>

<!-- Skeleton Loading -->
<div id="skeleton-form">
    <div class="card"><div class="card-body">
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
    </div></div>
</div>

<!-- Form Content (hidden until ready) -->
<div id="content-form" style="display:none;">
<div class="card"><div class="card-body">

    <?php if ($is_edit): ?>
    <div class="p-3 bg-light border rounded mb-4">
        <div class="d-flex flex-wrap align-items-center gap-4">
            <div>
                <small class="text-muted d-block text-uppercase fw-bold" style="font-size:10px;">SPK No.</small>
                <span class="fs-6 fw-bold text-dark"><?= htmlspecialchars($spk_no) ?></span>
            </div>
            <div class="border-start ps-4">
                <small class="text-muted d-block text-uppercase fw-bold" style="font-size:10px;">Status</small>
                <?php
                $status_badges = ['Material Requested'=>'bg-warning text-dark','Material Confirmed'=>'bg-info text-dark'];
                $badge = isset($status_badges[$spk['status']]) ? $status_badges[$spk['status']] : 'bg-secondary';
                ?>
                <span class="badge <?= $badge ?>"><?= htmlspecialchars($spk['status']) ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <form id="formSpk">
        <input type="hidden" name="mode" value="<?= $mode ?>">
        <?php if ($is_edit): ?>
            <input type="hidden" name="spk_no" value="<?= htmlspecialchars($spk_no) ?>">
        <?php endif; ?>

        <!-- Header Fields -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Tanggal SPK <span class="text-danger">*</span></label>
                <input type="text" id="tgl_spk" name="tgl_spk" class="form-control"
                       value="<?= htmlspecialchars($tgl_spk) ?>" placeholder="Pilih tanggal" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Due Date</label>
                <input type="text" id="due_date" name="due_date" class="form-control"
                       value="<?= htmlspecialchars($due_date) ?>" placeholder="Tanggal batas (opsional)">
            </div>
            <div class="col-md-3">
                <label class="form-label">Shift <span class="text-danger">*</span></label>
                <select id="header_shift" class="form-select" multiple="multiple"></select>
                <input type="hidden" id="shift_ids" name="shift_ids" value="<?= htmlspecialchars($shift_ids) ?>">
                <input type="hidden" id="shift_names" name="shift_names" value="<?= htmlspecialchars($shift_names) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Catatan</label>
                <textarea id="catatan" name="catatan" class="form-control" rows="1"
                          placeholder="Catatan tambahan (opsional)"><?= htmlspecialchars($catatan) ?></textarea>
            </div>
        </div>

        <!-- Product Lines Section -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0"><i class="fa fa-boxes-stacked me-1"></i> Daftar Produk</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddProduct">
                <i class="fa fa-plus"></i> Add More Product
            </button>
        </div>

        <div id="product-lines-container">
            <!-- Product lines will be rendered here -->
        </div>

        <!-- Submit Button -->
        <div class="mt-4 d-flex gap-2 justify-content-end">
            <a href="<?= site_url('spk_material') ?>" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
            <button type="button" class="btn btn-primary" id="btnSubmitSpk">
                <i class="fa fa-save"></i> <?= $is_edit ? 'Update SPK' : 'Create SPK' ?>
            </button>
        </div>
    </form>

</div></div>
</div>

<!-- Material Viewer Modal -->
<div class="modal fade" id="modalMaterial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-cubes me-2"></i> Detail Material (BOM)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-skeleton">
                    <div class="skeleton skeleton-line medium"></div>
                    <div class="skeleton skeleton-line"></div>
                    <div class="skeleton skeleton-line short"></div>
                    <div class="skeleton skeleton-line"></div>
                    <div class="skeleton skeleton-line medium"></div>
                </div>
                <div id="modal-content" style="display:none;">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Material</th>
                                    <th class="text-end">Berat Dibutuhkan</th>
                                    <th class="text-end">Stok WIP</th>
                                    <th class="text-end">Stok Gudang Produksi</th>
                                </tr>
                            </thead>
                            <tbody id="modal-material-body"></tbody>
                        </table>
                    </div>
                </div>
                <div id="modal-error" style="display:none;" class="text-center py-4">
                    <i class="fa fa-exclamation-triangle text-danger fa-2x mb-2"></i>
                    <p class="text-danger mb-3" id="modal-error-msg"></p>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnRetryMaterial">
                        <i class="fa fa-refresh"></i> Coba Lagi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_URL = siteurl + active_controller;
const MODE = '<?= $mode ?>';
const EXISTING_DETAILS = <?= json_encode($details ?: []) ?>;
const EXISTING_SHIFT_IDS = '<?= addslashes($shift_ids) ?>';

let lineCounter = 0;
let shiftListCache = null;

// ---------------------------------------------------------------
// INITIALIZATION
// ---------------------------------------------------------------
$(document).ready(function() {
    // Init Flatpickr for date fields
    flatpickr('#tgl_spk', { dateFormat: 'Y-m-d', defaultDate: '<?= $tgl_spk ?>' });
    flatpickr('#due_date', { dateFormat: 'Y-m-d', defaultDate: '<?= $due_date ?>' || null });

    // Load shift list, init header shift, then render product lines
    loadShiftList(function() {
        initHeaderShift();

        if (EXISTING_DETAILS.length > 0) {
            EXISTING_DETAILS.forEach(function(detail) { addProductLine(detail); });
        } else {
            addProductLine();
        }

        $('#skeleton-form').hide();
        $('#content-form').show();
    });

    $('#btnAddProduct').on('click', function() {
        if ($('#product-lines-container .product-line').length >= 100) {
            Swal.fire('Perhatian', 'Maksimal 100 baris produk dalam satu SPK.', 'warning');
            return;
        }
        addProductLine();
    });

    $('#btnSubmitSpk').on('click', submitForm);

    $('#btnRetryMaterial').on('click', function() {
        var produkId = $(this).data('produk-id');
        var targetQty = $(this).data('target-qty');
        if (produkId && targetQty) loadMaterialBom(produkId, targetQty);
    });
});

// ---------------------------------------------------------------
// SHIFT LIST LOADER
// ---------------------------------------------------------------
function loadShiftList(callback) {
    if (shiftListCache) { if (callback) callback(); return; }
    $.get(BASE_URL + '/get_shift_list', function(res) {
        shiftListCache = (res.status == 1) ? (res.data || []) : [];
        if (callback) callback();
    }, 'json').fail(function() { shiftListCache = []; if (callback) callback(); });
}

// ---------------------------------------------------------------
// INIT HEADER SHIFT (Select2 multi-select)
// ---------------------------------------------------------------
function initHeaderShift() {
    var $el = $('#header_shift');
    var selectedArr = EXISTING_SHIFT_IDS ? EXISTING_SHIFT_IDS.split(',') : [];
    var options = '';

    if (shiftListCache && shiftListCache.length > 0) {
        shiftListCache.forEach(function(s) {
            var sel = selectedArr.indexOf(String(s.id)) > -1 ? 'selected' : '';
            options += '<option value="' + s.id + '" ' + sel + '>' + escHtml(s.nama_shift) + '</option>';
        });
    }
    $el.html(options);
    $el.select2({ placeholder: 'Pilih Shift', allowClear: true, closeOnSelect: false, width: '100%' });

    // Sync hidden inputs on change
    $el.on('change', function() {
        var ids = $(this).val() || [];
        var names = $(this).find('option:selected').map(function() { return $(this).text(); }).get();
        $('#shift_ids').val(ids.join(','));
        $('#shift_names').val(names.join(', '));
    });
}

// ---------------------------------------------------------------
// ADD PRODUCT LINE (no shift — shift is in header now)
// ---------------------------------------------------------------
function addProductLine(data) {
    lineCounter++;
    var idx = lineCounter;
    var detail = data || {};

    var selectedProduct = detail.id_produk_fg || '';
    var selectedProductName = detail.nm_produk_fg || '';
    var targetQty = detail.target_qty || '';
    var beratPerUnit = detail.berat_per_unit || 0;
    var totalWeight = detail.total_weight || 0;

    var html = `
        <div class="product-line" data-line-idx="${idx}">
            <span class="line-number">${getLineCount()}</span>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-line" onclick="removeProductLine(${idx})" title="Hapus baris">
                <i class="fa fa-times"></i>
            </button>
            <div class="row g-3 mt-1">
                <div class="col-md-5">
                    <label class="form-label">Produk <span class="text-danger">*</span></label>
                    <select class="form-select select-produk" id="produk_${idx}" data-idx="${idx}">
                        <option value="">-- Pilih Produk --</option>
                        ${selectedProduct ? '<option value="'+escHtml(selectedProduct)+'" selected>'+escHtml(selectedProductName)+'</option>' : ''}
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Target Qty <span class="text-danger">*</span></label>
                    <input type="number" class="form-control input-target-qty" id="qty_${idx}" data-idx="${idx}"
                           min="1" max="999999" step="1" placeholder="0" value="${targetQty}">
                    <input type="hidden" class="input-berat-per-unit" id="berat_${idx}" value="${beratPerUnit}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total Weight (Kg)</label>
                    <input type="text" class="form-control input-total-weight" id="weight_${idx}" readonly
                           value="${totalWeight ? parseFloat(totalWeight).toFixed(2) : '0.00'}">
                    <div class="weight-warning" id="weight_warn_${idx}" style="display:none;">Berat/Unit belum diset</div>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-outline-info w-100 btn-show-material"
                            data-idx="${idx}" title="Show Material"><i class="fa fa-cubes"></i></button>
                </div>
            </div>
        </div>`;

    $('#product-lines-container').append(html);
    initProdukSelect2(idx);
    bindLineEvents(idx);
    renumberLines();
    toggleRemoveButtons();
}

// ---------------------------------------------------------------
// INIT SELECT2 - PRODUK
// ---------------------------------------------------------------
function initProdukSelect2(idx) {
    $('#produk_' + idx).select2({
        placeholder: '-- Pilih Produk --',
        allowClear: true,
        ajax: {
            url: BASE_URL + '/get_produk_list',
            dataType: 'json',
            delay: 300,
            data: function(params) { return { q: params.term || '', page: params.page || 1 }; },
            processResults: function(res) {
                var items = (res.data || []).map(function(p) { return { id: p.code_lv4, text: p.nama }; });
                return { results: items };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    $('#produk_' + idx).on('change', function() {
        var prodId = $(this).val();
        var lineIdx = $(this).data('idx');
        if (prodId) { fetchProdukWeight(lineIdx, prodId); }
        else { $('#berat_' + lineIdx).val(0); recalcWeight(lineIdx); }
    });
}

// ---------------------------------------------------------------
// BIND LINE EVENTS
// ---------------------------------------------------------------
function bindLineEvents(idx) {
    $('#qty_' + idx).on('change blur', function() { recalcWeight(idx); });

    $('#qty_' + idx).on('input', function() {
        var val = $(this).val().replace(/[^0-9]/g, '');
        if (val.length > 6) val = val.substring(0, 6);
        $(this).val(val);
    });

    $('#qty_' + idx).on('keydown', function(e) {
        if ([8,9,13,27,46,37,38,39,40].indexOf(e.keyCode) !== -1) return;
        if ((e.ctrlKey || e.metaKey) && [65,67,86,88].indexOf(e.keyCode) !== -1) return;
        if ([190,110,189,109,187,107,69].indexOf(e.keyCode) !== -1) { e.preventDefault(); return; }
        if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105)) return;
        e.preventDefault();
    });

    $(document).on('click', '.btn-show-material[data-idx="' + idx + '"]', function() {
        var lineIdx = $(this).data('idx');
        var prodId = $('#produk_' + lineIdx).val();
        var qty = parseInt($('#qty_' + lineIdx).val()) || 0;
        if (!prodId) { Swal.fire('Perhatian', 'Pilih produk terlebih dahulu.', 'warning'); return; }
        if (qty < 1) { Swal.fire('Perhatian', 'Masukkan Target Qty terlebih dahulu.', 'warning'); return; }
        loadMaterialBom(prodId, qty);
    });
}

// ---------------------------------------------------------------
// FETCH PRODUCT WEIGHT & WEIGHT CALCULATOR
// ---------------------------------------------------------------
function fetchProdukWeight(idx, prodId) {
    $.get(BASE_URL + '/get_produk_info/' + prodId, function(res) {
        if (res.status == 1) {
            var weight = parseFloat(res.data.weight) || 0;
            $('#berat_' + idx).val(weight);
            if (weight === 0) { $('#weight_warn_' + idx).show(); } else { $('#weight_warn_' + idx).hide(); }
            recalcWeight(idx);
        }
    }, 'json');
}

function recalcWeight(idx) {
    var berat = parseFloat($('#berat_' + idx).val()) || 0;
    var qty = parseInt($('#qty_' + idx).val()) || 0;
    $('#weight_' + idx).val((berat * qty).toFixed(2));
}

// ---------------------------------------------------------------
// REMOVE / RENUMBER / TOGGLE
// ---------------------------------------------------------------
function removeProductLine(idx) {
    if ($('#product-lines-container .product-line').length <= 1) {
        Swal.fire('Perhatian', 'Minimal harus ada 1 baris produk.', 'warning'); return;
    }
    $('#produk_' + idx).select2('destroy');
    $('[data-line-idx="' + idx + '"]').remove();
    renumberLines();
    toggleRemoveButtons();
}

function renumberLines() {
    $('#product-lines-container .product-line').each(function(i) { $(this).find('.line-number').text(i + 1); });
}

function toggleRemoveButtons() {
    var c = $('#product-lines-container .product-line').length;
    if (c <= 1) { $('#product-lines-container .btn-remove-line').hide(); }
    else { $('#product-lines-container .btn-remove-line').show(); }
}

function getLineCount() { return $('#product-lines-container .product-line').length + 1; }

// ---------------------------------------------------------------
// MATERIAL VIEWER MODAL
// ---------------------------------------------------------------
function loadMaterialBom(produkId, targetQty) {
    $('#modal-skeleton').show(); $('#modal-content').hide(); $('#modal-error').hide();
    $('#modalMaterial').modal('show');
    $('#btnRetryMaterial').data('produk-id', produkId).data('target-qty', targetQty);

    $.get(BASE_URL + '/get_material_bom/' + produkId, { target_qty: targetQty }, function(res) {
        $('#modal-skeleton').hide();
        if (res.status == 1) {
            var tbody = '';
            if (res.data && res.data.length > 0) {
                res.data.forEach(function(m) {
                    tbody += '<tr><td>'+escHtml(m.nm_material)+'</td><td class="text-end">'+parseFloat(m.qty_needed).toFixed(4)+'</td>';
                    tbody += '<td class="text-end">'+parseFloat(m.stok_wip||0).toFixed(2)+'</td><td class="text-end">'+parseFloat(m.stok_produksi||0).toFixed(2)+'</td></tr>';
                });
            } else { tbody = '<tr><td colspan="4" class="text-center text-muted">Tidak ada data material.</td></tr>'; }
            $('#modal-material-body').html(tbody);
            $('#modal-content').show();
        } else { $('#modal-error-msg').text(res.message || 'Gagal memuat data material.'); $('#modal-error').show(); }
    }, 'json').fail(function() { $('#modal-skeleton').hide(); $('#modal-error-msg').text('Terjadi kesalahan jaringan.'); $('#modal-error').show(); });
}

// ---------------------------------------------------------------
// FORM VALIDATION & SUBMIT
// ---------------------------------------------------------------
function submitForm() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    $('.select2-selection').css('border-color', '');

    var isValid = true;
    var products = [];

    // Validate date
    if (!$('#tgl_spk').val()) { markInvalid('#tgl_spk', 'Tanggal SPK wajib diisi.'); isValid = false; }

    // Validate shift (header level)
    var headerShiftVal = $('#header_shift').val() || [];
    if (headerShiftVal.length === 0) { markInvalid('#header_shift', 'Shift wajib dipilih minimal satu.'); isValid = false; }

    // Validate product lines
    var productIds = [];
    $('#product-lines-container .product-line').each(function(i) {
        var $line = $(this);
        var idx = $line.data('line-idx');

        var produkId = $('#produk_' + idx).val();
        var produkText = $('#produk_' + idx).find('option:selected').text() || '';
        var targetQty = parseInt($('#qty_' + idx).val()) || 0;
        var beratPerUnit = parseFloat($('#berat_' + idx).val()) || 0;
        var totalWeight = parseFloat($('#weight_' + idx).val()) || 0;

        if (!produkId) { markInvalid('#produk_' + idx, 'Produk harus dipilih.'); isValid = false; }
        else {
            if (productIds.indexOf(produkId) > -1) { markInvalid('#produk_' + idx, 'Produk sudah dipilih di baris lain.'); isValid = false; }
            productIds.push(produkId);
        }

        var rawQty = $('#qty_' + idx).val();
        if (!rawQty || rawQty.trim() === '') { markInvalid('#qty_' + idx, 'Target Qty wajib diisi.'); isValid = false; }
        else if (!/^\d+$/.test(rawQty.trim())) { markInvalid('#qty_' + idx, 'Target Qty harus bilangan bulat positif.'); isValid = false; }
        else if (targetQty < 1 || targetQty > 999999) { markInvalid('#qty_' + idx, 'Target Qty harus antara 1 - 999.999.'); isValid = false; }

        products.push({
            id_produk_fg: produkId,
            nm_produk_fg: produkText,
            target_qty: targetQty,
            berat_per_unit: beratPerUnit,
            total_weight: totalWeight
        });
    });

    if (!isValid) { Swal.fire('Validasi Gagal', 'Periksa form dan lengkapi data yang diperlukan.', 'error'); return; }

    var $btn = $('#btnSubmitSpk');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

    var formData = {
        mode: MODE,
        tgl_spk: $('#tgl_spk').val(),
        due_date: $('#due_date').val(),
        shift_ids: $('#shift_ids').val(),
        shift_names: $('#shift_names').val(),
        catatan: $('#catatan').val(),
        products: products
    };
    if (MODE === 'edit') { formData.spk_no = '<?= htmlspecialchars($spk_no) ?>'; }

    $.ajax({
        url: BASE_URL + '/save', type: 'POST', data: formData, dataType: 'json',
        success: function(res) {
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> <?= $is_edit ? "Update SPK" : "Create SPK" ?>');
            if (res.status == 1) {
                Swal.fire({ icon:'success', title:'Berhasil', text:res.message, showConfirmButton:false, timer:1500 })
                    .then(function() { window.location.href = siteurl + 'spk_material'; });
            } else { Swal.fire('Error', res.message || 'Gagal menyimpan SPK.', 'error'); }
        },
        error: function() {
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> <?= $is_edit ? "Update SPK" : "Create SPK" ?>');
            Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
        }
    });
}

// ---------------------------------------------------------------
// VALIDATION HELPER & UTILITY
// ---------------------------------------------------------------
function markInvalid(selector, message) {
    var $el = $(selector);
    $el.addClass('is-invalid');
    if ($el.hasClass('select-produk') || $el.attr('id') === 'header_shift') {
        $el.next('.select2-container').find('.select2-selection').css('border-color', '#f46a6a');
    }
    if ($el.siblings('.invalid-feedback').length === 0) {
        $el.closest('[class*="col-md"]').append('<div class="invalid-feedback d-block">' + escHtml(message) + '</div>');
    }
}

function escHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
</script>
