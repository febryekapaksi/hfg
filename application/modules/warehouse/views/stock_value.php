<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    #modal-history .modal-body table {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        overflow: visible !important;
    }

    #modal-history .modal-body thead {
        position: sticky;
        top: 0;
        z-index: 10;
    }

    #modal-history .modal-body thead th {
        background-color: #f8f9fa !important;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.2);
        border-bottom: 2px solid #dee2e6;
    }

    #modal-detail-coil-trx {
        z-index: 1060;
    }

    .modal-backdrop:nth-of-type(2) {
        z-index: 1055;
    }

    #modal-history.blur-background .modal-content {
        filter: blur(3px);
        transition: filter 0.2s ease;
    }

    #modal-history .modal-content {
        transition: filter 0.2s ease;
    }
</style>
<div class="card">
    <div class="card-body">

        <div class="row mb-3 g-2 align-items-end" id="bar-filter-live">
            <div class="col-md-4">
                <input type="text" id="filter_material" class="form-control"
                    placeholder="Search code / material name...">
            </div>
            <div class="col-md-8 text-end">
                <button class="btn btn-sm btn-primary" id="btn-filter">
                    <i class="fa fa-search"></i> Filter
                </button>
                <button class="btn btn-sm btn-secondary" id="btn-reset">
                    <i class="fa fa-refresh"></i> Reset
                </button>
                <button class="btn btn-sm btn-success" id="btn-excel">
                    <i class="fa fa-file-excel-o"></i> Download Excel
                </button>
            </div>
        </div>

        <!-- ── Tab ───────────────────────────────────────────────────── -->
        <ul class="nav nav-tabs mb-3" id="tabStockValue" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-sv-produksi-tab"
                    data-bs-toggle="tab" data-bs-target="#tab-sv-produksi"
                    type="button" role="tab">
                    <i class="fa fa-warehouse"></i> Production Warehouse
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-sv-slitting-tab"
                    data-bs-toggle="tab" data-bs-target="#tab-sv-slitting"
                    type="button" role="tab">
                    <i class="fa fa-store"></i> Slitting Warehouse
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-sv-history-tab"
                    data-bs-toggle="tab" data-bs-target="#tab-sv-history"
                    type="button" role="tab">
                    <i class="fa fa-history"></i> History Per Days
                </button>
            </li>
        </ul>

        <div class="tab-content" id="tabStockValueContent">

            <!-- TAB produksi -->
            <div class="tab-pane fade show active" id="tab-sv-produksi" role="tabpanel">
                <div class="table-responsive">
                    <table id="table-sv-produksi" class="table table-bordered table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th width="4%">No</th>
                                <th>Material Code</th>
                                <th>Material Name</th>
                                <th class="text-center">Coil Count</th>
                                <th class="text-end">Qty Stock (Kg)</th>
                                <th class="text-end">Purchase Price (Avg)</th>
                                <th class="text-end">Total Value</th>
                                <th width="8%" class="text-center no-sort">Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end"><strong>GRAND TOTAL PRODUKSI</strong></th>
                                <th class="text-end" id="grand-total-produksi">—</th>
                                <th></th>
                            </tr>
                        </tfoot>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB slitting -->
            <div class="tab-pane fade" id="tab-sv-slitting" role="tabpanel">
                <div class="table-responsive">
                    <table id="table-sv-slitting" class="table table-bordered table-striped table-hover">
                        <thead class="table-success">
                            <tr>
                                <th width="4%">No</th>
                                <th>Material Code</th>
                                <th>Material Name</th>
                                <th class="text-center">Coil Count</th>
                                <th class="text-end">Qty Stock (Kg)</th>
                                <th class="text-end">Purchase Price (Avg)</th>
                                <th class="text-end">Total Value</th>
                                <th width="8%" class="text-center no-sort">Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end"><strong>GRAND TOTAL SLITTING</strong></th>
                                <th class="text-end" id="grand-total-slitting">—</th>
                                <th></th>
                            </tr>
                        </tfoot>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB HISTORY PER DAYS -->
            <div class="tab-pane fade" id="tab-sv-history" role="tabpanel">

                <div class="row mb-3 g-2 align-items-end justify-content-between">

                    <div class="col-md-8">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <label class="form-label mb-1 fw-semibold" style="font-size:12px;">
                                    <i class="fa fa-search"></i> Search Material
                                </label>
                                <input type="text" id="sv_hist_material" class="form-control form-control-sm"
                                    placeholder="Code / material name...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1 fw-semibold" style="font-size:12px;">
                                    <i class="fa fa-calendar"></i> As of Date
                                </label>
                                <input type="text" id="sv_date_snap" class="form-control form-control-sm"
                                    placeholder="dd/mm/yyyy" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label mb-1 fw-semibold" style="font-size:12px;">
                                    <i class="fa fa-filter"></i> Gudang
                                </label>
                                <select id="sv_hist_gudang" class="form-select form-select-sm">
                                    <option value="">-- All --</option>
                                    <option value="PRO">Production Warehouse</option>
                                    <option value="SLI">Slitting Warehouse</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 text-end">
                        <button class="btn btn-primary btn-sm" id="btn-filter-svh">
                            <i class="fa fa-search"></i> Show
                        </button>
                        <button class="btn btn-secondary btn-sm" id="btn-reset-svh">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                        <button class="btn btn-success btn-sm d-none" id="btn-excel-history">
                            <i class="fa fa-file-excel-o"></i> Download Excel
                        </button>
                    </div>

                </div>

                <div class="row mb-2">
                    <div class="col text-end">
                        <span style="font-size:13px;">Grand Total:</span>
                        <strong id="grand-total-history" class="ms-2" style="font-size:14px;">—</strong>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="table-sv-history" class="table table-bordered table-striped table-hover">
                        <thead class="table-warning">
                            <tr>
                                <th width="4%">No</th>
                                <th>Material Code</th>
                                <th>Material Name</th>
                                <th class="text-center">Warehouse</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Coil Count</th>
                                <th class="text-end">Qty Stock (Kg)</th>
                                <th class="text-end">Purchase Price (Avg)</th>
                                <th class="text-end">Total Value</th>
                                <th width="8%" class="text-center no-sort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fa fa-info-circle"></i>
                                    Select a date then click <strong>Show</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal History -->
