<?php
$ENABLE_VIEW   = has_permission('Request_List.View');
$ENABLE_MANAGE = has_permission('Request_List.Manage');
?>

<style>
    .swal2-container {
        z-index: 999999 !important;
    }

    .swal2-popup {
        z-index: 1000000 !important;
    }

    /* Fix dropdown menu di DataTable agar muncul di depan */
    .table-responsive {
        overflow: visible !important;
    }

    .dropdown-menu {
        z-index: 9999 !important;
        position: fixed !important;
    }

    .card, .card-body {
        overflow: visible !important;
    }

    #table-request-list tbody td {
        overflow: visible !important;
    }

    .dataTables_wrapper {
        overflow: visible !important;
    }

    #table-request-list {
        margin-bottom: 60px !important;
    }

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

    .status-pill.not-scanned {
        background-color: #e63946 !important;
        color: #ffffff !important;
        border: 1px solid #d62828;
    }

    .status-pill.scanned {
        background-color: #ebfbee !important;
        color: #099268 !important;
        border: 1px solid #b2f2bb;
    }

    .status-pill.auto-wip {
        background-color: #e7f1ff !important;
        color: #0d6efd !important;
        border: 1px solid #9ec5fe;
    }

    #qr-reader {
        border: none !important;
    }

    #qr-reader button {
        background-color: #007bff !important;
        color: #fff !important;
        border: none !important;
        padding: 8px 18px !important;
        border-radius: 6px !important;
        cursor: pointer;
        margin: 6px 4px !important;
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

</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<!-- Modal Confirm SPK Coil -->
<div class="modal fade" id="modal-confirm-spk" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmLabel">Confirm SPK Coil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow: auto;">
                <div class="form-group mb-4">
                    <label class="fw-bold">Pilih Nomor SPK Coil (Request ID):</label>
                    <select class="form-select form-control" id="select-request-id">
                        <option value="">-- Pilih SPK Coil --</option>
                    </select>
                </div>

                <div id="scan-container" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0"><i class="fa fa-qrcode"></i> Scan QR Code Coil</h6>
                        <span class="scan-count">
                            Scanned: <span id="scanned-count">0</span> / <span id="total-count">0</span>
                        </span>
                    </div>

                    <div class="scan-input-wrapper mb-4">
                        <div class="input-group d-flex align-items-stretch">
                            <input type="text" id="scan-input" class="form-control text-center" placeholder="Scan/Ketik Kode disini (Enter)" autocomplete="off">
                            <div class="input-group-append d-flex">
                                <button class="btn btn-primary d-flex align-items-center justify-content-center" type="button" id="btn-camera" title="Gunakan Kamera">
                                    <i class="fa fa-camera me-2"></i> <span>Kamera</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="camera-container" class="mb-3" style="display: none;">
                        <div id="qr-reader" style="width:100%"></div>
                        <button type="button" class="btn btn-sm btn-secondary mt-2 w-100" id="btn-close-camera">Tutup Kamera</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="table-coil-detail">
                            <thead class="bg-light">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th>Kode Internal</th>
                                    <th>No Coil</th>
                                    <th>Material</th>
                                    <th class="text-center" width="20%">Status Scan</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-coil-detail">
                                <!-- Data dimuat via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" id="btn-submit-confirm" disabled>Confirm Pengeluaran</button>
            </div>
        </div>
    </div>
