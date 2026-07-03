<?php
$ENABLE_MANAGE = has_permission('Confirm_Spk_Coil.Manage');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

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
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .scan-input-wrapper {
        max-width: 600px;
        margin: 0 auto;
    }

    .scan-input-wrapper input {
        font-size: 1.5rem;
        padding: 15px 20px;
        text-align: center;
        letter-spacing: 1px;
    }

    .scan-count {
        font-size: 1.1rem;
        font-weight: 600;
    }

    .badge-scanned {
        background-color: #28a745;
        color: #fff;
    }

    .badge-not-scanned {
        background-color: #dc3545;
        color: #fff;
    }
</style>

<!-- Skeleton loading -->
<div id="skeleton-confirm-detail">
    <div class="skeleton">
        <div class="skeleton-line" style="width:60%"></div>
        <div class="skeleton-line" style="width:100%;height:80px"></div>
        <div class="skeleton-line" style="width:100%;height:250px"></div>
    </div>
</div>

<!-- Actual content (hidden until loaded) -->
<div id="content-confirm-detail" style="display:none">

    <!-- Back Button -->
    <div class="mb-3">
        <a href="<?= site_url('confirm_spk_coil') ?>" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Header Card -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>SPK Coil No</strong>
                    <p class="mb-0"><?= htmlspecialchars(isset($request['spk_coil_no']) ? $request['spk_coil_no'] : '-') ?></p>
                </div>
                <div class="col-md-3">
                    <strong>SPK Material No</strong>
                    <p class="mb-0"><?= htmlspecialchars(isset($request['spk_material_no']) ? $request['spk_material_no'] : (isset($request['spk_no']) ? $request['spk_no'] : '-')) ?></p>
                </div>
                <div class="col-md-3">
                    <strong>Tanggal</strong>
                    <p class="mb-0"><?= isset($request['tgl_spk']) ? date('d/m/Y', strtotime($request['tgl_spk'])) : '-' ?></p>
                </div>
                <div class="col-md-3">
                    <strong>Status</strong><br>
                    <?php
                    $status = isset($request['status']) ? $request['status'] : '-';
                    $badge_class = 'bg-info text-dark';
                    if ($status == 'Material Confirmed') {
                        $badge_class = 'bg-success text-white';
                    } elseif ($status == 'Material On Load') {
                        $badge_class = 'bg-warning text-dark';
                    }
                    ?>
                    <span class="badge rounded-pill <?= $badge_class ?>"><?= htmlspecialchars($status) ?></span>
                </div>
            </div>
            <?php if (!empty($request['shift_names'])): ?>
            <div class="row mt-2">
                <div class="col-md-3">
                    <strong>Shift</strong>
                    <p class="mb-0"><?= htmlspecialchars($request['shift_names']) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Coil Table -->
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0">Daftar Coil</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table-coil-detail">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th>Kode Internal</th>
                            <th>No Coil</th>
                            <th>Material</th>
                            <th>Sumber Gudang</th>
                            <th class="text-center">Plan Use</th>
                            <th class="text-center" width="15%">Status Scan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($coil_details as $coil): ?>
                        <tr id="coil-row-<?= $coil['id'] ?>">
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars(isset($coil['kode_internal']) ? $coil['kode_internal'] : '-') ?></td>
                            <td><?= htmlspecialchars(isset($coil['no_coil']) ? $coil['no_coil'] : '-') ?></td>
                            <td><?= htmlspecialchars(isset($coil['nm_material']) ? $coil['nm_material'] : (isset($coil['id_material']) ? $coil['id_material'] : '-')) ?></td>
                            <td>
                                <?php
                                $gudang_sumber = isset($coil['id_gudang_sumber']) ? $coil['id_gudang_sumber'] : '';
                                echo ($gudang_sumber == 1) ? 'Gudang Coil' : (($gudang_sumber == 3) ? 'WIP' : '-');
                                ?>
                            </td>
                            <td class="text-center"><?= isset($coil['plan_use']) ? $coil['plan_use'] : 0 ?></td>
                            <td class="text-center">
                                <input type="checkbox" class="coil-scan-check" data-id="<?= $coil['id'] ?>" disabled
                                    <?= ($coil['scan_status'] == 1) ? 'checked' : '' ?>>
                                <?php if ($coil['scan_status'] == 1): ?>
                                    <span class="badge badge-scanned ms-1">Sudah Scan</span>
                                <?php else: ?>
                                    <span class="badge badge-not-scanned ms-1" id="badge-<?= $coil['id'] ?>">Belum Scan</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Scan Input Section -->
    <?php if ($ENABLE_MANAGE): ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fa fa-qrcode"></i> Scan QR Code Coil</h6>
            <span class="scan-count">
                Scanned: <span id="scanned-count"><?= count(array_filter($coil_details, function($c){ return $c['scan_status'] == 1; })) ?></span> / <span id="total-count"><?= count($coil_details) ?></span>
            </span>
        </div>
        <div class="card-body">
            <div class="scan-input-wrapper">
                <input type="text" id="scan-input" class="form-control form-control-lg" 
                    placeholder="Scan atau ketik Kode Internal disini..." autofocus>
                <small class="form-text text-muted text-center d-block mt-2">Arahkan scanner ke QR Code atau tekan Enter setelah input manual</small>
            </div>
        </div>
    </div>

    <!-- Confirm Button -->
    <div class="text-center mb-4">
        <button type="button" id="btn-confirm" class="btn btn-primary btn-lg" disabled>
            <i class="fa fa-check-circle"></i> Confirm Pengeluaran Coil
        </button>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {

    const BASE_URL = siteurl + active_controller;
    const REQUEST_ID = '<?= $request['id'] ?>';

    // Show actual content after short delay (simulate skeleton)
    setTimeout(function() {
        $('#skeleton-confirm-detail').hide();
        $('#content-confirm-detail').fadeIn();
        $('#scan-input').focus();
    }, 300);

    // Track scanned count
    var scannedCount = parseInt($('#scanned-count').text()) || 0;
    var totalCount = parseInt($('#total-count').text()) || 0;

    // Check if all already scanned on load
    checkConfirmButton();

    // Scan input — trigger on Enter key
    $('#scan-input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            processScan($(this).val().trim());
        }
    });

    // Scan input — trigger on paste event
    $('#scan-input').on('paste', function(e) {
        var self = this;
        setTimeout(function() {
            processScan($(self).val().trim());
        }, 100);
    });

    // Process scan AJAX
    function processScan(kodeInternal) {
        if (!kodeInternal) {
            $('#scan-input').val('').focus();
            return;
        }

        $.ajax({
            url: BASE_URL + '/scan_coil',
            type: 'POST',
            data: {
                kode_internal: kodeInternal,
                request_id: REQUEST_ID
            },
            dataType: 'json',
            success: function(res) {
                if (res.status == 1) {
                    // Success — auto-check the corresponding row checkbox
                    var detailId = res.detail_id;
                    var $row = $('#coil-row-' + detailId);
                    $row.find('.coil-scan-check').prop('checked', true);
                    $row.find('.badge-not-scanned').removeClass('badge-not-scanned').addClass('badge-scanned').text('Sudah Scan');

                    // Update scanned count
                    scannedCount++;
                    $('#scanned-count').text(scannedCount);

                    // Toast notification
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Berhasil scan',
                        showConfirmButton: false,
                        timer: 1500
                    });

                    // Check if all scanned
                    checkConfirmButton();

                } else if (res.status == 0) {
                    // Not found
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Coil tidak ditemukan dalam SPK ini'
                    });

                } else if (res.status == 2) {
                    // Already scanned
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Coil sudah di-scan sebelumnya'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan jaringan. Silakan coba lagi.'
                });
            },
            complete: function() {
                // Clear input after each scan
                $('#scan-input').val('').focus();
            }
        });
    }

    // Check if confirm button should be enabled
    function checkConfirmButton() {
        var checkedCount = $('.coil-scan-check:checked').length;
        if (checkedCount >= totalCount && totalCount > 0) {
            $('#btn-confirm').prop('disabled', false);
        } else {
            $('#btn-confirm').prop('disabled', true);
        }
    }

    // Confirm button click
    $('#btn-confirm').on('click', function() {
        Swal.fire({
            title: 'Konfirmasi Pengeluaran',
            text: 'Apakah Anda yakin ingin mengkonfirmasi pengeluaran semua coil dalam SPK ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Konfirmasi',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/confirm/' + REQUEST_ID,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message || 'SPK Coil berhasil dikonfirmasi.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function() {
                                window.location.href = siteurl + 'confirm_spk_coil';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message || 'Gagal mengkonfirmasi. Silakan coba lagi.'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan jaringan. Silakan coba lagi.'
                        });
                    }
                });
            }
        });
    });

});
</script>
