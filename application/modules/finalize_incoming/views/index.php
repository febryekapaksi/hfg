<?php
$ENABLE_ADD     = has_permission('Finalize_Incoming.Add');
$ENABLE_MANAGE  = has_permission('Finalize_Incoming.Manage');
$ENABLE_VIEW    = has_permission('Finalize_Incoming.View');
$ENABLE_DELETE  = has_permission('Finalize_Incoming.Delete');

?>

<div class="card">
    <div class="card-body">
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-3" id="finalizeTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-draft-tab" data-bs-toggle="tab" href="#tab-draft" role="tab">
                    <i class="fa fa-clock"></i> Draft (Submitted)
                    <!-- <span class="badge bg-info ms-1" id="draft-count"></span> -->
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-close-tab" data-bs-toggle="tab" href="#tab-close" role="tab">
                    <i class="fa fa-check-circle"></i> Close
                </a>
            </li>
        </ul>

        <div class="tab-content" id="finalizeTabContent">

            <!-- TAB DRAFT -->
            <div class="tab-pane fade show active" id="tab-draft" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <table id="table-finalize-draft" class="table table-bordered table-striped dt-responsive" width="100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>ROS No.</th>
                                    <th>PO / Letter No.</th>
                                    <th>Supplier</th>
                                    <th>Submitted Date</th>
                                    <th>Submitted By</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB CLOSE -->
            <div class="tab-pane fade" id="tab-close" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <table id="table-finalize-close" class="table table-bordered table-striped dt-responsive" width="100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Transaction No.</th>
                                    <th>ROS No.</th>
                                    <th>PO / Letter No.</th>
                                    <th>Supplier</th>
                                    <th>Finalize Date</th>
                                    <th>Incoming Code</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Konfirmasi Finalize -->
