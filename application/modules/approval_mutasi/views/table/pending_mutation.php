<?php
$ENABLE_MANAGE = has_permission('Approval_mutation.Manage');
$ENABLE_VIEW   = has_permission('Approval_mutation.View');
?>

<div class="table-responsive">
    <table class="table table-hover table-bordered align-middle" id="tblPending" style="width: 100%;">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Mutation No.</th>
                <th>Date</th>
                <th>Requested By</th>
                <th>Source Warehouse</th>
                <th>Destination Warehouse</th>
                <th>Description</th>
                <th width="20">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($list as $i => $row): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= $row['mutation_number'] ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($row['mutation_date'])) ?></td>
                    <td>
                        <?= htmlspecialchars($row['create_by']) ?><br>
                        <small class="text-muted"><i><?= date('d/m/Y H:i', strtotime($row['create_date'])) ?></i></small>
                    </td>
                    <td><?= $row['nm_gudang_from'] ?></td>
                    <td><?= $row['nm_gudang_to'] ?></td>
                    <td><?= $row['description'] ?? '-' ?></td>
                    <td class="text-center">
                        <div class="d-flex gap-1 flex-wrap">
                            <?php if ($ENABLE_VIEW): ?>
                                <a href="<?= site_url('approval_mutasi/detail/' . $row['id']) ?>"
                                    class="btn btn-sm btn-primary" title="Review & Approve">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#tblPending')) {
            $('#tblPending').DataTable({
                "destroy": true,
                "responsive": true,
                "order": []
            });
        }
    });
</script>