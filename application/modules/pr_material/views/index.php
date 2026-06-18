<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <div>
            <a href="<?= site_url('pr_material/add') ?>" class="btn btn-success">
                <i class="fa fa-plus me-1"></i> Add Request
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle w-100" id="tabelPr">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px;">#</th>
                        <th>No.PR</th>
                        <th style="min-width: 200px;">Material</th>
                        <th style="min-width: 200px;">Alias Name</th>
                        <th>Qty</th>
                        <th>Required Date</th>
                        <th>Status</th>
                        <th>Request By</th>
                        <th>Request Date</th>
                        <th style="min-width: 100px;">Option</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        DataTables();
    });

    function DataTables(status = null) {
        const tableId = '#tabelPr';

        const dataTable = $(tableId).DataTable({
            // processing: true,
            serverSide: true,
            stateSave: false,
            autoWidth: false,
            destroy: true,
            responsive: true,
            order: [
                [7, "desc"]
            ],
            pageLength: 10,
            lengthMenu: [
                [10, 20, 50, 100, 150],
                [10, 20, 50, 100, 150]
            ],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }],
            ajax: {
                url: base_url + active_controller + 'data_side_material_planning',
                type: "POST",
                data: function(d) {
                    d.status = status;
                },
                cache: false,
                error: function() {
                    $(tableId + ' tbody').remove();
                    $(tableId).append(
                        "<tbody class='my-grid-error'><tr><td colspan='9' class='text-center'>No data found in the server</td></tr></tbody>"
                    );
                }
            }
        });
    }
</script>