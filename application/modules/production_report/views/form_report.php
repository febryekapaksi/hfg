<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

<style>
.section-card { border-left: 4px solid #0d6efd; margin-bottom: 1rem; }
.section-card .card-header { font-weight: 600; }
.summary-table th { font-size: 0.8rem; }
.summary-table td { font-size: 0.85rem; }
.warning-selisih { background: #fff3cd; border: 1px solid #ffc107; }
.danger-selisih  { background: #f8d7da; border: 1px solid #dc3545; }
.mode-toggle .btn { min-width: 120px; }
</style>

<?php if ($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <?= $this->session->flashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-clipboard-list"></i> Input Laporan Produksi</h5>
        <a href="<?= base_url('production_report') ?>" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <form id="form-report" action="<?= base_url('production_report/save_report') ?>" method="POST" enctype="multipart/form-data">

            <!-- ── Header ── -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal Produksi</label>
                    <input type="text" name="tgl_produksi" id="tgl_produksi" class="form-control flatpickr-date"
                        value="<?= date('Y-m-d') ?>" required autocomplete="off">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">No SPK <span class="text-danger">*</span></label>
                    <select name="spk_no" id="spk_no" class="form-select select2-spk" required>
                        <option value="">-- Pilih SPK --</option>
                        <?php if (!empty($spk_list)): ?>
                            <?php foreach ($spk_list as $s): ?>
                                <option value="<?= $s->spk_no ?>"
                                    <?= (isset($spk) && $spk && $spk->spk_no === $s->spk_no) ? 'selected' : '' ?>>
                                    <?= $s->spk_no ?> — <?= $s->nm_produk_fg ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Produk</label>
                    <input type="text" id="info_produk" class="form-control bg-light" readonly
                        placeholder="Otomatis dari SPK"
                        value="<?= isset($spk) && $spk ? htmlspecialchars($spk->nm_produk_fg) : '' ?>">
                    <input type="hidden" name="id_produk_fg" id="id_produk_fg"
                        value="<?= isset($spk) && $spk ? htmlspecialchars($spk->produk_fg) : '' ?>">
                    <input type="hidden" name="no_coil" id="no_coil"
                        value="<?= isset($spk) && $spk ? htmlspecialchars($spk->no_coil ?? '') : '' ?>">
                </div>
            </div>

            <!-- ── Info Coil & Supplier (dari SPK) ── -->
            <div id="section-coil-info" class="<?= isset($spk) && $spk ? '' : 'd-none' ?>">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">No Coil</label>
                        <input type="text" id="disp_no_coil" class="form-control bg-light" readonly
                            value="<?= isset($spk) && $spk ? htmlspecialchars($spk->no_coil ?? '') : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Supplier</label>
                        <input type="text" id="disp_supplier" class="form-control bg-light" readonly
                            value="<?= isset($spk) && $spk ? htmlspecialchars($spk->nm_supplier ?? '') : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">No Mesin</label>
                        <input type="text" name="no_mesin" class="form-control" placeholder="Input nama mesin">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nama Operator</label>
                        <input type="text" name="nama_operator" class="form-control" placeholder="Input nama operator">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Jam Mulai</label>
                        <input type="text" name="jam_start" class="form-control flatpickr-time" placeholder="HH:MM" autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jam Selesai</label>
                        <input type="text" name="jam_selesai" class="form-control flatpickr-time" placeholder="HH:MM" autocomplete="off">
                    </div>
                </div>
            </div>

            <!-- ── Packing List (dari stok coil) ── -->
            <div id="section-packing-list" class="card section-card mb-3 <?= isset($spk) && $spk ? '' : 'd-none' ?>">
                <div class="card-header">Packing List</div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="text-muted small">Berat Kotor (kg)</div>
                            <div class="fs-5 fw-bold" id="pl_berat_kotor">—</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Berat Bersih (kg)</div>
                            <div class="fs-5 fw-bold" id="pl_berat_bersih">—</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Total Meter (m)</div>
                            <div class="fs-5 fw-bold" id="pl_length">—</div>
                        </div>
                    </div>
                    <input type="hidden" id="pl_berat_bersih_val" name="pl_berat_bersih" value="0">
                </div>
            </div>

            <!-- ── Laporan Produksi ── -->
            <div id="section-laporan" class="<?= isset($spk) && $spk ? '' : 'd-none' ?>">

                <!-- Berat Kotor Aktual -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Berat Kotor Aktual (kg)</label>
                        <input type="number" step="0.001" name="berat_kotor_aktual" class="form-control calc-input"
                            placeholder="Input kg" value="0">
                    </div>
                </div>

                <!-- 1. PACKING -->
                <div class="card section-card mb-3">
                    <div class="card-header">1. PACKING</div>
                    <div class="card-body">
                        <div class="row">
                            <?php
                            $packing_fields = [
                                'packing_kulit'      => 'Kulit',
                                'packing_bobin'      => 'Bobin Kertas',
                                'packing_clamp_ring' => 'Clamp / Ring',
                                'packing_tong_coil'  => 'Tong Coil',
                                'packing_wrapping'   => 'Wrapping',
                            ];
                            foreach ($packing_fields as $name => $label):
                            ?>
                            <div class="col-md-2 col-sm-4 mb-2">
                                <label class="form-label small"><?= $label ?> (kg)</label>
                                <input type="number" step="0.001" name="<?= $name ?>"
                                    class="form-control form-control-sm calc-input" placeholder="0" value="0">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- 2. FINISH GOOD -->
                <div class="card section-card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>2. FINISH GOOD</span>
                        <div class="mode-toggle btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-primary active" id="btn-mode-total"
                                onclick="setFgMode('total')">
                                Pilihan 1 (Total + Qty)
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="btn-mode-per-pcs"
                                onclick="setFgMode('per_pcs')">
                                Pilihan 2 (Per Pcs + Qty)
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="fg_input_mode" id="fg_input_mode" value="total">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small">QTY (pcs)</label>
                                <input type="number" step="0.01" name="fg_qty" id="fg_qty"
                                    class="form-control calc-input" placeholder="0" value="0"
                                    oninput="calcFg()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small" id="lbl-fg-input">Berat Total (kg)</label>
                                <input type="number" step="0.001" name="fg_kg" id="fg_kg"
                                    class="form-control calc-input" placeholder="0" value="0"
                                    oninput="calcFg()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small" id="lbl-fg-berat-per-pcs">Berat / Pcs (kg) <span class="text-muted small">otomatis</span></label>
                                <input type="number" step="0.0001" name="fg_berat_per_pcs" id="fg_berat_per_pcs"
                                    class="form-control bg-light" readonly placeholder="0"
                                    oninput="calcFg()">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. KW2 -->
                <div class="card section-card mb-3">
                    <div class="card-header">3. KW 2</div>
                    <div class="card-body">
                        <?php foreach (['internal' => 'Internal', 'supplier' => 'Supplier'] as $key => $label): ?>
                        <div class="row align-items-end mb-2">
                            <div class="col-md-1 d-flex align-items-center">
                                <span class="badge bg-<?= $key === 'internal' ? 'info' : 'warning' ?> text-dark"><?= $label ?></span>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">QTY</label>
                                <input type="number" step="0.01" name="kw2_<?= $key ?>_qty"
                                    class="form-control form-control-sm calc-input" placeholder="0" value="0">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Berat/Pcs (kg)</label>
                                <input type="number" step="0.0001" name="kw2_<?= $key ?>_berat_per_pcs"
                                    class="form-control form-control-sm" placeholder="0" value="0">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Berat Total (kg)</label>
                                <input type="number" step="0.001" name="kw2_<?= $key ?>_kg"
                                    class="form-control form-control-sm calc-input" placeholder="0" value="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Keterangan</label>
                                <input type="text" name="kw2_<?= $key ?>_keterangan"
                                    class="form-control form-control-sm" placeholder="Keterangan KW2">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Upload Foto</label>
                                <input type="file" name="kw2_<?= $key ?>_foto" class="form-control form-control-sm"
                                    accept="image/*">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 4. REJECT PRODUK -->
                <div class="card section-card mb-3">
                    <div class="card-header">4. Reject Produk</div>
                    <div class="card-body">
                        <?php foreach (['internal' => 'Internal', 'supplier' => 'Supplier'] as $key => $label): ?>
                        <div class="row align-items-end mb-2">
                            <div class="col-md-1 d-flex align-items-center">
                                <span class="badge bg-<?= $key === 'internal' ? 'info' : 'warning' ?> text-dark"><?= $label ?></span>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">QTY</label>
                                <input type="number" step="0.01" name="reject_<?= $key ?>_qty"
                                    class="form-control form-control-sm calc-input" placeholder="0" value="0">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Berat/Pcs (kg)</label>
                                <input type="number" step="0.0001" name="reject_<?= $key ?>_berat_per_pcs"
                                    class="form-control form-control-sm" placeholder="0" value="0">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Berat Total (kg)</label>
                                <input type="number" step="0.001" name="reject_<?= $key ?>_kg"
                                    class="form-control form-control-sm calc-input" placeholder="0" value="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Keterangan</label>
                                <input type="text" name="reject_<?= $key ?>_keterangan"
                                    class="form-control form-control-sm" placeholder="Keterangan reject">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Upload Foto</label>
                                <input type="file" name="reject_<?= $key ?>_foto" class="form-control form-control-sm"
                                    accept="image/*">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 5. REJECT MATERIAL PLAT -->
                <div class="card section-card mb-3">
                    <div class="card-header">5. Reject Material Plat</div>
                    <div class="card-body">
                        <?php foreach (['internal' => 'Internal', 'supplier' => 'Supplier'] as $key => $label): ?>
                        <div class="row align-items-end mb-2">
                            <div class="col-md-1 d-flex align-items-center">
                                <span class="badge bg-<?= $key === 'internal' ? 'info' : 'warning' ?> text-dark"><?= $label ?></span>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Berat (kg)</label>
                                <input type="number" step="0.001" name="reject_mat_<?= $key ?>_kg"
                                    class="form-control form-control-sm calc-input" placeholder="0" value="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Keterangan</label>
                                <input type="text" name="reject_mat_<?= $key ?>_ket"
                                    class="form-control form-control-sm" placeholder="Keterangan">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Upload Foto</label>
                                <input type="file" name="reject_mat_<?= $key ?>_foto" class="form-control form-control-sm"
                                    accept="image/*">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 6. POTONGAN PISAU -->
                <div class="card section-card mb-3">
                    <div class="card-header">6. Potongan Pisau</div>
                    <div class="card-body">
                        <div class="col-md-3">
                            <label class="form-label small">Berat (kg)</label>
                            <input type="number" step="0.001" name="potongan_pisau"
                                class="form-control calc-input" placeholder="0" value="0">
                        </div>
                    </div>
                </div>

                <!-- 7. SUMMARY -->
                <div class="card section-card mb-3" id="section-summary">
                    <div class="card-header">7. Summary Packing List vs Aktual</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm summary-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Finish Good</th>
                                        <th>KW2</th>
                                        <th>Reject Produk</th>
                                        <th>Reject Material</th>
                                        <th>Potongan Pisau</th>
                                        <th>Tong Coil</th>
                                        <th>Wrapping</th>
                                        <th class="table-warning">Total Output (kg)</th>
                                        <th class="table-info">KG Packing List</th>
                                        <th class="table-danger">Selisih (kg)</th>
                                        <th class="table-danger">Selisih (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td id="sum_fg">0</td>
                                        <td id="sum_kw2">0</td>
                                        <td id="sum_reject_produk">0</td>
                                        <td id="sum_reject_mat">0</td>
                                        <td id="sum_potongan">0</td>
                                        <td id="sum_tong">0</td>
                                        <td id="sum_wrapping">0</td>
                                        <td id="sum_total_output" class="fw-bold table-warning">0</td>
                                        <td id="sum_pl" class="table-info">0</td>
                                        <td id="sum_selisih" class="fw-bold table-danger">0</td>
                                        <td id="sum_selisih_pct" class="fw-bold table-danger">0%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Warning selisih -->
                        <div id="alert-selisih" class="m-3 p-3 rounded d-none">
                            <i class="fa fa-exclamation-triangle"></i>
                            <span id="alert-selisih-msg"></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fa fa-save"></i> Save Laporan
                    </button>
                    <a href="<?= base_url('production_report') ?>" class="btn btn-secondary btn-lg">Batal</a>
                </div>

            </div><!-- /section-laporan -->
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script>
var TOLERANSI_SELISIH = 0.03; // 3%

// ── Init plugins ─────────────────────────────────────────────────────────────
flatpickr('.flatpickr-date', { dateFormat: 'Y-m-d', locale: 'id', allowInput: true });
flatpickr('.flatpickr-time', { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true });

$(document).ready(function () {

    // Select2 SPK
    $('#spk_no').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih SPK --',
        allowClear: true,
        width: '100%'
    }).on('change', function () {
        var spk_no = $(this).val();
        if (!spk_no) {
            $('#section-coil-info, #section-packing-list, #section-laporan').addClass('d-none');
            return;
        }
        // Load info SPK via AJAX
        $.get(siteurl + 'production_report/get_spk_info/' + spk_no, function (res) {
            if (!res.success) return;
            $('#info_produk').val(res.nm_produk_fg || '');
            $('#id_produk_fg').val(res.produk_fg || '');
            $('#no_coil').val(res.no_coil || '');
            $('#disp_no_coil').val(res.no_coil || '');
            $('#disp_supplier').val(res.nm_supplier || '');

            // Load packing list dari coil
            if (res.no_coil) {
                $.get(siteurl + 'production_report/get_coil_packing_list', { no_coil: res.no_coil }, function (pl) {
                    if (pl.success) {
                        // $('#pl_berat_kotor').text(parseFloat(pl.berat_kotor || 0).toFixed(3));
                        $('#pl_berat_bersih').text(parseFloat(pl.net_weight_coil || 0).toFixed(3));
                        // $('#pl_length').text(parseFloat(pl.length || 0).toFixed(2));
                        // $('#pl_berat_bersih_val').val(pl.berat_bersih || 0);
                        recalcSummary();
                    }
                }, 'json');
            }

            $('#section-coil-info, #section-packing-list, #section-laporan').removeClass('d-none');
        }, 'json');
    });

    // Recalc saat ada input berubah
    $(document).on('input change', '.calc-input', function () {
        recalcSummary();
    });
});

// ── Mode FG ──────────────────────────────────────────────────────────────────
function setFgMode(mode) {
    $('#fg_input_mode').val(mode);
    if (mode === 'total') {
        $('#lbl-fg-input').text('Berat Total (kg)');
        $('#fg_kg').attr('readonly', false).removeClass('bg-light');
        $('#fg_berat_per_pcs').attr('readonly', true).addClass('bg-light');
        $('#lbl-fg-berat-per-pcs').html('Berat / Pcs (kg) <span class="text-muted small">otomatis</span>');
        $('#btn-mode-total').addClass('active btn-primary').removeClass('btn-outline-primary');
        $('#btn-mode-per-pcs').removeClass('active btn-primary').addClass('btn-outline-primary');
    } else {
        $('#lbl-fg-input').text('Berat / Pcs (kg)');
        $('#fg_kg').attr('readonly', true).addClass('bg-light');
        $('#fg_berat_per_pcs').attr('readonly', false).removeClass('bg-light');
        $('#lbl-fg-berat-per-pcs').html('Berat / Pcs (kg)');
        $('#btn-mode-per-pcs').addClass('active btn-primary').removeClass('btn-outline-primary');
        $('#btn-mode-total').removeClass('active btn-primary').addClass('btn-outline-primary');
    }
    calcFg();
}

function calcFg() {
    var mode = $('#fg_input_mode').val();
    var qty  = parseFloat($('#fg_qty').val()) || 0;
    if (mode === 'total') {
        var total = parseFloat($('#fg_kg').val()) || 0;
        var per_pcs = (qty > 0) ? total / qty : 0;
        $('#fg_berat_per_pcs').val(per_pcs.toFixed(4));
    } else {
        var per_pcs = parseFloat($('#fg_berat_per_pcs').val()) || 0;
        var total = qty * per_pcs;
        $('#fg_kg').val(total.toFixed(3));
    }
    recalcSummary();
}

// ── Recalc Summary ───────────────────────────────────────────────────────────
function recalcSummary() {
    var fg          = parseFloat($('input[name="fg_kg"]').val()) || 0;
    var kw2_int     = parseFloat($('input[name="kw2_internal_kg"]').val()) || 0;
    var kw2_sup     = parseFloat($('input[name="kw2_supplier_kg"]').val()) || 0;
    var rej_int     = parseFloat($('input[name="reject_internal_kg"]').val()) || 0;
    var rej_sup     = parseFloat($('input[name="reject_supplier_kg"]').val()) || 0;
    var rej_mat_int = parseFloat($('input[name="reject_mat_internal_kg"]').val()) || 0;
    var rej_mat_sup = parseFloat($('input[name="reject_mat_supplier_kg"]').val()) || 0;
    var potongan    = parseFloat($('input[name="potongan_pisau"]').val()) || 0;
    var tong        = parseFloat($('input[name="packing_tong_coil"]').val()) || 0;
    var wrapping    = parseFloat($('input[name="packing_wrapping"]').val()) || 0;
    var pl_bersih   = parseFloat($('#pl_berat_bersih_val').val()) || 0;

    var sum_kw2          = kw2_int + kw2_sup;
    var sum_reject_produk = rej_int + rej_sup;
    var sum_reject_mat   = rej_mat_int + rej_mat_sup;
    var total_output     = fg + sum_kw2 + sum_reject_produk + sum_reject_mat + potongan + tong + wrapping;
    var selisih          = total_output - pl_bersih;
    var selisih_pct      = (pl_bersih > 0) ? (selisih / pl_bersih) : 0;

    $('#sum_fg').text(fg.toFixed(3));
    $('#sum_kw2').text(sum_kw2.toFixed(3));
    $('#sum_reject_produk').text(sum_reject_produk.toFixed(3));
    $('#sum_reject_mat').text(sum_reject_mat.toFixed(3));
    $('#sum_potongan').text(potongan.toFixed(3));
    $('#sum_tong').text(tong.toFixed(3));
    $('#sum_wrapping').text(wrapping.toFixed(3));
    $('#sum_total_output').text(total_output.toFixed(3));
    $('#sum_pl').text(pl_bersih.toFixed(3));
    $('#sum_selisih').text(selisih.toFixed(3));
    $('#sum_selisih_pct').text((selisih_pct * 100).toFixed(2) + '%');

    // Warning / danger berdasarkan selisih
    var $alert = $('#alert-selisih');
    var absPct = Math.abs(selisih_pct);
    if (pl_bersih > 0 && absPct > TOLERANSI_SELISIH) {
        var msg = 'Selisih ' + (selisih_pct * 100).toFixed(2) + '% melebihi toleransi ' + (TOLERANSI_SELISIH * 100) + '%. '
                + (selisih < 0 ? 'Material KURANG dari packing list.' : 'Material LEBIH dari packing list.')
                + ' Harap cek ulang input atau timbangan.';
        $alert.removeClass('d-none warning-selisih danger-selisih')
              .addClass(absPct > 0.1 ? 'danger-selisih' : 'warning-selisih')
              .find('#alert-selisih-msg').text(msg);
        $alert.removeClass('d-none');
    } else {
        $alert.addClass('d-none');
    }
}
</script>
