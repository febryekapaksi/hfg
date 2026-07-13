<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
    <div class="box-header">
        <a href="<?= base_url('pembayaran_material/payment_list') ?>" class="btn btn-sm btn-danger"><i class="fa fa-arrow-left"></i> Back</a>
        <button type="button" class="btn btn-sm btn-warning clear_choosed_payment"><i class="fa fa-refresh"></i> Clear Checked Payment</button>
        <button type="button" class="btn btn-sm btn-success proses_payment"><i class="fa fa-check"></i> Proses</button>
    </div>
    <div class="box-body">
        <table class="table table-bordered" id="table_list_req_payment">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">No. Dokumen</th>
                    <th class="text-center">Tipe</th>
                    <th class="text-center">Tgl</th>
                    <th class="text-center">Keperluan</th>
                    <th class="text-center">Currency</th>
                    <th class="text-center">Total Invoice</th>
                    <th class="text-center">Requestor</th>
                    <th class="text-center">Option</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- page script -->
<script>
    DataTables();

    function check_choosed_payment() {
        return $.ajax({
            type: "POST",
            url: siteurl + active_controller + 'proses_payment',
            cache: false,
            dataType: 'json'
        });
    }

    $(document).on('click', '.check_payment', function() {
        var val = $(this).val();
        var tipe = $(this).data('tipe'); // ← ambil dari data-tipe

        var checked = 0;
        if ($(this).is(':checked')) {
            checked = 1;
        }

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'check_payment',
            data: {
                'id': val,
                'checked': checked,
                'tipe': tipe // ← kirim ke controller
            },
            cache: false,
            success: function(result) {},
            error: function(result) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please try again later!',
                    icon: 'error'
                });
            }
        });
    });

    $(document).on('click', '.clear_choosed_payment', function() {
        Swal.fire({
            title: "Are you sure?",
            text: "Your choosed payment data will be cleared!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Yes, Clear it!",
            cancelButtonText: "No, cancel!"
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: siteurl + active_controller + 'clear_choosed_payment',
                    type: "POST",
                    cache: false,
                    dataType: 'json',
                    success: function(data) {
                        Swal.fire('Cleared!', 'Checked payment data has been cleared.', 'success').then(function() {
                            location.reload(true);
                        });
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Please try again later!',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.proses_payment', function() {
    check_choosed_payment().done(function(data) {
        var choosed_payment = data.count_choosed_payment;

        if (choosed_payment > 0) {
            window.location.href = siteurl + active_controller + 'form_payment_new/?id_payment=' + data.arr_choosed_payment + '&tipe=' + data.arr_tipe;
        } else {
            Swal.fire({
                title: 'Warning!',
                text: 'Please check at least 1 payment data!',
                icon: 'warning'
            });
        }
    }).fail(function(data) {
        Swal.fire({
            title: 'Error!',
            text: 'Failed to process payment. Please try again.',
            icon: 'error'
        });
    });
});

    function DataTables() {
        var DataTables = $('#table_list_req_payment').dataTable({
            serverSide: true,
            processing: true,
            destroy: true,
            paging: true,
            stateSave: true,
            ajax: {
                type: 'post',
                url: siteurl + active_controller + 'get_list_req_payment',
                dataType: 'json',
                data: function(d) {
                    d.jenis_payment = '<?= $jenis_payment ?>';
                }
            },
            columns: [{
                    data: 'no',
                    className: 'text-center'
                },
                {
                    data: 'no_dokumen'
                },
                {
                    data: 'tipe', // Menangkap data tipe dari Controller
                    className: 'text-center'
                },
                {
                    data: 'tgl',
                    className: 'text-center'
                },
                {
                    data: 'keperluan'
                },
                {
                    data: 'currency',
                    className: 'text-center'
                },
                {
                    data: 'total_invoice',
                    className: 'text-right'
                },
                {
                    data: 'requestor'
                },
                {
                    data: 'option',
                    className: 'text-center'
                }
            ]
        });
    }
</script>