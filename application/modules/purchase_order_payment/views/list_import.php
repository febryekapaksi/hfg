<?php
$tipe_btn   = 'import';
$id_select  = 'select_supplier_import';
$cls_table  = 'table_req_pay_import';
$cls_col    = 'col_table_import';
$url_search = 'search_import';
?>

<link rel="stylesheet" href="<?= base_url('assets/chosen_v1.8.7/chosen.min.css') ?>">

<div style="margin-top: 2vh;">
    <div class="row mb-3">
        <div class="col-md-4 mt-3">
            <select name="supplier" id="<?= $id_select ?>" class="form-control">
                <option value="">- Pilih Supplier -</option>
                <?php foreach ($list_supplier as $s): ?>
                    <option value="<?= $s->kode_supplier ?>"><?= $s->nama ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 mt-3">
            <button type="button" class="btn btn-sm btn-primary btn-search-il"
                data-target="<?= $cls_col ?>"
                data-select="<?= $id_select ?>"
                data-url="<?= $url_search ?>">
                <i class="fa fa-search"></i> Cari
            </button>
        </div>
    </div>

    <div class="<?= $cls_col ?>">
        <table class="table table-bordered table-hover table-sm <?= $cls_table ?>">
            <thead class="table-primary">
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">No. PO</th>
                    <th class="text-center">No. ROS</th>
                    <th class="text-center">No. Invoice</th>
                    <th class="text-center">No. Payment</th>
                    <th class="text-center">Nama Supplier</th>
                    <th class="text-center">Tanggal PO</th>
                    <th class="text-center">DP Sebelumnya (IDR)</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($list_po as $item): ?>
                <?php
                    if (empty($item['id_receive_il'])) {
                        $badge = '<span class="badge bg-secondary">Belum Receive</span>';
                    } elseif (!empty($item['no_payment'])) {
                        $badge = '<span class="badge bg-success">Lunas</span>';
                    } else {
                        $badge = '<span class="badge bg-warning text-dark">Menunggu Pembayaran</span>';
                    }

                    $dp_info = ((float)($item['total_dp_rupiah'] ?? 0) > 0)
                        ? '<span class="badge bg-info text-dark">Rp ' . number_format($item['total_dp_rupiah'], 0) . '</span>'
                        : '<span class="badge bg-light text-dark border">Tidak Ada DP</span>';

                    if (empty($item['id_receive_il'])) {
                        $action_btn = '
                            <button type="button"
                                class="btn btn-sm btn-primary btn-req-il"
                                data-id_top="' . ($item['id_top'] ?? '') . '"
                                data-no_po="'  . $item['no_po']  . '"
                                data-tipe="'   . $tipe_btn       . '"
                                data-id_dp=""
                                data-id_ros="' . ($item['no_ros'] ?? '') . '"
                                title="Receive Invoice Import">
                                <i class="fa fa-plus"></i>
                            </button>';
                    } else {
                        $action_btn = '
                            <button type="button"
                                class="btn btn-sm btn-info btn-view-il"
                                data-id="'   . $item['id_receive_il'] . '"
                                data-tipe="' . $tipe_btn              . '"
                                title="Lihat Invoice">
                                <i class="fa fa-eye"></i>
                            </button>';
                    }
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center"><?= $item['no_surat'] ?></td>
                    <td class="text-center"><?= $item['no_ros'] ?? '-' ?></td>
                    <td class="text-center"><?= $item['nomor_invoice'] ?? '-' ?></td>
                    <td class="text-center"><?= $item['no_payment'] ?? '-' ?></td>
                    <td><?= $item['nm_supplier'] ?></td>
                    <td class="text-center"><?= date('d F Y', strtotime($item['tanggal'])) ?></td>
                    <td class="text-center"><?= $dp_info ?></td>
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
        $('.<?= $cls_table ?>').DataTable();
        $('#<?= $id_select ?>').chosen();
    });
</script>
