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
        <?php $no = 1; foreach ($list_po as $item): ?>
            <?php
                $sudah_receive = !empty($item['id_receive_dp']);
                $sudah_request = !empty($item['id_request_payment']);
                $sudah_bayar   = !empty($item['no_payment']) && $item['status_payment'] == 2;

                if (!$sudah_receive) {
                    $badge = '<span class="badge bg-secondary">Belum Receive</span>';
                    $action = '
                        <button type="button" class="btn btn-sm btn-primary btn-req-dp"
                            data-id_top="' . $item['id_top'] . '"
                            data-no_po="' . $item['no_po'] . '"
                            title="Receive Invoice DP">
                            <i class="fa fa-plus"></i>
                        </button>';

                } elseif ($sudah_receive && !$sudah_request) {
                    $badge = '<span class="badge bg-info text-dark">Sudah Receive</span>';
                    $action = '
                        <button type="button" class="btn btn-sm btn-info btn-view-dp"
                            data-id="' . $item['id_receive_dp'] . '"
                            title="Lihat Invoice">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-warning ms-1 btn-req-payment"
                            data-id_receive="' . $item['id_receive_dp'] . '"
                            data-tipe="dp"
                            title="Request Payment">
                            <i class="fa fa-paper-plane"></i>
                        </button>';

                } elseif ($sudah_request && !$sudah_bayar) {
                    $badge = '<span class="badge bg-warning text-dark">Menunggu Pembayaran</span>';
                    $action = '
                        <button type="button" class="btn btn-sm btn-info btn-view-dp"
                            data-id="' . $item['id_receive_dp'] . '"
                            title="Lihat Invoice">
                            <i class="fa fa-eye"></i>
                        </button>';

                } else {
                    $badge = '<span class="badge bg-success">Lunas</span>';
                    $action = '
                        <button type="button" class="btn btn-sm btn-info btn-view-dp"
                            data-id="' . $item['id_receive_dp'] . '"
                            title="Lihat Invoice">
                            <i class="fa fa-eye"></i>
                        </button>';
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
                <td class="text-center"><?= $action ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
// Re-init DataTable setelah search
if ($.fn.DataTable.isDataTable('.table_req_pay_dp')) {
    $('.table_req_pay_dp').DataTable().destroy();
}
$('.table_req_pay_dp').DataTable();
</script>