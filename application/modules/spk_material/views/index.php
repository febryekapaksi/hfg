<?php
$ENABLE_ADD    = has_permission('Spk_Material.Add');
$ENABLE_MANAGE = has_permission('Spk_Material.Manage');
$ENABLE_VIEW   = has_permission('Spk_Material.View');
?>

<style>
    .skeleton {
        border-radius: 4px;
        animation: shimmer 1.5s infinite linear;
        background: linear-gradient(90deg, #f2f2f2 25%, #e0e0e0 50%, #f2f2f2 75%);
        background-size: 200% 100%;
    }

    .skeleton-line {
        height: 20px;
        margin: 8px 0;
        border-radius: 4px;
    }

    @keyframes shimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }
</style>

<!-- Skeleton loading -->
<div id="skeleton-spk">
    <div class="skeleton">
        <div class="skeleton-line" style="width:60%"></div>
        <div class="skeleton-line" style="width:100%;height:200px"></div>
    </div>
</div>

<!-- Actual content (hidden until loaded) -->
<div id="content-spk" style="display:none">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"></h5>
            <?php if ($ENABLE_ADD): ?>
                <a href="<?= site_url('spk_material/add') ?>" class="btn btn-sm btn-primary">
                    <i class="fa fa-plus"></i> Create SPK
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <table id="table-spk" class="table table-bordered table-striped" width="100%">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>SPK No</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Status</th>
                        <th>Jumlah Produk</th>
                        <th width="15%">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {

        const BASE_URL = siteurl + active_controller;

        // Initialize DataTables
        var tableSpk = $('#table-spk').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE_URL + '/data_side',
                type: 'GET'
            },
            columns: [{
                    data: 0,
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 1
                },
                {
                    data: 2
                },
                {
                    data: 3
                },
                {
                    data: 4,
                    className: 'text-center'
                },
                {
                    data: 5,
                    className: 'text-center'
                },
                {
                    data: 6,
                    orderable: false,
                    className: 'text-center'
                }
            ],
            order: [
                [1, 'desc']
            ],
            initComplete: function() {
                $('#skeleton-spk').hide();
                $('#content-spk').fadeIn();
            }
        });

        // Update Status action (handled in separate module)
    });
</script>