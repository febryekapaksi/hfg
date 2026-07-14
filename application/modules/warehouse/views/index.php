<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="card">
    <div class="card-body">

        <!-- ── Tab Gudang ─────────────────────────────────────────────── -->
        <ul class="nav nav-tabs mb-3" id="tabStockCoil" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-produksi-coil-tab"
                    data-bs-toggle="tab" href="#tab-produksi-coil" role="tab">
                    <i class="fa fa-warehouse"></i> Production Warehouse
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-slitting-coil-tab"
                    data-bs-toggle="tab" href="#tab-slitting-coil" role="tab">
                    <i class="fa fa-store"></i> Slitting Warehouse
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-history-coil-tab"
                    data-bs-toggle="tab" href="#tab-history-coil" role="tab">
                    <i class="fa fa-history"></i> History Per Days
                </a>
            </li>
        </ul>

        <div class="tab-content" id="tabStockCoilContent">

            <!-- TAB produksi -->
            <div class="tab-pane fade show active" id="tab-produksi-coil" role="tabpanel">
                <div class="mb-2 text-end">
                    <button class="btn btn-success btn-sm" id="btn-excel-produksi">
                        <i class="fa fa-file-excel-o"></i> Download Excel
                    </button>
                </div>
                <div class="table-responsive">
                    <table id="table-stock-produksi"
                        class="table table-bordered table-striped table-hover">
                        <thead class="bg-blue">
                            <tr>
                                <th width="4%">No</th>
                                <th>Material Name (Lv.4)</th>
                                <th class="text-center">No. Coil</th>
                                <th class="text-center">Internal Code</th>
                                <th class="text-right">Nett Weight (Kg)</th>
                                <th class="text-right">Gross Weight (Kg)</th>
                                <th class="text-right">Length (M)</th>
                                <th class="text-center" width="8%">QR Code</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB slitting -->
            <div class="tab-pane fade" id="tab-slitting-coil" role="tabpanel">
                <div class="mb-2 text-end">
                    <button class="btn btn-success btn-sm" id="btn-excel-slitting">
                        <i class="fa fa-file-excel-o"></i> Download Excel
                    </button>
                </div>
                <div class="table-responsive">
                    <table id="table-stock-slitting"
                        class="table table-bordered table-striped table-hover">
                        <thead class="bg-green">
                            <tr>
                                <th width="4%">No</th>
                                <th>Material Name (Lv.4)</th>
                                <th class="text-center">No. Coil</th>
                                <th class="text-center">Internal Code</th>
                                <th class="text-right">Nett Weight (Kg)</th>
                                <th class="text-right">Gross Weight (Kg)</th>
                                <th class="text-right">Length (M)</th>
                                <th class="text-center" width="8%">QR Code</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB HISTORY PER DAYS -->
            <div class="tab-pane fade" id="tab-history-coil" role="tabpanel">

                <!-- Filter -->
                <div class="row mb-3 g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label mb-1 fw-semibold" style="font-size:12px;">
                            <i class="fa fa-calendar"></i> As of Date
                        </label>
                        <input type="text" id="hc_date_snap" class="form-control form-control-sm"
                            placeholder="dd/mm/yyyy" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1 fw-semibold" style="font-size:12px;">
                            <i class="fa fa-filter"></i> Gudang
                        </label>
                        <select id="hc_gudang" class="form-select form-select-sm">
                            <option value="">-- All Warehouses --</option>
                            <option value="PRO">Production Warehouse</option>
                            <option value="SLI">Slitting Warehouse</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <button class="btn btn-primary btn-sm" id="btn-filter-hc">
                            <i class="fa fa-search"></i> Show
                        </button>
                        <button class="btn btn-secondary btn-sm" id="btn-reset-hc">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                        <button class="btn btn-success btn-sm d-none" id="btn-excel-hc">
                            <i class="fa fa-file-excel-o"></i> Download Excel
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="table-history-coil"
                        class="table table-bordered table-striped table-hover">
                        <thead class="table-warning">
                            <tr>
                                <th width="4%">No</th>
                                <th>Material Name (Lv.4)</th>
                                <th>Gudang</th>
                                <th class="text-center">No. Coil</th>
                                <th class="text-center">Internal Code</th>
                                <th class="text-right">Nett Weight (Kg)</th>
                                <th class="text-right">Gross Weight (Kg)</th>
                                <th class="text-right">Length (M)</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
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

