<?php
$ENABLE_ADD     = has_permission('Purchase_Order.Add');
$ENABLE_MANAGE  = has_permission('Purchase_Order.Manage');
$ENABLE_VIEW    = has_permission('Purchase_Order.View');
$ENABLE_DELETE  = has_permission('Purchase_Order.Delete');
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    #app-loader {
        display: none;
    }

    thead input {
        width: 100%;
    }

    .swal2-container {
        z-index: 1065 !important;
    }

    .nav-tabs .nav-link {
        cursor: pointer;
    }

    .skeleton-line {
        height: 14px;
        border-radius: 4px;
        background: linear-gradient(90deg, #e2e2e2 25%, #f0f0f0 37%, #e2e2e2 63%);
        background-size: 400% 100%;
        animation: skeleton-shimmer 1.4s ease infinite;
    }

    @keyframes skeleton-shimmer {
        0% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0 50%;
        }
    }
</style>

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="invoiceTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active tab-invoice" data-tipe="dp" type="button" role="tab">DP</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link tab-invoice" data-tipe="import" type="button" role="tab">Import (by ROS)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link tab-invoice" data-tipe="local" type="button" role="tab">Local (by Incoming)</button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="dic"></div>
    </div>
</div>

<div class="modal fade" id="dialog-popup" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="" method="post" id="frm-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel"><i class="fa fa-users"></i> Receive Invoice Down Payment (DP)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="ModalView"></div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary save_btn_modal"><i class="fa fa-save"></i> Save</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
    const MODAL_TITLES = {
        dp: '<i class="fa fa-users"></i> Receive Invoice Down Payment (DP)',
        import: '<i class="fa fa-ship"></i> Receive Invoice Import',
        local: '<i class="fa fa-truck"></i> Receive Invoice Local'
    };

    function setModalTitle(tipe) {
        $('.modal-title').html(MODAL_TITLES[tipe] || '<i class="fa fa-users"></i> Receive Invoice');
    }

    function showAjaxError() {
        Swal.fire({
            title: 'Error !',
            text: 'Please try again later !',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }

    function skeletonTable(rows = 6, cols = 9) {
        const headCells = Array.from({
                length: cols
            }, () =>
            '<th class="bg-primary text-white"><div class="skeleton-line" style="width:80%;margin:0 auto;"></div></th>'
        ).join('');
        const bodyRows = Array.from({
            length: rows
        }, () => {
            const cells = Array.from({
                length: cols
            }, () => '<td><div class="skeleton-line"></div></td>').join('');
            return `<tr>${cells}</tr>`;
        }).join('');
        return `<table class="table table-bordered"><thead><tr>${headCells}</tr></thead><tbody>${bodyRows}</tbody></table>`;
    }

    function loadInvoiceTab(tipe) {
        $('.dic').html(skeletonTable());
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'checkbx',
            data: {
                checkbx: tipe
            },
            cache: false,
            success: function(result) {
                $('.dic').html(result);
            },
            error: function() {
                $('.dic').html('');
                showAjaxError();
            }
        });
    }

    $(document).ready(function() {
        loadInvoiceTab('dp');
    });

    $(document).on('click', '.tab-invoice', function() {
        if ($(this).hasClass('active')) return;
        $('.tab-invoice').removeClass('active');
        $(this).addClass('active');
        loadInvoiceTab($(this).data('tipe'));
    });

    // Handler untuk tab DP
    $(document).on('click', '.req_app', function() {
        var tipe = $(this).data('tipe');
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'req_app',
            data: {
                no_po: $(this).data('no_po'),
                id_top: $(this).data('id_top'),
                tipe: tipe
            },
            cache: false,
            success: function(result) {
                setModalTitle(tipe);
                $('.save_btn_modal').show();
                $('#ModalView').html(result);
                $('#dialog-popup').modal('show');
            },
            error: showAjaxError
        });
    });

    $(document).on('click', '.req_inc_app', function() {
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'req_inc_app',
            data: {
                no_po: $(this).data('kode_trans'),
                tipe_incoming: $(this).data('tipe_incoming')
            },
            cache: false,
            success: function(result) {
                $('.save_btn_modal').show();
                $('#ModalView').html(result);
                $('#dialog-popup').modal('show');
            },
            error: showAjaxError
        });
    });

    $(document).on('click', '.view', function() {
        var tipe = $(this).data('tipe');
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'view',
            data: {
                id: $(this).data('id'),
                id_top: $(this).data('id_top'),
                tipe: tipe
            },
            cache: false,
            success: function(result) {
                setModalTitle(tipe);
                $('.save_btn_modal').hide();
                $('#ModalView').html(result);
                $('#dialog-popup').modal('show');
            },
            error: showAjaxError
        });
    });

    $(document).on('click', '.view_inc', function() {
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'view_inc',
            data: {
                id: $(this).data('id')
            },
            cache: false,
            success: function(result) {
                setModalTitle('inc');
                $('.save_btn_modal').hide();
                $('#ModalView').html(result);
                $('#dialog-popup').modal('show');
            },
            error: showAjaxError
        });
    });

    // Handler untuk tab Import & Local (btn-req-il dan btn-view-il)
    $(document).on('click', '.btn-req-il', function() {
        var tipe = $(this).data('tipe');
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'form_il',
            data: {
                id_top: $(this).data('id_top'),
                no_po: $(this).data('no_po'),
                tipe: tipe,
                id_dp: $(this).data('id_dp'),
                id_ros: $(this).data('id_ros'),
                id_incoming: $(this).data('id_incoming')
            },
            cache: false,
            success: function(result) {
                setModalTitle(tipe);
                $('.save_btn_modal').show();
                $('#ModalView').html(result);
                $('#dialog-popup').modal('show');
            },
            error: showAjaxError
        });
    });

    $(document).on('click', '.btn-view-il', function() {
        var tipe = $(this).data('tipe');
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'view_il',
            data: {
                id: $(this).data('id')
            },
            cache: false,
            success: function(result) {
                setModalTitle(tipe); // ← fix: set judul modal sesuai tipe
                $('.save_btn_modal').hide();
                $('#ModalView').html(result);
                $('#dialog-popup').modal('show');
            },
            error: showAjaxError
        });
    });

    $(document).on('click', '.btn-search-il', function() {
        var target = $(this).data('target');
        var select = $(this).data('select');
        var url = $(this).data('url');
        var supplier = $('#' + select).val();

        if (!supplier) {
            Swal.fire({
                title: 'Warning!',
                text: 'Pilih supplier terlebih dahulu!',
                icon: 'warning'
            });
            return;
        }
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + url,
            data: {
                kode_supplier: supplier
            },
            cache: false,
            success: function(result) {
                $('.' + target).html(result);
            },
            error: showAjaxError
        });
    });

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();
        var tipe_req = $('#frm-data input[name="tipe_req"]').val();
        var url_save;
        if (tipe_req === 'dp') {
            url_save = siteurl + active_controller + 'save_dp';
        } else if (tipe_req === 'import') {
            url_save = siteurl + active_controller + 'save_import';
        } else {
            url_save = siteurl + active_controller + 'save_local';
        }

        var currency = $('#frm-data input[name="currency"]').val();
        var kurs = parseFloat(($('#frm-data input[name="kurs"]').val() || '0').replace(/,/g, ''));
        if (currency && currency.toUpperCase() !== 'IDR' && kurs <= 0) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Kurs wajib diisi jika currency bukan IDR!',
                icon: 'warning'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi',
            text: 'Invoice akan disimpan, lanjutkan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            var formdata = new FormData($('#frm-data')[0]);
            $.ajax({
                type: 'POST',
                url: url_save,
                data: formdata,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.status == 1) {
                        Swal.fire({
                                title: 'Berhasil!',
                                text: res.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            })
                            .then(function() {
                                location.reload();
                            });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: res.message,
                            icon: 'error'
                        });
                    }
                },
                error: showAjaxError
            });
        });
    });

    $(document).on('shown.bs.modal', '#dialog-popup', function() {
        $('#dialog-popup .fp-date').flatpickr({
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',
            allowInput: false,
            locale: 'id'
        });
    });
</script>