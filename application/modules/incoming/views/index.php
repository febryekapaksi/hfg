<div class="card">
    <div class="card-body">
        <table id="table-incoming-open" class="table table-bordered table-striped dt-responsive" width="100%">
            <thead>
               <tr>
                    <th>No</th>
                    <th>ROS No.</th>
                    <th>PO / Letter No.</th>
                    <th>Supplier</th>
                    <th>PIB Rate</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {

        var tableOpen = $('#table-incoming-open').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: siteurl + active_controller + 'data_side_all',
                type: 'POST'
            },
            columns: [{
                    data: 0,
                    className: 'text-center'
                },
                {
                    data: 1
                },
                {
                    data: 2
                },
                {
                    data: 3
                },
                {
                    data: 4
                },
                {
                    data: 5,
                    className: 'text-center'
                },
                {
                    data: 6,
                    orderable: false,
                    className: 'text-center'
                }
            ],
            order: [
                [1, 'desc']
            ],
            pageLength: 10
        });

        $(document).on('click', '.btn-submit-draft', function() {
            var no_ros = $(this).data('id');

            Swal.fire({
                title: 'Submit Draft?',
                html: 'ROS <b>' + no_ros + '</b> will be submitted to Finalize Incoming.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Submit!',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: siteurl + active_controller + 'submit_draft',
                    type: 'POST',
                    data: {
                        no_ros: no_ros
                    },
                    dataType: 'json',
                    success: function(r) {
                        if (r.status == 1) {
                            Swal.fire({
                                title: 'Success!',
                                text: r.pesan ?? 'Draft submitted successfully.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(function() {
                                tableOpen.ajax.reload(null, false);
                            });
                        } else {
                            Swal.fire({
                                title: 'Failed',
                                text: r.pesan ?? 'Failed to submit draft.',
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error',
                            text: 'A system error occurred. Please contact IT.',
                            icon: 'error'
                        });
                    }
                });
            });
        });

    });
</script>