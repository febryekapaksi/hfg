<div class="card border-primary mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="my-grid">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Menu</th>
                        <th class="text-center">Action / Fungsi</th>
                        <th class="text-center">Kode Master Jurnal</th>
                        <th class="text-center">Keterangan</th>
                        <th class="text-center">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($results)) : ?>
                        <?php foreach ($results as $key => $row) : ?>
                            <tr>
                                <td class="text-center align-middle"><?= $key + 1 ?></td>
                                <td class="align-middle"><?= $row->menu ?></td>
                                <td class="align-middle"><?= $row->action ?></td>
                                <td class="text-center align-middle"><span class="badge bg-success"><?= $row->kode_master_jurnal ?></span></td>
                                <td class="align-middle"><?= $row->keterangan ?></td>
                                <td class="text-center align-middle">
                                    <button type="button" class="btn btn-sm btn-warning btn-edit text-dark fw-bold"
                                        data-id="<?= $row->id ?>"
                                        data-menu="<?= $row->menu ?>"
                                        data-action="<?= $row->action ?>"
                                        data-kode="<?= $row->kode_master_jurnal ?>"
                                        data-ket="<?= $row->keterangan ?>">
                                        <i class="fa fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Tidak ada data.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="ModalEdit" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="myModalLabel">Edit Master Jurnal Mapping</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-edit-mapping" method="post">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Menu</label>
                        <input type="text" class="form-control bg-light" name="menu" id="edit_menu" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Action / Fungsi</label>
                        <input type="text" class="form-control bg-light" name="action" id="edit_action" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Kode Master Jurnal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="kode_master_jurnal" id="edit_kode" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan</label>
                        <textarea class="form-control" name="keterangan" id="edit_ket" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-edit"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('.btn-edit').click(function() {
            var id = $(this).data('id');
            var menu = $(this).data('menu');
            var action = $(this).data('action');
            var kode = $(this).data('kode');
            var ket = $(this).data('ket');

            $('#edit_id').val(id);
            $('#edit_menu').val(menu);
            $('#edit_action').val(action);
            $('#edit_kode').val(kode);
            $('#edit_ket').val(ket);

            // Use Bootstrap 5 modal API if loaded, otherwise fallback to jQuery style if they still use BS3 JS underneath
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var myModal = new bootstrap.Modal(document.getElementById('ModalEdit'));
                myModal.show();
            } else {
                $('#ModalEdit').modal('show');
            }
        });

        $('#form-edit-mapping').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            Swal.fire({
                title: "Anda Yakin?",
                text: "Data mapping jurnal akan diupdate!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#0d6efd", // Bootstrap 5 primary color
                cancelButtonColor: "#dc3545", // Bootstrap 5 danger color
                confirmButtonText: "Ya, Simpan!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: siteurl + 'master_jurnal_mapping/save',
                        type: 'POST',
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(result) {
                            if (result.status == 1) {
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: result.message,
                                    icon: "success"
                                }).then((value) => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: "Gagal!",
                                    text: result.message,
                                    icon: "error"
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: "Error!",
                                text: "Terjadi kesalahan sistem, silakan hubungi administrator.",
                                icon: "error"
                            });
                        }
                    });
                }
            });
        });
    });
</script>