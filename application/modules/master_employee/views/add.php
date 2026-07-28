<!-- CDN Flatpickr & ViewerJS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js"></script>

<?php
$id                    = (!empty($header[0]->id)) ? $header[0]->id : '';
$nik                = (!empty($header[0]->id)) ? $header[0]->nik : '';
$nm_karyawan        = (!empty($header[0]->id)) ? $header[0]->nm_karyawan : '';
$no_ktp                = (!empty($header[0]->id)) ? $header[0]->no_ktp : '';
$tmp_lahir            = (!empty($header[0]->id)) ? $header[0]->tmp_lahir : '';
$tgl_lahir            = (!empty($header[0]->id)) ? $header[0]->tgl_lahir : '';
$gender                = (!empty($header[0]->id)) ? $header[0]->gender : '';
$agama                = (!empty($header[0]->id)) ? $header[0]->agama : '';
$department            = (!empty($header[0]->id)) ? $header[0]->department : '';
$sub_department        = (!empty($header[0]->id)) ? $header[0]->sub_department : '';
$cost_center        = (!empty($header[0]->id)) ? $header[0]->cost_center : '';
$no_ponsel            = (!empty($header[0]->id)) ? $header[0]->no_ponsel : '';
$email                = (!empty($header[0]->id)) ? $header[0]->email : '';
$pendidikan            = (!empty($header[0]->id)) ? $header[0]->pendidikan : '';
$position            = (!empty($header[0]->id)) ? $header[0]->position : '';
$ktp_provinsi        = (!empty($header[0]->id)) ? $header[0]->ktp_provinsi : '';
$domisili_provinsi    = (!empty($header[0]->id)) ? $header[0]->domisili_provinsi : '';
$ktp_kota            = (!empty($header[0]->id)) ? $header[0]->ktp_kota : '';
$domisili_kota        = (!empty($header[0]->id)) ? $header[0]->domisili_kota : '';
$ktp_kecamatan        = (!empty($header[0]->id)) ? $header[0]->ktp_kecamatan : '';
$domisili_kecamatan    = (!empty($header[0]->id)) ? $header[0]->domisili_kecamatan : '';
$ktp_kelurahan        = (!empty($header[0]->id)) ? $header[0]->ktp_kelurahan : '';
$domisili_kelurahan    = (!empty($header[0]->id)) ? $header[0]->domisili_kelurahan : '';
$ktp_kode_pos        = (!empty($header[0]->id)) ? $header[0]->ktp_kode_pos : '';
$domisili_kode_pos    = (!empty($header[0]->id)) ? $header[0]->domisili_kode_pos : '';
$ktp_alamat            = (!empty($header[0]->id)) ? $header[0]->ktp_alamat : '';
$domisili_alamat    = (!empty($header[0]->id)) ? $header[0]->domisili_alamat : '';
$npwp                = (!empty($header[0]->id)) ? $header[0]->npwp : '';
$bpjs                = (!empty($header[0]->id)) ? $header[0]->bpjs : '';
$tgl_join            = (!empty($header[0]->id)) ? $header[0]->tgl_join : '';
$tgl_end            = (!empty($header[0]->id)) ? $header[0]->tgl_end : '';
$rek_number            = (!empty($header[0]->id)) ? $header[0]->rek_number : '';
$bank_account        = (!empty($header[0]->id)) ? $header[0]->bank_account : '';
$sts_karyawan        = (!empty($header[0]->id)) ? $header[0]->sts_karyawan : '';
$status                = (!empty($header[0]->id)) ? $header[0]->status : '';
$tanda_tangan        = (!empty($header[0]->id)) ? $header[0]->tanda_tangan : '';
$tanda               = (!empty($tanda)) ? $tanda : '';

