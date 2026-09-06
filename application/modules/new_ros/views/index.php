<?php
$ENABLE_ADD    = has_permission('ROS_(Packing_List).Add');
$ENABLE_MANAGE = has_permission('ROS_(Packing_List).Manage');
$ENABLE_VIEW   = has_permission('ROS_(Packing_List).View');
$ENABLE_DELETE = has_permission('ROS_(Packing_List).Delete');
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
    .swal2-container {
        z-index: 99999 !important;
    }

    .section-title-preview {
        background: #f8f9fa;
        padding: 7px 12px;
        border-left: 4px solid #0d6efd;
        margin: 15px 0 10px;
        font-weight: bold;
        font-size: 13px;
    }

    .section-title-preview.pib {
        border-left-color: #0d6efd;
    }

    .section-title-preview.ls {
        border-left-color: #198754;
    }

    .section-title-preview.insurance {
        border-left-color: #ffc107;
    }

    .section-title-preview.others {
        border-left-color: #dc3545;
    }

    .section-title-preview.data-po {
        border-left-color: #6f42c1;
    }

    .section-title-preview.coil-sec {
        border-left-color: #17a2b8;
    }
</style>

<div class="card">
    <div class="card-header">
        <?php if ($ENABLE_ADD) : ?>
            <a class="btn btn-success btn-md" href="<?= base_url('new_ros/add') ?>" title="Add">
                <i class="fa fa-plus"></i> Add New ROS
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body">

        <!-- ── TABS ── -->
        <ul class="nav nav-tabs mb-3" id="rosTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-open-btn" data-bs-toggle="tab"
                    data-bs-target="#tab-open" type="button" role="tab">
                    <i class="fas fa-file-alt text-warning me-1"></i> Open
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-payment-btn" data-bs-toggle="tab"
                    data-bs-target="#tab-payment" type="button" role="tab">
                    <i class="fas fa-hourglass-half text-info me-1"></i> Close (Payment Process)
                    <span class="badge rounded-pill bg-danger ms-1" id="badge_payment_count" style="display:none;">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-completed-btn" data-bs-toggle="tab"
                    data-bs-target="#tab-completed" type="button" role="tab">
                    <i class="fas fa-check-double text-success me-1"></i> Close (Payment Completed)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="rosTabContent">

            <!-- TAB OPEN  -->
            <div class="tab-pane fade show active" id="tab-open" role="tabpanel">
                <table id="tbl_ros_open" class="table table-bordered table-striped" width="100%">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th class="text-center">ROS Number</th>
                            <th class="text-center">PO Number</th>
                            <th class="text-center">Supplier</th>
                            <th class="text-center">PIB Value (Rp)</th>
                            <th class="text-center" width="8%">Status</th>
                            <th class="text-center" width="18%">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- TAB CLOSE (PAYMENT PROCESS) -->
            <div class="tab-pane fade" id="tab-payment" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="input-group input-group-sm" style="max-width:320px;">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="search_payment" class="form-control" placeholder="Search ROS / PO / Supplier...">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn_refresh_payment">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <table class="table table-bordered table-sm align-middle" id="tbl_payment_process" width="100%">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="4%">No</th>
                            <th class="text-center">ROS Number</th>
                            <th class="text-center">PO Number</th>
                            <th class="text-center">Supplier</th>
                            <th class="text-center">Payment Type</th>
                            <th class="text-center">Description</th>
                            <th class="text-center">Nominal (Rp)</th>
                            <th class="text-center" width="12%">Payment Status</th>
                            <th class="text-center" width="10%">Action</th>
                        </tr>
                    </thead>
                    <tbody id="payment_process_body">
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- TAB CLOSE (PAYMENT COMPLETED) -->
            <div class="tab-pane fade" id="tab-completed" role="tabpanel">
                <table id="tbl_ros_completed" class="table table-bordered table-striped" width="100%">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th class="text-center">ROS Number</th>
                            <th class="text-center">PO Number</th>
                            <th class="text-center">Supplier</th>
                            <th class="text-center">PIB Value (Rp)</th>
                            <th class="text-center" width="12%">Status</th>
                            <th class="text-center" width="12%">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Close ROS                                    -->
