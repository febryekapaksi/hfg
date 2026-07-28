<div class="card card-custom">
    <div class="card-header border-0 pt-4 pb-2 d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-bold text-dark mb-0">
            <!-- <i class="fa fa-volume-up me-2 text-primary"></i> Master Sound & Vibrate App -->
        </h5>
        <button type="button" class="btn btn-sm btn-primary btn-sm" id="btn-add-sound">
            <i class="fa fa-plus me-1"></i> Tambah Sound
        </button>
    </div>
    <div class="card-body pt-2">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle w-100" id="table-master-sound">
                <thead class="table-light text-muted fw-bold text-uppercase" style="font-size: 12px;">
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th width="20%">Nama Sound</th>
                        <th width="15%">Kode Event</th>
                        <th width="15%" class="text-center">Level Vibrate</th>
                        <th width="25%">File Audio</th>
                        <th width="10%" class="text-center">Status</th>
                        <th width="10%" class="text-center">Diubah</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form Sound -->
<div class="modal fade" id="modal-sound" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalSoundLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-sound" enctype="multipart/form-data">
                <input type="hidden" name="id" id="sound_id" value="">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalSoundLabel"><i class="fa fa-volume-up me-2"></i> Tambah Master Sound</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow: auto;">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Sound <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="sound_name" id="sound_name" placeholder="Misal: Scan Success" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Kode Event <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="sound_code" id="sound_code" placeholder="Misal: scan_success, scan_error" required>
                        <small class="text-muted">Kode unik yang dipanggil oleh sistem saat event terjadi.</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold mb-0">Level Vibrate (Getar HP/Perangkat Mobile)</label>
                            <span class="badge bg-primary fs-6" id="vibrate_level_val">Level 5 (500ms)</span>
                        </div>
                        <input type="range" class="form-range" name="vibrate_level" id="vibrate_level" min="0" max="10" step="1" value="5">
                        <div class="d-flex justify-content-between text-muted small mt-1" style="font-size: 11px;">
                            <span>0 (Off)</span>
                            <span>5 (500ms)</span>
                            <span>10 (1000ms / 1s)</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">File Audio (MP3 / WAV / OGG / M4A)</label>
                        <input type="file" class="form-control" name="file_sound" id="file_sound" accept="audio/*">
                        <div id="file-existing-info" class="mt-2 text-muted small" style="display:none;"></div>
                        <small class="text-muted d-block mt-1">Maksimal ukuran file: 10MB.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <select class="form-select" name="status" id="sound_status">
                            <option value="1" selected>Aktif</option>
                            <option value="0">Non-Aktif</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan</label>
                        <textarea class="form-control" name="keterangan" id="keterangan" rows="2" placeholder="Catatan tambahan (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-sound"><i class="fa fa-save me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const BASE_URL = siteurl + active_controller;
        let currentAudio = null;

        // Init DataTables
        const table = $('#table-master-sound').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE_URL + '/data_side',
                type: 'POST'
            },
            columnDefs: [{
                    targets: [0, 3, 5, 6, 7],
                    orderable: false
                },
                {
                    targets: [0, 3, 5, 6, 7],
                    className: 'text-center'
                }
            ],
            order: [
                [1, 'asc']
            ]
        });

        // Play Audio Tes Button
        $(document).on('click', '.btn-play-audio', function() {
            const soundUrl = $(this).data('url');
            const vibrateLevel = parseInt($(this).data('vibrate')) || 0;

            if (currentAudio) {
                currentAudio.pause();
                currentAudio.currentTime = 0;
            }

            if (soundUrl) {
                currentAudio = new Audio(soundUrl);
                currentAudio.play().catch(err => {
                    console.log('Audio playback error:', err);
                });
            }

            // Trigger Vibration if supported
            triggerVibration(vibrateLevel);
        });

        // Range Slider Input Listener & Live Vibrate Test
        $('#vibrate_level').on('input change', function() {
            const val = parseInt($(this).val()) || 0;
            updateVibrateBadge(val);
            triggerVibration(val);
        });

        function updateVibrateBadge(val) {
            const ms = val * 100;
            const text = (val === 0) ? '0 - Off' : 'Level ' + val + ' (' + ms + 'ms)';
            $('#vibrate_level_val').text(text);
        }

        function triggerVibration(level) {
            level = parseInt(level) || 0;
            if (level <= 0) return;

            if ("vibrate" in navigator) {
                try {
                    const duration = level * 100; // level 1 = 100ms, level 5 = 500ms, level 10 = 1000ms
                    // Pola getar berulang (pulse) agar terasa lebih tegas di HP
                    navigator.vibrate([duration, 50, duration]);
                } catch (e) {
                    try {
                        navigator.vibrate(level * 100);
                    } catch (err) {}
                }
            }
        }

        // Open Modal Add
        $('#btn-add-sound').click(function() {
            $('#form-sound')[0].reset();
            $('#sound_id').val('');
            $('#vibrate_level').val(5);
            updateVibrateBadge(5);
            $('#modalSoundLabel').html('<i class="fa fa-volume-up me-2"></i> Tambah Master Sound');
            $('#file-existing-info').hide().html('');
            $('#modal-sound').modal('show');
        });

        // Open Modal Edit
        $(document).on('click', '.btn-edit-sound', function() {
            const id = $(this).data('id');

            $.ajax({
                url: BASE_URL + '/get_detail/' + id,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status == 1) {
                        const d = res.data;
                        $('#sound_id').val(d.id);
                        $('#sound_name').val(d.sound_name);
                        $('#sound_code').val(d.sound_code);
                        $('#vibrate_level').val(d.vibrate_level || 0);
                        updateVibrateBadge(d.vibrate_level || 0);
                        $('#sound_status').val(d.status);
                        $('#keterangan').val(d.keterangan);

                        if (d.file_original_name) {
                            $('#file-existing-info').html('<i class="fa fa-file-audio me-1"></i> File saat ini: <strong>' + escHtml(d.file_original_name) + '</strong>').show();
                        } else {
                            $('#file-existing-info').hide().html('');
                        }

                        $('#modalSoundLabel').html('<i class="fa fa-edit me-2"></i> Edit Master Sound');
                        $('#modal-sound').modal('show');
                    } else {
                        Swal.fire('Error', res.msg, 'error');
                    }
                }
            });
        });

        // Submit Form
        $('#form-sound').submit(function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const $btn = $('#btn-save-sound');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

            $.ajax({
                url: BASE_URL + '/save',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(res) {
                    $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan');
                    if (res.status == 1) {
                        $('#modal-sound').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire('Berhasil', res.msg, 'success');
                    } else {
                        Swal.fire('Peringatan', res.msg, 'warning');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan');
                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                }
            });
        });

        // Delete Sound
        $(document).on('click', '.btn-delete-sound', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus sound "' + name + '"?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: BASE_URL + '/delete',
                        type: 'POST',
                        data: {
                            id: id
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.status == 1) {
                                table.ajax.reload(null, false);
                                Swal.fire('Berhasil', res.msg, 'success');
                            } else {
                                Swal.fire('Gagal', res.msg, 'error');
                            }
                        }
                    });
                }
            });
        });

        function escHtml(str) {
            return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
    });
</script>