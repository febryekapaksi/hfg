<?php

/**
 * form.php — Incoming Material
 * Mode ADD        : incoming/add/{no_ros}
 * Mode EDIT_DRAFT : incoming/edit_draft/{no_ros}
 * Mode VIEW       : incoming/view/{kode_trans}
 */

$page_mode = $page_mode ?? 'add';
$is_view   = ($page_mode === 'view');
$is_edit   = ($page_mode === 'edit_draft');
$is_add    = ($page_mode === 'add');
$tgl_default = !empty($ros_data->incoming_date) ? $ros_data->incoming_date : date('Y-m-d');

?>

<style>
    #table-coil th,
    #table-coil td {
        vertical-align: middle !important;
        font-size: 12px;
    }

    input.hitung-selisih {
        font-weight: bold;
        background-color: #fff9c4;
    }
</style>

<div class="card">
    <div class="card-body">

        <?php if ($is_view): ?>
            <!-- ============================================================
                 MODE VIEW — tampilan read-only, tidak ada form/input
            ============================================================= -->
            <div class="row mb-3">
                <!-- Kolom Kiri -->
                <div class="col-md-6">
                    <div class="form-group row mb-2">
                        <div class="col-md-4"><label class="col-form-label fw-bold">Supplier</label></div>
                        <div class="col-md-8">
                            <p class="form-control-plaintext"><?= htmlspecialchars($nm_supplier_view ?? '-') ?></p>
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <div class="col-md-4"><label class="col-form-label fw-bold">No. PO</label></div>
                        <div class="col-md-8">
                            <p class="form-control-plaintext"><?= htmlspecialchars($no_surat ?? '-') ?></p>
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <div class="col-md-4"><label class="col-form-label fw-bold">No. ROS</label></div>
                        <div class="col-md-8">
                            <p class="form-control-plaintext"><?= htmlspecialchars($no_ros_view ?? '-') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="col-md-6">
                    <div class="form-group row mb-2">
                        <div class="col-md-4"><label class="col-form-label fw-bold">Receipt Date</label></div>
                        <div class="col-md-8">
                            <p class="form-control-plaintext"><?= htmlspecialchars($tanggal ?? '-') ?></p>
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <div class="col-md-4"><label class="col-form-label fw-bold">Document</label></div>
                        <div class="col-md-8">
                            <?php if (!empty($file_incoming_material)): ?>
                                <div class="d-flex flex-column gap-1 pt-2">
                                    <?php foreach (explode('|', $file_incoming_material) as $f):
                                        if (file_exists($f)): ?>
                                            <a href="<?= base_url($f) ?>" target="_blank">
                                                <i class="fa fa-download"></i> <?= basename($f) ?>
                                            </a>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="form-control-plaintext text-muted">No documents available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <!-- Tabel View -->
            <div class="table-responsive">
                <table id="table-coil" class="table table-bordered table-condensed" width="100%">
                    <thead class="bg-blue">
                        <tr>
                            <th class="text-center" rowspan="2" style="vertical-align:middle;" width="3%">No</th>
                            <th class="text-center" rowspan="2" style="vertical-align:middle;" width="20%">Material</th>
                            <th class="text-center" rowspan="2" style="vertical-align:middle;" width="6%">Unit</th>
                            <th class="text-center" rowspan="2" style="vertical-align:middle;" width="8%">Qty PO</th>
                            <th class="text-center" colspan="4" style="background-color:#69c79d !important;">Data ROS (Packing List)</th>
                            <th class="text-center" rowspan="2" style="vertical-align:middle; background-color:#f3b44e !important;" width="8%">Status QC</th>
                            <th class="text-center" rowspan="2" style="vertical-align:middle; background-color:#c8e6c9 !important;" width="10%">Destination Warehouse</th>
                        </tr>
                        <tr>
                            <th class="text-center" style="background-color:#69c79d !important;">No. Coil</th>
                            <th class="text-center" style="background-color:#69c79d !important;">Gross Weight</th>
                            <th class="text-center" style="background-color:#69c79d !important;">Net Weight</th>
                            <th class="text-center" style="background-color:#69c79d !important;">Length</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($detail_ros)):
                            $grouped = [];
                            foreach ($detail_ros as $item) {
                                $gk = $item['id_material'] ?? $item['id_barang'] ?? uniqid();
                                $grouped[$gk][] = $item;
                            }
                            $no = 1;
                            foreach ($grouped as $rows):
                                foreach ($rows as $idx => $row):
                                    $qc    = strtoupper($row['status_qc'] ?? 'OK');
                                    $badge = $qc === 'OK' ? 'success' : 'danger';
                        ?>
                                    <tr>
                                        <?php if ($idx === 0):
                                            $rowspan = count($rows); ?>
                                            <td class="text-center" rowspan="<?= $rowspan ?>" style="vertical-align:middle;"><?= $no ?></td>
                                            <td rowspan="<?= $rowspan ?>" style="vertical-align:middle;">
                                                <b><?= htmlspecialchars($row['nm_material'] ?? $row['nm_barang'] ?? '-') ?></b><br>
                                                <small class="text-muted"><?= htmlspecialchars($row['id_material'] ?? $row['id_barang'] ?? '') ?></small>
                                            </td>
                                            <td class="text-center" rowspan="<?= $rowspan ?>" style="vertical-align:middle;">Kg</td>
                                            <td class="text-right" rowspan="<?= $rowspan ?>" style="vertical-align:middle;"><?= number_format((float)($row['qty_order'] ?? 0), 2) ?></td>
                                        <?php endif; ?>
                                        <td class="text-center bg-light"><?= htmlspecialchars($row['no_coil'] ?? '-') ?></td>
                                        <td class="text-right bg-light"><?= number_format((float)($row['berat_kotor'] ?? 0), 2) ?></td>
                                        <td class="text-right bg-light"><?= number_format((float)($row['berat_bersih'] ?? 0), 2) ?></td>
                                        <td class="text-right bg-light"><?= number_format((float)($row['length'] ?? 0), 2) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $badge ?>"><?= $qc ?></span>
                                        </td>
                                        <td class="text-center"><?= htmlspecialchars($row['kd_gudang'] ?? $row['kd_gudang_ke'] ?? '-') ?></td>
                                    </tr>
                            <?php
                                endforeach;
                                $no++;
                            endforeach;
                        else: ?>
                            <tr>
                                <td colspan="10" class="text-center">Data not found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tombol Back -->
            <div class="text-center mt-3">
                <a href="<?= base_url('incoming') ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>

        <?php else: ?>
            <!-- ============================================================
                 MODE ADD / EDIT DRAFT — Form input proses incoming
            ============================================================= -->

            <?php
            // Supplier display name
            $nm_supplier_display = '';
            if (!empty($ros_data)) {
                foreach ($list_supplier as $sup) {
                    if ($sup->kode_supplier == $ros_data->id_supplier) {
                        $nm_supplier_display = $sup->nama;
                        break;
                    }
                }
            }

            $no_po_display  = $ros_data->no_surat ?? '-';
            $no_ros_display = $ros_data->id       ?? '-';
            ?>

            <form action="" id="data-form" enctype="multipart/form-data">

                <!-- Hidden fields -->
                <input type="hidden" name="id_supplier" value="<?= htmlspecialchars($ros_data->id_supplier ?? '') ?>">
                <input type="hidden" name="no_po" value="<?= htmlspecialchars($ros_data->no_po ?? '') ?>">
                <input type="hidden" name="no_ros" value="<?= htmlspecialchars($ros_data->id ?? '') ?>">
                <input type="hidden" name="uang_muka" id="uang_muka" value="">
                <input type="hidden" name="uang_muka_idr" id="uang_muka_idr" value="">

                <div class="col-md-12">
                    <div class="row">
                        <!-- Kolom Kiri -->
                        <div class="col-md-6">
                            <div class="form-group row mb-3">
                                <div class="col-md-4"><label class="col-form-label">Supplier</label></div>
                                <div class="col-md-8">
                                    <p class="form-control-plaintext fw-semibold">
                                        <?= htmlspecialchars($nm_supplier_display ?: '-') ?>
                                    </p>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <div class="col-md-4"><label class="col-form-label">No. PO</label></div>
                                <div class="col-md-8">
                                    <p class="form-control-plaintext fw-semibold" id="no-po-display">
                                        <?= htmlspecialchars($no_po_display) ?>
                                    </p>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <div class="col-md-4"><label class="col-form-label">No. ROS</label></div>
                                <div class="col-md-8">
                                    <p class="form-control-plaintext fw-semibold">
                                        <?= htmlspecialchars($no_ros_display) ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="col-md-6">
                            <div class="form-group row mb-3">
                                <div class="col-md-4"><label class="col-form-label">Incoming Date</label></div>
                                <div class="col-md-8">
                                    <input type="text" name="tanggal" id="tgl-incoming" class="form-control"
                                        value="<?= htmlspecialchars($tgl_default) ?>" placeholder="Select date" autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <div class="col-md-4"><label class="col-form-label">Upload Document</label></div>
                                <div class="col-md-8">
                                    <div class="d-flex flex-column gap-2">
                                        <?php
                                        $existing_original = $ros_data->file_original ?? '';
                                        $existing_hash     = $ros_data->file_hash     ?? '';
                                        ?>

                                        <?php if (!empty($existing_original) && !empty($existing_hash) && file_exists($existing_hash)): ?>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="fa fa-file-alt text-primary"></i>
                                                <a href="<?= base_url($existing_hash) ?>" target="_blank" class="small">
                                                    <?= htmlspecialchars($existing_original) ?>
                                                </a>
                                                <span class="badge bg-secondary">Saved</span>
                                            </div>
                                            <small class="text-warning">
                                                <i class="fa fa-info-circle"></i>
                                                Upload a new file to <b>replace</b> the existing file.
                                            </small>
                                        <?php endif; ?>

                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <input type="file" name="file_incoming_material[]" id="file_incoming_material"
                                                class="d-none" accept=".pdf,.jpg,.jpeg,.png" multiple>
                                            <button type="button" class="btn btn-outline-warning" id="btnPickFile">
                                                <i class="ti ti-upload me-1"></i>
                                                <?= !empty($existing_original) ? 'Replace File' : 'Choose File' ?>
                                            </button>
                                            <span class="text-muted" id="docFileName">No file chosen</span>
                                            <button type="button" class="btn btn-light border" id="btnClearFile" style="display:none;">
                                                <i class="ti ti-x me-1"></i> Clear
                                            </button>
                                        </div>

                                        <input type="hidden" name="existing_file_original" value="<?= htmlspecialchars($existing_original) ?>">
                                        <input type="hidden" name="existing_file_hash" value="<?= htmlspecialchars($existing_hash) ?>">
                                        <small class="text-muted">Allowed: PDF/JPG/PNG. Max 2MB.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /row -->

                    <hr>

                    <!-- Gudang Check All & Search -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6">
                            <label class="fw-bold me-2">Assign All to Warehouse:</label>
                            <?php foreach ($list_gudang as $idx => $gd): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input check-all-gudang" type="checkbox"
                                        id="checkall-gudang-<?= $gd['id'] ?>"
                                        data-gudang-id="<?= $gd['id'] ?>"
                                        data-gudang-kd="<?= htmlspecialchars($gd['kd_gudang']) ?>">
                                    <label class="form-check-label" for="checkall-gudang-<?= $gd['id'] ?>">
                                        <?= htmlspecialchars($gd['nm_gudang']) ?> (<?= htmlspecialchars($gd['kd_gudang']) ?>)
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                                <input type="text" class="form-control" id="search-coil-table"
                                    placeholder="Search material / coil no...">
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Pack -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="table-coil">
                            <thead>
                                <tr>
                                    <th class="text-center" style="vertical-align:middle;" width="3%">No</th>
                                    <th class="text-center" style="vertical-align:middle;" width="12%">Pack Code</th>
                                    <th class="text-center" style="vertical-align:middle;" width="18%">Materials</th>
                                    <th class="text-center" style="vertical-align:middle;" width="6%">Coil Count</th>
                                    <th class="text-center" style="vertical-align:middle; background-color:#d2d6de !important; color:#000;" width="10%">Total N.W. (Kg)</th>
                                    <th class="text-center" style="vertical-align:middle; background-color:#d2d6de !important; color:#000;" width="10%">Total G.W. (Kg)</th>
                                    <th class="text-center" style="vertical-align:middle;" width="5%">Detail</th>
                                    <?php foreach ($list_gudang as $gd): ?>
                                        <th class="text-center" style="vertical-align:middle; background-color:#c8e6c9 !important; color:#000;" width="10%">
                                            <?= htmlspecialchars($gd['nm_gudang']) ?><br><small>(<?= htmlspecialchars($gd['kd_gudang']) ?>)</small>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody id="list-item-coil">
                                <tr>
                                    <td colspan="<?= 7 + count($list_gudang) ?>" class="text-center">
                                        <i class="fa fa-spinner fa-spin"></i> Loading pack data...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="text-center mt-3 d-flex gap-2 justify-content-center">
                        <a href="<?= base_url('incoming') ?>" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>

                        <button type="button" class="btn btn-primary" id="save-draft">
                            <i class="fa fa-save"></i>
                            <?= $is_edit ? 'Update' : 'Save' ?>
                        </button>

                        <button type="button" class="btn btn-success" id="save-and-submit">
                            <i class="fa fa-paper-plane"></i>
                            Save & Submit
                        </button>

                        <?php if ($is_edit && !empty($no_ros_default)): ?>
                            <a href="<?= base_url('incoming/print_qr/' . $no_ros_default) ?>" target="_blank" class="btn btn-info">
                                <i class="fa fa-print"></i> Print Label
                            </a>
                            <a href="<?= base_url('incoming/print_pl_by_gudang/' . $no_ros_default) ?>" target="_blank" class="btn btn-success">
                                <i class="fa fa-file-alt"></i> Print PL per Gudang
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            </form>
        <?php endif; ?>

    </div><!-- /card-body -->