<div class="modal fade" id="modalFinalize" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalFinalizeLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalFinalizeLabel">
                    <i class="fa fa-check-circle"></i> Confirm Finalize Incoming — ROS: <span id="modal-no-ros"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <!-- Info Header -->
                <div class="row mb-3" id="modal-header-info">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="35%" class="fw-bold">Supplier</td>
                                <td>: <span id="modal-supplier">-</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">No. PO</td>
                                <td>: <span id="modal-no-po">-</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">No. ROS</td>
                                <td>: <span id="modal-no-ros-info">-</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="fw-bold">Incoming Date</label>
                            <input type="text" id="modal-tanggal" class="form-control"
                                placeholder="Select date" autocomplete="off" readonly>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Search bar -->
                <div class="row mb-3">
                    <div class="col-md-6 ms-auto">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="text" class="form-control" id="search-modal-coil"
                                placeholder="Search material / coil no...">
                        </div>
                    </div>
                </div>

                <!-- Tabel Detail Pack -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="modal-table-coil">
                        <thead>
                            <tr>
                                <th class="text-center" style="vertical-align:middle;" width="3%">No</th>
                                <th class="text-center" style="vertical-align:middle;" width="12%">Pack Code</th>
                                <th class="text-center" style="vertical-align:middle;" width="22%">Materials</th>
                                <th class="text-center" style="vertical-align:middle;" width="6%">Coils</th>
                                <th class="text-center" style="vertical-align:middle; background-color:#69c79d !important;" width="10%">Total N.W.</th>
                                <th class="text-center" style="vertical-align:middle; background-color:#69c79d !important;" width="10%">Total G.W.</th>
                                <th class="text-center" style="vertical-align:middle; background-color:#c8e6c9 !important;" width="10%">Dest. Warehouse</th>
                            </tr>
                        </thead>
                        <tbody id="modal-body-coil">
                            <tr>
                                <td colspan="7" class="text-center">
                                    <i class="fa fa-spinner fa-spin"></i> Loading data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="btn-confirm-finalize">
                    <i class="fa fa-check-circle"></i> Yes, Finalize Now!
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 & Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    $(document).ready(function() {

        var fpModalTanggal = flatpickr('#modal-tanggal', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: false,
            defaultDate: new Date(),
        });

        // DataTable Draft
        var tableDraft = $('#table-finalize-draft').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: siteurl + active_controller + 'data_side_draft',
                type: 'POST',
                dataSrc: function(json) {
                    // $('#draft-count').text(json.recordsTotal > 0 ? json.recordsTotal : '');
                    return json.data;
                }
            },
            columns: [{
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
                }, {
                    data: 7,
                    orderable: false
                }
            ],
            order: [
                [4, 'desc']
            ]
        });

        // DataTable Close
        var tableClose = $('#table-finalize-close').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: siteurl + active_controller + 'data_side_close',
                type: 'POST'
            },
            columns: [{
                    data: 0
                }, // No
                {
                    data: 1
                }, // No. Transaksi
                {
                    data: 2
                }, // No. ROS
                {
                    data: 3
                }, // No. PO / Surat
                {
                    data: 4
                }, // Supplier
                {
                    data: 5
                }, // Tgl Finalize
                {
                    data: 6
                }, // Kode Incoming
                {
                    data: 7
                }, // Status
                {
                    data: 8,
                    orderable: false
                } // Aksi
            ],
            order: [
                [5, 'desc']
            ]
        });

        $('#tab-close-tab').one('shown.bs.tab', function() {
            tableClose.ajax.reload();
        });

        /* ── Tombol Finalize -> buka modal preview ── */
        $(document).on('click', '.btn-finalize', function() {
            var no_ros = $(this).data('id');

            // Reset modal
            $('#modal-no-ros').text(no_ros);
            $('#modal-no-ros-info').text(no_ros);
            $('#modal-supplier').text('-');
            $('#modal-no-po').text('-');
            $('#modal-tanggal').val(new Date().toISOString().split('T')[0]);
            $('#search-modal-coil').val('');
            $('#modal-body-coil').html(
                '<tr><td colspan="10" class="text-center">' +
                '<i class="fa fa-spinner fa-spin"></i> Loading data...</td></tr>'
            );
            $('#btn-confirm-finalize').data('ros', no_ros);

            var modal = new bootstrap.Modal(document.getElementById('modalFinalize'));
            modal.show();

            $.ajax({
                url: siteurl + active_controller + 'get_draft_preview',
                type: 'POST',
                data: {
                    no_ros: no_ros
                },
                dataType: 'json',
                success: function(res) {
                    if (!res || res.status === 0) {
                        $('#modal-body-coil').html(
                            '<tr><td colspan="10" class="text-center text-danger">' +
                            (res.pesan || 'Failed to load data.') + '</td></tr>'
                        );
                        return;
                    }

                    $('#modal-supplier').text(res.header.nm_supplier || '-');
                    $('#modal-no-po').text(res.header.no_surat || '-');
                    var tglDb = res.header.incoming_date || new Date().toISOString().split('T')[0];
                    fpModalTanggal.setDate(tglDb, true);

                    var html = '';

                    if (res.packs && res.packs.length > 0) {
                        res.packs.forEach(function(pack, packIdx) {
                            var packCode = pack.pack_code || ('Pack #' + pack.pack_no);

                            // Build material list with dot prefix
                            var matList = '';
                            var matKeys = Object.keys(pack.materials || {});
                            matKeys.forEach(function(key) {
                                var m = pack.materials[key];
                                matList += '<div style="font-size:11px;"><span class="text-primary me-1">&#9679;</span><b>' + (m.nm_alias || '') + '</b> <small class="text-muted">(' + (m.nm_material || '') + ')</small></div>';
                            });

                            html += '<tr class="modal-pack-row" data-pack-code="' + (pack.pack_code || '').toLowerCase() + '">' +
                                '<td class="text-center">' + (packIdx + 1) + '</td>' +
                                '<td class="text-center"><span class="badge bg-primary">' + packCode + '</span></td>' +
                                '<td>' + matList + '</td>' +
                                '<td class="text-center">' + (pack.coil_count || 0) + '</td>' +
                                '<td class="text-end fw-bold">' + parseFloat(pack.total_nw || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>' +
                                '<td class="text-end fw-bold">' + parseFloat(pack.total_gw || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</td>' +
                                '<td class="text-center">' + (pack.kd_gudang_ke || '-') + '</td>' +
                                '</tr>';
                        });
                    } else {
                        html = '<tr><td colspan="7" class="text-center text-warning">No pack data found.</td></tr>';
                    }

                    $('#modal-body-coil').html(html);
                },
                error: function() {
                    $('#modal-body-coil').html(
                        '<tr><td colspan="7" class="text-center text-danger">Failed to connect to server.</td></tr>'
                    );
                }
            });
        });

        /* ── Search modal pack ── */
        $(document).on('keyup', '#search-modal-coil', function() {
            var keyword = $(this).val().toLowerCase().trim();
            var $rows = $('#modal-body-coil .modal-pack-row');

            if (!keyword) {
                $rows.show();
                $('#no-result-modal-coil').remove();
                return;
            }

            var visibleCount = 0;
            $rows.each(function() {
                var packCode = $(this).data('pack-code') || '';
                var rowText = $(this).text().toLowerCase();
                if (packCode.indexOf(keyword) > -1 || rowText.indexOf(keyword) > -1) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            $('#no-result-modal-coil').remove();
            if (visibleCount === 0) {
                $('#modal-body-coil').append(
                    '<tr id="no-result-modal-coil"><td colspan="7" class="text-center text-muted py-3">' +
                    '<i class="fa fa-search"></i> No results found for "<b>' + keyword + '</b>"</td></tr>'
                );
            }
        });

        /* ── Konfirmasi Finalize ── */
        $(document).on('click', '#btn-confirm-finalize', function() {
            var no_ros = $(this).data('ros');
            var tanggal = $('#modal-tanggal').val();

            if (!tanggal) {
                Swal.fire({
                    title: 'Warning',
                    text: 'Incoming date is required!',
                    icon: 'warning'
                });
                return;
            }

            // QC data — semua coil default OK (QC per pack view)
            var qcData = [];

            bootstrap.Modal.getInstance(document.getElementById('modalFinalize')).hide();

            Swal.fire({
                title: 'Process Finalize?',
                html: '<b>ROS: ' + no_ros + '</b><br>Stock and accounting journals will be processed. This cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Finalize!',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Processing...',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: siteurl + active_controller + 'finalize',
                    type: 'POST',
                    data: {
                        no_ros: no_ros,
                        tanggal: tanggal,
                        qc_data: JSON.stringify(qcData)
                    },
                    dataType: 'json',
                    success: function(r) {
                        Swal.close();
                        if (r.status == 1) {
                            Swal.fire({
                                title: 'Success!',
                                text: r.pesan,
                                icon: 'success',
                                timer: 1800,
                                showConfirmButton: false
                            }).then(function() {
                                tableDraft.ajax.reload();
                                tableClose.ajax.reload();
                            });
                        } else if (r.status == 2) {
                            Swal.fire({
                                    title: 'Attention',
                                    text: r.pesan,
                                    icon: 'warning'
                                })
                                .then(function() {
                                    tableDraft.ajax.reload();
                                    tableClose.ajax.reload();
                                });
                        } else if (r.status == 3) {
                            Swal.fire({
                                title: 'Master COA Incomplete!',
                                html: '<div class="text-start">' +
                                    '<p>The <b>Finalize</b> process has been cancelled because the following COA numbers are not registered in the Master COA:</p>' +
                                    '<div class="alert alert-danger fw-bold">' + r.pesan.replace(/:\s*/, ':<br><code>').replace(/$/, '</code>') + '</div>' +
                                    '<p class="mb-0 text-muted small">Please add the COA numbers in the <b>Master COA</b> menu first.</p>' +
                                    '</div>',
                                icon: 'error',
                                confirmButtonText: 'Understood',
                                confirmButtonColor: '#dc3545',
                            });
                        } else {
                            Swal.fire({
                                title: 'Failed',
                                text: r.pesan,
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Failed to process finalize.', 'error');
                    }
                });
            });
        });

        /* ── Tombol Revisi -> SweetAlert dengan input keterangan ── */
        $(document).on('click', '.btn-revisi', function() {
            var no_ros = $(this).data('id');

            Swal.fire({
                title: 'Return to Incoming?',
                html: '<p>ROS <b>' + no_ros + '</b> will be returned for re-editing.</p>' +
                    '<div class="text-start">' +
                    '<textarea id="swal-revision-note" class="form-control" rows="3" placeholder="Describe the revision reason..."></textarea>' +
                    '</div>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fa fa-undo"></i> Yes, Return!',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                preConfirm: function() {
                    var note = document.getElementById('swal-revision-note').value.trim();
                    if (!note) {
                        Swal.showValidationMessage('Revision note is required!');
                        return false;
                    }
                    return note;
                }
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: siteurl + active_controller + 'revisi',
                    type: 'POST',
                    data: {
                        no_ros: no_ros,
                        revision_note: result.value
                    },
                    dataType: 'json',
                    success: function(r) {
                        if (r.status == 1) {
                            Swal.fire({
                                title: 'Success!',
                                text: r.pesan,
                                icon: 'success',
                                timer: 1800,
                                showConfirmButton: false
                            }).then(function() {
                                tableDraft.ajax.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Failed',
                                text: r.pesan,
                                icon: 'error'
                            });
                        }
                    }
                });
            });
        });
    });
</script>