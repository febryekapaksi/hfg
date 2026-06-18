<?php
$ENABLE_VIEW   = has_permission('Gl_interface.View');
$ENABLE_MANAGE = has_permission('Gl_interface.Manage');
?>

<style>
    .badge-status {
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 4px;
    }

    .badge-pending {
        background: #ffc107;
        color: #333;
    }

    .badge-posted {
        background: #28a745;
        color: #fff;
    }

    .badge-error {
        background: #dc3545;
        color: #fff;
    }

    .filter-row {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 15px;
    }

    .filter-row .form-control,
    .filter-row select {
        height: 34px;
        font-size: 13px;
    }
</style>

<div class="card">
    <div class="card-body">
        <!-- FILTER ROW -->
        <div class="filter-row">
            <input type="text" id="filterSearch" class="form-control" style="width:300px;" placeholder="Search number / description...">
            <select id="filterJenis" class="form-control" style="width:200px;">
                <option value="">-- All Transaction Types --</option>
            </select>
            <select id="filterStatus" class="form-control" style="width:180px;">
                <option value="">-- All Status --</option>
                <option value="pending">Pending</option>
                <option value="posted">Posted</option>
                <option value="error">Error</option>
            </select>
            <button class="btn btn-sm btn-default" id="btnResetFilter"><i class="fa fa-refresh"></i> Reset</button>
        </div>

        <table id="tblGlInterface" class="table table-bordered table-striped" width="100%">
            <thead>
                <tr class="bg-blue">
                    <th class="text-center">Number</th>
                    <th class="text-center">Date</th>
                    <th class="text-center">Type</th>
                    <th class="text-center">Transaction Type</th>
                    <th class="text-center">Description</th>
                    <th class="text-center">Total Debet</th>
                    <th class="text-center">Total Kredit</th>
                    <th class="text-center">Status</th>
                    <th class="text-center no-sort" width="60">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        function fmt(n) {
            n = parseFloat(n) || 0;
            return n.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        // Load jenis_transaksi options
        $.get('<?= base_url("gl_interface/get_jenis_list") ?>', function(res) {
            if (res && res.length) {
                $.each(res, function(i, v) {
                    $('#filterJenis').append('<option value="' + v + '">' + v.charAt(0).toUpperCase() + v.slice(1) + '</option>');
                });
            }
        }, 'json');

        // DataTable
        var table = $('#tblGlInterface').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ordering: false,
            autoWidth: false,
            responsive: true,
            iDisplayLength: 10,
            aLengthMenu: [
                [10, 20, 50, 100],
                [10, 20, 50, 100]
            ],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }],
            ajax: {
                url: '<?= base_url("gl_interface/data") ?>',
                type: 'POST',
                data: function(d) {
                    d.jenis_transaksi = $('#filterJenis').val();
                    d.filter_status = $('#filterStatus').val();
                    d.search = {
                        value: $('#filterSearch').val()
                    };
                }
            },
            columns: [{
                    data: 'nomor',
                    className: 'text-center',
                    render: function(d) {
                        return d ? d : '<span class="text-muted">-</span>';
                    }
                },
                {
                    data: 'tgl',
                    className: 'text-center'
                },
                {
                    data: 'jenis',
                    className: 'text-center'
                },
                {
                    data: 'jenis_transaksi',
                    className: 'text-center',
                    render: function(d) {
                        return d ? d.charAt(0).toUpperCase() + d.slice(1) : '-';
                    }
                },
                {
                    data: 'keterangan',
                    render: function(d) {
                        if (!d) return '-';
                        return d;
                    }
                },
                {
                    data: 'total_debet',
                    className: 'text-right',
                    render: function(d) {
                        return fmt(d);
                    }
                },
                {
                    data: 'total_kredit',
                    className: 'text-right',
                    render: function(d) {
                        return fmt(d);
                    }
                },
                {
                    data: 'status',
                    className: 'text-center',
                    render: function(d) {
                        var cls = d === 'posted' ? 'badge-posted' : (d === 'error' ? 'badge-error' : 'badge-pending');
                        return '<span class="badge badge-status ' + cls + '">' + d.toUpperCase() + '</span>';
                    }
                },
                {
                    data: 'id',
                    className: 'text-center',
                    render: function(data) {
                        return '<a href="<?= base_url("gl_interface/view/") ?>' + data + '" class="btn btn-sm btn-info" title="View Detail"><i class="fa fa-eye"></i></a>';
                    }
                }
            ]
        });

        // Filters
        var debounce;
        $('#filterSearch').on('keyup', function() {
            clearTimeout(debounce);
            debounce = setTimeout(function() {
                table.draw();
            }, 400);
        });
        $('#filterJenis, #filterStatus').on('change', function() {
            table.draw();
        });
        $('#btnResetFilter').on('click', function() {
            $('#filterSearch').val('');
            $('#filterJenis').val('');
            $('#filterStatus').val('');
            table.draw();
        });
    });
</script>