</div>


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
                    data: 1,
                    className: 'text-nowrap'
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

        // Fix dropdown position di dalam DataTable
        $(document).on('show.bs.dropdown', '#table-request-list .dropdown', function() {
            var $btn = $(this).find('[data-bs-toggle="dropdown"]');
            var $menu = $(this).find('.dropdown-menu');
            var btnRect = $btn[0].getBoundingClientRect();

            $menu.css({
                position: 'fixed',
                top: btnRect.bottom + 'px',
                left: (btnRect.right - $menu.outerWidth()) + 'px',
                transform: 'none'
            });
        });

        // -----------------------------------------------------------------
        // CONFIRM SPK COIL LOGIC
        // -----------------------------------------------------------------

        let html5QrcodeScanner = null;
        let activeRequestId = '';
        let totalCoils = 0;
        let scannedCoils = 0;
        let isProcessingScan = false;

        // Load Sound Config dari Master Sound App
        let activeSoundConfig = {};

        function loadSoundConfig() {
            $.ajax({
                url: siteurl + 'master_sound/get_sound_config',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status == 1) {
                        activeSoundConfig = res.data;
                    }
                }
            });
        }
        loadSoundConfig();

        function triggerVibrate(level) {
            level = parseInt(level) || 0;
            if (level <= 0) return;

            if ("vibrate" in navigator) {
                try {
                    const duration = level * 100;
                    navigator.vibrate([duration, 50, duration]);
                } catch (e) {
                    try {
                        navigator.vibrate(level * 100);
                    } catch (err) {}
                }
            }
        }

        // Play Beep Sound & Trigger Vibrate
        function playBeep(eventCode = 'scan_success') {
            try {
                if (activeSoundConfig && activeSoundConfig[eventCode] && activeSoundConfig[eventCode].sound_url) {
                    const audio = new Audio(activeSoundConfig[eventCode].sound_url);
                    audio.play().catch(e => {});

                    const vibLevel = activeSoundConfig[eventCode].vibrate_level || 0;
                    triggerVibrate(vibLevel);
                    return;
                }

                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const ctx = new AudioContext();

                function playTone(freq, startTime, duration, volume = 0.4) {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, startTime);

                    gain.gain.setValueAtTime(0, startTime);
                    gain.gain.linearRampToValueAtTime(volume, startTime + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.01, startTime + duration);

                    osc.start(startTime);
                    osc.stop(startTime + duration);
                }

                const now = ctx.currentTime;
                playTone(1046.50, now, 0.15); // C6
                playTone(1318.51, now + 0.15, 0.15); // E6
                playTone(1567.98, now + 0.30, 0.35); // G6

                // Getaran (hanya jalan di HP)
                if (navigator.vibrate) {
                    navigator.vibrate([50, 30, 50, 30, 100]);
                }

            } catch (e) {}
        }
        // 1. Buka Modal ketika tombol Confirm ditekan di DataTable
        $(document).on('click', '.btn-confirm-spk', function() {
            var spkNo = $(this).data('spk');

            $('#select-request-id').html('<option value="">Loading...</option>');
            $('#scan-container').hide();
            $('#modal-confirm-spk').modal('show');

            $.ajax({
                url: BASE_URL + '/get_pending_spkc/' + spkNo,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status == 1 && res.data.length > 0) {
                        var options = '<option value="">-- Pilih SPK Coil --</option>';
                        $.each(res.data, function(i, v) {
                            options += '<option value="' + v.id + '">' + v.spk_coil_no + ' (' + v.request_date + ')</option>';
                        });
                        $('#select-request-id').html(options);
                    } else {
                        $('#select-request-id').html('<option value="">Tidak ada SPK Coil pending</option>');
                    }
                }
            });
        });

        // 2. Saat SPKC dipilih
        $('#select-request-id').on('change', function() {
            activeRequestId = $(this).val();

            if (activeRequestId) {
                loadCoilsToConfirm(activeRequestId);
                $('#scan-container').fadeIn();
                setTimeout(() => $('#scan-input').focus(), 100);
            } else {
                $('#scan-container').hide();
            }
        });

        function loadCoilsToConfirm(request_id) {
            $('#tbody-coil-detail').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
            $('#btn-submit-confirm').prop('disabled', true);

            $.ajax({
                url: BASE_URL + '/get_coils_to_confirm/' + request_id,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status == 1) {
                        var html = '';
                        totalCoils = res.data.length;
                        scannedCoils = 0;

                        if (totalCoils === 0) {
                            $('#tbody-coil-detail').html('<tr><td colspan="5" class="text-center">Tidak ada coil</td></tr>');
                            return;
                        }

                        $.each(res.data, function(i, c) {
                            var statusHtml = '';
                            var isWip = (c.id_gudang_sumber == 4 || c.id_gudang_sumber == '4');

                            if (c.scan_status == 1 && isWip) {
                                scannedCoils++;
                                statusHtml = '<div class="status-pill auto-wip" id="status-pill-' + c.id + '"><i class="fa fa-bolt"></i> <span>Auto (WIP)</span></div>';
                            } else if (c.scan_status == 1) {
                                scannedCoils++;
                                statusHtml = '<div class="status-pill scanned" id="status-pill-' + c.id + '"><i class="fa fa-check-circle"></i> <span>Sudah</span></div>';
                            } else {
                                statusHtml = '<div class="status-pill not-scanned" id="status-pill-' + c.id + '"><i class="fa fa-exclamation-triangle"></i> <span>Belum</span></div>';
                            }

                            html += '<tr id="row-coil-' + c.id + '">' +
                                '<td class="text-center">' + (i + 1) + '</td>' +
                                '<td>' + (c.kode_internal || '-') + '</td>' +
                                '<td>' + (c.no_coil || '-') + '</td>' +
                                '<td>' + (c.nm_material || c.id_material || '-') + '</td>' +
                                '<td class="text-center">' + statusHtml + '</td>' +
                                '</tr>';
                        });

                        $('#tbody-coil-detail').html(html);
                        $('#total-count').text(totalCoils);
                        $('#scanned-count').text(scannedCoils);
                        checkSubmitBtn();
                    }
                }
            });
        }

        function checkSubmitBtn() {
            if (totalCoils > 0 && scannedCoils >= totalCoils) {
                $('#btn-submit-confirm').prop('disabled', false);
            } else {
                $('#btn-submit-confirm').prop('disabled', true);
            }
        }

        // 3. Scan Input Enter
        $('#scan-input').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                processScan($(this).val().trim());
            }
        });

        function processScan(kodeStr) {
            if (!kodeStr || !activeRequestId || isProcessingScan) return;
            isProcessingScan = true;

            $.ajax({
                url: BASE_URL + '/scan_coil',
                type: 'POST',
                data: {
                    kode_internal: kodeStr,
                    request_id: activeRequestId
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status == 1) {
                        var $pill = $('#status-pill-' + res.detail_id);
                        if ($pill.hasClass('not-scanned')) {
                            $pill.removeClass('not-scanned').addClass('scanned')
                                .html('<i class="fa fa-check-circle"></i> <span>Sudah</span>');
                            scannedCoils++;
                            $('#scanned-count').text(scannedCoils);
                            checkSubmitBtn();
                        }
                        playBeep();

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: res.message,
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        });
                    } else if (res.status == 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message,
                        });
                    } else if (res.status == 2) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Info',
                            text: res.message,
                        });
                    }
                },
                complete: function() {
                    $('#scan-input').val('').focus();
                    isProcessingScan = false;
                }
            });
        }

        // 4. Camera Handling
        $('#btn-camera').click(function() {
            $('#camera-container').slideDown();
            if (!html5QrcodeScanner) {
                html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    }
                }, false);
                html5QrcodeScanner.render(onScanSuccess, function(err) {});
            } else {
                html5QrcodeScanner.resume();
            }
        });

        $('#btn-close-camera').click(function() {
            $('#camera-container').slideUp();
            if (html5QrcodeScanner) {
                try {
                    html5QrcodeScanner.pause(true);
                } catch (e) {}
            }
        });

        function onScanSuccess(decodedText) {
            try {
                html5QrcodeScanner.pause(true);
            } catch (e) {}
            $('#camera-container').slideUp();
            $('#scan-input').val(decodedText);
            processScan(decodedText);
        }

        // Cleanup saat modal ditutup
        $('#modal-confirm-spk').on('hidden.bs.modal', function() {
            if (html5QrcodeScanner) {
                try {
                    html5QrcodeScanner.clear();
                    html5QrcodeScanner = null;
                } catch (e) {}
            }
            tableRequestList.ajax.reload(null, false);
        });

        // 5. Submit Confirm
        $('#btn-submit-confirm').click(function() {
            if (!activeRequestId) return;

            Swal.fire({
                icon: 'warning',
                title: 'Apakah Anda yakin?',
                text: 'Konfirmasi pengeluaran ini tidak dapat dibatalkan setelah diproses.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Proses!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                reverseButtons: true
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                $('#btn-submit-confirm').prop('disabled', true).html('<i class="fa fa-spin fa-spinner"></i> Processing...');

                $.ajax({
                    url: BASE_URL + '/confirm_spk_coil/' + activeRequestId,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function() {
                                $('#modal-confirm-spk').modal('hide');
                                $('#btn-submit-confirm').prop('disabled', false).text('Confirm Pengeluaran');
                                tableRequestList.ajax.reload(null, false);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message
                            });
                            $('#btn-submit-confirm').prop('disabled', false).text('Confirm Pengeluaran');
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan jaringan'
                        });
                        $('#btn-submit-confirm').prop('disabled', false).text('Confirm Pengeluaran');
                    }
                });
            });
        });

        // -----------------------------------------------------------------
        // CLOSE SPK (Manual update status ke Material Confirmed)
        // -----------------------------------------------------------------
        $(document).on('click', '.btn-close-spk', function() {
            var spkNo = $(this).data('spk');

            Swal.fire({
                icon: 'warning',
                title: 'Close SPK?',
                text: 'Apakah Anda yakin ingin menutup SPK ' + spkNo + '? Status akan berubah menjadi Material Confirmed.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Close SPK',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#343a40',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: BASE_URL + '/close_spk',
                    type: 'POST',
                    data: { spk_no: spkNo },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function() {
                                tableRequestList.ajax.reload(null, false);
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                    }
                });
            });
        });

    });
</script>