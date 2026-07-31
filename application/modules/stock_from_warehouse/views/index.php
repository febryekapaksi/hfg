<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="card">
    <div class="card-body">

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-3" id="tabStockFromWarehouse" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-transit-tab"
                    data-bs-toggle="tab" href="#tab-transit" role="tab">
                    <i class="fa fa-truck-loading"></i> WRH. Production 2
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-wip-tab"
                    data-bs-toggle="tab" href="#tab-wip" role="tab">
                    <i class="fa fa-cogs"></i> WIP (Coil Remains)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-history-tab"
                    data-bs-toggle="tab" href="#tab-history" role="tab">
                    <i class="fa fa-history"></i> History Per Days
                </a>
            </li>
        </ul>

        <div class="tab-content" id="tabStockFromWarehouseContent">

            <!-- TAB: Production Transit -->
            <div class="tab-pane fade show active" id="tab-transit" role="tabpanel">
                <div class="table-responsive">
                    <table id="table-stock-transit"
                        class="table table-bordered table-striped table-hover">
                        <thead class="bg-blue">
                            <tr>
                                <th width="4%">No</th>
                                <th>Material Name</th>
                                <th class="text-center">No. Coil</th>
                                <th class="text-center">Internal Code</th>
                                <th class="text-right">Nett Weight (Kg)</th>
                                <th class="text-right">Gross Weight (Kg)</th>
                                <th class="text-right">Length (M)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB: WIP -->
            <div class="tab-pane fade" id="tab-wip" role="tabpanel">
                <div class="table-responsive">
                    <table id="table-stock-wip"
                        class="table table-bordered table-striped table-hover">
                        <thead class="bg-green">
                            <tr>
                                <th width="4%">No</th>
                                <th>Material Name</th>
                                <th class="text-center">No. Coil</th>
                                <th class="text-center">Internal Code</th>
                                <th class="text-right">Nett Weight (Kg)</th>
                                <th class="text-right">Gross Weight (Kg)</th>
                                <th class="text-right">Length (M)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB: History Per Days -->
            <div class="tab-pane fade" id="tab-history" role="tabpanel">

                <!-- Filter -->
                <div class="row mb-3 g-2 align-items-end">
                    <!-- Input Tanggal -->
                    <div class="col-md-3">
                        <label class="form-label mb-1 fw-semibold" style="font-size:12px;">
                            <i class="fa fa-calendar"></i> Tanggal
                        </label>
                        <input type="text" id="hist_date" class="form-control form-control-sm"
                            placeholder="dd/mm/yyyy" readonly>
                    </div>

                    <!-- Dropdown Sumber -->
                    <div class="col-md-3">
                        <label class="form-label mb-1 fw-semibold" style="font-size:12px;">
                            <i class="fa fa-filter"></i> Sumber
                        </label>
                        <select id="hist_source" class="form-select form-select-sm">
                            <option value="">-- Semua Sumber --</option>
                            <option value="PRO">Production (PRO)</option>
                            <option value="WIP">WIP</option>
                        </select>
                    </div>

                    <!-- Group Tombol Sejajar -->
                    <div class="col-md-6">
                        <div class="d-flex gap-1">
                            <button class="btn btn-primary btn-sm" id="btn-filter-hist">
                                <i class="fa fa-search"></i> Show
                            </button>
                            <button class="btn btn-secondary btn-sm" id="btn-reset-hist">
                                <i class="fa fa-refresh"></i> Reset
                            </button>
                            <button class="btn btn-success btn-sm d-none" id="btn-excel-hist">
                                <i class="fa fa-file-excel-o"></i> Download Excel
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="table-history"
                        class="table table-bordered table-striped table-hover">
                        <thead class="table-warning">
                            <tr>
                                <th width="4%">No</th>
                                <th>Material Name</th>
                                <th class="text-center">No. Coil</th>
                                <th class="text-center">Internal Code</th>
                                <th class="text-center">Gudang Asal</th>
                                <th class="text-right">Nett Weight (Kg)</th>
                                <th class="text-right">Gross Weight (Kg)</th>
                                <th class="text-right">Length (M)</th>
                                <th class="text-center">Kode Trans</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fa fa-info-circle"></i>
                                    Pilih tanggal lalu klik <strong>Show</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {

        var BASE_URL = siteurl + 'stock_from_warehouse';

        // ── Column definitions — Transit & WIP (7 columns) ──
        var colDef7 = [{
                data: 0,
                width: '4%'
            },
            {
                data: 1
            },
            {
                data: 2,
                className: 'text-center'
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
        ];

        // ── Column definitions — History (9 columns) ──
        var colDefHistory = [{
                data: 0,
                width: '4%'
            },
            {
                data: 1
            },
            {
                data: 2,
                className: 'text-center'
            },
            {
                data: 3,
                className: 'text-center'
            },
            {
                data: 4,
                className: 'text-center'
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
                className: 'text-end'
            },
            {
                data: 8,
                className: 'text-center'
            },
        ];

        // ── Tab Transit (active by default) ──
        var dtTransit = $('#table-stock-transit').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            autoWidth: false,
            sPaginationType: 'simple_numbers',
            iDisplayLength: 25,
            aLengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            ajax: {
                url: BASE_URL + '/data_side_transit',
                type: 'POST',
                cache: false
            },
            columns: colDef7,
            order: [
                [1, 'asc']
            ],
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Loading data...',
                zeroRecords: 'No coil data available.',
                emptyTable: 'No data available.',
            }
        });

        // ── Tab WIP (lazy load) ──
        var dtWip = null;
        document.getElementById('tab-wip-tab')
            .addEventListener('shown.bs.tab', function() {
                if (!dtWip) {
                    dtWip = $('#table-stock-wip').DataTable({
                        processing: true,
                        serverSide: true,
                        destroy: true,
                        autoWidth: false,
                        sPaginationType: 'simple_numbers',
                        iDisplayLength: 25,
                        aLengthMenu: [
                            [10, 25, 50, 100],
                            [10, 25, 50, 100]
                        ],
                        ajax: {
                            url: BASE_URL + '/data_side_wip',
                            type: 'POST',
                            cache: false
                        },
                        columns: colDef7,
                        order: [
                            [1, 'asc']
                        ],
                        language: {
                            processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Loading data...',
                            zeroRecords: 'No coil data available.',
                            emptyTable: 'No data available.',
                        }
                    });
                }
            });

        // ── Flatpickr ──
        var fpHist = flatpickr('#hist_date', {
            locale: 'id',
            dateFormat: 'd/m/Y',
        });

        // ── Helper dd/mm/yyyy → yyyy-mm-dd ──
        function getYmd(dmy) {
            if (!dmy) return '';
            var p = dmy.split('/');
            return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : '';
        }

        // ── Tab History Per Days ──
        var dtHistory = null;

        function buildHistoryDt() {
            if (dtHistory) dtHistory.destroy();

            dtHistory = $('#table-history').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                autoWidth: false,
                sPaginationType: 'simple_numbers',
                iDisplayLength: 25,
                aLengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                ajax: {
                    url: BASE_URL + '/data_side_history',
                    type: 'POST',
                    data: function(d) {
                        d.date_filter = getYmd($('#hist_date').val());
                        d.kd_gudang = $('#hist_source').val();
                    },
                    cache: false,
                },
                columns: colDefHistory,
                order: [
                    [1, 'asc']
                ],
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Loading data...',
                    zeroRecords: 'Tidak ada data untuk tanggal ini.',
                    emptyTable: 'Tidak ada data.',
                }
            });
        }

        $('#btn-filter-hist').on('click', function() {
            if (!$('#hist_date').val()) {
                Swal.fire('Perhatian', 'Pilih tanggal terlebih dahulu.', 'warning');
                return;
            }
            buildHistoryDt();
            $('#btn-excel-hist').removeClass('d-none');
        });

        $('#btn-reset-hist').on('click', function() {
            fpHist.clear();
            $('#hist_source').val('');
            $('#btn-excel-hist').addClass('d-none');
            if (dtHistory) {
                dtHistory.destroy();
                dtHistory = null;
                $('#table-history tbody').html(
                    '<tr><td colspan="9" class="text-center text-muted py-4">' +
                    '<i class="fa fa-info-circle"></i> ' +
                    'Pilih tanggal lalu klik <strong>Show</strong>' +
                    '</td></tr>'
                );
            }
        });

        $('#btn-excel-hist').on('click', function() {
            var params = new URLSearchParams({
                date_filter: getYmd($('#hist_date').val()),
                kd_gudang: $('#hist_source').val(),
            });
            window.location.href = BASE_URL + '/export_excel_history?' + params.toString();
        });

    });
</script>