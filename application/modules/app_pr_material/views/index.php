<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="card shadow-sm border-0">
    <!-- <div class="card-header bg-white">
        <div class="row g-2 align-items-end">
            <div class="col-md-6" hidden>
                <label class="form-label mb-1"><b>Product Type</b></label>
                <select name="product" id="product" class="form-control chosen-select">
                    <option value="0">All Product Type</option>
                    <?php
                    foreach (get_list_inventory_lv1('product') as $val => $valx) {
                        echo "<option value='" . $valx['code_lv1'] . "'>" . strtoupper($valx['nama']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-6" hidden>
                <label class="form-label mb-1"><b>Costcenter</b></label>
                <select name="costcenter" id="costcenter" class="form-control chosen-select">
                    <option value="0">All Costcenter</option>
                    <?php
                    foreach (get_costcenter() as $val => $valx) {
                        echo "<option value='" . $valx['id_costcenter'] . "'>" . strtoupper($valx['nama_costcenter']) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
    </div> -->

    <div class="card-body">
        <div class="table-responsive">
            <table id="example1" class="table table-striped table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px;" class="text-center">#</th>
                        <th>Request Origin</th>
                        <th>Req No./SO No.</th>
                        <th class="text-center">PR No.</th>
                        <th>Purpose</th>
                        <th>Request By</th>
                        <th>Request Date</th>
                        <th>Status</th>
                        <th style="width:160px;" class="no-sort">Option</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ✅ Modal BS5 -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="head_title">Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="ModalView"></div>
        </div>
    </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
    let dtApproval;

    $(document).ready(function() {
        const product = $("#product").val();
        const costcenter = $("#costcenter").val();
        DataTables(costcenter, product);

        $(document).on('change', '#costcenter, #product', function() {
            const product = $("#product").val();
            const costcenter = $("#costcenter").val();
            DataTables(costcenter, product);
        });

        $(document).on('click', '.detail', function() {
            const so_number = $(this).data('so_number');
            console.log(so_number); // Pastikan ini menampilkan 'SPR26010001'

            $("#head_title").html("<b>Detail Request</b>");

            $.ajax({
                type: 'POST',
                url: base_url + active_controller + 'detail_planning',
                data: {
                    so_number: so_number
                },
                success: function(html) {
                    $("#ModalView").html(html);

                    // Tampilkan modal Bootstrap 5
                    const modalEl = document.getElementById('dialog-popup');
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                }
            });
        });
    });

    function DataTables(costcenter = null, product = null) {

        // destroy kalau sudah ada
        if ($.fn.DataTable.isDataTable('#example1')) {
            $('#example1').DataTable().destroy();
        }

        dtApproval = $('#example1').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            fixedHeader: true,
            autoWidth: false,
            destroy: true,
            searching: true,
            responsive: true,
            aaSorting: [
                [1, "desc"]
            ],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }],
            pagingType: "simple_numbers",
            pageLength: 10,
            lengthMenu: [
                [10, 20, 50, 100, 150],
                [10, 20, 50, 100, 150]
            ],
            ajax: {
                url: siteurl + active_controller + 'data_side_approval',
                type: "POST",
                data: function(d) {
                    d.costcenter = 0;
                    d.product = 0;
                },
                cache: false,
                error: function() {
                    // ✅ FIX: table yang benar
                    $('#example1 tbody').remove();
                    $('#example1').append(
                        "<tbody class='my-grid-error'><tr><td colspan='9' class='text-center'>No data found in the server</td></tr></tbody>"
                    );
                }
            }
        });
    }
</script>