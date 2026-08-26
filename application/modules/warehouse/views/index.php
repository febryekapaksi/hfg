<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<div class="card">
    <div class="card-body">

        <ul class="nav nav-tabs mb-3" id="tabStockCoil" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-produksi-coil-tab" data-bs-toggle="tab" href="#tab-produksi-coil" role="tab">
                    <i class="fa fa-warehouse"></i> Production Warehouse
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-slitting-coil-tab" data-bs-toggle="tab" href="#tab-slitting-coil" role="tab">
                    <i class="fa fa-store"></i> Slitting Warehouse
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-history-coil-tab" data-bs-toggle="tab" href="#tab-history-coil" role="tab">
                    <i class="fa fa-history"></i> History Per Days
                </a>
            </li>
        </ul>

        <div class="tab-content" id="tabStockCoilContent">

            <!-- TAB PRODUKSI -->
            <div class="tab-pane fade show active" id="tab-produksi-coil" role="tabpanel">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="text" class="form-control" id="search-produksi" placeholder="Search pack / material...">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th width="3%">No</th>
                                <th class="text-center" width="10%">Pack</th>
                                <th>Material</th>
                                <th class="text-center" width="5%">Roll</th>
                                <th class="text-end" width="9%">N.W. Total</th>
                                <th class="text-end" width="9%">N.W. Per Roll</th>
                                <th class="text-end" width="9%">G.W. Total</th>
                                <th class="text-end" width="9%">G.W. Per Roll</th>
                                <th class="text-center" width="8%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-produksi">
                            <tr><td colspan="9" class="text-center py-3"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB SLITTING -->
            <div class="tab-pane fade" id="tab-slitting-coil" role="tabpanel">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="text" class="form-control" id="search-slitting" placeholder="Search pack / material...">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th width="3%">No</th>
                                <th class="text-center" width="10%">Pack</th>
                                <th>Material</th>
                                <th class="text-center" width="5%">Roll</th>
                                <th class="text-end" width="9%">N.W. Total</th>
                                <th class="text-end" width="9%">N.W. Per Roll</th>
                                <th class="text-end" width="9%">G.W. Total</th>
                                <th class="text-end" width="9%">G.W. Per Roll</th>
                                <th class="text-center" width="8%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-slitting">
                            <tr><td colspan="9" class="text-center py-3 text-muted">Click tab to load data.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB HISTORY PER DAYS -->
            <div class="tab-pane fade" id="tab-history-coil" role="tabpanel">
                <div class="row mb-3 g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label mb-1 fw-semibold" style="font-size:12px;"><i class="fa fa-calendar"></i> As of Date</label>
                        <input type="text" id="hc_date_snap" class="form-control form-control-sm" placeholder="dd/mm/yyyy" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1 fw-semibold" style="font-size:12px;"><i class="fa fa-filter"></i> Gudang</label>
                        <select id="hc_gudang" class="form-select form-select-sm">
                            <option value="">-- All --</option>
                            <option value="PRO">Production</option>
                            <option value="SLI">Slitting</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary btn-sm" id="btn-filter-hc"><i class="fa fa-search"></i> Show</button>
                        <button class="btn btn-secondary btn-sm" id="btn-reset-hc"><i class="fa fa-refresh"></i> Reset</button>
                        <button class="btn btn-success btn-sm d-none" id="btn-excel-hc"><i class="fa fa-file-excel-o"></i> Excel</button>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="text" class="form-control" id="search-history" placeholder="Filter pack / material...">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead class="table-warning">
                            <tr>
                                <th width="3%">No</th>
                                <th class="text-center" width="10%">Pack</th>
                                <th>Material</th>
                                <th class="text-center" width="5%">Roll</th>
                                <th class="text-end" width="9%">N.W. Total</th>
                                <th class="text-end" width="9%">N.W. Per Roll</th>
                                <th class="text-end" width="9%">G.W. Total</th>
                                <th class="text-end" width="9%">G.W. Per Roll</th>
                                <th class="text-center" width="8%">Gudang</th>
                                <th class="text-center" width="6%">Status</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-history">
                            <tr><td colspan="10" class="text-center text-muted py-4"><i class="fa fa-info-circle"></i> Select a date then click <strong>Show</strong></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal QR Code -->
