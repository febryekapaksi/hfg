<?php
$pembeda = substr($header[0]['so_number'], 0, 1);
$due_date = (!empty($header[0]['due_date'])) ? date('d F Y', strtotime($header[0]['due_date'])) : '-';
$tgl_dibutuhkan = (!empty($header[0]['tgl_dibutuhkan'])) ? date('d F Y', strtotime($header[0]['tgl_dibutuhkan'])) : '-';

$alasan_reject3 = (!empty($header)) ? $header[0]['reject_reason3'] : '';
$keterangan_3 = (!empty($header)) ? $header[0]['keterangan_3'] : '';

$status3 = '';
$tgl_appre_3 = '';
if (!empty($header[0]['app_3']) && $header[0]['app_3'] == '1') {
    $status3 = '<div class="badge bg-green">Approved</div>';
    $tgl_appre_3 = date('d F Y', strtotime($header[0]['app_3_date']));
} elseif (!empty($header[0]['sts_reject3']) && $header[0]['sts_reject3'] == '1') {
    $status3 = '<div class="badge bg-red">Rejected</div>';
    $tgl_appre_3 = date('d F Y', strtotime($header[0]['sts_reject3_date']));
} else {
    $status3 = '<div class="badge bg-blue">Waiting Approval</div>';
}
?>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form id="data-form" method="post" autocomplete="off">
            <input type="hidden" name='so_number' id='so_number' value='<?= $header[0]['so_number']; ?>'>
            <input type="hidden" name="tingkat_approval" id="tingkat_approval" value="3">

            <!-- Tabel Informasi SO -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="so_number" class="form-label"><b>No. Request/SO</b></label>
                    <input type="text" class="form-control" id="so_number" value="<?= $header[0]['so_number']; ?>" readonly>
                </div>

                <div class="col-md-6">
                    <label for="no_pr" class="form-label"><b>No. PR</b></label>
                    <input type="text" class="form-control" id="no_pr" value="<?= $header[0]['no_pr']; ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="tgl_dibutuhkan" class="form-label"><b>Required Date</b></label>
                    <input type="text" class="form-control" id="tgl_dibutuhkan" value="<?= $tgl_dibutuhkan; ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="tingkat_pr" class="form-label"><b>PR Priority</b></label>
                    <input type="text" class="form-control" id="tingkat_pr" value="<?= ($header[0]['tingkat_pr'] == 2) ? 'Urgent' : 'Normal' ?>" readonly>
                </div>

                <div class="col-md-6" hidden>
                    <label for="name_customer" class="form-label"><b>Customer</b></label>
                    <input type="text" class="form-control" id="name_customer" value="<?= $header[0]['name_customer']; ?>" readonly>
                </div>
                <div class="col-md-6" hidden>
                    <label for="due_date" class="form-label"><b>Due Date SO</b></label>
                    <input type="text" class="form-control" id="due_date" value="<?= $due_date; ?>" readonly>
                </div>
            </div>

            <!-- Tabel Approval -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead>
                        <tr class="table-light">
                            <th class="text-center">Approval By</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Approval / Reject Date</th>
                            <th class="text-center">Rejection Reason</th>
                            <th class="text-center">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">Management</td>
                            <td class="text-center"><?= $status3 ?></td>
                            <td class="text-center"><?= $tgl_appre_3 ?></td>
                            <td>
                                <input type="text" name="reject_reason3" class="form-control" value="<?= $alasan_reject3 ?>" readonly>
                            </td>
                            <td>
                                <input type="text" name="keterangan_3" class="form-control" value="<?= $keterangan_3 ?>">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Tabel Detail Material -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center"><input type="checkbox" name="chk_all" id="chk_all"></th>
                            <th class="text-center">Material Name</th>
                            <th class="text-center">Alias Name</th>
                            <?php if ($pembeda == 'SO') { ?>
                                <th class="text-center">Estimation (Kg)</th>
                                <th class="text-center">Stock Free (Kg)</th>
                                <th class="text-center">Use Stock (Kg)</th>
                                <th class="text-center">Remaining Free Stock (Kg)</th>
                            <?php } ?>
                            <th class="text-center">Min Stock</th>
                            <th class="text-center">Max Stock</th>
                            <th class="text-center">Min Order</th>
                            <th class="text-center">Qty PR</th>
                            <th class="text-center">Note</th>
                            <th class="text-center">Qty Rev</th>
                            <th class="text-center">#</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detail as $key => $value) {
                            $key++;
                            $nm_material = $value['nama'];
                            $nm_lain = $value['trade_name'];
                            $stock_free = $value['stock_free'];
                            $use_stock = $value['use_stock'];
                            $sisa_free = $stock_free - $use_stock;
                            $propose = $value['propose_purchase'];
                        ?>
                            <tr>
                                <td class="text-center">
                                    <?php if ($value['status_app'] == 'N') { ?>
                                        <input type="checkbox" name="check[<?= $value['id'] ?>]" class="chk_personal" value="<?= $value['id'] ?>">
                                    <?php } ?>
                                </td>
                                <td class="text-left"><?= $nm_material ?>
                                    <input type="hidden" name="detail[<?= $key ?>][id]" value="<?= $value['id'] ?>">
                                </td>
                                <td><?= $nm_lain ?></td>
                                <?php if ($pembeda == 'SO') { ?>
                                    <td class="text-right qty_order"><?= number_format($value['qty_order'], 5) ?></td>
                                    <td class="text-right stock_free"><?= number_format($stock_free, 5) ?></td>
                                    <td class="text-right stock_free"><?= number_format($use_stock, 5) ?></td>
                                    <td class="text-right sisa_free"><?= number_format($sisa_free, 5) ?></td>
                                <?php } ?>
                                <td class="text-right min_stok"><?= number_format($value['min_stok'], 2) ?></td>
                                <td class="text-right max_stok"><?= number_format($value['max_stok'], 2) ?></td>
                                <td class="text-right min_order"><?= number_format(0, 2) ?></td>
                                <td class="text-right"><?= number_format($propose, 2) ?></td>
                                <td class="text-left"><?= $value['note'] ?></td>
                                <td class="text-center">
                                    <?php if ($value['status_app'] == 'N') { ?>
                                        <input type="text" class="form-control input-sm text-center autoNumeric5 propose" id="pr_rev_<?= $value['id'] ?>" name="pr_rev_<?= $value['id'] ?>" value="<?= $propose ?>" style="width: 100px;">
                                    <?php } else { ?>
                                        <span><?= number_format($value['propose_rev'], 2) ?></span>
                                    <?php } ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($value['status_app'] == 'N') { ?>
                                        <button type="button" class="btn btn-sm btn-success processSatuan" data-id="<?= $value['id'] ?>" data-action="approve">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger processSatuan" data-id="<?= $value['id'] ?>" data-action="reject">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    <?php } elseif ($value['status_app'] == 'Y') { ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php } elseif ($value['status_app'] == 'D') { ?>
                                        <span class="badge bg-danger">Rejected</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Alasan Reject -->
            <div class="form-group row mb-3">
                <div class="col-md-12">
                    <label for="reject_reason" class="form-label">Reject Reason</label>
                    <textarea name="reject_reason" id="reject_reason" class="form-control form-control-sm"></textarea>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="form-group row">
                <div class="col-md-12 text-center">
                    <button type="button" class="btn btn-success" id="save">
                        <i class="fas fa-check-double"></i> Approve
                    </button>
                    <button type="button" class="btn btn-danger" id="reject">
                        <i class="fa fa-ban me-1"></i> Reject
                    </button>
                    <button type="button" class="btn btn-dark" id="back">
                        <i class="fa fa-reply me-1"></i> Back
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Detail -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title" id="head_title"><i class="fa fa-users"></i>&nbsp;Detail Data</h5>
            </div>
            <div class="modal-body" id="ModalView">...</div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<style>
    .datepicker {
        cursor: pointer;
    }

    textarea {
        resize: none;
    }
</style>

<script type="text/javascript">
    // Inisialisasi
    $(document).ready(function() {
        $('.datepicker').datepicker({
            dateFormat: 'dd-M-yy'
        });

        $('.autoNumeric5').autoNumeric('init', {
            mDec: '5',
            aPad: false
        });

        $('.chosen-select').select2();

        // Handle check all functionality
        $("#chk_all").click(function() {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });

        // Handle 'back' button
        $('#back').click(function() {
            window.location.href = base_url + active_controller;
        });

        // Handle 'approve' button
        $('#save').click(function(e) {
            e.preventDefault();
            if ($('.chk_personal:checked').length == 0) {
                swal({
                    title: "Error Message!",
                    text: 'Please check at least one item!',
                    type: "warning"
                });
                return false;
            }

            swal({
                    title: "Are you sure?",
                    text: "You will not be able to process again this data!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Yes, Process it!",
                    cancelButtonText: "No, cancel process!",
                    closeOnConfirm: true,
                    closeOnCancel: false
                },
                function(isConfirm) {
                    if (isConfirm) {
                        var formData = new FormData($('#data-form')[0]);
                        var baseurl = siteurl + active_controller + '/process_approval_all';
                        $.ajax({
                            url: baseurl,
                            type: "POST",
                            data: formData,
                            cache: false,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function(data) {
                                if (data.status == 1) {
                                    swal({
                                        title: "Save Success!",
                                        text: data.pesan,
                                        type: "success",
                                        timer: 7000
                                    });
                                    window.location.href = base_url + active_controller;
                                } else {
                                    swal({
                                        title: "Save Failed!",
                                        text: data.pesan,
                                        type: "warning",
                                        timer: 7000
                                    });
                                }
                            },
                            error: function() {
                                swal({
                                    title: "Error Message!",
                                    text: 'An Error Occured During Process. Please try again..',
                                    type: "warning",
                                    timer: 7000
                                });
                            }
                        });
                    } else {
                        swal("Cancelled", "Data can be process again :)", "error");
                    }
                });
        });

        // Handle 'reject' button
        $('#reject').click(function(e) {
            e.preventDefault();

            swal({
                    title: "Are you sure?",
                    text: "This PR will be rejected !",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Yes, Process it!",
                    cancelButtonText: "No, cancel process!",
                    closeOnConfirm: true,
                    closeOnCancel: false
                },
                function(isConfirm) {
                    if (isConfirm) {
                        var formData = new FormData($('#data-form')[0]);
                        var baseurl = siteurl + active_controller + '/process_reject';
                        $.ajax({
                            url: baseurl,
                            type: "POST",
                            data: formData,
                            cache: false,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function(data) {
                                if (data.status == 1) {
                                    swal({
                                        title: "Reject Success!",
                                        text: data.pesan,
                                        type: "success",
                                        timer: 7000
                                    });
                                    window.location.href = base_url + active_controller;
                                } else {
                                    swal({
                                        title: "Reject Failed!",
                                        text: data.pesan,
                                        type: "warning",
                                        timer: 7000
                                    });
                                }
                            },
                            error: function() {
                                swal({
                                    title: "Error Message!",
                                    text: 'An Error Occured During Process. Please try again..',
                                    type: "warning",
                                    timer: 7000
                                });
                            }
                        });
                    } else {
                        swal("Cancelled", "Data can be process again :)", "error");
                    }
                });
        });
    });
</script>