</div><!-- /card -->

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<?php if (!$is_view): ?>
    <script>
        $(document).ready(function() {

            flatpickr('#tgl-incoming', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                allowInput: false,
                defaultDate: document.getElementById('tgl-incoming').value || '<?= date('Y-m-d') ?>',
            });

            /* ── File upload handler ── */
            (function() {
                var input = document.getElementById('file_incoming_material');
                var btnPick = document.getElementById('btnPickFile');
                var btnClr = document.getElementById('btnClearFile');
                var label = document.getElementById('docFileName');
                if (!input || !btnPick) return;

                btnPick.addEventListener('click', function() {
                    input.click();
                });

                input.addEventListener('change', function() {
                    label.textContent = (input.files && input.files.length) ?
                        input.files[0].name :
                        'No file chosen';
                    btnClr.style.display = (input.files && input.files.length) ? 'inline-flex' : 'none';
                });

                if (btnClr) btnClr.addEventListener('click', function() {
                    input.value = '';
                    label.textContent = 'No file chosen';
                    btnClr.style.display = 'none';
                });
            })();

            /* ── Prefill uang muka dari PO ── */
            (function() {
                var no_po = '<?= addslashes($ros_data->no_po ?? '') ?>';
                var id_supplier = '<?= addslashes($ros_data->id_supplier ?? '') ?>';
                if (!no_po || !id_supplier) return;

                $.ajax({
                    url: siteurl + active_controller + 'get_po_by_supplier',
                    type: 'POST',
                    data: {
                        id_supplier: id_supplier
                    },
                    dataType: 'json',
                    success: function(data) {
                        data.forEach(function(item) {
                            if (item.no_po === no_po) {
                                $('#uang_muka').val(item.uang_muka || '');
                                $('#uang_muka_idr').val(item.uang_muka_idr || '');
                            }
                        });
                    }
                });
            })();

            /* ── Data draft coils map untuk prefill mode edit_draft ── */
            <?php if ($is_edit): ?>
                var draftCoilsMap = <?= json_encode($draft_coils_map ?? []) ?>;
            <?php else: ?>
                var draftCoilsMap = {};
            <?php endif; ?>

            /* ── Load tabel coil via AJAX ── */
            var listGudang = <?= json_encode($list_gudang ?? []) ?>;

            var packDataStore = []; // Store pack data untuk modal detail

            function loadCoilTable(no_ros) {
                var colSpan = 7 + listGudang.length;
                $('#list-item-coil').html(
                    '<tr><td colspan="' + colSpan + '" class="text-center">' +
                    '<i class="fa fa-spinner fa-spin"></i> Loading data...</td></tr>'
                );

                $.ajax({
                    url: siteurl + active_controller + 'get_ros_detail_to_table',
                    type: 'POST',
                    data: {
                        no_ros: no_ros
                    },
                    dataType: 'json',
                    success: function(data) {
                        var html = '';
                        packDataStore = data || [];

                        if (data && data.length > 0) {
                            data.forEach(function(pack, packIndex) {
                                var packCode = pack.pack_code || ('Pack #' + pack.pack_no);

                                /* Build material list */
                                var matList = '';
                                var matKeys = Object.keys(pack.materials || {});
                                matKeys.forEach(function(key, idx) {
                                    var m = pack.materials[key];
                                    var dot = '<span class="text-primary me-1">&#9679;</span>';
                                    matList += '<div style="font-size:11px;">' + dot + '<b>' + (m.nm_alias || '') + '</b> <small class="text-muted">(' + (m.nm_material || '') + ')</small></div>';
                                });

                                /* Prefill gudang dari draft: ambil dari coil pertama (semua coil dalam pack harus sama) */
                                var savedGudang = pack.id_gudang_ke || '';
                                var savedKdGudang = pack.kd_gudang_ke || '';

                                /* Juga cek dari draftCoilsMap jika ada */
                                if (!savedGudang && pack.coils && pack.coils.length > 0) {
                                    var firstCoilId = pack.coils[0].id_ros_coil_detail;
                                    var saved = draftCoilsMap[firstCoilId] || {};
                                    savedGudang = saved.id_gudang_ke || '';
                                    savedKdGudang = saved.kd_gudang_ke || '';
                                }

                                /* Build checkbox gudang per pack */
                                var gudangCheckboxes = '';
                                listGudang.forEach(function(g) {
                                    var isChecked = (String(g.id) === String(savedGudang)) ? ' checked' : '';
                                    gudangCheckboxes +=
                                        '<td class="text-center" style="background-color:#f1f8e9;">' +
                                        '<input type="checkbox" class="form-check-input gudang-checkbox" ' +
                                        'data-pack-index="' + packIndex + '" ' +
                                        'data-gudang-id="' + g.id + '" ' +
                                        'data-gudang-kd="' + g.kd_gudang + '"' + isChecked + '>' +
                                        '</td>';
                                });

                                /* Hidden inputs untuk semua coils dalam pack ini */
                                var hiddenInputs = '';
                                if (pack.coils) {
                                    pack.coils.forEach(function(coil, coilIdx) {
                                        var globalIdx = packIndex + '_' + coilIdx;
                                        hiddenInputs +=
                                            '<input type="hidden" name="detail[' + globalIdx + '][id_ros_header]"   value="' + (coil.no_ros || '') + '">' +
                                            '<input type="hidden" name="detail[' + globalIdx + '][id_ros_material]" value="' + (coil.id_ros_material || '') + '">' +
                                            '<input type="hidden" name="detail[' + globalIdx + '][id_ros_coil]"     value="' + (coil.id_ros_coil_detail || '') + '">' +
                                            '<input type="hidden" name="detail[' + globalIdx + '][id_po_detail]"    value="' + (coil.id_po_detail || '') + '">' +
                                            '<input type="hidden" name="detail[' + globalIdx + '][id_material]"     value="' + (coil.id_material || '') + '">' +
                                            '<input type="hidden" name="detail[' + globalIdx + '][no_coil]"         value="' + (coil.no_coil || '') + '">' +
                                            '<input type="hidden" name="detail[' + globalIdx + '][aktual_bersih]"   value="' + (coil.ros_bersih || '') + '">' +
                                            '<input type="hidden" name="detail[' + globalIdx + '][id_gudang_ke]" class="hidden-gudang-id" value="' + savedGudang + '">' +
                                            '<input type="hidden" name="detail[' + globalIdx + '][kd_gudang_ke]" class="hidden-gudang-kd" value="' + savedKdGudang + '">';
                                    });
                                }

                                html +=
                                    '<tr class="pack-row"' +
                                    ' data-pack-index="' + packIndex + '"' +
                                    ' data-pack-code="' + (pack.pack_code || '').toLowerCase() + '"' +
                                    '>' +
                                    '<td class="text-center">' + (packIndex + 1) + '</td>' +
                                    '<td class="text-center"><span class="badge bg-primary">' + packCode + '</span></td>' +
                                    '<td>' + matList + '</td>' +
                                    '<td class="text-center">' + (pack.coil_count || 0) + '</td>' +
                                    '<td class="text-end bg-light fw-bold">' + parseFloat(pack.total_nw || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>' +
                                    '<td class="text-end bg-light fw-bold">' + parseFloat(pack.total_gw || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>' +
                                    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-info btn-view-pack" data-pack-index="' + packIndex + '" title="View Material & Coil"><i class="fa fa-eye"></i></button></td>' +
                                    gudangCheckboxes +
                                    '<td class="d-none">' + hiddenInputs + '</td>' +
                                    '</tr>';
                            });
                        } else {
                            html = '<tr><td colspan="' + colSpan + '" class="text-center text-warning">Data Pack tidak ditemukan untuk ROS ini.</td></tr>';
                        }

                        $('#list-item-coil').html(html);
                    },
                    error: function(xhr) {
                        console.error('Error load pack:', xhr.responseText);
                        $('#list-item-coil').html(
                            '<tr><td colspan="' + colSpan + '" class="text-center text-danger">Failed to load pack data.</td></tr>'
                        );
                    }
                });
            }

            /* ── Auto-load coil saat halaman dibuka ── */
            <?php if (!empty($no_ros_default) && !empty($ros_data)): ?>
                loadCoilTable('<?= addslashes($ros_data->id) ?>');
            <?php endif; ?>

            /* ── Checkbox gudang: hanya boleh pilih 1 per pack (mutual exclusive) ── */
            $(document).on('change', '.gudang-checkbox', function() {
                var gudangId = $(this).data('gudang-id');
                var gudangKd = $(this).data('gudang-kd');
                var row = $(this).closest('tr');

                if ($(this).is(':checked')) {
                    // Uncheck checkbox gudang lain di baris yang sama
                    row.find('.gudang-checkbox').not(this).prop('checked', false);
                    // Update ALL hidden fields dalam pack ini
                    row.find('.hidden-gudang-id').val(gudangId);
                    row.find('.hidden-gudang-kd').val(gudangKd);
                } else {
                    // Jika uncheck, kosongkan hidden
                    row.find('.hidden-gudang-id').val('');
                    row.find('.hidden-gudang-kd').val('');
                }

                // Sync "Assign All" checkbox state
                syncCheckAllState();
            });

            /* ── Check All per gudang (assign semua pack ke gudang tertentu) ── */
            $(document).on('change', '.check-all-gudang', function() {
                var gudangId = $(this).data('gudang-id');
                var gudangKd = $(this).data('gudang-kd');
                var isChecked = $(this).is(':checked');

                if (isChecked) {
                    // Uncheck "check all" gudang lain
                    $('.check-all-gudang').not(this).prop('checked', false);

                    // Set semua pack ke gudang ini
                    $('#list-item-coil .pack-row').each(function() {
                        var row = $(this);
                        row.find('.gudang-checkbox').prop('checked', false);
                        row.find('.gudang-checkbox[data-gudang-id="' + gudangId + '"]').prop('checked', true);
                        row.find('.hidden-gudang-id').val(gudangId);
                        row.find('.hidden-gudang-kd').val(gudangKd);
                    });
                } else {
                    // Uncheck semua pack dari gudang ini
                    $('#list-item-coil .pack-row').each(function() {
                        var row = $(this);
                        row.find('.gudang-checkbox[data-gudang-id="' + gudangId + '"]').prop('checked', false);
                        var anyChecked = row.find('.gudang-checkbox:checked');
                        if (anyChecked.length === 0) {
                            row.find('.hidden-gudang-id').val('');
                            row.find('.hidden-gudang-kd').val('');
                        }
                    });
                }
            });

            /* ── Sync check-all state berdasarkan keadaan tabel ── */
            function syncCheckAllState() {
                var totalPacks = $('#list-item-coil .pack-row').length;
                if (totalPacks === 0) return;

                listGudang.forEach(function(g) {
                    var checkedCount = $('#list-item-coil .pack-row .gudang-checkbox[data-gudang-id="' + g.id + '"]:checked').length;
                    $('#checkall-gudang-' + g.id).prop('checked', checkedCount === totalPacks && totalPacks > 0);
                });
            }

            /* ── Search / filter tabel pack ── */
            $(document).on('keyup', '#search-coil-table', function() {
                var keyword = $(this).val().toLowerCase().trim();
                var $rows = $('#list-item-coil .pack-row');

                if (!keyword) {
                    $rows.show();
                    $('#no-result-coil').remove();
                    return;
                }

                var visibleCount = 0;
                $rows.each(function() {
                    var packCode = $(this).data('pack-code') || '';
                    var rowText = $(this).text().toLowerCase();
                    if (packCode.indexOf(keyword) > -1 || rowText.indexOf(keyword) > -1) {
                        $(this).show();
                        visibleCount++;
                    } else {
                        $(this).hide();
                    }
                });

                $('#no-result-coil').remove();
                if (visibleCount === 0) {
                    var colSpan = 7 + listGudang.length;
                    $('#list-item-coil').append(
                        '<tr id="no-result-coil"><td colspan="' + colSpan + '" class="text-center text-muted py-3">' +
                        '<i class="fa fa-search"></i> No results found for "<b>' + keyword + '</b>"</td></tr>'
                    );
                }
            });

            /* ── Validasi gudang semua pack sudah dipilih ── */
            function validateGudang() {
                var kosong = false;
                $('#list-item-coil .pack-row').each(function() {
                    // Cek apakah ada minimal 1 hidden-gudang-id yang terisi di pack ini
                    var firstGudang = $(this).find('.hidden-gudang-id').first().val();
                    if (!firstGudang) kosong = true;
                });
                return !kosong;
            }

            /* ── Collect details dari semua pack rows ── */
            function collectDetails() {
                var details = [];
                $('#list-item-coil .pack-row').each(function() {
                    var row = $(this);
                    row.find('input[name*="[id_ros_coil]"]').each(function() {
                        var name = $(this).attr('name');
                        // Extract index prefix: detail[X_Y]
                        var match = name.match(/detail\[([^\]]+)\]/);
                        if (!match) return;
                        var idx = match[1];

                        details.push({
                            id_ros_coil: row.find('input[name="detail[' + idx + '][id_ros_coil]"]').val(),
                            id_ros_header: row.find('input[name="detail[' + idx + '][id_ros_header]"]').val(),
                            id_ros_material: row.find('input[name="detail[' + idx + '][id_ros_material]"]').val(),
                            id_po_detail: row.find('input[name="detail[' + idx + '][id_po_detail]"]').val(),
                            id_material: row.find('input[name="detail[' + idx + '][id_material]"]').val(),
                            no_coil: row.find('input[name="detail[' + idx + '][no_coil]"]').val(),
                            aktual_bersih: row.find('input[name="detail[' + idx + '][aktual_bersih]"]').val(),
                            id_gudang_ke: row.find('input[name="detail[' + idx + '][id_gudang_ke]"]').val(),
                            kd_gudang_ke: row.find('input[name="detail[' + idx + '][kd_gudang_ke]"]').val(),
                            status_qc: 'OK',
                        });
                    });
                });
                return details;
            }

            /* ── SAVE DRAFT ── */
            $(document).on('click', '#save-draft', function(e) {
                e.preventDefault();

                if (!validateGudang()) {
                    Swal.fire({
                        title: 'Warning',
                        text: 'All coils must have a destination warehouse assigned!',
                        icon: 'warning'
                    });
                    return;
                }

                var endpoint = '<?= $is_edit ? "update_draft" : "save_draft" ?>';

                Swal.fire({
                    title: 'Save Draft?',
                    text: 'Warehouse and QC data per coil will be saved. You can still modify it later.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Save Draft!',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    // ── detail sebagai array JSON ──
                    var details = collectDetails();

                    // ── Kirim via FormData (untuk support file upload) ──
                    var fd = new FormData();
                    fd.append('no_ros', $('input[name="no_ros"]').val());
                    fd.append('id_supplier', $('input[name="id_supplier"]').val());
                    fd.append('no_po', $('input[name="no_po"]').val());
                    fd.append('tanggal', $('input[name="tanggal"]').val());
                    fd.append('uang_muka', $('input[name="uang_muka"]').val());
                    fd.append('uang_muka_idr', $('input[name="uang_muka_idr"]').val());
                    fd.append('existing_file_original', $('input[name="existing_file_original"]').val());
                    fd.append('existing_file_hash', $('input[name="existing_file_hash"]').val());
                    fd.append('detail_json', JSON.stringify(details));

                    // Append file jika ada
                    var fileInput = document.getElementById('file_incoming_material');
                    if (fileInput && fileInput.files.length > 0) {
                        for (var i = 0; i < fileInput.files.length; i++) {
                            fd.append('file_incoming_material[]', fileInput.files[i]);
                        }
                    }

                    $.ajax({
                        url: siteurl + active_controller + endpoint,
                        type: 'POST',
                        data: fd,
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(res) {
                            if (res.status == 1) {
                                Swal.fire({
                                    title: 'Draft Saved!',
                                    html: res.pesan + '<br><br><b>Pilih aksi selanjutnya:</b>',
                                    icon: 'success',
                                    showCancelButton: true,
                                    showDenyButton: true,
                                    confirmButtonText: '<i class="fa fa-print"></i> Print Label',
                                    denyButtonText: '<i class="fa fa-file-alt"></i> Print Packing List',
                                    cancelButtonText: 'Nanti Saja',
                                    confirmButtonColor: '#3085d6',
                                    denyButtonColor: '#28a745',
                                }).then(function(r2) {
                                    if (r2.isConfirmed && res.print_url) {
                                        window.open(res.print_url, '_blank');
                                    } else if (r2.isDenied && res.print_pl_url) {
                                        window.open(res.print_pl_url, '_blank');
                                    }
                                    window.location.href = siteurl + active_controller;
                                });
                            } else {
                                Swal.fire({
                                    title: 'Gagal',
                                    text: res.pesan,
                                    icon: 'error'
                                });
                            }
                        }
                    });
                });
            });

            /* ── SIMPAN & AJUKAN DRAFT ── */
            $(document).on('click', '#save-and-submit', function(e) {
                e.preventDefault();

                if (!validateGudang()) {
                    Swal.fire({
                        title: 'Warning',
                        text: 'All coils must have a destination warehouse assigned!',
                        icon: 'warning'
                    });
                    return;
                }

                var endpoint = '<?= $is_edit ? "update_draft" : "save_draft" ?>';

                Swal.fire({
                    title: 'Save & Submit?',
                    text: 'Data will be saved and submitted to Finalize Incoming.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Save & Submit!',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    var details = collectDetails();

                    var fd = new FormData();
                    fd.append('no_ros', $('input[name="no_ros"]').val());
                    fd.append('id_supplier', $('input[name="id_supplier"]').val());
                    fd.append('no_po', $('input[name="no_po"]').val());
                    fd.append('tanggal', $('input[name="tanggal"]').val());
                    fd.append('uang_muka', $('input[name="uang_muka"]').val());
                    fd.append('uang_muka_idr', $('input[name="uang_muka_idr"]').val());
                    fd.append('existing_file_original', $('input[name="existing_file_original"]').val());
                    fd.append('existing_file_hash', $('input[name="existing_file_hash"]').val());
                    fd.append('detail_json', JSON.stringify(details));
                    fd.append('submit_after_save', '1'); // Flag untuk langsung ajukan

                    var fileInput = document.getElementById('file_incoming_material');
                    if (fileInput && fileInput.files.length > 0) {
                        for (var i = 0; i < fileInput.files.length; i++) {
                            fd.append('file_incoming_material[]', fileInput.files[i]);
                        }
                    }

                    $.ajax({
                        url: siteurl + active_controller + endpoint,
                        type: 'POST',
                        data: fd,
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(res) {
                            if (res.status == 1) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Data saved and submitted to Finalize Incoming successfully.',
                                    icon: 'success',
                                    timer: 1800,
                                    showConfirmButton: false
                                }).then(function() {
                                    window.location.href = siteurl + active_controller;
                                });
                            } else {
                                Swal.fire({
                                    title: 'Gagal',
                                    text: res.pesan,
                                    icon: 'error'
                                });
                            }
                        }
                    });
                });
            });

            /* ── SAVE FINALISASI (process_incoming_coil) ── */
            $(document).on('click', '#save-incoming', function(e) {
                e.preventDefault();

                if (!validateGudang()) {
                    Swal.fire({
                        title: 'Warning',
                        text: 'All coils must have a destination warehouse assigned!',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Apakah Anda Yakin?',
                    text: 'Data akan diproses ke stok dan jurnal akuntansi!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Process!',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: siteurl + active_controller + 'process_incoming_coil',
                        type: 'POST',
                        data: new FormData($('#data-form')[0]),
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(res) {
                            if (res.status == 1) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: res.pesan,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(function() {
                                    window.location.href = siteurl + active_controller;
                                });
                            } else if (res.status == 2) {
                                Swal.fire({
                                    title: 'Transaksi Saved',
                                    text: res.pesan,
                                    icon: 'warning',
                                    confirmButtonText: 'OK'
                                }).then(function() {
                                    window.location.href = siteurl + active_controller;
                                });
                            } else {
                                Swal.fire({
                                    title: 'Gagal',
                                    text: res.pesan,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error',
                                text: 'Terjadi kesalahan koneksi server.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                });
            });

            /* ── View Pack Detail Modal ── */
            $(document).on('click', '.btn-view-pack', function() {
                var packIndex = $(this).data('pack-index');
                var pack = packDataStore[packIndex];
                if (!pack) return;

                var packCode = pack.pack_code || ('Pack #' + pack.pack_no);
                var modalHtml = '';

                /* Materials section */
                modalHtml += '<h6 class="fw-bold mb-2"><i class="fa fa-boxes text-primary me-1"></i> Materials in this Pack</h6>';
                modalHtml += '<table class="table table-sm table-bordered mb-3" style="font-size:12px;">';
                modalHtml += '<thead class="table-light"><tr><th>No</th><th>Alias Name</th><th>Material Name (ERP)</th></tr></thead><tbody>';
                var matKeys = Object.keys(pack.materials || {});
                matKeys.forEach(function(key, idx) {
                    var m = pack.materials[key];
                    modalHtml += '<tr><td class="text-center">' + (idx + 1) + '</td><td><b>' + (m.nm_alias || '-') + '</b></td><td>' + (m.nm_material || '-') + '</td></tr>';
                });
                modalHtml += '</tbody></table>';

                /* Coils section */
                modalHtml += '<h6 class="fw-bold mb-2"><i class="fa fa-circle text-info me-1"></i> Coils in this Pack</h6>';
                modalHtml += '<table class="table table-sm table-bordered table-striped" style="font-size:11px;">';
                modalHtml += '<thead class="table-light"><tr><th class="text-center">No</th><th>Coil No.</th><th>Material</th><th class="text-end">N.W. (Kg)</th><th class="text-end">G.W. (Kg)</th><th class="text-end">Length (M)</th><th class="text-center">Type</th></tr></thead><tbody>';

                var displayNo = 0;
                if (pack.coils) {
                    pack.coils.forEach(function(coil) {
                        // Skip mother coil yang punya baby
                        var isMother = (parseInt(coil.is_baby_coil) === 0);
                        var qtyRoll = parseInt(coil.qty_roll) || 1;
                        if (isMother && qtyRoll > 1) return;

                        displayNo++;
                        var typeBadge = parseInt(coil.is_baby_coil) === 1
                            ? '<span class="badge bg-warning text-dark">Baby</span>'
                            : '<span class="badge bg-secondary">Normal</span>';

                        modalHtml += '<tr>' +
                            '<td class="text-center">' + displayNo + '</td>' +
                            '<td>' + (coil.no_coil || '-') + '</td>' +
                            '<td>' + (coil.nm_alias || coil.nm_material || '-') + '</td>' +
                            '<td class="text-end">' + parseFloat(coil.ros_bersih || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>' +
                            '<td class="text-end">' + parseFloat(coil.ros_kotor || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>' +
                            '<td class="text-end">' + parseFloat(coil.panjang || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>' +
                            '<td class="text-center">' + typeBadge + '</td>' +
                            '</tr>';
                    });
                }

                modalHtml += '</tbody>';
                modalHtml += '<tfoot class="table-secondary"><tr><td colspan="3" class="text-end fw-bold">Total</td>';
                modalHtml += '<td class="text-end fw-bold">' + parseFloat(pack.total_nw || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>';
                modalHtml += '<td class="text-end fw-bold">' + parseFloat(pack.total_gw || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>';
                modalHtml += '<td colspan="2"></td></tr></tfoot>';
                modalHtml += '</table>';

                $('#modalPackDetailLabel').text('Pack Detail — ' + packCode);
                $('#modalPackDetailBody').html(modalHtml);
                $('#modalPackDetail').modal('show');
            });

        });
    </script>
<?php endif; ?>

<!-- Modal Pack Detail -->
<div class="modal fade" id="modalPackDetail" tabindex="-1" aria-labelledby="modalPackDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalPackDetailLabel"><i class="fa fa-eye me-1"></i> Pack Detail</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalPackDetailBody" style="max-height:70vh; overflow-y:auto;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fa fa-times"></i> Close</button>
            </div>
        </div>
    </div>
</div>