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

                    <!-- Tabel Coil -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="table-coil">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="text-center" style="vertical-align:middle;" width="3%">No</th>
                                    <th rowspan="2" class="text-center" style="vertical-align:middle;" width="18%">Material</th>
                                    <th rowspan="2" class="text-center" style="vertical-align:middle;" width="8%">Qty Order</th>
                                    <th rowspan="2" class="text-center" style="vertical-align:middle;" width="5%">Uom</th>
                                    <th rowspan="2" class="text-center" style="vertical-align:middle;" width="8%">Qty Not Shipped</th>
                                    <th colspan="4" class="text-center" style="background-color:#d2d6de !important; color:#000;">From ROS Data (Packing List)</th>
                                    <?php foreach ($list_gudang as $gd): ?>
                                        <th rowspan="2" class="text-center" style="vertical-align:middle; background-color:#c8e6c9 !important; color:#000;" width="10%">
                                            <?= htmlspecialchars($gd['nm_gudang']) ?><br><small>(<?= htmlspecialchars($gd['kd_gudang']) ?>)</small>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <th class="text-center" style="background-color:#d2d6de !important; color:#000;">No. Coil</th>
                                    <th class="text-center" style="background-color:#d2d6de !important; color:#000;">Gross Weight</th>
                                    <th class="text-center" style="background-color:#d2d6de !important; color:#000;">Net Weight</th>
                                    <th class="text-center" style="background-color:#d2d6de !important; color:#000;">Length</th>
                                </tr>
                            </thead>
                            <tbody id="list-item-coil">
                                <tr>
                                    <td colspan="<?= 9 + count($list_gudang) ?>" class="text-center">
                                        <i class="fa fa-spinner fa-spin"></i> Loading coil data...
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
                            <?php $coil_ids_print = implode('-', array_column($draft_coils, 'id_ros_coil_detail')); ?>
                            <a href="<?= base_url('incoming/print_qr/' . $coil_ids_print) ?>" target="_blank" class="btn btn-info">
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

            function loadCoilTable(no_ros) {
                var colSpan = 9 + listGudang.length;
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
                        var currentMaterial = '';
                        var rowCounter = 0;

                        if (data && data.length > 0) {
                            data.forEach(function(item, index) {

                                /* Prefill dari draft jika mode edit */
                                var saved = draftCoilsMap[item.id_ros_coil_detail] || {};
                                var savedGudang = saved.id_gudang_ke || '';
                                var savedKdGudang = saved.kd_gudang_ke || '';

                                /* Kolom material — hanya muncul di baris pertama per material */
                                var rowMaterial = '';
                                if (item.id_material !== currentMaterial) {
                                    var qty_belum = (parseFloat(item.qty_po) || 0) - (parseFloat(item.qty_in) || 0);
                                    rowCounter++;
                                    rowMaterial =
                                        '<td class="text-center" style="vertical-align:top;">' + rowCounter + '</td>' +
                                        '<td style="vertical-align:top;"><b>' + item.nm_alias + '</b><br><small><i>' + item.nm_material + '</i></small></td>' +
                                        '<td class="text-right" style="vertical-align:top;">' + (parseFloat(item.qty_po) || 0).toLocaleString('id-ID') + '</td>' +
                                        '<td class="text-center" style="vertical-align:top;">Kg</td>' +
                                        '<td class="text-right" style="vertical-align:top;">' + qty_belum.toLocaleString('id-ID') + '</td>';
                                    currentMaterial = item.id_material;
                                } else {
                                    rowMaterial = '<td colspan="5" style="border-top:none;border-bottom:none;"></td>';
                                }

                                /* Build checkbox gudang per coil */
                                var gudangCheckboxes = '';
                                listGudang.forEach(function(g) {
                                    var isChecked = (String(g.id) === String(savedGudang)) ? ' checked' : '';
                                    gudangCheckboxes +=
                                        '<td class="text-center" style="background-color:#f1f8e9;">' +
                                        '<input type="checkbox" class="form-check-input gudang-checkbox" ' +
                                        'name="detail[' + index + '][gudang_' + g.id + ']" ' +
                                        'data-index="' + index + '" ' +
                                        'data-gudang-id="' + g.id + '" ' +
                                        'data-gudang-kd="' + g.kd_gudang + '"' + isChecked + '>' +
                                        '</td>';
                                });

                                    html +=
                                    '<tr class="coil-row"' +
                                    ' data-material="' + (item.nm_material || '').toLowerCase() + '"' +
                                    ' data-alias="' + (item.nm_alias || '').toLowerCase() + '"' +
                                    ' data-nocoil="' + (item.no_coil || '').toLowerCase() + '"' +
                                    ' data-group="' + rowCounter + '">' +
                                    rowMaterial +
                                    '<td class="text-center bg-light">' + item.no_coil + '</td>' +
                                    '<td class="text-end bg-light">' + parseFloat(item.ros_kotor).toLocaleString('id-ID') + '</td>' +
                                    '<td class="text-end bg-light">' + parseFloat(item.ros_bersih).toLocaleString('id-ID') + '</td>' +
                                    '<td class="text-end bg-light">' + (parseFloat(item.panjang || 0)).toLocaleString('id-ID') + '</td>' +
                                    gudangCheckboxes +
                                    /* Hidden inputs in last td */
                                    '<td class="d-none">' +
                                    '<input type="hidden" name="detail[' + index + '][id_ros_header]"   value="' + item.no_ros + '">' +
                                    '<input type="hidden" name="detail[' + index + '][id_ros_material]" value="' + item.id_ros_material + '">' +
                                    '<input type="hidden" name="detail[' + index + '][id_ros_coil]"     value="' + item.id_ros_coil_detail + '">' +
                                    '<input type="hidden" name="detail[' + index + '][id_po_detail]"    value="' + item.id_po_detail + '">' +
                                    '<input type="hidden" name="detail[' + index + '][id_material]"     value="' + item.id_material + '">' +
                                    '<input type="hidden" name="detail[' + index + '][no_coil]"         value="' + item.no_coil + '">' +
                                    '<input type="hidden" name="detail[' + index + '][aktual_bersih]"   value="' + item.ros_bersih + '">' +
                                    '<input type="hidden" name="detail[' + index + '][id_gudang_ke]" class="hidden-gudang-id" value="' + savedGudang + '">' +
                                    '<input type="hidden" name="detail[' + index + '][kd_gudang_ke]" class="hidden-gudang-kd" value="' + savedKdGudang + '">' +
                                    '</td>' +
                                    '</tr>';
                            });
                        } else {
                            html = '<tr><td colspan="' + (9 + listGudang.length) + '" class="text-center text-warning">Data Coil tidak ditemukan untuk ROS ini.</td></tr>';
                        }

                        $('#list-item-coil').html(html);
                    },
                    error: function(xhr) {
                        console.error('Error load coil:', xhr.responseText);
                        $('#list-item-coil').html(
                            '<tr><td colspan="' + (9 + listGudang.length) + '" class="text-center text-danger">Failed to load coil data.</td></tr>'
                        );
                    }
                });
            }

            /* ── Auto-load coil saat halaman dibuka ── */
            <?php if (!empty($no_ros_default) && !empty($ros_data)): ?>
                loadCoilTable('<?= addslashes($ros_data->id) ?>');
            <?php endif; ?>

            /* ── Checkbox gudang: hanya boleh pilih 1 per coil (mutual exclusive) ── */
            $(document).on('change', '.gudang-checkbox', function() {
                var idx = $(this).data('index');
                var gudangId = $(this).data('gudang-id');
                var gudangKd = $(this).data('gudang-kd');
                var row = $(this).closest('tr');

                if ($(this).is(':checked')) {
                    // Uncheck checkbox gudang lain di baris yang sama
                    row.find('.gudang-checkbox').not(this).prop('checked', false);
                    // Update hidden fields
                    row.find('.hidden-gudang-id').val(gudangId);
                    row.find('.hidden-gudang-kd').val(gudangKd);
                } else {
                    // Jika uncheck, kosongkan hidden
                    row.find('.hidden-gudang-id').val('');
                    row.find('.hidden-gudang-kd').val('');
                }
            });

            /* ── Check All per gudang ── */
            $(document).on('change', '.check-all-gudang', function() {
                var gudangId = $(this).data('gudang-id');
                var gudangKd = $(this).data('gudang-kd');
                var isChecked = $(this).is(':checked');

                if (isChecked) {
                    // Uncheck "check all" gudang lain
                    $('.check-all-gudang').not(this).prop('checked', false);

                    // Set semua coil ke gudang ini
                    $('#list-item-coil .coil-row').each(function() {
                        var row = $(this);
                        // Uncheck semua checkbox gudang di row
                        row.find('.gudang-checkbox').prop('checked', false);
                        // Check yang sesuai gudang ini
                        row.find('.gudang-checkbox[data-gudang-id="' + gudangId + '"]').prop('checked', true);
                        // Update hidden
                        row.find('.hidden-gudang-id').val(gudangId);
                        row.find('.hidden-gudang-kd').val(gudangKd);
                    });
                } else {
                    // Uncheck semua coil dari gudang ini
                    $('#list-item-coil .coil-row').each(function() {
                        var row = $(this);
                        row.find('.gudang-checkbox[data-gudang-id="' + gudangId + '"]').prop('checked', false);
                        // Cek apakah masih ada gudang lain yang checked
                        var anyChecked = row.find('.gudang-checkbox:checked');
                        if (anyChecked.length === 0) {
                            row.find('.hidden-gudang-id').val('');
                            row.find('.hidden-gudang-kd').val('');
                        }
                    });
                }
            });

            /* ── Search / filter tabel coil ── */
            $(document).on('keyup', '#search-coil-table', function() {
                var keyword = $(this).val().toLowerCase().trim();
                var $rows = $('#list-item-coil .coil-row');

                // Hapus highlight sebelumnya
                $rows.find('.highlight-search').each(function() {
                    var parent = $(this).parent();
                    $(this).replaceWith($(this).text());
                    parent.get(0).normalize();
                });

                if (!keyword) {
                    $rows.show();
                    $('#no-result-coil').remove();
                    return;
                }

                // Cari group yang punya match
                var matchedGroups = {};
                $rows.each(function() {
                    var group = $(this).data('group');
                    var material = $(this).data('material') || '';
                    var alias = $(this).data('alias') || '';
                    var nocoil = $(this).data('nocoil') || '';
                    if (
                        material.indexOf(keyword) > -1 ||
                        alias.indexOf(keyword) > -1 ||
                        nocoil.indexOf(keyword) > -1
                    ) {
                        matchedGroups[group] = true;
                    }
                });

                // Show/hide + highlight teks yang match
                var regex = new RegExp('(' + keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                $rows.each(function() {
                    var group = $(this).data('group');
                    if (matchedGroups[group]) {
                        $(this).show();
                        $(this).find('td').each(function() {
                            var td = $(this);
                            if (td.find('input,select,button').length) return;
                            var text = td.text();
                            if (text.toLowerCase().indexOf(keyword) > -1) {
                                td.html(td.html().replace(regex, '<span class="highlight-search" style="background-color:#ffe066;border-radius:2px;padding:0 2px;">$1</span>'));
                            }
                        });
                    } else {
                        $(this).hide();
                    }
                });

                // Tampilkan keterangan jika tidak ada hasil
                $('#no-result-coil').remove();
                if (Object.keys(matchedGroups).length === 0) {
                    var colSpan = 9 + listGudang.length;
                    $('#list-item-coil').append(
                        '<tr id="no-result-coil"><td colspan="' + colSpan + '" class="text-center text-muted py-3">' +
                        '<i class="fa fa-search"></i> No results found for "<b>' + keyword + '</b>"</td></tr>'
                    );
                }
            });

            /* ── Validasi gudang semua coil sudah dipilih ── */
            function validateGudang() {
                var kosong = false;
                $('#list-item-coil .coil-row').each(function() {
                    if (!$(this).find('.hidden-gudang-id').val()) kosong = true;
                });
                return !kosong;
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
                    var details = [];
                    $('#list-item-coil .coil-row').each(function() {
                        var row = $(this);
                        var idCoil = row.find('input[name*="[id_ros_coil]"]').val();
                        if (!idCoil) return; // skip baris tanpa coil

                        details.push({
                            id_ros_coil: idCoil,
                            id_ros_header: row.find('input[name*="[id_ros_header]"]').val(),
                            id_ros_material: row.find('input[name*="[id_ros_material]"]').val(),
                            id_po_detail: row.find('input[name*="[id_po_detail]"]').val(),
                            id_material: row.find('input[name*="[id_material]"]').val(),
                            no_coil: row.find('input[name*="[no_coil]"]').val(),
                            aktual_bersih: row.find('input[name*="[aktual_bersih]"]').val(),
                            id_gudang_ke: row.find('.hidden-gudang-id').val(),
                            kd_gudang_ke: row.find('.hidden-gudang-kd').val(),
                            status_qc: 'OK',
                        });
                    });

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

                    var details = [];
                    $('#list-item-coil .coil-row').each(function() {
                        var row = $(this);
                        var idCoil = row.find('input[name*="[id_ros_coil]"]').val();
                        if (!idCoil) return;

                        details.push({
                            id_ros_coil: idCoil,
                            id_ros_header: row.find('input[name*="[id_ros_header]"]').val(),
                            id_ros_material: row.find('input[name*="[id_ros_material]"]').val(),
                            id_po_detail: row.find('input[name*="[id_po_detail]"]').val(),
                            id_material: row.find('input[name*="[id_material]"]').val(),
                            no_coil: row.find('input[name*="[no_coil]"]').val(),
                            aktual_bersih: row.find('input[name*="[aktual_bersih]"]').val(),
                            id_gudang_ke: row.find('.hidden-gudang-id').val(),
                            kd_gudang_ke: row.find('.hidden-gudang-kd').val(),
                            status_qc: 'OK',
                        });
                    });

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

        });
    </script>
<?php endif; ?>