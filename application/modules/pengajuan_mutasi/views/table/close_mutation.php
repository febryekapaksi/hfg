<?php
$ENABLE_VIEW = has_permission('Request_Mutation.View');

$status_label = [
    2 => ['label' => 'Approved', 'class' => 'bg-success'],
    4 => ['label' => 'Done',     'class' => 'bg-dark'],
];
?>

<div class="table-responsive">
    <table class="table table-hover table-bordered align-middle" id="tblClose" style="width: 100%;">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Mutation No.</th>
                <th>Date</th>
                <th>Source Warehouse</th>
                <th>Destination Warehouse</th>
                <th>Description</th>
                <th>Approved By</th>
                <th>Status</th>
                <th width="20">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($list)): ?>
                <?php foreach ($list as $i => $row): ?>
                    <?php $s = $status_label[$row['status']] ?? ['label' => '-', 'class' => 'bg-secondary']; ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= ($row['mutation_number']) ?></strong></td>
                        <td><?= date('d/m/Y', strtotime($row['mutation_date'])) ?></td>
                        <td><?= ($row['nm_gudang_from']) ?></td>
                        <td><?= ($row['nm_gudang_to']) ?></td>
                        <td><?= ($row['description'] ?? '-') ?></td>
                        <td><?= ($row['approved_by'] ?? '-') ?></td>
                        <td><span class="badge <?= $s['class'] ?>"><?= $s['label'] ?></span></td>
                        <td class="text-center">
                            <?php if ($ENABLE_VIEW): ?>
                                <a href="<?= site_url('pengajuan_mutasi/form/view/' . $row['id']) ?>"
                                    class="btn btn-sm btn-info" title="View">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="<?= site_url('pengajuan_mutasi/print_qr/' . $row['id']) ?>"
                                    class="btn btn-sm btn-dark" title="Print QR Label" target="_blank">
                                    <i class="fa-solid fa-qrcode"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#tblClose')) {
            $('#tblClose').DataTable({
                "destroy": true,
                "responsive": true,
                "order": []
            });
        }
    });
</script>
