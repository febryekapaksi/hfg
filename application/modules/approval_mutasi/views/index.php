<?php
$ENABLE_MANAGE = has_permission('Approval_mutasi.Manage');
$ENABLE_VIEW   = has_permission('Approval_mutasi.View');
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

    .skeleton-line.short {
        width: 60%;
    }

    .skeleton-line.medium {
        width: 80%;
    }

    @keyframes shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>

<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs mb-3" id="ApprovalTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab"
                    data-bs-target="#tab-pending" type="button" role="tab">
                    <i class="fa-solid fa-clock text-warning"></i> Menunggu Approval
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab"
                    data-bs-target="#tab-approved" type="button" role="tab">
                    <i class="fa-solid fa-check-circle text-success"></i> Approved
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="revision-tab" data-bs-toggle="tab"
                    data-bs-target="#tab-revision" type="button" role="tab">
                    <i class="fa-solid fa-rotate-left text-info"></i> Revisi
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rejected-tab" data-bs-toggle="tab"
                    data-bs-target="#tab-rejected" type="button" role="tab">
                    <i class="fa-solid fa-times-circle text-danger"></i> Rejected
                </button>
            </li>
        </ul>

        <div class="tab-content" id="ApprovalTabsContent">

            <div class="tab-pane fade show active" id="tab-pending" role="tabpanel">
                <div id="skeleton-pending">
                    <div class="skeleton skeleton-line medium"></div>
                    <div class="skeleton skeleton-line"></div>
                    <div class="skeleton skeleton-line short"></div>
                </div>
                <div id="content-pending" style="display:none;"></div>
            </div>

            <div class="tab-pane fade" id="tab-approved" role="tabpanel">
                <div id="skeleton-approved">
                    <div class="skeleton skeleton-line medium"></div>
                    <div class="skeleton skeleton-line"></div>
                    <div class="skeleton skeleton-line short"></div>
                </div>
                <div id="content-approved" style="display:none;"></div>
            </div>

            <div class="tab-pane fade" id="tab-revision" role="tabpanel">
                <div id="skeleton-revision">
                    <div class="skeleton skeleton-line medium"></div>
                    <div class="skeleton skeleton-line"></div>
                    <div class="skeleton skeleton-line short"></div>
                </div>
                <div id="content-revision" style="display:none;"></div>
            </div>

            <div class="tab-pane fade" id="tab-rejected" role="tabpanel">
                <div id="skeleton-rejected">
                    <div class="skeleton skeleton-line medium"></div>
                    <div class="skeleton skeleton-line"></div>
                    <div class="skeleton skeleton-line short"></div>
                </div>
                <div id="content-rejected" style="display:none;"></div>
            </div>

        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const BASE_URL = siteurl + active_controller;

    function loadTab(tab) {
        const skeleton = $('#skeleton-' + tab);
        const content = $('#content-' + tab);

        if (content.data('loaded')) return;

        skeleton.show();
        content.hide();

        $.get(BASE_URL + '/render_' + tab, function(html) {
            skeleton.hide();
            content.html(html).show();
            content.data('loaded', true);
        });
    }

    $(document).ready(function() {
        loadTab('pending');

        $('#approved-tab').on('shown.bs.tab', function() { loadTab('approved'); });
        $('#revision-tab').on('shown.bs.tab', function() { loadTab('revision'); });
        $('#rejected-tab').on('shown.bs.tab', function() { loadTab('rejected'); });
    });

    function reloadTab(tab) {
        $('#content-' + tab).data('loaded', false);
        loadTab(tab);
    }

    function reloadAllTabs() {
        ['pending', 'approved', 'revision', 'rejected'].forEach(function(tab) {
            $('#content-' + tab).data('loaded', false);
        });
        loadTab('pending');
    }
</script>