<div class="modal fade" id="modal-qr" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR Code Pack</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qr-container" class="d-flex justify-content-center align-items-center" style="min-height:200px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pack Detail -->
<div class="modal fade" id="modal-pack-detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa fa-eye me-1"></i> Pack Detail — <span id="pack-detail-code"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="pack-detail-body" style="max-height:70vh; overflow-y:auto;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fa fa-times"></i> Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script>
$(document).ready(function() {

    /* ── Helper: parse materials_concat → HTML ── */
    function parseMaterials(str) {
        if (!str) return '-';
        var mats = [...new Set(str.split(';;'))];
        var html = '';
        mats.forEach(function(m) {
            var parts = m.split('||');
            html += '<div style="font-size:13px;"><span class="text-primary me-1">&#9679;</span><b>' + (parts[0]||'') + '</b> <small class="text-muted">(' + (parts[1]||'') + ')</small></div>';
        });
        return html;
    }

    /* ── Helper: render pack rows ── */
    function renderPackRows(data, tbodyId) {
        if (!data || data.length === 0) {
            $('#' + tbodyId).html('<tr><td colspan="9" class="text-center text-muted py-3">No pack data available.</td></tr>');
            return;
        }
        var html = '';
        data.forEach(function(row, i) {
            var qrBtn = "<button type='button' class='btn btn-sm btn-info btn-show-qr' data-qr='" + row.pack_code + "'><i class='fa fa-qrcode'></i></button>";
            var detBtn = "<button type='button' class='btn btn-sm btn-outline-primary btn-show-pack-detail' data-pack-id='" + row.id_pack + "' data-pack-code='" + row.pack_code + "'><i class='fa fa-eye'></i></button>";
            html += '<tr class="pack-row" data-search="' + (row.pack_code + ' ' + (row.materials_concat || '')).toLowerCase() + '">' +
                '<td class="text-center">' + (i + 1) + '</td>' +
                '<td class="text-center"><span class="badge bg-primary">' + row.pack_code + '</span></td>' +
                '<td>' + parseMaterials(row.materials_concat) + '</td>' +
                '<td class="text-center">' + (parseInt(row.roll_count) || 0) + '</td>' +
                '<td class="text-end">' + parseFloat(row.total_nw || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>' +
                '<td class="text-end">' + parseFloat(row.nw_per_roll || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>' +
                '<td class="text-end">' + parseFloat(row.total_gw || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>' +
                '<td class="text-end">' + parseFloat(row.gw_per_roll || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>' +
                '<td class="text-center"><div class="d-flex justify-content-center gap-1">' + qrBtn + ' ' + detBtn + '</div></td></tr>';
        });
        $('#' + tbodyId).html(html);
    }

    /* ── Helper: render history rows ── */
    function renderHistoryRows(data) {
        if (!data || data.length === 0) {
            $('#tbody-history').html('<tr><td colspan="10" class="text-center text-muted py-3">No data available for this date.</td></tr>');
            return;
        }
        var html = '';
        data.forEach(function(row, i) {
            html += '<tr class="pack-row" data-search="' + (row.pack_code + ' ' + (row.materials_concat || '') + ' ' + (row.kd_gudang || '')).toLowerCase() + '">' +
                '<td class="text-center">' + (i + 1) + '</td>' +
                '<td class="text-center"><span class="badge bg-primary">' + row.pack_code + '</span></td>' +
                '<td>' + parseMaterials(row.materials_concat) + '</td>' +
                '<td class="text-center">' + (parseInt(row.roll_count) || 0) + '</td>' +
                '<td class="text-end">' + parseFloat(row.total_nw || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>' +
                '<td class="text-end">' + parseFloat(row.nw_per_roll || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>' +
                '<td class="text-end">' + parseFloat(row.total_gw || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>' +
                '<td class="text-end">' + parseFloat(row.gw_per_roll || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>' +
                '<td class="text-center">' + (row.kd_gudang || '-') + '</td>' +
                '<td class="text-center"><span class="badge bg-success">IN</span></td></tr>';
        });
        $('#tbody-history').html(html);
    }

    /* ── Load Production ── */
    $.post(siteurl + 'warehouse/get_packs_by_gudang', { kd_gudang: 'PRO' }, function(data) {
        renderPackRows(data, 'tbody-produksi');
    }, 'json');

    /* ── Load Slitting (lazy) ── */
    var slittingLoaded = false;
    document.getElementById('tab-slitting-coil-tab').addEventListener('shown.bs.tab', function() {
        if (!slittingLoaded) {
            $('#tbody-slitting').html('<tr><td colspan="9" class="text-center py-3"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
            $.post(siteurl + 'warehouse/get_packs_by_gudang', { kd_gudang: 'SLI' }, function(data) {
                renderPackRows(data, 'tbody-slitting');
            }, 'json');
            slittingLoaded = true;
        }
    });

    /* ── Client-side search ── */
    function filterTable(inputId, tbodyId) {
        $('#' + inputId).on('keyup', function() {
            var kw = $(this).val().toLowerCase().trim();
            var $rows = $('#' + tbodyId + ' .pack-row');
            // Remove previous no-result row
            $('#' + tbodyId + ' .no-result-row').remove();

            if (!kw) {
                $rows.show();
                return;
            }

            var visible = 0;
            $rows.each(function() {
                var match = ($(this).data('search') || '').indexOf(kw) > -1;
                $(this).toggle(match);
                if (match) visible++;
            });

            if (visible === 0) {
                var cols = $('#' + tbodyId).closest('table').find('thead th').length;
                $('#' + tbodyId).append('<tr class="no-result-row"><td colspan="' + cols + '" class="text-center text-muted py-3"><i class="fa fa-search"></i> No results found for "<b>' + kw + '</b>"</td></tr>');
            }
        });
    }
    filterTable('search-produksi', 'tbody-produksi');
    filterTable('search-slitting', 'tbody-slitting');
    filterTable('search-history', 'tbody-history');

    /* ── Flatpickr ── */
    flatpickr('#hc_date_snap', { locale: 'id', dateFormat: 'd/m/Y' });
    function getYmd(dmy) { var p = (dmy||'').split('/'); return p.length===3 ? p[2]+'-'+p[1]+'-'+p[0] : ''; }

    /* ── History filter ── */
    $('#btn-filter-hc').on('click', function() {
        var d = getYmd($('#hc_date_snap').val());
        if (!d) { Swal.fire({icon:'warning',title:'Warning',text:'Please select a date.'}); return; }
        $('#tbody-history').html('<tr><td colspan="10" class="text-center py-3"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
        $.post(siteurl + 'warehouse/get_packs_perday', { date_snap: d, kd_gudang: $('#hc_gudang').val() }, function(data) {
            renderHistoryRows(data);
            $('#btn-excel-hc').removeClass('d-none');
        }, 'json');
    });
    $('#btn-reset-hc').on('click', function() {
        $('#hc_date_snap').val(''); $('#hc_gudang').val(''); $('#search-history').val('');
        $('#tbody-history').html('<tr><td colspan="10" class="text-center text-muted py-4"><i class="fa fa-info-circle"></i> Select a date then click <strong>Show</strong></td></tr>');
        $('#btn-excel-hc').addClass('d-none');
    });
    $('#btn-excel-hc').on('click', function() {
        var d = getYmd($('#hc_date_snap').val());
        var g = $('#hc_gudang').val();
        if (!d) { Swal.fire({icon:'warning',title:'Warning',text:'Please select a date first.'}); return; }
        window.location.href = siteurl + 'warehouse/export_excel_history_pack?date_snap=' + d + '&kd_gudang=' + g;
    });

    /* ── QR Modal ── */
    var qrModal = null;
    $(document).on('click', '.btn-show-qr', function() {
        $('#qr-container').empty();
        new QRCode(document.getElementById("qr-container"), { text: $(this).data('qr'), width: 200, height: 200 });
        if (!qrModal) qrModal = new bootstrap.Modal(document.getElementById('modal-qr'));
        qrModal.show();
    });

    /* ── Pack Detail Modal ── */
    $(document).on('click', '.btn-show-pack-detail', function() {
        var idPack = $(this).data('pack-id'), packCode = $(this).data('pack-code');
        $('#pack-detail-code').text(packCode);
        $('#pack-detail-body').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>');
        new bootstrap.Modal(document.getElementById('modal-pack-detail')).show();

        $.post(siteurl + 'warehouse/get_pack_detail_ajax', { id_pack: idPack }, function(res) {
            if (res.status == 1 && res.data.length > 0) {
                var html = '<table class="table table-bordered table-sm table-striped" style="font-size:11px;"><thead class="table-light"><tr><th class="text-center">No</th><th>Material</th><th>No. Coil</th><th>Internal Code</th><th class="text-end">N.W.</th><th class="text-end">G.W.</th><th class="text-end">Length</th><th class="text-center">Type</th></tr></thead><tbody>';
                var tNw=0, tGw=0;
                res.data.forEach(function(c,i) {
                    tNw += parseFloat(c.net_weight)||0; tGw += parseFloat(c.gross_weight)||0;
                    var badge = parseInt(c.is_baby_coil)===1 ? '<span class="badge bg-warning text-dark">Baby</span>' : '<span class="badge bg-secondary">Normal</span>';
                    html += '<tr><td class="text-center">'+(i+1)+'</td><td>'+(c.trade_name||c.nm_material||'-')+'</td><td class="text-center">'+(c.no_coil||'-')+'</td><td class="text-center">'+(c.kode_internal||'-')+'</td><td class="text-end">'+parseFloat(c.net_weight||0).toLocaleString('id-ID',{minimumFractionDigits:2})+'</td><td class="text-end">'+parseFloat(c.gross_weight||0).toLocaleString('id-ID',{minimumFractionDigits:2})+'</td><td class="text-end">'+parseFloat(c.length||0).toLocaleString('id-ID',{minimumFractionDigits:2})+'</td><td class="text-center">'+badge+'</td></tr>';
                });
                html += '</tbody><tfoot class="table-secondary"><tr><td colspan="4" class="text-end fw-bold">Total</td><td class="text-end fw-bold">'+tNw.toLocaleString('id-ID',{minimumFractionDigits:2})+'</td><td class="text-end fw-bold">'+tGw.toLocaleString('id-ID',{minimumFractionDigits:2})+'</td><td colspan="2"></td></tr></tfoot></table>';
                $('#pack-detail-body').html(html);
            } else {
                $('#pack-detail-body').html('<div class="text-center text-muted py-4">No coil data found.</div>');
            }
        }, 'json');
    });

});
</script>