<div class="modal fade" id="modal-history" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa fa-history"></i>
                    History Stok — <span id="modal-title-material"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="max-height: 80vh; overflow-y: auto;">
                <table class="table table-bordered table-striped table-sm mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Date</th>
                            <th>No. Transaksi</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Coil Count</th>
                            <th class="text-end">Qty Awal (Kg)</th>
                            <th class="text-end">Qty Transaksi (Kg)</th>
                            <th class="text-end">Qty Akhir (Kg)</th>
                            <th class="text-end">Total Harga</th>
                            <th class="text-end">Saldo Awal</th>
                            <th class="text-end">Saldo Akhir</th>
                            <th class="text-end">Harga Lama</th>
                            <th class="text-end">Harga Baru (Avg)</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-history">
                        <tr>
                            <td colspan="13" class="text-center">Select a material to view history</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Coil -->
<div class="modal fade" id="modal-detail-coil" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fa fa-list"></i>
                    Detail Coil — <span id="coil-title-material"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>No. Coil</th>
                                <th>Internal Code</th>
                                <th class="text-end">Net Weight (Kg)</th>
                                <th class="text-end">Gross Weight (Kg)</th>
                                <th class="text-end">Panjang (m)</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-detail-coil">
                            <tr>
                                <td colspan="6" class="text-center">—</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Coil Per Transaksi (drill-down dari modal history) -->