<!-- Modal QR Code -->
<div class="modal fade" id="modal-qr" tabindex="-1" aria-labelledby="modalQrLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalQrLabel">QR Code Coil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qr-container" class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                    <i class="fa fa-spinner fa-spin fa-3x text-muted"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script>
    $(document).ready(function() {

        // ── Kolom live (7 kolom) ──────────────────────────────────────────────
        var colDefLive = [{
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
            {
                data: 7,
                className: 'text-center',
                orderable: false
            },
        ];

        // ── Kolom history per days
        var colDefHistory = [{
                data: 0,
                width: '4%'
            },
            {
                data: 1
            }, // Nama Material
            {
                data: 2,
                className: 'text-center'
            }, // Gudang
            {
                data: 3,
                className: 'text-center'
            }, // No. Coil
            {
                data: 4,
                className: 'text-center'
            }, // Internal Code
            {
                data: 5,
                className: 'text-end'
            }, // Nett Weight
            {
                data: 6,
                className: 'text-end'
            }, // Gross Weight
            {
                data: 7,
                className: 'text-end'
            }, // Length
            {
                data: 8,
                className: 'text-center'
            }, // Status
        ];

        // ── Tab produksi (live) ──────────────────────────────────────────────────
        var dtProduksi = $('#table-stock-produksi').DataTable({
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
                url: siteurl + 'warehouse/data_side_stock_produksi',
                type: 'POST',
                cache: false
            },
            columns: colDefLive,
            order: [
                [1, 'asc']
            ],
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Loading data...',
                zeroRecords: 'No coil data available.',
                emptyTable: 'No data available.',
            }
        });

        // ── Tab slitting (live, lazy) ────────────────────────────────────────
        var dtSlitting = null;
        document.getElementById('tab-slitting-coil-tab')
            .addEventListener('shown.bs.tab', function() {
                if (!dtSlitting) {
                    dtSlitting = $('#table-stock-slitting').DataTable({
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
                            url: siteurl + 'warehouse/data_side_stock_slitting',
                            type: 'POST',
                            cache: false
                        },
                        columns: colDefLive,
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

        // ── Flatpickr ─────────────────────────────────────────────────────────
        var fpSnap = flatpickr('#hc_date_snap', {
            locale: 'id',
            dateFormat: 'd/m/Y',
        });

        // ── Helper dd/mm/yyyy → yyyy-mm-dd ────────────────────────────────────
        function getYmd(dmy) {
            if (!dmy) return '';
            var p = dmy.split('/');
            return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : '';
        }

        // ── Tab History Per Days ──────────────────────────────────────────────
        var dtHistory = null;

        function buildHistoryDt() {
            if (dtHistory) dtHistory.destroy();

            dtHistory = $('#table-history-coil').DataTable({
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
                    url: siteurl + 'warehouse/data_side_stock_perday',
                    type: 'POST',
                    data: function(d) {
                        d.date_snap = getYmd($('#hc_date_snap').val()); // ← 1 tanggal
                        d.kd_gudang = $('#hc_gudang').val();
                    },
                    cache: false,
                },
                columns: colDefHistory,
                order: [
                    [1, 'desc']
                ],
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Loading data...',
                    zeroRecords: 'No data available for this date.',
                    emptyTable: 'No data available.',
                }
            });
        }

        $('#btn-filter-hc').on('click', function() {
            buildHistoryDt();
            $('#btn-excel-hc').removeClass('d-none'); // ← tampilkan tombol excel
        });

        $('#btn-reset-hc').on('click', function() {
            fpSnap.clear();
            $('#hc_gudang').val('');
            $('#btn-excel-hc').addClass('d-none'); // ← sembunyikan tombol excel
            if (dtHistory) {
                dtHistory.destroy();
                dtHistory = null;
                $('#table-history-coil tbody').html(
                    '<tr><td colspan="9" class="text-center text-muted py-4">' +
                    '<i class="fa fa-info-circle"></i> ' +
                    'Select a date then click <strong>Show</strong>' +
                    '</td></tr>'
                );
            }
        });

        $('#btn-excel-hc').on('click', function() {
            var params = new URLSearchParams({
                kd_gudang: $('#hc_gudang').val(),
                date_snap: getYmd($('#hc_date_snap').val()),
            });
            window.location.href = siteurl + 'warehouse/export_excel_stock_coil_perday?' + params.toString();
        });

        $('#btn-excel-produksi').on('click', function() {
            window.location.href = siteurl + 'warehouse/export_excel_stock_coil?kd_gudang=PUS';
        });

        $('#btn-excel-slitting').on('click', function() {
            window.location.href = siteurl + 'warehouse/export_excel_stock_coil?kd_gudang=PEN';
        });

        // ── Tampilkan Modal QR Code ───────────────────────────────────────────
        var qrModalObj = null;

        $(document).on('click', '.btn-show-qr', function() {
            var qrText = $(this).data('qr');
            // Bersihkan container sebelumnya
            $('#qr-container').empty();

            // Generate QR Code menggunakan qrcode.js
            new QRCode(document.getElementById("qr-container"), {
                text: qrText,
                width: 200,
                height: 200,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });

            // Tampilkan Modal
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                if (!qrModalObj) {
                    qrModalObj = new bootstrap.Modal(document.getElementById('modal-qr'));
                }
                qrModalObj.show();
            } else {
                $('#modal-qr').modal('show');
            }
        });

    });
</script>