<div class="modal fade" id="modalCloseROS" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" style="color: white;>
                    <i class=" fas fa-check-double"></i> Verification Close ROS —
                    <span id="modal_close_ros_id" "></span>
                </h5>
                <button type=" button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal_close_ros_body" style="max-height:75vh; overflow-y:auto;">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <div class="mt-2 text-muted">Loading data...</div>
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted me-auto">
                    <i class="fas fa-info-circle"></i>
                    Please review the data before closing. Once closed, the ROS will proceed to the Incoming process.
                </small>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-success btn-sm" id="btn_confirm_close_ros">
                    <i class="fas fa-check-double"></i> Confirm Close ROS
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        var dtColumns = [{
                data: 0
            }, {
                data: 1
            }, {
                data: 2
            }, {
                data: 3
            },
            {
                data: 4
            }, {
                data: 5
            }, {
                data: 6
            }
        ];

        // DATATABLE — OPEN (status = 0)
        var tblOpen = $('#tbl_ros_open').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: siteurl + 'new_ros/data_side',
                type: 'POST',
                data: {
                    tab: 'open'
                }
            },
            columns: dtColumns,
            order: [
                [1, 'desc']
            ],
            pageLength: 25
        });

        // DATATABLE — CLOSE (PAYMENT COMPLETED)
        var tblCompleted = $('#tbl_ros_completed').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: siteurl + 'new_ros/data_side',
                type: 'POST',
                data: {
                    tab: 'payment_completed'
                }
            },
            columns: dtColumns,
            order: [
                [1, 'desc']
            ],
            pageLength: 25
        });

        // ── PAYMENT PROCESS (tabel custom) ──
        var fmtRp = function(val) {
            return (parseFloat(val) || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };

        var paymentTypeLabel = {
            'bm': 'BM',
            'ls': 'LS (Surveyor)',
            'insurance': 'Insurance',
            'other_cost': 'Other Cost'
        };

        function paymentStatusBadge(status) {
            switch (status) {
                case 'belum_diajukan':
                    return '<span class="badge bg-secondary">Belum Diajukan</span>';
                case 'diajukan':
                    return '<span class="badge bg-warning text-dark">Diajukan</span>';
                case 'approve checker':
                    return '<span class="badge bg-info text-dark">Approve Checker</span>';
                case 'approve management':
                    return '<span class="badge bg-primary">Approve Management</span>';
                case 'lunas':
                    return '<span class="badge bg-success">Lunas</span>';
                default:
                    return '<span class="badge bg-light text-dark">' + status + '</span>';
            }
        }

        function loadPaymentProcess() {
            var search = $('#search_payment').val() || '';
            $('#payment_process_body').html('<tr><td colspan="9" class="text-center text-muted py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Loading...</td></tr>');

            $.ajax({
                url: siteurl + 'new_ros/get_payment_process_list',
                type: 'POST',
                data: {
                    search: search
                },
                dataType: 'json',
                success: function(res) {
                    var html = '';
                    if (res.status == 1 && res.data.length > 0) {
                        var no = 1;
                        $.each(res.data, function(i, ros) {
                            var payments = ros.payments || [];
                            var rowspan = payments.length > 0 ? payments.length : 1;
                            var rosLink = '<a href="' + siteurl + 'new_ros/view/' + ros.id + '" title="View ROS"><b>' + ros.id + '</b></a>';
                            var poDisplay = ros.no_surat ? ros.no_surat : ros.no_po;

                            if (payments.length === 0) {
                                html += '<tr>';
                                html += '<td class="text-center">' + no + '</td>';
                                html += '<td>' + rosLink + '</td>';
                                html += '<td>' + poDisplay + '</td>';
                                html += '<td>' + (ros.nm_supplier || '') + '</td>';
                                html += '<td colspan="5" class="text-center text-muted">No payment items.</td>';
                                html += '</tr>';
                            } else {
                                $.each(payments, function(j, p) {
                                    html += '<tr>';
                                    if (j === 0) {
                                        html += '<td class="text-center align-middle" rowspan="' + rowspan + '">' + no + '</td>';
                                        html += '<td class="align-middle" rowspan="' + rowspan + '">' + rosLink + '</td>';
                                        html += '<td class="align-middle" rowspan="' + rowspan + '">' + poDisplay + '</td>';
                                        html += '<td class="align-middle" rowspan="' + rowspan + '">' + (ros.nm_supplier || '') + '</td>';
                                    }
                                    html += '<td>' + (paymentTypeLabel[p.payment_type] || p.payment_type) + '</td>';
                                    html += '<td>' + (p.keterangan || '-') + '</td>';
                                    html += '<td class="text-end">' + fmtRp(p.nominal) + '</td>';
                                    html += '<td class="text-center">' + paymentStatusBadge(p.status) + '</td>';
                                    html += '<td class="text-center">';

                                    // Handler Tombol Aksi Berdasarkan Status
                                    if (p.status === 'belum_diajukan') {
                                        html += '<button type="button" class="btn btn-sm btn-primary btn-ajukan-payment" data-id="' + p.id + '"><i class="fas fa-paper-plane"></i> Ajukan</button>';
                                    } else {
                                        html += '<span class="text-muted small">-</span>';
                                    }

                                    html += '</td>';
                                    html += '</tr>';
                                });
                            }
                            no++;
                        });
                    } else {
                        html = '<tr><td colspan="9" class="text-center text-muted py-3">No data in payment process.</td></tr>';
                    }
                    $('#payment_process_body').html(html);
                },
                error: function() {
                    $('#payment_process_body').html('<tr><td colspan="9" class="text-center text-danger py-3">Failed to load data.</td></tr>');
                }
            });
        }

        function loadPaymentBadge() {
            $.ajax({
                url: siteurl + 'new_ros/get_payment_process_count',
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    if (res.status == 1 && res.count > 0) {
                        $('#badge_payment_count').text(res.count).show();
                    } else {
                        $('#badge_payment_count').hide();
                    }
                }
            });
        }

        // Load badge saat halaman dibuka
        loadPaymentBadge();

        var paymentTabLoaded = false;
        $('#tab-payment-btn').on('shown.bs.tab', function() {
            if (!paymentTabLoaded) {
                loadPaymentProcess();
                paymentTabLoaded = true;
            }
        });

        // Search (debounce)
        var searchTimer = null;
        $('#search_payment').on('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(loadPaymentProcess, 400);
        });

        $('#btn_refresh_payment').on('click', function() {
            loadPaymentProcess();
            loadPaymentBadge();
        });

        // Tombol Ajukan
        $(document).on('click', '.btn-ajukan-payment', function() {
            var idPayment = $(this).data('id');

            Swal.fire({
                title: 'Ajukan Pembayaran',
                html: '<div style="text-align:left;">' +
                    '<label class="form-label small fw-bold mb-1">Bank <span class="text-danger">*</span></label>' +
                    '<input id="swal_bank" class="form-control form-control-sm mb-2" placeholder="Nama Bank">' +
                    '<label class="form-label small fw-bold mb-1">No. Rekening <span class="text-danger">*</span></label>' +
                    '<input id="swal_accnumber" class="form-control form-control-sm mb-2" placeholder="No. Rekening">' +
                    '<label class="form-label small fw-bold mb-1">Atas Nama <span class="text-danger">*</span></label>' +
                    '<input id="swal_accname" class="form-control form-control-sm" placeholder="Nama Pemilik Rekening">' +
                    '</div>',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: '<i class="fas fa-paper-plane"></i> Ajukan',
                cancelButtonText: 'Batal',
                focusConfirm: false,
                preConfirm: function() {
                    var bank = document.getElementById('swal_bank').value.trim();
                    var accnumber = document.getElementById('swal_accnumber').value.trim();
                    var accname = document.getElementById('swal_accname').value.trim();
                    if (!bank || !accnumber || !accname) {
                        Swal.showValidationMessage('Bank, No. Rekening, dan Atas Nama wajib diisi.');
                        return false;
                    }
                    return {
                        bank_id: bank,
                        accnumber: accnumber,
                        accname: accname
                    };
                }
            }).then(function(result) {
                if (!result.isConfirmed) return;
                var payload = result.value;
                Swal.fire({
                    title: 'Processing...',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: siteurl + 'new_ros/ajukan_payment',
                    type: 'POST',
                    data: {
                        id_payment: idPayment,
                        bank_id: payload.bank_id,
                        accnumber: payload.accnumber,
                        accname: payload.accname
                    },
                    dataType: 'json',
                    success: function(res) {
                        Swal.close();
                        if (res.status == 1) {
                            Swal.fire({
                                title: 'Success',
                                text: res.msg,
                                icon: 'success',
                                timer: 1600,
                                showConfirmButton: false
                            });
                            loadPaymentProcess();
                        } else {
                            Swal.fire('Failed', res.msg, 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Gagal mengajukan payment.', 'error');
                    }
                });
            });
        });

        var completedTabLoaded = false;
        $('#tab-completed-btn').on('shown.bs.tab', function() {
            if (!completedTabLoaded) {
                tblCompleted.ajax.reload();
                completedTabLoaded = true;
            }
        });

        // DELETE
        $(document).on('click', '.del_ros', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Delete ROS?',
                text: 'ROS ' + id + ' will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.post(siteurl + 'new_ros/delete', {
                        id: id
                    }, function(res) {
                        var resp = (typeof res === 'string') ? JSON.parse(res) : res;
                        if (resp.status == 1) {
                            Swal.fire('Deleted!', 'ROS data has been deleted.', 'success');
                            tblOpen.ajax.reload();
                        } else {
                            Swal.fire('Failed!', 'Failed to delete data.', 'error');
                        }
                    });
                }
            });
        });

        // CLOSE ROS — Buka Modal Preview
        var currentCloseRosId = null;
        $(document).on('click', '.btn_close_ros', function() {
            var id = $(this).data('id');
            currentCloseRosId = id;

            $('#modal_close_ros_id').text(id);
            $('#modal_close_ros_body').html(
                '<div class="text-center py-4">' +
                '<div class="spinner-border text-primary"></div>' +
                '<div class="mt-2 text-muted">Loading ROS data...</div>' +
                '</div>'
            );
            $('#modalCloseROS').modal('show');

            $.ajax({
                url: siteurl + 'new_ros/get_ros_preview',
                type: 'POST',
                data: {
                    id_ros: id
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status == 1) {
                        $('#modal_close_ros_body').html(buildPreviewHtml(res));

                        // PO Lokal: sembunyikan kolom biaya (BM, LS, Forwarding, Insurance, Others)
                        if (res.loi && String(res.loi).toLowerCase() === 'lokal') {
                            $('#modal_close_ros_body .col-bm, ' +
                                '#modal_close_ros_body .col-ls, ' +
                                '#modal_close_ros_body .col-forwarding, ' +
                                '#modal_close_ros_body .col-insurance, ' +
                                '#modal_close_ros_body .col-others').hide();
                        }
                    } else {
                        $('#modal_close_ros_body').html(
                            '<div class="alert alert-danger">' + res.msg + '</div>'
                        );
                    }
                },
                error: function() {
                    $('#modal_close_ros_body').html(
                        '<div class="alert alert-danger">Failed to load data.</div>'
                    );
                }
            });
        });

        $('#btn_confirm_close_ros').on('click', function() {
            if (!currentCloseRosId) return;

            Swal.fire({
                title: 'Close ROS ' + currentCloseRosId + '?',
                text: 'Once closed, the ROS will proceed to Incoming and can no longer be edited.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check-double"></i> Yes, Close',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: siteurl + 'new_ros/close_ros',
                    type: 'POST',
                    data: {
                        id_ros: currentCloseRosId
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Processing...',
                            allowOutsideClick: false,
                            didOpen: function() {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(res) {
                        Swal.close();
                        if (res.status == 1) {
                            $('#modalCloseROS').modal('hide');
                            Swal.fire({
                                title: 'Success!',
                                text: res.msg,
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function() {
                                // Reload semua tab: Open berkurang, Payment Process / Completed bertambah
                                tblOpen.ajax.reload();
                                tblCompleted.ajax.reload();
                                loadPaymentProcess();
                                loadPaymentBadge();
                                paymentTabLoaded = true;
                                completedTabLoaded = true;
                            });
                        } else if (res.status == 2) {
                            Swal.fire('Warning', res.msg, 'warning');
                        } else if (res.status == 3) {
                            // COA tidak ditemukan di master
                            Swal.fire({
                                title: 'Master COA Incomplete!',
                                html: '<div class="text-start">' +
                                    '<p>The <b>Close ROS</b> process has been cancelled because the following COA numbers are not registered in the Master COA:</p>' +
                                    '<div class="alert alert-danger fw-bold">' + res.msg.replace(/:\s*/, ':<br><code>').replace(/$/, '</code>') + '</div>' +
                                    '<p class="mb-0 text-muted small">Please add the COA numbers in the <b>Master COA</b> menu first, then retry this process.</p>' +
                                    '</div>',
                                icon: 'error',
                                confirmButtonText: 'Understood',
                                confirmButtonColor: '#dc3545',
                            });
                        } else {
                            Swal.fire('Failed!', res.msg, 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Failed to process close ROS.', 'error');
                    }
                });
            });
        });


        // BUILD PREVIEW HTML (sama seperti tampilan View ROS)
        function buildPreviewHtml(res) {
            var h = res.header;
            var materials = res.materials;
            var others = res.others;
            var isLokal = (res.loi && String(res.loi).toLowerCase() === 'lokal');

            var fmt = function(val, dec) {
                dec = (dec !== undefined) ? dec : 0;
                return (parseFloat(val) || 0).toLocaleString('en-US', {
                    minimumFractionDigits: dec,
                    maximumFractionDigits: dec
                });
            };

            var html = '';
            // ── Header Info ──
            html += '<div class="row mb-3">';
            html += '<div class="col-md-4"><strong>No. ROS:</strong> ' + h.id + '</div>';
            html += '<div class="col-md-4"><strong>Supplier:</strong> ' + h.nm_supplier + '</div>';
            html += '<div class="col-md-4"><strong>No. PO:</strong> ' + (h.no_surat || h.no_po) + '</div>';
            html += '</div>';

            // ── Data PIB ──
            html += '<div class="section-title-preview pib"><i class="fas fa-file-invoice"></i> PIB Data</div>';

            html += '<div class="row mb-3">';
            html += '<div class="col-md-3"><strong>No. Submission:</strong> ' + (h.no_pengajuan || '-') + '</div>';
            html += '<div class="col-md-3"><strong>No. Billing:</strong> ' + (h.no_billing || '-') + '</div>';
            html += '<div class="col-md-3"><strong>Tgl. Billing:</strong> ' + (h.tgl_billing ? h.tgl_billing : '-') + '</div>';
            html += '<div class="col-md-3"><strong>Doc PIB:</strong> ';
            if (h.file_original_name_pib) {
                html += '<a href="' + siteurl + 'uploads/pib_ros/' + h.file_hash_name_pib + '" target="_blank"><i class="fas fa-paperclip"></i> ' + h.file_original_name_pib + '</a>';
            } else {
                html += '-';
            }
            html += '</div></div>';

            html += '<div class="row mb-2">';
            html += '<div class="col-md-4"><strong>PO Value (U$):</strong> ' + fmt(h.nilai_po_usd, 4) + '</div>';
            html += '<div class="col-md-4"><strong>PIB Exchange Rate:</strong> ' + fmt(h.kurs_pib, 2) + '</div>';
            html += '<div class="col-md-4"><strong>PO PIB Value (Rp):</strong> ' + fmt(h.nilai_po_pib_rp, 2) + '</div>';
            html += '</div>';
            html += '<div class="row mb-3">';
            html += '<div class="col-md-4"><strong>Total Gross KG:</strong> ' + fmt(h.total_kg_kotor_pib, 4) + '</div>';
            html += '<div class="col-md-4"><strong>Total Net KG:</strong> ' + fmt(h.total_kg_bersih_pib, 4) + '</div>';
            html += '</div>';

            // ── F&C + LS + Insurance + Others (hanya untuk PO Import) ──
            if (!isLokal) {
                // ── F&C ──
                html += '<div class="row mb-3"><div class="col-md-5">';
                html += '<table class="table table-bordered table-sm" style="font-size:12px;">';
                html += '<thead class="table-light"><tr><th colspan="2">F&amp;C Estimation</th></tr></thead><tbody>';
                var fc_items = [
                    ['BM', h.cost_bm],
                    ['BM Kite', h.cost_bm_kite],
                    ['BMT', h.cost_bmt],
                    ['Excise Duty', h.cost_cukai],
                    ['PPN', h.cost_ppn],
                    ['PPnBM', h.cost_ppnbm],
                    ['PPH Import', h.cost_pph_import]
                ];
                $.each(fc_items, function(i, item) {
                    html += '<tr><td>' + item[0] + '</td><td class="text-end">' + fmt(item[1]) + '</td></tr>';
                });
                html += '<tr class="table-secondary"><td class="fw-bold">TOTAL</td>';
                html += '<td class="text-end fw-bold">' + fmt(res.total_fc) + '</td></tr>';
                html += '</tbody></table></div></div>';

                // ── Biaya LS ──
                html += '<div class="section-title-preview ls"><i class="fas fa-search-dollar"></i> LS Cost (Surveyor)</div>';
                html += '<div class="row mb-3">';
                html += '<div class="col-md-3"><strong>No. Invoice LS:</strong> ' + (h.no_invoice_ls || '-') + '</div>';
                html += '<div class="col-md-3"><strong>LS Cost:</strong> ' + fmt(h.biaya_ls) + '</div>';
                html += '<div class="col-md-3"><strong>PPN LS:</strong> ' + fmt(h.ppn_ls) + '</div>';
                html += '<div class="col-md-3"><strong>PPH LS:</strong> ' + fmt(h.pph_ls) + '</div>';
                html += '</div>';
                var dpp_ls = (parseFloat(h.biaya_ls) || 0) * (11 / 12);
                var total_ls = dpp_ls + (parseFloat(h.ppn_ls) || 0) - (parseFloat(h.pph_ls) || 0);
                html += '<div class="row mb-3">';
                html += '<div class="col-md-3"><strong>DPP (LS × 11/12):</strong> ' + fmt(dpp_ls) + '</div>';
                html += '<div class="col-md-3"><strong>Total Biaya LS:</strong> ' + fmt(total_ls) + '</div>';
                html += '</div>';

                // ── Insurance ──
                html += '<div class="section-title-preview insurance"><i class="fas fa-shield-alt"></i> Insurance</div>';
                html += '<div class="row mb-3">';
                html += '<div class="col-md-3"><strong>No. Insurance:</strong> ' + (h.no_insurance || '-') + '</div>';
                html += '<div class="col-md-3"><strong>Insurance Value:</strong> ' + fmt(h.insurance) + '</div>';
                html += '</div>';

                // ── Biaya Lain ──
                if (others && others.length > 0) {
                    html += '<div class="section-title-preview others"><i class="fas fa-coins"></i> Other Costs</div>';
                    html += '<div class="row mb-3"><div class="col-md-6">';
                    html += '<table class="table table-bordered table-sm" style="font-size:12px;">';
                    html += '<thead class="table-light"><tr><th>No</th><th>No. Ref</th><th>Description</th><th class="text-end">Amount (Rp)</th></tr></thead><tbody>';
                    $.each(others, function(i, ot) {
                        html += '<tr>';
                        html += '<td class="text-center">' + (i + 1) + '</td>';
                        html += '<td>' + (ot.no_others || '-') + '</td>';
                        html += '<td>' + ot.keterangan + '</td>';
                        html += '<td class="text-end">' + fmt(ot.nilai) + '</td>';
                        html += '</tr>';
                    });
                    html += '<tr class="table-secondary">';
                    html += '<td colspan="3" class="text-end fw-bold">Total</td>';
                    html += '<td class="text-end fw-bold">' + fmt(res.total_others_val) + '</td>';
                    html += '</tr>';
                    html += '</tbody></table></div></div>';
                }
            } // end if (!isLokal)

            // ── Data PO & Kalkulasi ──
            html += '<div class="section-title-preview data-po"><i class="fas fa-calculator"></i> PO Data &amp; Inventory Value Calculation</div>';
            html += '<div class="table-responsive">';
            html += '<table class="table table-bordered table-sm" style="font-size:11px;">';
            html += '<thead class="table-light"><tr>';
            var po_cols = [
                ['No', ''],
                ['PO Name', ''],
                ['Alias Name', ''],
                ['Kg Unit', ''],
                ['Unit Price (U$)', ''],
                ['Total Value (U$)', ''],
                ['Total Value (Rp)', ''],
                ['BM %', 'col-bm'],
                ['BM (Rp)', 'col-bm'],
                ['Prorate LS', 'col-ls'],
                ['Forwarding', 'col-forwarding'],
                ['Insurance', 'col-insurance'],
                ['Other Costs', 'col-others'],
                ['Total Inventory', ''],
                ['Cost Book', '']
            ];
            $.each(po_cols, function(i, t) {
                html += '<th class="text-center ' + t[1] + '">' + t[0] + '</th>';
            });
            html += '</tr></thead><tbody>';

            var sum_kg = 0,
                sum_usd = 0,
                sum_rp = 0,
                sum_bm = 0,
                sum_ls = 0,
                sum_fwd = 0,
                sum_ins = 0,
                sum_oth = 0,
                sum_inv = 0;

            $.each(materials, function(idx, m) {
                sum_kg += parseFloat(m.kg_unit) || 0;
                sum_usd += parseFloat(m.total_value_usd) || 0;
                sum_rp += parseFloat(m.total_value_rp) || 0;
                sum_bm += parseFloat(m.bm_rp) || 0;
                sum_ls += parseFloat(m.prorate_ls) || 0;
                sum_fwd += parseFloat(m.forwarding_cost) || 0;
                sum_ins += parseFloat(m.prorate_insurance) || 0;
                sum_oth += parseFloat(m.prorate_others) || 0;
                sum_inv += parseFloat(m.total_nilai_inventory) || 0;

                html += '<tr>';
                html += '<td class="text-center">' + (idx + 1) + '</td>';
                html += '<td>' + (m.nm_barang || '') + '</td>';
                html += '<td>' + (m.nm_alias || '') + '</td>';
                html += '<td class="text-end">' + fmt(m.kg_unit, 4) + '</td>';
                html += '<td class="text-end">' + fmt(m.unit_price_usd, 6) + '</td>';
                html += '<td class="text-end">' + fmt(m.total_value_usd, 4) + '</td>';
                html += '<td class="text-end">' + fmt(m.total_value_rp) + '</td>';
                html += '<td class="text-center col-bm">' + fmt(m.bm_persen, 0) + '%' + '</td>';
                html += '<td class="text-end col-bm">' + fmt(m.bm_rp) + '</td>';
                html += '<td class="text-end col-ls">' + fmt(m.prorate_ls) + '</td>';
                html += '<td class="text-end col-forwarding">' + fmt(m.forwarding_cost) + '</td>';
                html += '<td class="text-end col-insurance">' + fmt(m.prorate_insurance) + '</td>';
                html += '<td class="text-end col-others">' + fmt(m.prorate_others) + '</td>';
                html += '<td class="text-end fw-bold">' + fmt(m.total_nilai_inventory) + '</td>';
                html += '<td class="text-end fw-bold">' + fmt(m.cost_book) + '</td>';
                html += '</tr>';
            });

            html += '</tbody>';
            html += '<tfoot><tr class="table-secondary" style="font-weight:bold;">';
            html += '<td colspan="3" class="text-end">Total PO</td>';
            html += '<td class="text-end">' + fmt(sum_kg, 4) + '</td>';
            html += '<td></td>';
            html += '<td class="text-end">' + fmt(sum_usd, 4) + '</td>';
            html += '<td class="text-end">' + fmt(sum_rp) + '</td>';
            html += '<td class="col-bm"></td>';
            html += '<td class="text-end col-bm">' + fmt(sum_bm) + '</td>';
            html += '<td class="text-end col-ls">' + fmt(sum_ls) + '</td>';
            html += '<td class="text-end col-forwarding">' + fmt(sum_fwd) + '</td>';
            html += '<td class="text-end col-insurance">' + fmt(sum_ins) + '</td>';
            html += '<td class="text-end col-others">' + fmt(sum_oth) + '</td>';
            html += '<td class="text-end">' + fmt(sum_inv) + '</td>';
            html += '<td></td></tr></tfoot>';
            html += '</table></div>';

            // ── Data Coil ──
            if (res.total_coil > 0) {
                html += '<div class="section-title-preview coil-sec"><i class="fas fa-list"></i> Coil Data</div>';
                html += '<div class="table-responsive">';
                html += '<table class="table table-bordered table-sm" style="font-size:11px;">';
                html += '<thead class="table-light"><tr>';
                $.each([
                    'No', 'Original Name', 'Alias Name', 'Coil No.',
                    'Internal Code', 'N.W. (Kg)', 'G.W. (Kg)', 'Length (M)'
                ], function(i, t) {
                    html += '<th class="text-center">' + t + '</th>';
                });
                html += '</tr></thead><tbody>';

                var no = 1;
                $.each(materials, function(i, mat) {
                    if (!mat.coils || mat.coils.length === 0) return;
                    var rowspan = mat.coils.length;
                    var nm_asli = mat.nm_barang || mat.nm_erp || '';
                    var nm_alias = mat.nm_alias || mat.nm_barang || '';
                    $.each(mat.coils, function(j, coil) {
                        html += '<tr>';
                        if (j === 0) {
                            html += '<td class="text-center align-middle" rowspan="' + rowspan + '">' + no + '</td>';
                            html += '<td class="align-middle" rowspan="' + rowspan + '">' + nm_asli + '</td>';
                            html += '<td class="align-middle" rowspan="' + rowspan + '">' + nm_alias + '</td>';
                        }
                        html += '<td class="text-center">' + coil.no_coil + '</td>';
                        html += '<td class="text-center"><small><b>' + (coil.kode_internal || '') + '</b></small></td>';
                        html += '<td class="text-end">' + fmt(coil.berat_bersih, 2) + '</td>';
                        html += '<td class="text-end">' + fmt(coil.berat_kotor, 2) + '</td>';
                        html += '<td class="text-end">' + fmt(coil.panjang, 2) + '</td>';
                        html += '</tr>';
                    });
                    no++;
                });

                html += '</tbody>';
                html += '<tfoot><tr class="table-secondary">';
                html += '<td colspan="3" class="text-end fw-bold">Total Coil: ' + res.total_coil + '</td>';
                html += '<td colspan="2"></td>';
                html += '<td class="text-end fw-bold">' + fmt(res.total_nw, 2) + '</td>';
                html += '<td class="text-end fw-bold">' + fmt(res.total_gw, 2) + '</td>';
                html += '<td></td></tr></tfoot>';
                html += '</table></div>';
            }

            return html;
        }

    });
</script>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        var table = $('#tbl_new_ros').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: siteurl + 'new_ros/data_side',
                type: 'POST'
            },
            columns: [{
                    data: 0
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
                    data: 5
                },
                {
                    data: 6
                }
            ],
            order: [
                [1, 'desc']
            ],
            pageLength: 25
        });

        // Delete
        $(document).on('click', '.del_ros', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Delete ROS?',
                text: 'ROS ' + id + ' will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(siteurl + 'new_ros/delete', {
                        id: id
                    }, function(res) {
                        var resp = JSON.parse(res);
                        if (resp.status == 1) {
                            Swal.fire('Deleted!', 'ROS data has been deleted.', 'success');
                            location.reload();
                        } else {
                            Swal.fire('Failed!', 'Failed to delete data.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>