<div class="modal fade" id="modal-detail-coil-trx" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fa fa-list"></i>
                    Detail Coil Transaksi — <span id="coil-trx-title"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>No. Coil</th>
                                <th>Internal Code</th>
                                <th class="text-end">Net Weight (Kg)</th>
                                <th class="text-end">Gross Weight (Kg)</th>
                                <th class="text-end">Panjang (m)</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-detail-coil-trx">
                            <tr>
                                <td colspan="7" class="text-center">—</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script>
    $(document).ready(function() {

        // ── Kolom live ────────────────────────────────────────────────────────
        var colDefLive = [{
                data: 0,
                width: '4%'
            },
            {
                data: 1
            }, {
                data: 2
            },
            {
                data: 3,
                className: 'text-center'
            },
            {
                data: 4,
                className: 'text-end'
            },
            {
                data: 5,
                className: 'text-end'
            },
            {
                data: 6,
                className: 'text-end'
            },
            {
                data: 7,
                className: 'text-center',
                orderable: false
            },
        ];

        // ── Kolom history per days ────────────────────────────────────────────
        var colDefHistory = [{
                targets: 0,
                orderable: false
            }, // No
            {
                targets: 1
            }, // Material Code
            {
                targets: 2
            }, // Material Name
            {
                targets: 3,
                className: 'text-center'
            }, // Gudang
            {
                targets: 4,
                className: 'text-center'
            }, // Snapshot Date
            {
                targets: 5,
                className: 'text-center'
            }, // Coil Count
            {
                targets: 6,
                className: 'text-right'
            }, // Qty Stock
            {
                targets: 7,
                className: 'text-right'
            }, // Harga Beli
            {
                targets: 8,
                className: 'text-right'
            }, // Total Value
            {
                targets: 9,
                orderable: false,
                className: 'text-center'
            } // Action
        ];

        // ── Helper dd/mm/yyyy → yyyy-mm-dd ────────────────────────────────────
        function getYmd(dmy) {
            if (!dmy) return '';
            var p = dmy.split('/');
            return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : '';
        }

        // ── DataTables live ───────────────────────────────────────────────────
        function dtOptions(endpoint, grandTotalId, kdGudang) {
            return {
                processing: true,
                serverSide: true,
                destroy: true,
                autoWidth: false,
                responsive: true,
                pagingType: 'simple_numbers',
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                columnDefs: [{
                    targets: 'no-sort',
                    orderable: false
                }],
                ajax: {
                    url: siteurl + 'warehouse/' + endpoint,
                    type: 'POST',
                    data: function(d) {
                        d.filter_material = $('#filter_material').val();
                    },
                    cache: false,
                },
                columns: colDefLive,
                order: [
                    [2, 'asc']
                ],
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Loading data...',
                    zeroRecords: 'No stock data available.',
                    emptyTable: 'No data available.',
                },
                drawCallback: function() {
                    $.post(siteurl + 'warehouse/get_grand_total_stock_value', {
                        kd_gudang: kdGudang,
                        filter_material: $('#filter_material').val(),
                    }, function(res) {
                        $('#' + grandTotalId).html('<strong>' + res.total + '</strong>');
                    }, 'json');
                }
            };
        }

        var dtProduksi = $('#table-sv-produksi').DataTable(
            dtOptions('data_side_stock_value_produksi', 'grand-total-produksi', 'PRO')
        );

        var dtSlitting = null;
        document.getElementById('tab-sv-slitting-tab')
            .addEventListener('shown.bs.tab', function() {
                if (!dtSlitting) {
                    dtSlitting = $('#table-sv-slitting').DataTable(
                        dtOptions('data_side_stock_value_slitting', 'grand-total-slitting', 'SLI')
                    );
                }
            });

        $('#btn-filter').on('click', function() {
            dtProduksi.ajax.reload();
            if (dtSlitting) dtSlitting.ajax.reload();
        });

        $('#btn-reset').on('click', function() {
            $('#filter_material').val('');
            dtProduksi.ajax.reload();
            if (dtSlitting) dtSlitting.ajax.reload();
        });

        $('#btn-excel').on('click', function() {
            var kd = document.getElementById('tab-sv-produksi').classList.contains('active') ? 'PRO' : 'SLI';
            window.location.href = siteurl + 'warehouse/export_excel_stock_value?kd_gudang=' + kd;
        });

        // ── Flatpickr untuk tab History Per Days ─────────────────────────────
        var fpSnap = flatpickr('#sv_date_snap', {
            locale: 'id',
            dateFormat: 'd/m/Y',
        });

        // ── DataTable History Per Days (lazy) ─────────────────────────────────
        var dtHistory = null;

        function buildHistoryDt() {
            if (dtHistory) dtHistory.destroy();

            var kdGudang = $('#sv_hist_gudang').val();
            var dateSnap = getYmd($('#sv_date_snap').val());

            var endpoint = 'data_side_stock_value_perday';
            if (kdGudang === 'PRO') endpoint = 'data_side_stock_value_perday_produksi';
            if (kdGudang === 'SLI') endpoint = 'data_side_stock_value_perday_slitting';

            dtHistory = $('#table-sv-history').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                autoWidth: false,
                responsive: true,
                pagingType: 'simple_numbers',
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                columnDefs: [{
                    targets: 'no-sort',
                    orderable: false
                }],
                ajax: {
                    url: siteurl + 'warehouse/' + endpoint,
                    type: 'POST',
                    data: function(d) {
                        d.filter_material = $('#sv_hist_material').val();
                        d.date_snap = dateSnap; // kirim 1 tanggal
                        d.kd_gudang = kdGudang;
                    },
                    cache: false,
                },
                columns: colDefHistory,
                order: [
                    [3, 'desc']
                ],
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Loading data...',
                    zeroRecords: 'No data available for this date.',
                    emptyTable: 'No data available.',
                },
                drawCallback: function() {
                    $.post(siteurl + 'warehouse/get_grand_total_stock_value_perday', {
                        kd_gudang: kdGudang,
                        filter_material: $('#sv_hist_material').val(),
                        date_snap: dateSnap,
                    }, function(res) {
                        $('#grand-total-history').text(res.total);
                    }, 'json');
                }
            });
        }

        $('#btn-filter-svh').on('click', function() {
            buildHistoryDt();
            $('#btn-excel-history').removeClass('d-none');
        });

        $('#btn-reset-svh').on('click', function() {
            fpSnap.clear();
            $('#sv_hist_material').val('');
            $('#sv_hist_gudang').val('');
            $('#grand-total-history').text('—');
            $('#btn-excel-history').addClass('d-none');
            if (dtHistory) {
                dtHistory.destroy();
                dtHistory = null;
                $('#table-sv-history tbody').html(
                    '<tr><td colspan="8" class="text-center text-muted py-4">' +
                    '<i class="fa fa-info-circle"></i> ' +
                    'Select a date then click <strong>Show</strong>' +
                    '</td></tr>'
                );
            }
        });

        $('#btn-excel-history').on('click', function() {
            var params = new URLSearchParams({
                kd_gudang: $('#sv_hist_gudang').val(),
                filter_material: $('#sv_hist_material').val(),
                date_snap: getYmd($('#sv_date_snap').val()),
            });
            window.location.href = siteurl + 'warehouse/export_excel_stock_value_history?' + params.toString();
        });

    });

    // ── Modal History ──────────────────────────────────────────────────────────
    // function showHistory(id_material, nm_material, id_gudang) {
    //     $('#modal-title-material').text(nm_material);
    //     $('#tbody-history').html('<tr><td colspan="13" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
    //     new bootstrap.Modal(document.getElementById('modal-history')).show();
    //     $.post(siteurl + 'warehouse/get_history_material', {
    //         id_material,
    //         id_gudang
    //     }, function(data) {
    //         if (!data.length) {
    //             $('#tbody-history').html('<tr><td colspan="13" class="text-center">No history available</td></tr>');
    //             return;
    //         }
    //         var html = '';
    //         $.each(data, function(i, r) {
    //             html += '<tr>' +
    //                 '<td class="text-center">' + (i + 1) + '</td>' +
    //                 '<td>' + (r.update_date || '-') + '</td><td>' + (r.no_ipp || '-') + '</td>' +
    //                 '<td>' + (r.no_coil || '-') + '</td><td>' + (r.kd_gudang || '-') + '</td>' +
    //                 '<td>' + (r.ket || '-') + '</td>' +
    //                 '<td class="text-end">' + fmtNum(r.jumlah_mat) + '</td>' +
    //                 '<td class="text-end">' + fmtNum(r.harga_beli) + '</td>' +
    //                 '<td class="text-end">' + fmtNum(r.total_harga) + '</td>' +
    //                 '<td class="text-end">' + fmtNum(r.saldo_awal) + '</td>' +
    //                 '<td class="text-end">' + fmtNum(r.saldo_akhir) + '</td>' +
    //                 '<td class="text-end">' + fmtNum(r.harga_lama) + '</td>' +
    //                 '<td class="text-end">' + fmtNum(r.harga_baru) + '</td>' +
    //                 '</tr>';
    //         });
    //         $('#tbody-history').html(html);
    //     }, 'json');
    // }

    function showHistory(id_material, nm_material, id_gudang) {
        $('#modal-title-material').text(nm_material);
        $('#tbody-history').html('<tr><td colspan="14" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
        new bootstrap.Modal(document.getElementById('modal-history')).show();

        $.post(siteurl + 'warehouse/get_history_summary', {
            id_material,
            id_gudang
        }, function(data) {
            if (!data.length) {
                $('#tbody-history').html('<tr><td colspan="14" class="text-center">No history available</td></tr>');
                return;
            }
            var html = '';
            $.each(data, function(i, r) {
                // Determine IN/OUT status
                var status = 'IN';
                var statusBadge = '<span class="badge bg-success">IN</span>';
                if (parseFloat(r.qty_akhir) < parseFloat(r.qty_awal)) {
                    status = 'OUT';
                    statusBadge = '<span class="badge bg-danger">OUT</span>';
                }

                html += '<tr>' +
                    '<td class="text-center">' + (i + 1) + '</td>' +
                    '<td>' + (r.tanggal || '-') + '</td>' +
                    '<td>' + (r.no_ipp || '-') + '</td>' +
                    '<td class="text-center">' + statusBadge + '</td>' +
                    // Jumlah coil — klik → drill-down
                    '<td class="text-center">' +
                    '<a href="javascript:void(0)" class="badge bg-primary" ' +
                    'data-no-ipp="' + r.no_ipp + '" ' +
                    'data-id-material="' + r.id_material + '" ' +
                    'data-id-gudang="' + r.id_gudang + '" ' +
                    'data-nm-material="' + r.nm_material + '" ' +
                    'onclick="showSummaryDetailCoil(this)">' +
                    r.jumlah_coil + ' coil</a>' +
                    '</td>' +
                    '<td class="text-end">' + fmtDec(r.qty_awal) + '</td>' +
                    '<td class="text-end">' + fmtDec(r.qty_transaksi) + '</td>' +
                    '<td class="text-end">' + fmtDec(r.qty_akhir) + '</td>' +
                    '<td class="text-end">' + fmtNum(r.total_harga) + '</td>' +
                    '<td class="text-end">' + fmtNum(r.saldo_awal) + '</td>' +
                    '<td class="text-end">' + fmtNum(r.saldo_akhir) + '</td>' +
                    '<td class="text-end">' + fmtNum(r.harga_lama) + '</td>' +
                    '<td class="text-end">' + fmtNum(r.costbook) + '</td>' +
                    '</tr>';
            });
            $('#tbody-history').html(html);
        }, 'json');
    }

    function showSummaryDetailCoil(el) {
        var no_ipp = el.dataset.noIpp;
        var id_material = el.dataset.idMaterial;
        var id_gudang = el.dataset.idGudang;
        var nm_material = el.dataset.nmMaterial;

        $('#coil-trx-title').text(nm_material + ' — ' + no_ipp);
        $('#tbody-detail-coil-trx').html(
            '<tr><td colspan="7" class="text-center">' +
            '<i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>'
        );

        // Buka modal baru DI ATAS modal history — tidak perlu tutup modal history
        new bootstrap.Modal(document.getElementById('modal-detail-coil-trx')).show();

        $.post(siteurl + 'warehouse/get_summary_detail_coil', {
            no_ipp: no_ipp,
            id_material: id_material,
            id_gudang: id_gudang
        }, function(data) {
            if (!data.length) {
                $('#tbody-detail-coil-trx').html(
                    '<tr><td colspan="7" class="text-center">No data available.coil</td></tr>'
                );
                return;
            }
            var html = '';
            $.each(data, function(i, r) {
                html += '<tr>' +
                    '<td class="text-center">' + (i + 1) + '</td>' +
                    '<td>' + (r.no_coil || '-') + '</td>' +
                    '<td>' + (r.kode_internal || '-') + '</td>' +
                    '<td class="text-end">' + fmtDec(r.net_weight) + '</td>' +
                    '<td class="text-end">' + fmtDec(r.gross_weight) + '</td>' +
                    '<td class="text-end">' + fmtDec(r.length) + '</td>' +
                    '</tr>';
            });
            $('#tbody-detail-coil-trx').html(html);
        }, 'json');
    }

    function showDetailCoil(id_material, nm_material, id_gudang) {
        $('#coil-title-material').text(nm_material);
        $('#tbody-detail-coil').html('<tr><td colspan="6" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
        new bootstrap.Modal(document.getElementById('modal-detail-coil')).show();
        $.post(siteurl + 'warehouse/get_detail_coil', {
            id_material,
            id_gudang
        }, function(data) {
            if (!data.length) {
                $('#tbody-detail-coil').html('<tr><td colspan="6" class="text-center">No data available.coil</td></tr>');
                return;
            }
            var html = '';
            $.each(data, function(i, r) {
                html += '<tr>' +
                    '<td class="text-center">' + (i + 1) + '</td>' +
                    '<td>' + (r.no_coil || '-') + '</td><td>' + (r.kode_internal || '-') + '</td>' +
                    '<td class="text-end">' + fmtDec(r.net_weight) + '</td>' +
                    '<td class="text-end">' + fmtDec(r.gross_weight) + '</td>' +
                    '<td class="text-end">' + fmtDec(r.length) + '</td>' +
                    '</tr>';
            });
            $('#tbody-detail-coil').html(html);
        }, 'json');
    }

    function fmtNum(val, decimal = 2) {
        if (val == null || val === '') return '-';
        return Number(val).toLocaleString('id-ID', {
            minimumFractionDigits: decimal,
            maximumFractionDigits: decimal
        });
    }

    function fmtDec(val) {
        return fmtNum(val, 3);
    }

    // Blur modal history saat modal transaksi muncul
    document.getElementById('modal-detail-coil-trx').addEventListener('show.bs.modal', function() {
        document.getElementById('modal-history').classList.add('blur-background');
    });

    // Hapus blur saat modal transaksi ditutup
    document.getElementById('modal-detail-coil-trx').addEventListener('hidden.bs.modal', function() {
        document.getElementById('modal-history').classList.remove('blur-background');
    });

    document.getElementById('modal-history').addEventListener('shown.bs.modal', function() {
        // Paksa hilangkan overflow hidden pada tabel
        const table = this.querySelector('table');
        if (table) {
            table.style.setProperty('overflow', 'visible', 'important');
            table.style.setProperty('overflow-x', 'visible', 'important');
            table.style.setProperty('overflow-y', 'visible', 'important');
            table.style.setProperty('border-collapse', 'separate', 'important');
            table.style.setProperty('border-spacing', '0', 'important');
        }
    });

    document.getElementById('tab-sv-history-tab')
        .addEventListener('shown.bs.tab', function() {
            document.getElementById('bar-filter-live').classList.add('d-none');
        });

    document.getElementById('tab-sv-produksi-tab')
        .addEventListener('shown.bs.tab', function() {
            document.getElementById('bar-filter-live').classList.remove('d-none');
        });

    document.getElementById('tab-sv-slitting-tab')
        .addEventListener('shown.bs.tab', function() {
            document.getElementById('bar-filter-live').classList.remove('d-none');
        });
</script>