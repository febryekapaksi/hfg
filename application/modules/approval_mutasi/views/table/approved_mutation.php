<?php
$ENABLE_VIEW = has_permission('Approval_mutasi.View');
?>

<div class="table-responsive">
    <table class="table table-hover table-bordered align-middle" id="tblApproved" style="width: 100%;">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>No. Mutasi</th>
                <th>Tanggal</th>
                <th>Gudang Asal</th>
                <th>Gudang Tujuan</th>
                <th>Keterangan</th>
                <th>Approved By</th>
                <th>Tgl Approve</th>
                <th width="20">Aksi</th>
            </tr>
        </thead>
        <tbody>
                <?php foreach ($list as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= $row['mutation_number'] ?></strong></td>
                        <td><?= date('d/m/Y', strtotime($row['mutation_date'])) ?></td>
                        <td><?= $row['nm_gudang_from'] ?></td>
                        <td><?= $row['nm_gudang_to'] ?></td>
                        <td><?= $row['description'] ?? '-' ?></td>
                        <td><?= $row['approved_by'] ?? '-' ?></td>
                        <td><?= !empty($row['approved_date']) ? date('d/m/Y H:i', strtotime($row['approved_date'])) : '-' ?></td>
                        <td>
                            <?php if ($ENABLE_VIEW): ?>
                                <a href="<?= site_url('approval_mutasi/detail/' . $row['id']) ?>"
                                    class="btn btn-sm btn-info text-white" title="View Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#tblApproved')) {
            $('#tblApproved').DataTable({
                "destroy": true,
                "responsive": true,
                "order": []
            });
        }
    });
</script>
