<?php
$ENABLE_ADD    = has_permission('Pengajuan_mutasi.Add');
$ENABLE_MANAGE = has_permission('Pengajuan_mutasi.Manage');

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
                                <small class="text-muted d-block text-uppercase font-size-xs fw-bold">No. Mutasi</small>
                                <span class="fs-6 fw-bold text-dark"><?= ($m['mutation_number']) ?></span>
                            </div>

                            <div class="px-2 flex-fill border-start-custom">
                                <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Tanggal Pengajuan</small>
                                <span class="text-dark fw-semibold">
                                    <?= date('d/m/Y', strtotime($m['mutation_date'])) ?>
                                </span>
                            </div>

                            <div class="px-2 flex-fill border-start-custom">
                                <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Oleh</small>
                                <span class="text-dark fw-semibold">
                                    <?= !empty($m['create_by']) ? $m['create_by'] : '-' ?>
                                </span>
                            </div>

                            <div class="px-2 flex-fill border-start-custom">
                                <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Status</small>
                                <?php
                                $status_map = [
                                    0 => ['Open',      'primary'],
                                    1 => ['Menunggu Approve',   'warning'],
                                    2 => ['Approved',  'success'],
                                    3 => ['Rejected',  'danger'],
                                    4 => ['Done',      'dark'],
                                    5 => ['Cancelled', 'secondary'],
                                    6 => ['Revisi', 'danger'],
                                ];
                                $st = $status_map[$m['status']] ?? ['-', 'secondary'];
                                ?>
                                <span class="badge bg-<?= $st[1] ?> px-2 py-1"><?= $st[0] ?></span>
                            </div>

                        </div>
                    </div>

                    <?php if (!empty($m['reject_reason'])): ?>
                        <div class="col-md-5 col-12 border-start-md ps-md-4 py-1 text-start">
                            <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Alasan Reject/Cancel</small>
                            <span class="text-danger fw-semibold">
                                <i class="fa-solid fa-circle-exclamation me-1"></i> <?= ($m['reject_reason']) ?>
                            </span>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php endif; ?>


        <form id="formMutasi" enctype="multipart/form-data">

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">No. Berita Acara <span class="text-danger">*</span></label>
                    <input type="text" id="no_berita_acara" name="no_berita_acara"
                        class="form-control" <?= $readonly ?>
                        value="<?= ($m['no_berita_acara'] ?? '') ?>"
                        placeholder="Masukkan No. Berita Acara">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Gudang Asal <span class="text-danger">*</span></label>
                    <select id="id_gudang_from" class="form-select" <?= $disabled ?>>
                        <option value="">-- Pilih Gudang Asal --</option>
                        <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>"
                                <?= (isset($m['id_gudang_from']) && $m['id_gudang_from'] == $wh['id']) ? 'selected' : '' ?>>
                                <?= ($wh['nm_gudang']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Gudang Tujuan <span class="text-danger">*</span></label>
                    <select id="id_gudang_to" class="form-select" <?= $disabled ?>>
                        <option value="">-- Pilih Gudang Tujuan --</option>
                        <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>"
                                <?= (isset($m['id_gudang_to']) && $m['id_gudang_to'] == $wh['id']) ? 'selected' : '' ?>>
                                <?= ($wh['nm_gudang']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Keterangan <span class="text-danger">*</span></label>
                    <input type="text" id="description" name="description"
                        class="form-control" <?= $readonly ?>
                        value="<?= ($m['description'] ?? '') ?>"
                        placeholder="Masukkan Alasan Mutasi">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Attach File <small class="text-muted">(PDF/JPG/PNG, maks. 5MB)</small></label>
                    <?php if ($is_view): ?>
                        <?php if (!empty($m['file_name_hash'])): ?>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-paperclip text-primary"></i>
                                <a href="<?= base_url('uploads/berita_acara_mutasi/' . $m['file_name_hash']) ?>"
                                    target="_blank" class="text-truncate" style="max-width:200px;"
                                    title="<?= ($m['file_name_original']) ?>">
                                    <?= ($m['file_name_original']) ?>
                                </a>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">Tidak ada file</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <input type="file" id="berita_acara_file" name="berita_acara_file"
                            class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <?php if ($is_edit && !empty($m['file_name_hash'])): ?>
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <small class="text-muted">File saat ini:</small>
                                <a href="<?= base_url('uploads/berita_acara_mutasi/' . $m['file_name_hash']) ?>"
                                    target="_blank" class="small text-truncate" style="max-width:200px;"
                                    title="<?= ($m['file_name_original']) ?>">
                                    <?= ($m['file_name_original']) ?>
                                </a>
                                <small class="text-muted">(kosongkan jika tidak ingin mengganti)</small>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Detail Material & Coil</h6>
                <?php if (!$is_view): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddMaterial">
                        <i class="fa-solid fa-plus"></i> Kelompokkan Material
                    </button>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="tblDetail">
                    <thead class="table-light">
                        <tr>
                            <th>Material</th>
                            <th>Kode Internal (Coil)</th>
                            <th>No. Coil</th>
                            <th width="140">Net Weight (kg)</th>
                            <th width="140">Length (m)</th> <?php if (!$is_view): ?>
                                <th width="60">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="detailBody"></tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="3" class="text-end">Total Keseluruhan</td>
                            <td id="totalNetWeight">0.00</td>
                            <td id="totalLength">0.00</td> <?php if (!$is_view): ?><td></td><?php endif; ?>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <input type="hidden" id="details_json" name="details_json" value="">

            <?php if (!$is_view): ?>
                <div class="mt-3 d-flex gap-2 justify-content-end">
                    <a href="<?= site_url('pengajuan_mutasi') ?>" class="btn btn-secondary">Batal</a>
                    <button type="button" class="btn btn-primary" id="btnSave">
                        <i class="fa-solid fa-save"></i> Simpan
                    </button>
                </div>
            <?php endif; ?>

        </form>
    </div>
</div>

<div class="modal fade" id="modalCoil" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Coil untuk Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="coilLoading" class="text-center py-3" style="display:none;">
                    <div class="spinner-border spinner-border-sm text-primary"></div> Memuat data coil...
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="tblCoil">
                        <thead class="table-light">
                            <tr>
                                <th width="50" class="text-center th-clickable" style="cursor: pointer; vertical-align: middle;">
                                    <input type="checkbox" id="checkAllCoil" class="form-check-input"
                                        style="width: 20px; height: 20px; margin: 0; cursor: pointer;">
                                </th>
                                <th>Kode Internal</th>
                                <th>No. Coil</th>
                                <th>Gross (kg)</th>
                                <th>Net (kg)</th>
                                <th>Length (m)</th>
                            </tr>
                        </thead>
                        <tbody id="coilBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnConfirmCoil">
                    <i class="fa-solid fa-check"></i> Konfirmasi Pilihan
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMaterial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-box-open me-2"></i>Pilih Kelompok Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" id="searchMaterialInput" class="form-control" placeholder="Cari nama material atau nama dagang...">
                </div>

                <div class="list-group" id="materialListContainer" style="max-height: 350px; overflow-y: auto;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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

    let detailRows = [];
    let currentRowId = null;
    let rowCounter = 0;

    $(document).ready(function() {
        // Load existing data
        if (EXISTING_DETAILS.length > 0) {
            EXISTING_DETAILS.forEach(function(d) {
                const rowId = ++rowCounter;
                detailRows.push({
                    rowId: rowId,
                    material: {
                        id_warehouse_stock: d.id_warehouse_stock,
                        id_material: d.id_material,
                        nm_material: d.nm_material,
                        trade_name: d.trade_name,
                        code_lv4: d.code_lv4,
                        id_unit: d.id_unit,
                        harga_beli: d.harga_beli
                    },
                    coils: d.coils || []
                });
            });
            renderTable();
        }

        $('#btnAddMaterial').on('click', function() {
            const idGudang = $('#id_gudang_from').val();
            if (!idGudang) {
                Swal.fire('Perhatian', 'Pilih gudang asal terlebih dahulu.', 'warning');
                return;
            }
            showMaterialPicker(idGudang);
        });

        $('#btnSave').on('click', saveForm);

        $('#checkAllCoil').on('change', function() {
            $('#coilBody input[type=checkbox]').prop('checked', this.checked);
        });

        $('#btnConfirmCoil').on('click', confirmCoilSelection);
    });

    // ---------------------------------------------------------------
    // MATERIAL PICKER
    // ---------------------------------------------------------------
    function showMaterialPicker(idGudang) {
        // Tampilkan loading/kosongkan list lama terlebih dahulu
        $('#materialListContainer').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat data...</div>');
        $('#searchMaterialInput').val(''); // Reset form input pencarian
        $('#modalMaterial').modal('show');

        // Ambil daftar ID material yang sudah berada di tabel detail saat ini
        const existingIds = detailRows.map(r => parseInt(r.material.id_warehouse_stock));

        $.get(BASE_URL + '/get_material?id_gudang=' + idGudang, function(res) {
            if (res.status != 1 || !res.data.length) {
                $('#materialListContainer').html('<div class="text-center text-muted py-3">Tidak ada material tersedia di gudang ini.</div>');
                return;
            }

            let listHtml = '';
            res.data.forEach(function(m) {
                const isAlreadySelected = existingIds.includes(parseInt(m.id));
                const tradeNameLabel = m.trade_name ? ` <span class="text-muted small">(${escHtml(m.trade_name)})</span>` : '';

                // Konfigurasi atribut jika material sudah dipilih sebelumnya
                const disabledAttr = isAlreadySelected ? 'disabled' : '';
                const customStyle = isAlreadySelected ? 'style="background-color: #f8f9fa; cursor: not-allowed; opacity: 0.6;"' : '';
                const statusLabel = isAlreadySelected ? '<span class="badge bg-secondary text-white small">Sudah Dipilih</span>' : '<i class="fa-solid fa-chevron-right text-muted small"></i>';

                listHtml += `
                <button type="button" class="list-group-item list-group-item-action material-item-btn py-2.5" 
                        data-json='${JSON.stringify(m)}' 
                        data-search="${escHtml(m.nm_material.toLowerCase())} ${escHtml((m.trade_name || '').toLowerCase())}"
                        ${disabledAttr}
                        ${customStyle}>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-semibold text-dark search-target">${escHtml(m.nm_material)}</span>
                            ${tradeNameLabel}
                        </div>
                        <div>
                            ${statusLabel}
                        </div>
                    </div>
                </button>
            `;
            });

            $('#materialListContainer').html(listHtml);

            // --- Logika Fitur Pencarian / Search Di Dalam Modal ---
            $('#searchMaterialInput').off('input').on('input', function() {
                const keyword = this.value.toLowerCase().trim();

                $('.material-item-btn').each(function() {
                    const searchTarget = $(this).attr('data-search');
                    if (searchTarget.includes(keyword)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // --- Logika Saat Material Dipilih ---
            $('.material-item-btn').off('click').on('click', function() {
                // Pengaman tambahan jika button disabled dipaksa klik bypass HTML
                if ($(this).prop('disabled')) return;

                const mat = JSON.parse($(this).attr('data-json'));

                // Tutup modal material
                $('#modalMaterial').modal('hide');

                const rowId = ++rowCounter;
                detailRows.push({
                    rowId: rowId,
                    material: {
                        id_warehouse_stock: mat.id,
                        id_material: mat.id,
                        nm_material: mat.nm_material,
                        trade_name: mat.trade_name,
                        code_lv4: mat.code_lv4,
                        id_unit: mat.id_unit,
                        harga_beli: mat.harga_beli,
                    },
                    coils: []
                });

                // Render ulang tabel utama
                renderTable();

                // Langsung buka modal coil secara otomatis setelah modal material tertutup
                setTimeout(function() {
                    openCoilModal(rowId);
                }, 400);
            });

        }, 'json');
    }

    // ---------------------------------------------------------------
    // RENDER TABEL (GROUP BY MATERIAL)
    // ---------------------------------------------------------------
    function renderTable() {
        const tbody = $('#detailBody');
        tbody.empty();

        if (detailRows.length === 0) {
            tbody.html('<tr><td colspan="6" class="text-center text-muted py-3">Belum ada data material dipilih.</td></tr>');
            recalcTotals();
            return;
        }

        detailRows.forEach(function(row) {
            const coilsCount = row.coils.length;
            const actionBtnHtml = IS_VIEW ? '' : `
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMaterialGroup(${row.rowId})" title="Hapus Kelompok Material">
                <i class="fa-solid fa-trash"></i>
            </button>
        `;

            const addCoilBtnHtml = IS_VIEW ? '' : `
            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-primary" style="font-size: 11px; padding: 2px 8px;" onclick="openCoilModal(${row.rowId})">
                    <i class="fa-solid fa-plus-circle"></i> Pilih / Atur Coil
                </button>
            </div>
        `;

            if (coilsCount === 0) {
                let emptyRow = `
                <tr class="table-warning-bg" id="group-${row.rowId}">
                    <td>
                        <div class="fw-bold text-dark">${escHtml(row.material.nm_material)}</div>
                        ${row.material.trade_name ? '<small class="text-muted d-block">' + escHtml(row.material.trade_name) + '</small>' : ''}
                        ${addCoilBtnHtml}
                    </td>
                    <td colspan="4" class="text-center text-danger fw-semibold">Belum ada coil yang dipilih</td>
                    <td class="text-center">${actionBtnHtml}</td>
                </tr>
            `;
                tbody.append(emptyRow);
            } else {
                row.coils.forEach(function(coil, index) {
                    const netW = parseFloat(coil.net_weight || 0);
                    const lenW = parseFloat(coil.length || 0); // Ambil data length coil

                    let rowHtml = '<tr>';

                    if (index === 0) {
                        rowHtml += `
                        <td rowspan="${coilsCount}">
                            <div class="fw-bold text-dark">${escHtml(row.material.nm_material)}</div>
                            ${row.material.trade_name ? '<small class="text-muted d-block">' + escHtml(row.material.trade_name) + '</small>' : ''}
                            ${addCoilBtnHtml}
                        </td>
                    `;
                    }

                    rowHtml += `
                    <td><span class="badge bg-light text-dark border">${escHtml(coil.kode_internal || '-')}</span></td>
                    <td>${escHtml(coil.no_coil)}</td>
                    <td>${formatNum(netW, 2)}</td>
                    <td>${formatNum(lenW, 2)}</td> `;

                    if (index === 0) {
                        rowHtml += `
                        <td rowspan="${coilsCount}" class="text-center">
                            ${actionBtnHtml}
                        </td>
                    `;
                    }

                    rowHtml += '</tr>';
                    tbody.append(rowHtml);
                });
            }
        });

        recalcTotals();
    }

    function removeMaterialGroup(rowId) {
        detailRows = detailRows.filter(r => r.rowId !== rowId);
        renderTable();
    }

    // ---------------------------------------------------------------
    // MODAL COIL MAPPER
    // ---------------------------------------------------------------
    function openCoilModal(rowId) {
        currentRowId = rowId;
        const rowData = detailRows.find(r => r.rowId === rowId);
        if (!rowData) return;

        if ($.fn.DataTable.isDataTable('#tblCoil')) {
            $('#tblCoil').DataTable().destroy();
        }

        $('#coilBody').empty();
        $('#coilLoading').show();
        $('#checkAllCoil').prop('checked', false);
        $('#modalCoil').modal('show');

        $.get(BASE_URL + '/get_coil?code_lv4=' + rowData.material.code_lv4, function(res) {
            $('#coilLoading').hide();

            if (res.status != 1 || !res.data.length) {
                $('#coilBody').html('<tr><td colspan="7" class="text-center text-muted">Tidak ada coil tersedia untuk material ini</td></tr>');
                return;
            }

            const selectedIds = rowData.coils.map(c => parseInt(c.id_warehouse_stock_coil || c.id));
            let html = '';

            res.data.forEach(function(c) {
                const isChecked = selectedIds.includes(parseInt(c.id)) ? 'checked' : '';
                html += `
        <tr>
            <td class="text-center align-middle td-clickable" style="cursor: pointer; min-width: 50px;">
                <input type="checkbox" class="form-check-input coil-check" value="${c.id}"
                    style="width: 20px; height: 20px; margin: 0; cursor: pointer;"
                    data-json='${JSON.stringify(c)}' ${isChecked}>
            </td>
            <td class="align-middle"><span class="badge bg-light text-dark border">${escHtml(c.kode_internal || '-')}</span></td>
            <td class="align-middle"><strong>${escHtml(c.no_coil)}</strong></td>
            <td class="align-middle">${formatNum(c.gross_weight, 2)}</td>
            <td class="align-middle">${formatNum(c.net_weight, 2)}</td>
            <td class="align-middle">${formatNum(c.length, 2)}</td>
        </tr>`;
            });

            $('#coilBody').html(html);

            // 2. Inisialisasi DataTables setelah HTML berhasil di-render ke tbody
            const tableCoil = $('#tblCoil').DataTable({
                "responsive": true,
                "paging": false,
                "order": [],
                "columnDefs": [{
                    "orderable": false,
                    "targets": 0
                }]
            });

            // 3. Fix Check All agar bekerja di seluruh halaman DataTables
            $('#checkAllCoil').off('change').on('change', function() {
                const cells = tableCoil.cells({
                    page: 'current'
                }).nodes();
                $(cells).find('.coil-check').prop('checked', this.checked);
            });

            // BARU: Event Listener untuk membuat satu kolom TD bisa diklik
            $('#tblCoil').off('click', '.td-clickable').on('click', '.td-clickable', function(e) {
                // Mencegah trigger double jika yang diklik tepat di target input checkbox-nya
                if ($(e.target).is('.coil-check')) {
                    return;
                }

                const checkbox = $(this).find('.coil-check');
                checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
            });

        }, 'json');
    }

    function confirmCoilSelection() {
        const selectedCoils = [];
        $('#coilBody .coil-check:checked').each(function() {
            selectedCoils.push(JSON.parse($(this).attr('data-json')));
        });

        if (!selectedCoils.length) {
            Swal.fire('Perhatian', 'Pilih minimal satu coil, atau hapus kelompok jika batal.', 'warning');
            return;
        }

        const rowData = detailRows.find(r => r.rowId === currentRowId);
        if (rowData) {
            rowData.coils = selectedCoils.map(c => ({
                id: c.id,
                id_warehouse_stock_coil: c.id,
                no_coil: c.no_coil,
                no_ipp: c.no_ipp,
                no_po: c.no_po,
                no_ros: c.no_ros,
                kode_internal: c.kode_internal,
                gross_weight: c.gross_weight,
                net_weight: c.net_weight,
                length: c.length,
                harga_beli: c.harga_beli,
                total_nilai: c.total_nilai,
                total_nilai_mutasi: parseFloat(c.total_nilai || 0)
            }));
        }

        $('#modalCoil').modal('hide');
        renderTable();
    }

    // ---------------------------------------------------------------
    // CALCULATE TOTALS
    // ---------------------------------------------------------------
    function recalcTotals() {
        let totalNet = 0,
            totalLen = 0;

        detailRows.forEach(function(r) {
            r.coils.forEach(function(c) {
                totalNet += parseFloat(c.net_weight || 0);
                totalLen += parseFloat(c.length || 0); // Menjumlahkan total length
            });
        });

        $('#totalNetWeight').text(formatNum(totalNet, 2));
        $('#totalLength').text(formatNum(totalLen, 2)); // Render total length ke footer
    }

    // ---------------------------------------------------------------
    // SAVE FORM HANDLER
    // ---------------------------------------------------------------
    function saveForm() {
        const no_berita_acara = $('#no_berita_acara').val().trim();
        const id_gudang_from = $('#id_gudang_from').val();
        const id_gudang_to = $('#id_gudang_to').val();
        const description = $('#description').val().trim();

        if (!no_berita_acara) {
            Swal.fire('Perhatian', 'No. Berita Acara wajib diisi.', 'warning');
            return;
        }
        if (!id_gudang_from) {
            Swal.fire('Perhatian', 'Gudang asal wajib dipilih.', 'warning');
            return;
        }
        if (!id_gudang_to) {
            Swal.fire('Perhatian', 'Gudang tujuan wajib dipilih.', 'warning');
            return;
        }
        if (id_gudang_from === id_gudang_to) {
            Swal.fire('Perhatian', 'Gudang asal dan tujuan tidak boleh sama.', 'warning');
            return;
        }
        if (!detailRows.length) {
            Swal.fire('Perhatian', 'Minimal satu material harus ditambahkan.', 'warning');
            return;
        }
        if (!description) {
            Swal.fire('Perhatian', 'Keterangan Alasan mutasi wajib diisi.', 'warning');
            return;
        }

        const noCoil = detailRows.find(r => r.coils.length === 0);
        if (noCoil) {
            Swal.fire('Perhatian', `Material "${noCoil.material.nm_material}" belum memiliki coil terpilih.`, 'warning');
            return;
        }

        const fileInput = document.getElementById('berita_acara_file');
        if (fileInput && fileInput.files.length > 0) {
            const maxSize = 5 * 1024 * 1024;
            if (fileInput.files[0].size > maxSize) {
                Swal.fire('Perhatian', 'Ukuran file maksimal 5MB.', 'warning');
                return;
            }
        }

        const detailsPayload = detailRows.map(r => ({
            id_warehouse_stock: r.material.id_warehouse_stock,
            id_material: r.material.id_material,
            nm_material: r.material.nm_material,
            trade_name: r.material.trade_name,
            code_lv4: r.material.code_lv4,
            id_unit: r.material.id_unit,
            harga_beli: r.material.harga_beli,
            coils: r.coils,
        }));

        $('#details_json').val(JSON.stringify(detailsPayload));

        const formData = new FormData(document.getElementById('formMutasi'));
        formData.set('no_berita_acara', no_berita_acara);
        formData.set('id_gudang_from', id_gudang_from);
        formData.set('id_gudang_to', id_gudang_to);
        formData.set('description', $('#description').val());
        formData.set('details_json', JSON.stringify(detailsPayload));

        const url = MODE === 'edit' ? BASE_URL + '/update/' + RECORD_ID : BASE_URL + '/save';

        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                $('#btnSave').prop('disabled', false).html('<i class="fa-solid fa-save"></i> Simpan');
                if (res.status == 1) {
                    Swal.fire({
                        title: 'Berhasil',
                        text: res.message,
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = '<?= site_url('pengajuan_mutasi') ?>';
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function() {
                $('#btnSave').prop('disabled', false).html('<i class="fa-solid fa-save"></i> Simpan');
                Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
            }
        });
    }

    // ---------------------------------------------------------------
    // UTILITIES
    // ---------------------------------------------------------------
    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function formatNum(val, dec) {
        return parseFloat(val || 0).toLocaleString('id-ID', {
            minimumFractionDigits: dec,
            maximumFractionDigits: dec
        });
    }

    function formatRp(val) {
        return 'Rp ' + parseFloat(val || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 0
        });
    }
</script>