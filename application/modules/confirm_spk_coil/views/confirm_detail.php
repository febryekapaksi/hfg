<?php
$ENABLE_MANAGE = has_permission('Confirm_Spk_Coil.Manage');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

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

    /* ===== Info grid (header) ===== */
    .info-item {
        padding: 4px 0;
    }

    .info-item strong {
        display: block;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #6c757d;
        margin-bottom: 2px;
    }

    .info-item p {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }

    /* ===== Scan input (dibikin lebih proporsional, tidak sebesar sebelumnya) ===== */
    .scan-input-wrapper {
        max-width: 480px;
        margin: 0 auto;
    }

    .scan-input-wrapper input#scan-input {
        font-size: 1.05rem;
        padding: 12px 16px;
        letter-spacing: .5px;
        border-radius: 8px 0 0 8px !important;
        border: 2px solid #ced4da;
        border-right: none;
        transition: all .2s ease-in-out;
    }

    .scan-input-wrapper input#scan-input:focus {
        border-color: #007bff;
        box-shadow: none;
    }

    .scan-input-wrapper #btn-camera {
        border-radius: 0 8px 8px 0 !important;
        font-size: .95rem;
        font-weight: 600;
        box-shadow: none;
        padding: 0 18px;
    }

    @media (max-width: 576px) {
        .scan-input-wrapper input#scan-input {
            font-size: .95rem;
            padding: 10px 12px;
        }

        .scan-input-wrapper #btn-camera span {
            display: none;
        }
    }


    /* ===== Status Pill Component (Premium Look) ===== */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        font-size: 0.8rem;
        font-weight: 700;
        border-radius: 30px;
        letter-spacing: 0.3px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    /* State: Belum Scan (Soft Red) */
    .status-pill.not-scanned {
        background-color: #e63946 !important;
        color: #ffffff !important;
        border: 1px solid #d62828;
        position: relative;
        /* Menjalankan animasi pulse merah terus menerus */
        animation: pulse-red 2s infinite;
    }

    .status-pill.not-scanned .status-dot {
        background-color: #e03131;
        box-shadow: 0 0 0 3px rgba(224, 49, 49, 0.2);
    }

    /* State: Sudah Scan (Soft Green dengan animasi sukses) */
    .status-pill.scanned {
        background-color: #ebfbee !important;
        color: #099268 !important;
        border: 1px solid #b2f2bb;
        animation: popIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .status-pill.scanned .status-dot {
        background-color: #099268;
        box-shadow: 0 0 0 3px rgba(9, 146, 104, 0.2);
    }

    /* Indikator Titik (Dot) di dalam Pill */
    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }

    /* Efek khusus untuk baris yang berhasil discan */
    .row-scanned-success {
        background-color: rgba(9, 146, 104, 0.04) !important;
    }

    /* Animasi ketika status berubah menjadi Sudah Scan */
    @keyframes popIn {
        0% {
            transform: scale(0.9);
            opacity: 0.5;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    /* ===== Styling tombol bawaan html5-qrcode di dalam modal kamera ===== */
    #qr-reader {
        border: none !important;
    }

    #qr-reader button,
    #qr-reader__dashboard_section_csr button,
    #qr-reader__dashboard_section_fsr button {
        background-color: #007bff !important;
        color: #fff !important;
        border: none !important;
        padding: 8px 18px !important;
        border-radius: 6px !important;
        font-weight: 500 !important;
        font-size: .9rem !important;
        cursor: pointer;
        transition: background-color .2s ease-in-out;
        margin: 6px 4px !important;
    }

    #qr-reader button:hover,
    #qr-reader__dashboard_section_csr button:hover,
    #qr-reader__dashboard_section_fsr button:hover {
        background-color: #0069d9 !important;
    }

    #qr-reader select {
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid #ced4da;
        font-size: .9rem;
        margin: 6px 4px;
    }

    #qr-reader__dashboard_section_swaplink,
    #html5-qrcode-anchor-scan-type-change {
        color: #007bff !important;
        text-decoration: none !important;
        font-size: .85rem !important;
        display: inline-block;
        margin-top: 8px !important;
    }

    #qr-reader__status_span {
        font-size: .8rem;
        color: #6c757d;
    }

    #qr-reader__dashboard_section_csr,
    #qr-reader__dashboard_section_fsr {
        text-align: center;
    }

    #qr-reader__filescan_input {
        margin: 8px 4px;
    }

    #qr-reader__camera_selection {
        margin: 6px 4px;
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
        <a href="<?= site_url('confirm_spk_coil') ?>" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Header + Scan Input Card (digabung jadi satu) -->
    <div class="card mb-3">
        <div class="card-body">

            <!-- Info SPK -->
            <div class="row gy-3 align-items-center">
                <div class="col-6 col-md info-item">
                    <strong>SPK Coil No</strong>
                    <p class="mb-0"><?= htmlspecialchars(isset($request['spk_coil_no']) ? $request['spk_coil_no'] : '-') ?></p>
                </div>
                <div class="col-6 col-md info-item">
                    <strong>SPK Material No</strong>
                    <p class="mb-0"><?= htmlspecialchars(isset($request['spk_material_no']) ? $request['spk_material_no'] : (isset($request['spk_no']) ? $request['spk_no'] : '-')) ?></p>
                </div>
                <div class="col-6 col-md info-item">
                    <strong>Tanggal</strong>
                    <p class="mb-0"><?= isset($request['tgl_spk']) ? date('d/m/Y', strtotime($request['tgl_spk'])) : '-' ?></p>
                </div>
                <div class="col-6 col-md info-item">
                    <strong>Status</strong>
                    <?php
                    $status = isset($request['status']) ? $request['status'] : '-';
                    $badge_class = 'bg-info text-dark';
                    if ($status == 'Material Confirmed') {
                        $badge_class = 'bg-success text-white';
                    } elseif ($status == 'Material On Load') {
                        $badge_class = 'bg-warning text-dark';
                    }
                    ?>
                    <div class="mt-1">
                        <span class="badge rounded-pill <?= $badge_class ?>"><?= htmlspecialchars($status) ?></span>
                    </div>
                </div>
                <?php if (!empty($request['shift_names'])): ?>
                    <div class="col-6 col-md info-item">
                        <strong>Shift</strong>
                        <p class="mb-0"><?= htmlspecialchars($request['shift_names']) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($ENABLE_MANAGE): ?>
                <hr class="my-3">

                <!-- Scan Input Section -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h6 class="mb-0"><i class="fa fa-qrcode"></i> Scan QR Code Coil</h6>
                    <span class="scan-count">
                        Scanned: <span id="scanned-count"><?= count(array_filter($coil_details, function ($c) {
                                                                return $c['scan_status'] == 1;
                                                            })) ?></span> / <span id="total-count"><?= count($coil_details) ?></span>
                    </span>
                </div>

                <div class="scan-input-wrapper">
                    <div class="input-group mb-2 d-flex align-items-stretch">
                        <input type="text" id="scan-input" class="form-control text-center"
                            placeholder="Scan atau ketik Kodenya disini..." autofocus>
                        <div class="input-group-append d-flex">
                            <button class="btn btn-primary d-flex align-items-center justify-content-center"
                                type="button" id="btn-camera" data-bs-toggle="modal" data-bs-target="#cameraModal" title="Gunakan Kamera">
                                <i class="fa fa-camera me-2"></i> <span class="d-none d-sm-inline">Camera</span>
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-muted text-center d-block mt-2">
                        <i class="fa fa-info-circle mr-1"></i> Tempel/ketik kode lalu tekan <strong>Enter</strong>, atau gunakan kamera
                    </small>
                </div>

                <!-- Confirm Button -->
                <div class="text-center mt-4">
                    <button type="button" id="btn-confirm" class="btn btn-primary btn-lg" disabled>
                        <i class="fa fa-check-circle"></i> Confirm Pengeluaran Coil
                    </button>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Camera Modal -->
    <div class="modal fade" id="cameraModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cameraModalLabel">Scan QR dengan Kamera</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="qr-reader" style="width:100%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Coil Table -->
    <div class="card mb-3">
        <div class="card-header m-1">
            <h4 class="mb-0">Daftar Coil</h4>
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
                            <th class="text-center" width="15%">Status Scan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($coil_details as $coil): ?>
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
                                <td class="text-center align-middle">
                                    <input type="checkbox" class="coil-scan-check d-none" data-id="<?= $coil['id'] ?>"
                                        <?= ($coil['scan_status'] == 1) ? 'checked' : '' ?>>

                                    <div class="d-flex align-items-center justify-content-center">
                                        <?php if ($coil['scan_status'] == 1): ?>
                                            <div class="status-pill scanned" id="status-pill-<?= $coil['id'] ?>">
                                                <i class="fa fa-check-circle"></i>
                                                <span>Sudah</span>
                                            </div>
                                        <?php else: ?>
                                            <div class="status-pill not-scanned" id="status-pill-<?= $coil['id'] ?>">
                                                <i class="fa fa-exclamation-triangle"></i>
                                                <span>Belum</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Fungsi untuk memutar suara success (2-tone chime)
    function playBeep() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();

            // Nada pertama (B5)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.connect(gain1);
            gain1.connect(ctx.destination);

            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(987.77, ctx.currentTime);
            gain1.gain.setValueAtTime(0.5, ctx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);

            osc1.start(ctx.currentTime);
            osc1.stop(ctx.currentTime + 0.15);

            // Nada kedua (E6) - lebih tinggi dan panjang
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.connect(gain2);
            gain2.connect(ctx.destination);

            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(1318.51, ctx.currentTime + 0.1);
            gain2.gain.setValueAtTime(0.5, ctx.currentTime + 0.1);
            gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.6);

            osc2.start(ctx.currentTime + 0.1);
            osc2.stop(ctx.currentTime + 0.6);
        } catch (e) {
            console.log("Audio not supported");
        }
    }

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

        // Scan input — trigger HANYA saat tekan Enter.
        // Ctrl+V / paste TIDAK memicu scan, hanya mengisi field seperti input biasa.
        $('#scan-input').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                processScan($(this).val().trim());
            }
        });

        var isProcessingScan = false;
        // Process scan AJAX
        function processScan(kodeInternal) {
            if (!kodeInternal) {
                $('#scan-input').val('').focus();
                return;
            }

            // Cegah submit dobel
            if (isProcessingScan) {
                return;
            }
            isProcessingScan = true;

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

                        // Play success sound
                        playBeep();

                        Swal.fire({
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
                    $('#scan-input').val('').focus();
                    isProcessingScan = false;
                }
            });
        }

        // Kamera QR logic
        let html5QrcodeScanner;
        const cameraModalEl = document.getElementById('cameraModal');
        const cameraModal = new bootstrap.Modal(cameraModalEl);

        cameraModalEl.addEventListener('shown.bs.modal', function() {
            if (!html5QrcodeScanner) {
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "qr-reader", {
                        fps: 10,
                        qrbox: {
                            width: 250,
                            height: 250
                        }
                    },
                    false);
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            } else {
                try {
                    html5QrcodeScanner.resume();
                } catch (e) {
                    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                }
            }
        });

        cameraModalEl.addEventListener('hidden.bs.modal', function() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().catch(error => {
                    console.error("Failed to clear html5QrcodeScanner. ", error);
                });
            }
        });

        function onScanSuccess(decodedText, decodedResult) {
            // Pause dulu supaya tidak scan berulang untuk kode yang sama
            if (html5QrcodeScanner) {
                try {
                    html5QrcodeScanner.pause(true);
                } catch (e) {
                    console.warn('Scanner sudah tidak aktif', e);
                }
            }

            $('#cameraModal').modal('hide');

            $('#scan-input').val(decodedText);
            processScan(decodedText);
        }

        function onScanFailure(error) {
            // handle scan failure, usually better to ignore and keep scanning.
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