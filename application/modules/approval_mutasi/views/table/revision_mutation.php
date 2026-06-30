<?php
$ENABLE_VIEW = has_permission('Approval_mutation.View');
?>

<div class="table-responsive">
    <table class="table table-hover table-bordered align-middle" id="tblRevision" style="width: 100%;">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Mutation No.</th>
                <th>Date</th>
                <th>Source Warehouse</th>
                <th>Destination Warehouse</th>
                <th>Description</th>
                <th>Revision Notes</th>
                <th>Requested By</th>
                <th width="20">Action</th>
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
                    <td>
                        <span class="text-info">
                            <?= !empty($row['reject_reason']) ? htmlspecialchars($row['reject_reason']) : '-' ?>
                        </span>
                    </td>
                    <td><?= $row['create_by'] ?? '-' ?></td>
                    <td class="text-center">
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
        if (!$.fn.DataTable.isDataTable('#tblRevision')) {
            $('#tblRevision').DataTable({
                "destroy": true,
                "responsive": true,
                "order": []
            });
        }
    });
</script>