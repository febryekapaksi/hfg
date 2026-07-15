<link rel="stylesheet" href="<?= base_url('assets/chosen_v1.8.7/chosen.min.css') ?>">

<div class="req_payment_dp" style="margin-top: 2vh;">
    <div class="row mb-3">
        <div class="col-md-4 mt-3">
            <select name="supplier" id="select_supplier" class="form-control">
                <option value="">- Pilih Supplier -</option>
                <?php foreach ($list_supplier as $item_supp): ?>
                    <option value="<?= $item_supp->kode_supplier ?>"><?= $item_supp->nama ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 mt-3">
            <button type="button" class="btn btn-sm btn-primary search_dp">
                <i class="fa fa-search"></i> Cari
            </button>
        </div>
    </div>

    <div class="col_table">
        <table class="table table-bordered table-hover table-sm table_req_pay_dp">
            <thead class="table-primary">
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">No. PO</th>
                    <th class="text-center">No. Invoice</th>
                    <th class="text-center">No. Payment</th>
                    <th class="text-center">Nama Supplier</th>
                    <th class="text-center">Tanggal PO</th>
                    <th class="text-center">Keterangan</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach ($list_po as $item): ?>
                    <?php
                    $sudah_receive = !empty($item['id_receive_dp']);
                    $sudah_request = !empty($item['id_request_payment']);
                    $sudah_bayar   = !empty($item['no_payment']) && $item['status_payment'] == 'payment';

                    if (!$sudah_receive) {
                        // Belum receive invoice
                        $badge = '<span class="badge bg-secondary">Belum Receive</span>';
                        $action_btn = '
                        <button type="button" class="btn btn-sm btn-primary btn-req-dp"
                            data-id_top="' . $item['id_top'] . '"
                            data-no_po="' . $item['no_po'] . '"
                            title="Receive Invoice DP">
                            <i class="fa fa-plus"></i>
                        </button>';
                    } else {
                        // Jika sudah receive, tampilkan status ENUM
                        $status_enum = strtolower($item['status_receive_dp']);
                        $status_uc   = ucwords($status_enum);

                        if ($status_enum === 'draft') {
                            $badge = '<span class="badge bg-info text-dark">Draft</span>';
                        } elseif ($status_enum === 'payment') {
                            $badge = '<span class="badge bg-success">Payment</span>';
                        } else {
                            $badge = '<span class="badge bg-warning text-dark">' . $status_uc . '</span>';
                        }

                        $action_btn = '
                        <button type="button" class="btn btn-sm btn-info btn-view-dp"
                            data-id="' . $item['id_receive_dp'] . '"
                            title="Lihat Invoice">
                            <i class="fa fa-eye"></i>
                        </button>';

                        if ($status_enum === 'draft') {
                            $action_btn .= '
                            <button type="button" class="btn btn-sm btn-warning btn-req-payment ms-1"
                                data-id_receive="' . $item['id_receive_dp'] . '"
                                data-tipe="dp"
                                title="Request Payment">
                                <i class="fa fa-paper-plane"></i>
                            </button>';
                        }
                    }
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="text-center"><?= $item['no_surat'] ?></td>
                        <td class="text-center"><?= $item['nomor_invoice'] ?? '-' ?></td>
                        <td class="text-center"><?= $item['no_payment'] ?? '-' ?></td>
                        <td><?= $item['nm_supplier'] ?></td>
                        <td class="text-center"><?= date('d F Y', strtotime($item['tanggal'])) ?></td>
                        <td><?= $item['keterangan_top'] ?? '-' ?></td>
                        <td class="text-center"><?= $badge ?></td>
                        <td class="text-center"><?= $action_btn ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="<?= base_url('assets/chosen_v1.8.7/chosen.jquery.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        $('.table_req_pay_dp').DataTable();
        $('#select_supplier').chosen();

        $(document).on('click', '.btn-req-payment', function() {
            var id_receive = $(this).data('id_receive');
            var tipe = $(this).data('tipe');

            Swal.fire({
                title: 'Request Payment',
                text: 'Ajukan request payment untuk invoice ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Ajukan',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: siteurl + active_controller + 'req_payment_dp',
                        data: {
                            id_receive: id_receive,
                            tipe: tipe
                        },
                        cache: false,
                        success: function(res) {
                            var response = typeof res === 'string' ? JSON.parse(res) : res;
                            if (response.status == 1) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false,
                                }).then(function() {
                                    // Refresh tabel dengan supplier yang sedang aktif
                                    var supplier = $('#select_supplier').val();
                                    if (supplier) {
                                        $('.search_dp').trigger('click');
                                    } else {
                                        location.reload();
                                    }
                                });
                            } else {
                                Swal.fire('Gagal', response.message, 'error');
                            }
                        },
                        error: showAjaxError
                    });
                }
            });
        });
    });

    $(document).on('click', '.btn-req-dp', function() {
        var id_top = $(this).data('id_top');
        var no_po = $(this).data('no_po');

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'form_dp',
            data: {
                id_top: id_top,
                no_po: no_po
            },
            cache: false,
            success: function(result) {
                $('#ModalView').html(result);
                $('#dialog-popup').modal('show');
            },
            error: function() {
                showAjaxError();
            }
        });
    });

    $(document).on('click', '.btn-view-dp', function() {
        var id = $(this).data('id');

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'view_dp',
            data: {
                id: id
            },
            cache: false,
            success: function(result) {
                $('.save_btn_modal').hide();
                $('#ModalView').html(result);
                $('#dialog-popup').modal('show');
            },
            error: function() {
                showAjaxError();
            }
        });
    });

    $(document).on('click', '.search_dp', function() {
        var supplier = $('#select_supplier').val();
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
            url: siteurl + active_controller + 'search_dp',
            data: {
                kode_supplier: supplier
            },
            cache: false,
            success: function(result) {
                // Ganti seluruh col_table dengan hasil partial
                $('.col_table').html(result);
            },
            error: showAjaxError
        });
    });
</script>