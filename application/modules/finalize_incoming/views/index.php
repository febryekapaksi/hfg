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

                <!-- Tabel Detail Coil -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="modal-table-coil">
                        <thead>
                            <tr>
                                <th class="text-center" rowspan="2" style="vertical-align:middle;" width="3%">No</th>
                                <th class="text-center" rowspan="2" style="vertical-align:middle;" width="18%">Material</th>
                                <th class="text-center" rowspan="2" style="vertical-align:middle;" width="6%">Unit</th>
                                <th class="text-center" colspan="4" style="background-color:#69c79d !important;">ROS Data (Packing List)</th>
                                <th class="text-center" colspan="2" style="background-color:#f3b44e !important;">QC Status</th>
                                <th class="text-center" rowspan="2" style="vertical-align:middle; background-color:#c8e6c9 !important;" width="10%">Destination Warehouse</th>
                            </tr>
                            <tr>
                                <th class="text-center" style="background-color:#69c79d !important;">Coil No.</th>
                                <th class="text-center" style="background-color:#69c79d !important;">Gross Weight</th>
                                <th class="text-center" style="background-color:#69c79d !important;">Net Weight</th>
                                <th class="text-center" style="background-color:#69c79d !important;">Length</th>
                                <th class="text-center" style="background-color:#f3b44e !important;" width="5%">OK</th>
                                <th class="text-center" style="background-color:#f3b44e !important;" width="5%">Reject</th>
                            </tr>
                        </thead>
                        <tbody id="modal-body-coil">
                            <tr>
                                <td colspan="10" class="text-center">
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
                    $('#modal-no-po').text(res.header.no_po || '-');
                    var tglDb = res.header.incoming_date || new Date().toISOString().split('T')[0];
                    fpModalTanggal.setDate(tglDb, true);

                    var html = '';
                    var grouped = {};
                    var coilIndex = 0;
                    res.coils.forEach(function(c) {
                        var key = c.id_material;
                        if (!grouped[key]) grouped[key] = [];
                        grouped[key].push(c);
                    });

                    var no = 1;
                    var groupIdx = 0;
                    Object.keys(grouped).forEach(function(key) {
                        var rows = grouped[key];
                        rows.forEach(function(row, idx) {
                            html += '<tr class="modal-coil-row" data-material="' + (row.nm_material || '').toLowerCase() + '" data-nocoil="' + (row.no_coil || '').toLowerCase() + '" data-group="' + groupIdx + '">';
                            if (idx === 0) {
                                html +=
                                    '<td class="text-center" rowspan="' + rows.length + '" style="vertical-align:middle;">' + no + '</td>' +
                                    '<td rowspan="' + rows.length + '" style="vertical-align:middle;">' +
                                    '<b>' + row.nm_alias + '</b><br>' +
                                    '<small class="text-muted" style="font-style: italic;">' + (row.nm_material || '') + '</small>' +
                                    '</td>' +
                                    '<td class="text-center" rowspan="' + rows.length + '" style="vertical-align:middle;">Kg</td>';
                            }
                            html +=
                                '<td class="text-center bg-light">' + row.no_coil + '</td>' +
                                '<td class="text-end bg-light">' + parseFloat(row.berat_kotor || 0).toLocaleString('id-ID') + '</td>' +
                                '<td class="text-end bg-light">' + parseFloat(row.berat_bersih || 0).toLocaleString('id-ID') + '</td>' +
                                '<td class="text-end bg-light">' + parseFloat(row.panjang || 0).toLocaleString('id-ID') + '</td>' +
                                '<td class="text-center">' +
                                '<input type="radio" class="form-check-input modal-qc-radio" name="modal_qc[' + coilIndex + ']" value="OK" data-nocoil="' + row.no_coil + '" checked>' +
                                '</td>' +
                                '<td class="text-center">' +
                                '<input type="radio" class="form-check-input modal-qc-radio" name="modal_qc[' + coilIndex + ']" value="REJECT" data-nocoil="' + row.no_coil + '">' +
                                '</td>' +
                                '<td class="text-center">' + (row.kd_gudang_ke || '-') + '</td>' +
                                '</tr>';
                            coilIndex++;
                        });
                        no++;
                        groupIdx++;
                    });

                    $('#modal-body-coil').html(html);
                },
                error: function() {
                    $('#modal-body-coil').html(
                        '<tr><td colspan="11" class="text-center text-danger">Failed to connect to server.</td></tr>'
                    );
                }
            });
        });

        /* ── Search modal coil ── */
        $(document).on('keyup', '#search-modal-coil', function() {
            var keyword = $(this).val().toLowerCase().trim();
            var $rows = $('#modal-body-coil .modal-coil-row');

            // Hapus highlight sebelumnya
            $rows.find('.highlight-search').each(function() {
                var parent = $(this).parent();
                $(this).replaceWith($(this).text());
                parent.get(0).normalize();
            });

            if (!keyword) {
                $rows.show();
                return;
            }

            // Cari group yang punya match
            var matchedGroups = {};
            $rows.each(function() {
                var group = $(this).data('group');
                var material = $(this).data('material') || '';
                var nocoil = $(this).data('nocoil') || '';
                if (material.indexOf(keyword) > -1 || nocoil.indexOf(keyword) > -1) {
                    matchedGroups[group] = true;
                }
            });

            // Show/hide + highlight teks yang match
            var regex = new RegExp('(' + keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
            $rows.each(function() {
                var group = $(this).data('group');
                if (matchedGroups[group]) {
                    $(this).show();
                    // Highlight teks di dalam td
                    $(this).find('td').each(function() {
                        var td = $(this);
                        // Hanya proses td yang berisi teks langsung (bukan input/select)
                        if (td.find('input,select,button').length) return;
                        var text = td.text();
                        if (text.toLowerCase().indexOf(keyword) > -1) {
                            td.html(td.html().replace(regex, '<span class="highlight-search" style="background-color:#ffe066;border-radius:2px;padding:0 2px;">$1</span>'));
                        }
                    });
                } else {
                    $(this).hide();
                }
            });

            // Tampilkan keterangan jika tidak ada hasil
            $('#no-result-modal-coil').remove();
            if (Object.keys(matchedGroups).length === 0) {
                $('#modal-body-coil').append(
                    '<tr id="no-result-modal-coil"><td colspan="10" class="text-center text-muted py-3">' +
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

            // Kumpulkan data status QC
            var qcData = [];
            $('#modal-body-coil .modal-qc-radio:checked').each(function() {
                qcData.push({
                    no_coil: $(this).data('nocoil'),
                    status_qc: $(this).val()
                });
            });

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