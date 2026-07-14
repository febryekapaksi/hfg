<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="card">
    <div class="card-body">

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-3" id="tabStockFromWarehouse" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-transit-tab"
                    data-bs-toggle="tab" href="#tab-transit" role="tab">
                    <i class="fa fa-truck-loading"></i> Production Transit
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-wip-tab"
                    data-bs-toggle="tab" href="#tab-wip" role="tab">
                    <i class="fa fa-cogs"></i> WIP (Work In Progress)
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
                                <th class="text-center">Type</th>
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
                                <th class="text-right">Qty</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {

        // ── Column definitions — Transit (8 columns) ──
        var colDefTransit = [
            { data: 0, width: '4%' },
            { data: 1 },
            { data: 2, className: 'text-center' },
            { data: 3, className: 'text-center' },
            { data: 4, className: 'text-end' },
            { data: 5, className: 'text-end' },
            { data: 6, className: 'text-end' },
            { data: 7, className: 'text-center', orderable: false },
        ];

        // ── Column definitions — WIP (7 columns) ──
        var colDefWip = [
            { data: 0, width: '4%' },
            { data: 1 },
            { data: 2, className: 'text-center' },
            { data: 3, className: 'text-center' },
            { data: 4, className: 'text-end' },
            { data: 5, className: 'text-end' },
            { data: 6, className: 'text-end' },
        ];

        // ── Tab Transit (active by default) ──
        var dtTransit = $('#table-stock-transit').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            autoWidth: false,
            sPaginationType: 'simple_numbers',
            iDisplayLength: 25,
            aLengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            ajax: {
                url: siteurl + 'stock_from_warehouse/data_side_transit',
                type: 'POST',
                cache: false
            },
            columns: colDefTransit,
            order: [[1, 'asc']],
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
                        aLengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                        ajax: {
                            url: siteurl + 'stock_from_warehouse/data_side_wip',
                            type: 'POST',
                            cache: false
                        },
                        columns: colDefWip,
                        order: [[1, 'asc']],
                        language: {
                            processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Loading data...',
                            zeroRecords: 'No coil data available.',
                            emptyTable: 'No data available.',
                        }
                    });
                }
            });

    });
</script>
