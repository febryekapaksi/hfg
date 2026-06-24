<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="<?= base_url('assets/chosen_v1.8.7/chosen.min.css') ?>">
<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between">
            <span class="pull-left">
                <h4>Receive Invoice by DP</h4>
            </span>
            <span class="pull-right">
                <button type="button" class="btn btn-sm btn-danger clear_checked_invoice">Clear Checked Invoice</button>
                <button type="button" class="btn btn-sm btn-success rec_invoice_btn">Receive Invoice</button>
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="req_payment_inc">
            <div class="col_table">
                <table class="table table-bordered table_req_pay_inc">
                    <thead class="bg-blue">
                        <tr>
                            <th style="text-align: center;"></th>
                            <th class="text-center">No</th>
                            <th class="text-center">No. PO</th>
                            <th class="text-center">No. PR</th>
                            <th class="text-center">Harga DP PO</th>
                            <th class="text-center">Nama Supplier</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (!empty($list_dp)) {
                            foreach ($list_dp as $item) {
                                $checked = '';
                                $checked_invoice = $this->db->get_where('tr_check_invoice', [
                                    'kode_trans' => $item['no_po'],
                                    'id_user'    => $this->auth->user_id()
                                ])->row();

                                if (!empty($checked_invoice)) {
                                    $checked = 'checked';
                                }
                                $check_box = '<input type="checkbox" name="check_invoice[]" class="check_invoice" data-kode_trans="' . $item['no_po'] . '" data-tipe_incoming="dp" value="' . $item['no_po'] . '" ' . $checked . '>';
                                $harga_po = isset($item['nilai']) ? $item['nilai'] : 0;
                        ?>
                                <tr>
                                    <td style="text-align: center;"><?= $check_box; ?></td>
                                    <td style="text-align: center;"><?= $no; ?></td>
                                    <td style="text-align: center;"><?= !empty($item['no_surat']) ? $item['no_surat'] : $item['no_po']; ?></td>
                                    <td style="text-align: center;"><?= !empty($item['no_pr']) ? $item['no_pr'] : '-'; ?></td>
                                    <td style="text-align: right;"><?= number_format($harga_po); ?></td>
                                    <td style="text-align: center;"><?= !empty($item['nama_supplier']) ? $item['nama_supplier'] : '-'; ?></td>
                                </tr>
                            <?php
                                $no++;
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data PO dengan Group TOP 76 (DP)</td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="" method="post" id="frm-data">
                <div class="modal-header">
                    <h4 class="modal-title">Receive Invoice by DP</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="ModalView">

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary save_btn_modal"><i class="fa fa-save"></i> Save</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        <span class="glyphicon glyphicon-remove"></span> Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/chosen_v1.8.7/chosen.jquery.min.js') ?>"></script>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('.table_req_pay_inc').dataTable();

        $('#select_supplier').chosen();
    });

    function checkCheckedInv() {
        var jqXHR = $.ajax({
            url: siteurl + active_controller + 'checkCheckedInv',
            async: false
        });

        return jqXHR.responseText;
    }

    $(document).on('click', '.check_invoice', function() {
        var kode_trans = $(this).data('kode_trans');
        var tipe_incoming = $(this).data('tipe_incoming');

        if ($(this).is(':checked')) {
            var tipe = 1;
        } else {
            var tipe = 0;
        }

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'check_invoice',
            data: {
                'kode_trans': kode_trans,
                'tipe_incoming': tipe_incoming,
                'tipe': tipe
            },
            cache: false,
            dataType: 'json',
            success: function(result) {

            },
            error: function(result) {
                Swal.fire({
                    title: 'Error !',
                    text: 'Please try again later !',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    $(document).on('click', '.clear_checked_invoice', function() {
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'clear_checked_invoice',
            cache: false,
            dataType: 'json',
            success: function(result) {
                if (result.status == 1) {
                    Swal.fire({
                            title: 'Success !',
                            text: 'Your checked invoice has been removed !',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        })
                        .then(function() {
                            location.reload(true);
                        });
                } else {
                    Swal.fire({
                        title: 'Warning !',
                        text: 'Your checked invoice cannot removed !',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(result) {
                Swal.fire({
                    title: 'Error !',
                    text: 'Please try again later !',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    $(document).on('click', '.rec_invoice_btn', function() {

        var check_inv = checkCheckedInv();
        if (check_inv <= 0) {
            Swal.fire({
                title: 'Warning !',
                text: 'Please check at least 1 Invoice below !',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + 'rec_invoice_btn',
                cache: false,
                success: function(result) {
                    $('#ModalView').html(result);
                    $('#dialog-popup').modal('show');
                },
                error: function(result) {

                }
            });
        }
    });

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        Swal.fire({
            title: "Warning !",
            text: "PO Invoice will be created !",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Create it!",
            cancelButtonText: "Cancel!"
        }).then(function(result) {
            if (result.isConfirmed) {
                var formdata = new FormData($('#frm-data')[0]);
                $.ajax({
                    type: 'POST',
                    url: siteurl + active_controller + '/save_invoice',
                    data: formdata,
                    cache: false,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        if (result.status == 1) {
                            Swal.fire({
                                    title: 'Success !',
                                    text: 'PO Invoice has been saved !',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                })
                                .then(function() {
                                    window.location.href = siteurl + active_controller;
                                });
                        } else {
                            Swal.fire({
                                title: 'Failed !',
                                text: 'PO Invoice has not been saved !',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(result) {
                        Swal.fire({
                            title: 'Error !',
                            text: 'Please try again later !',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });
</script>