?>
<form method="POST" id="form_employee" autocomplete="off" enctype="multipart/form-data">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold">Personal Information</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">

                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="nik" value="<?= $nik ?>">

                <div class="col-md-6">
                    <label class="form-label">Employee Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nm_karyawan" value="<?= $nm_karyawan ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">ID Number</label>
                    <input type="text" class="form-control" name="no_ktp" value="<?= $no_ktp ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Place of Birth</label>
                    <input type="text" class="form-control" name="tmp_lahir" value="<?= $tmp_lahir ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Date of Birth</label>
                    <input type="text" class="form-control flatpickr-date" name="tgl_lahir" value="<?= $tgl_lahir ?>" placeholder="YYYY-MM-DD">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Religion</label>
                    <select name="agama" class="form-select">
                        <option value="">Select Religion</option>
                        <?php foreach ($agamax as $v): ?>
                            <option value="<?= $v['name'] ?>" <?= ($v['name'] == $agama ? 'selected' : '') ?>>
                                <?= strtoupper($v['data1']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select Gender</option>
                        <?php foreach ($genderx as $v): ?>
                            <option value="<?= $v['name'] ?>" <?= ($v['name'] == $gender ? 'selected' : '') ?>>
                                <?= strtoupper($v['data1']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class='col-sm-6'>
                    <label class='form-label'>Department</label>
                    <select name='department' id='department' class='form-select'>
                        <option value='0'>Select An Department</option>
                        <?php
                        foreach ($departmentx as $val => $valx) {
                            $selected = ($valx['id'] == $department) ? 'selected' : '';
                            echo "<option value='" . $valx['id'] . "' " . $selected . ">" . strtoupper($valx['nama']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class='col-sm-6' id="container_sub_department" style="display: none;">
                    <label class='form-label'>Sub Department / Role</label>
                    <select name='sub_department' id='sub_department' class='form-select'>
                        <option value=''>Select Role</option>
                        <option value='Setter' <?= ($sub_department == 'Setter' ? 'selected' : '') ?>>Setter</option>
                        <option value='Helper' <?= ($sub_department == 'Helper' ? 'selected' : '') ?>>Helper</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Last Education</label>
                    <select name='pendidikan' id='pendidikan' class='form-control input-md'>
                        <option value='0'>Select An Last Education</option>
                        <?php
                        foreach ($pendidikanx as $val => $valx) {
                            $selected = ($valx['name'] == $pendidikan) ? 'selected' : '';
                            echo "<option value='" . $valx['name'] . "' " . $selected . ">" . $valx['data1'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold">Address</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">ID Card Address</label>
                    <textarea class="form-control" rows="3" name="ktp_alamat"><?= $ktp_alamat ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Domicile Address</label>
                    <textarea class="form-control" rows="3" name="domisili_alamat"><?= $domisili_alamat ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">ID Card Postcode</label>
                    <input type="text" class="form-control numberOnly" name="ktp_kode_pos" value="<?= $ktp_kode_pos ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Domicile Postcode</label>
                    <input type="text" class="form-control numberOnly" name="domisili_kode_pos" value="<?= $domisili_kode_pos ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Contact Number</label>
                    <?php
                    echo form_input(array('id' => 'no_ponsel', 'name' => 'no_ponsel', 'class' => 'form-control numberOnly', 'placeholder' => 'Contact Number'), $no_ponsel);
                    ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <?php
                    echo form_input(array('id' => 'email', 'name' => 'email', 'class' => 'form-control', 'placeholder' => 'Email'), $email);
                    ?>
                </div>

            </div>
        </div>
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold">Additional Information</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">NPWP</label>
                    <input type="text" class="form-control numberOnly" name="npwp" value="<?= $npwp ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">BPJS</label>
                    <input type="text" class="form-control" name="bpjs" value="<?= $bpjs ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Account Bank</label>
                    <select name="bank_account" id="bank_account" class="form-control select">
                        <option value='0'>Select An Account Bank</option>
                        <?php
                        foreach ($bankx as $val => $valx) {
                            $selected = ($valx['code'] == $bank_account) ? 'selected' : '';
                            echo "<option value='" . $valx['code'] . "' " . $selected . ">" . strtoupper($valx['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Account Number</label>
                    <?php
                    echo form_input(array('id' => 'rek_number', 'name' => 'rek_number', 'class' => 'form-control', 'placeholder' => 'Account Number'), $rek_number);
                    ?>
                </div>

                <div class="col-md-6">
                    <label class='form-label'>Employee Status</label>
                    <select name='sts_karyawan' id='sts_karyawan' class='form-control select'>
                        <option value='0'>Select An Employee Status</option>
                        <?php
                        foreach ($sts_karyawanx as $val => $valx) {
                            $selected = ($valx['name'] == $sts_karyawan) ? 'selected' : '';
                            echo "<option value='" . $valx['name'] . "' " . $selected . ">" . strtoupper($valx['data1']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class='form-label'>Status</label>
                    <select name='status' id='status' class='form-control select'>
                        <option value='0'>Select An Status</option>
                        <?php
                        foreach ($statusx as $val => $valx) {
                            $selected = ($valx['name'] == $status) ? 'selected' : '';
                            echo "<option value='" . $valx['name'] . "' " . $selected . ">" . strtoupper($valx['data1']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Join Date</label>
                    <input type="text" class="form-control flatpickr-date" name="tgl_join" value="<?= $tgl_join ?>" placeholder="YYYY-MM-DD">
                </div>

                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="text" class="form-control flatpickr-date" name="tgl_end" value="<?= $tgl_end ?>" placeholder="YYYY-MM-DD">
                </div>

                <div class="col-md-12">
                    <label for="tanda_tangan" class="form-label">Upload Tanda Tangan (.jpeg/.jpg/.png)</label>

                    <div class="d-flex flex-column gap-2">

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <!-- input asli disembunyikan -->
                            <input type="file"
                                name="tanda_tangan"
                                id="tanda_tangan"
                                class="d-none"
                                accept=".jpg,.jpeg,.png,image/jpeg,image/png">

                            <!-- tombol pilih file -->
                            <button type="button" class="btn btn-outline-warning" id="btnPickTtd">
                                <i class="ti ti-upload me-1"></i> Choose File
                            </button>

                            <!-- nama file -->
                            <span class="text-muted" id="ttdFileName">No file chosen</span>

                            <!-- tombol clear -->
                            <button type="button" class="btn btn-light border" id="btnClearTtd" style="display:none;">
                                <i class="ti ti-x me-1"></i> Clear
                            </button>
                        </div>

                        <!-- hint -->
                        <small class="text-muted">
                            Allowed: JPG/JPEG/PNG.
                        </small>

                        <!-- warning jika belum ada file existing -->
                        <?php if (empty($tanda_tangan)) : ?>
                            <small class="text-danger">Belum diupload !!!</small>
                        <?php endif; ?>

                        <!-- existing file -->
                        <?php if (!empty($tanda_tangan)) : ?>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-light text-dark border">
                                    <i class="ti ti-file-description me-1"></i> Existing Tanda Tangan
                                </span>

                                <a href="<?= base_url() . $tanda_tangan; ?>" download class="btn btn-sm btn-success">
                                    <i class="ti ti-download me-1"></i> Download
                                </a>

                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnPreviewTtd">
                                    <i class="ti ti-eye me-1"></i> Preview
                                </button>
                            </div>

                            <!-- thumbnail preview -->
                            <div id="ttdImageContainer">
                                <img id="ttdImage" src="<?= base_url() . $tanda_tangan; ?>"
                                    alt="Ttd"
                                    style="width:160px; height:100px; object-fit:contain; border:1px solid #ddd; border-radius:6px; padding:4px; background:#fff; cursor:pointer;">
                            </div>
                        <?php endif; ?>

                    </div>

                    <script>
                        (function() {
                            const input = document.getElementById('tanda_tangan');
                            const btnPick = document.getElementById('btnPickTtd');
                            const btnClear = document.getElementById('btnClearTtd');
                            const fileName = document.getElementById('ttdFileName');

                            if (!input || !btnPick || !fileName) return;

                            btnPick.addEventListener('click', function() {
                                input.click();
                            });

                            input.addEventListener('change', function() {
                                const name = (input.files && input.files.length) ? input.files[0].name : 'No file chosen';
                                fileName.textContent = name;

                                if (btnClear) {
                                    btnClear.style.display = (input.files && input.files.length) ? 'inline-flex' : 'none';
                                }
                            });

                            if (btnClear) {
                                btnClear.addEventListener('click', function() {
                                    input.value = '';
                                    fileName.textContent = 'No file chosen';
                                    btnClear.style.display = 'none';
                                });
                            }
                        })();
                    </script>
                </div>
            </div>
        </div>
        <div class="card-body text-center">
            <button type="button" id="back" class="btn btn-dark">
                <i class="ti ti-arrow-left"></i> Back
            </button>

            <button type="button" id="saved" class="btn btn-primary">
                <i class="ti ti-device-floppy"></i> Save
            </button>
        </div>
    </div>
</form>


<script>
    $(document).ready(function() {
        $('select').select2({
            width: '100%'
        });

        // Inisialisasi Flatpickr
        flatpickr('.flatpickr-date', {
            dateFormat: 'Y-m-d',
            allowInput: true
        });

        // Inisialisasi ViewerJS jika ada gambar ttd
        var ttdImg = document.getElementById('ttdImage');
        if (ttdImg) {
            var viewer = new Viewer(ttdImg, {
                inline: false,
                viewed: function() {}
            });

            $(document).on('click', '#btnPreviewTtd', function(e) {
                e.preventDefault();
                viewer.show();
            });
        }

        // Mode View Read-Only
        var modeTanda = '<?= $tanda ?>';
        if (modeTanda === 'view') {
            $('#form_employee input, #form_employee select, #form_employee textarea, #btnPickTtd, #btnClearTtd').prop('disabled', true);
            $('#saved').hide();
        }

        function checkDepartment() {
            var deptId = $('#department').val();
            // Produksi ID = 8
            if (deptId == '8') {
                $('#container_sub_department').show();
            } else {
                $('#container_sub_department').hide();
                $('#sub_department').val('').trigger('change');
            }
        }

        checkDepartment();

        $(document).on('change', '#department', function() {
            checkDepartment();
        });
    })

    $(document).on('click', '#back', function() {
        window.location.href = base_url + active_controller
    });

    $('#saved').click(function(e) {
        e.preventDefault();
        //Customer
        var nm_karyawan = $("#nm_karyawan").val();
        var tmp_lahir = $("#tmp_lahir").val();
        var tgl_lahir = $("#tgl_lahir").val();
        var department = $("#department").val();
        var gender = $("#gender").val();
        var agama = $("#agama").val();
        var pendidikan = $("#pendidikan").val();
        var ktp_kode_pos = $("#ktp_kode_pos").val();
        var ktp_alamat = $("#ktp_alamat").val();
        var domisili_kode_pos = $("#domisili_kode_pos").val();
        var domisili_alamat = $("#domisili_alamat").val();
        var no_ponsel = $("#no_ponsel").val();
        var email = $("#email").val();
        var npwp = $("#npwp").val();
        var bpjs = $("#bpjs").val();
        var no_ktp = $("#no_ktp").val();
        var tgl_join = $("#tgl_join").val();
        var tgl_end = $("#tgl_end").val();
        var sts_karyawan = $("#sts_karyawan").val();
        var rek_number = $("#rek_number").val();
        var bank_account = $("#bank_account").val();
        var status = $("#status").val();

        if (nm_karyawan == '') {
            swal({
                title: "Error Message!",
                text: 'Employee name is empty, please input first ...',
                type: "warning"
            });
            $('#simpan-bro').prop('disabled', false);
            return false;
        }

        // if(nm_customer=='' || nm_customer==null || nm_customer=='-' || nm_customer=='0'){
        //     swal({
        //         title	: "Error Message!",
        //         text	: 'Customer Name in master customer tab is empty, please input first ...',
        //         type	: "warning"
        //     });
        //     return false;
        // }
        // $('#saved').prop('disabled',false);

        swal({
                title: "Are you sure?",
                text: "You will not be able to process again this data!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, Process it!",
                cancelButtonText: "No, cancel process!",
                closeOnConfirm: false,
                closeOnCancel: false
            },
            function(isConfirm) {
                if (isConfirm) {
                    // loading_spinner();
                    var formData = new FormData($('#form_employee')[0]);
                    var baseurl = base_url + active_controller + '/add';
                    $.ajax({
                        url: baseurl,
                        type: "POST",
                        data: formData,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        beforeSend: function() {
                            $(this).prop('disabled', true);
                        },
                        success: function(data) {
                            if (data.status == 1) {
                                swal({
                                    title: "Save Success!",
                                    text: data.pesan,
                                    type: "success",
                                    timer: 7000
                                });
                                window.location.href = base_url + active_controller;
                            } else {
                                swal({
                                    title: "Save Failed!",
                                    text: data.pesan,
                                    type: "warning",
                                    timer: 7000
                                });
                            }
                            $('#saved').prop('disabled', false);
                        },
                        error: function() {

                            swal({
                                title: "Error Message !",
                                text: 'An Error Occured During Process. Please try again..',
                                type: "warning",
                                timer: 7000
                            });
                            $('#saved').prop('disabled', false);
                        }
                    });
                } else {
                    swal("Cancelled", "Data can be process again :)", "error");
                    $('#saved').prop('disabled', false);
                    return false;
                }
            });
    });
</script>