<?php
$ENABLE_VIEW   = has_permission('Request_List.View');
$ENABLE_MANAGE = has_permission('Request_List.Manage');
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
<div id="skeleton-content">
    <div class="skeleton">
        <div class="skeleton-line" style="width:60%"></div>
        <div class="skeleton-line" style="width:100%;height:200px"></div>
    </div>
</div>

<!-- Actual content (hidden until loaded) -->
<div id="actual-content" style="display:none">
    <div class="card">
        <div class="card-body">
            <table id="table-request-list" class="table table-bordered table-striped" width="100%">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>SPK No</th>
                        <th>Tanggal SPK</th>
                        <th>Shift</th>
                        <th>Status Coil</th>
                        <th>Jumlah Produk</th>
                        <th width="18%">Aksi</th>
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
        var tableRequestList = $('#table-request-list').DataTable({
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
                $('#skeleton-content').hide();
                $('#actual-content').fadeIn();
            }
        });

    });
</script>