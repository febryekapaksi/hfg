<?php
$ENABLE_ADD     = has_permission('Material_Master.Add');
$ENABLE_MANAGE  = has_permission('Material_Master.Manage');
$ENABLE_VIEW    = has_permission('Material_Master.View');
$ENABLE_DELETE  = has_permission('Material_Master.Delete');

$id       = (!empty($listData[0]->id)) ? $listData[0]->id : '';
$code_lv1 = (!empty($listData[0]->code_lv1)) ? $listData[0]->code_lv1 : '';
$code_lv2 = (!empty($listData[0]->code_lv2)) ? $listData[0]->code_lv2 : '';
$code_lv3 = (!empty($listData[0]->code_lv3)) ? $listData[0]->code_lv3 : '';
$code_lv4 = (!empty($listData[0]->code_lv4)) ? $listData[0]->code_lv4 : '';
$nama     = (!empty($listData[0]->nama)) ? $listData[0]->nama : '';
$id_coating        	= (!empty($listData[0]->id_coating)) ? $listData[0]->id_coating : '';
$id_tensile     	= (!empty($listData[0]->id_tensile)) ? $listData[0]->id_tensile : '';
$hscode          	= (!empty($listData[0]->hscode)) ? $listData[0]->hscode : '';


$code       = (!empty($listData[0]->code)) ? $listData[0]->code : '';
$trade_name = (!empty($listData[0]->trade_name)) ? $listData[0]->trade_name : '';
$max_stok   = (!empty($listData[0]->max_stok)) ? $listData[0]->max_stok : '';
$min_stok   = (!empty($listData[0]->min_stok)) ? $listData[0]->min_stok : '';

$id_unit_packing  = (!empty($listData[0]->id_unit_packing)) ? $listData[0]->id_unit_packing : '';
$konversi         = (!empty($listData[0]->konversi)) ? $listData[0]->konversi : '';
$id_unit          = (!empty($listData[0]->id_unit)) ? $listData[0]->id_unit : '';
$id_unit_other    = (!empty($listData[0]->id_unit_other)) ? $listData[0]->id_unit_other : '';
$konversi_other   = (!empty($listData[0]->konversi_other)) ? $listData[0]->konversi_other : '';

$thickness = (!empty($listData[0]->thickness)) ? $listData[0]->thickness : '';
$width = (!empty($listData[0]->width)) ? $listData[0]->width : '';
$hardness = (!empty($listData[0]->hardness)) ? $listData[0]->hardness : '';
$length = (!empty($listData[0]->length)) ? $listData[0]->length : '';
$warna = (!empty($listData[0]->warna)) ? $listData[0]->warna : '';
$no_accurate = (!empty($listData[0]->no_accurate)) ? $listData[0]->no_accurate : '';
$wide   = (!empty($listData[0]->wide)) ? $listData[0]->wide : '';
$high   = (!empty($listData[0]->high)) ? $listData[0]->high : '';
$cub    = (!empty($listData[0]->cub)) ? $listData[0]->cub : '';

$id_supplier = (!empty($listData[0]->id_supplier)) ? $listData[0]->id_supplier : '';
$file_msds   = (!empty($listData[0]->file_msds)) ? $listData[0]->file_msds : '';

$status1 = (!empty($listData[0]->status) && $listData[0]->status == '1') ? 'checked' : '';
$status2 = (!empty($listData[0]->status) && $listData[0]->status == '0') ? 'checked' : '';
?>

<!-- Ini hanya isi body form (dipakai di modal #ModalView), TANPA wrapper box/form -->

<div class="form-group row mb-3">
	<div class="col-md-2">
		<label for="code_lv1">Metal Type <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-10">
		<select name="code_lv1" id="code_lv1" class="form-control chosen-select" required onchange="generateNama()">
			<option value="0">Select Metal Type</option>
			<?php
			foreach ($listLevel1 as $key => $value) {
				$selected = ($code_lv1 == $value['code_lv1']) ? 'selected' : '';
				echo "<option value='" . $value['code_lv1'] . "' " . $selected . ">" . strtoupper($value['nama']) . "</option>";
			}
			?>
		</select>
	</div>
</div>

<div class="form-group row mb-3">
	<div class="col-md-2">
		<label for="code_lv2">Slithed / Non Slithed <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-10">
		<select name="code_lv2" id="code_lv2" class="form-control chosen-select" required onchange="generateNama()">
			<?php
			if (!empty($id) && !empty($listLevel2)) {
				echo "<option value='0'>Select Slithed / Mother Coil</option>";
				foreach ($listLevel2 as $key => $value) {
					$selected = ($code_lv2 == $value['code_lv2']) ? 'selected' : '';
					echo "<option value='" . $value['code_lv2'] . "' " . $selected . ">" . strtoupper($value['nama']) . "</option>";
				}
			} else {
				echo "<option value='0'>Select Slithed / Mother Coil</option>";
			}
			?>
		</select>
	</div>
</div>

<div class="form-group row mb-3">
	<div class="col-md-2">
		<label for="code_lv3">Boron / Non Boron <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-10">
		<select name="code_lv3" id="code_lv3" class="form-control chosen-select" required onchange="generateNama()">
			<?php
			if (!empty($id) && !empty($listLevel3)) {
				echo "<option value='0'>Select Boron / Non Boron</option>";
				foreach ($listLevel3 as $key => $value) {
					$selected = ($code_lv3 == $value['code_lv3']) ? 'selected' : '';
					echo "<option value='" . $value['code_lv3'] . "' " . $selected . ">" . strtoupper($value['nama']) . "</option>";
				}
			} else {
				echo "<option value='0'>Select Boron / Non Boron</option>";
			}
			?>
		</select>
	</div>
</div>

<div class="form-group row mb-3">
	<div class="col-md-2">
		<label for="nama">Material Name <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-10">
		<input type="hidden" class="form-control" id="id" name="id" value="<?= $id; ?>">
		<input type="hidden" class="form-control" id="code_lv4" name="code_lv4" value="<?= $code_lv4; ?>">
		<input type="text" class="form-control" id="nama" name="nama" required placeholder="Material Master" value="<?= $nama; ?>">
	</div>
</div>

<hr>

<div class="form-group row mb-3">
	<div class="col-md-2">
		<label for="code">HS Code <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-4">
		<select name="hscode" id="hscode" class="form-control chosen-select" required>
			<?php

			if (!empty($hscodes)) {
				echo "<option value='0'>Select HS Code</option>";
				foreach ($hscodes as $key => $value) {
					$selected = ($hscode == $value['id']) ? 'selected' : '';
					echo "<option value='" . $value['id'] . "' " . $selected . ">" . $value['origin_code'] . "</option>";
				}
			} else {
				echo "<option value='0'>Select HS Code</option>";
			}
			?>
		</select>
	</div>
	<div class="col-md-2">
		<label for="trade_name">Alias Name</label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control" id="trade_name" name="trade_name" value="<?= $trade_name; ?>" placeholder="Alias">
	</div>
</div>


<div class="form-group row mb-3">
	<div class="col-md-2">
		<label>Width <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control maskM" id="width" name="width" value="<?= $width ?>" placeholder="Width" onkeyup="generateNama()">
	</div>
	<div class="col-md-2">
		<label>Coating <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-4">
		<select id="id_coating" name="id_coating" class="form-control chosen-select" onchange="generateNama()" required>
			<option value="0">Select An Option</option>
			<?php foreach ($coating as $value) {
				$sel = ($value->id == $id_coating) ? 'selected' : '';
			?>
				<option value="<?= $value->id; ?>" <?= $sel; ?>><?= strtoupper(strtolower($value->nama)) ?></option>
			<?php } ?>
		</select>
	</div>
</div>

<div class="form-group row mb-3">
	<div class="col-md-2">
		<label>Thickness <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control maskThickness" id="thickness" name="thickness" value="<?= $thickness ?>" placeholder="Thickness" onkeyup="generateNama()">
	</div>
	<div class="col-md-2">
		<label>Color</label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control" id="warna" name="warna" value="<?= $warna ?>" onkeyup="generateNama()" placeholder="Color">
	</div>
</div>

<div class="form-group row mb-3">
	<div class="col-md-2">
		<label>Hardness <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control" id="hardness" name="hardness" value="<?= $hardness ?>" placeholder="Hardness">
	</div>
	<div class="col-md-2">
		<label>Tensile <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-4">
		<select id="id_tensile" name="id_tensile" class="form-control chosen-select" onchange="generateNama()" required>
			<option value="0">Select An Option</option>
			<?php foreach ($tensile as $value) {
				$sel = ($value->id == $id_tensile) ? 'selected' : '';
			?>
				<option value="<?= $value->id; ?>" <?= $sel; ?>><?= strtoupper(strtolower($value->nama)) ?></option>
			<?php } ?>
		</select>
	</div>
</div>


<div class="form-group row mb-3">
	<div class="col-md-2">
		<label>No. Accurate <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control" id="no_accurate" name="no_accurate" value="<?= $no_accurate ?>" placeholder="No. Accurate">
	</div>
</div>

<!-- <div class="form-group row mb-3">
	<div class="col-md-2">
		<label for="id_unit_other">Other Unit <span class='text-danger'>*</span> / Conversion <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-2">
		<select id="id_unit_other" name="id_unit_other" class="form-control chosen-select" required>
			<option value="0">Select An Option</option>
			<?php foreach ($satuan as $value) {
				$sel = ($value->id == $id_unit_other) ? 'selected' : '';
			?>
				<option value="<?= $value->id; ?>" <?= $sel; ?>><?= strtoupper(strtolower($value->code)) ?></option>
			<?php } ?>
		</select>
	</div>
	<div class="col-md-2">
		<input type="text" id="konversi_other" name="konversi_other" class="form-control maskM" placeholder="Conversion" value="<?= $konversi_other; ?>">
	</div>
</div> -->


<div class="form-group row mb-3">
	<div class="col-md-2">
		<label for="id_unit_packing">Packing Unit / Conversion </label>
	</div>
	<div class="col-md-2">
		<select id="id_unit_packing" name="id_unit_packing" class="form-control chosen-select">
			<option value="0">Select An Option</option>
			<?php foreach ($satuan_packing as $value) {
				$sel = ($value->id == $id_unit_packing) ? 'selected' : '';
			?>
				<option value="<?= $value->id; ?>" <?= $sel; ?>><?= strtoupper(strtolower($value->code)) ?></option>
			<?php } ?>
		</select>
	</div>
	<div class="col-md-2">
		<input type="text" id="konversi" name="konversi" class="form-control maskM" placeholder="Conversion" value="<?= $konversi; ?>">
	</div>
	<div class="col-md-2">
		<label for="id_unit">Unit Measurement</label>
	</div>
	<div class="col-md-4">
		<select id="id_unit" name="id_unit" class="form-control chosen-select">
			<option value="0">Select An Option</option>
			<?php foreach ($satuan as $value) {
				$sel = ($value->id == $id_unit) ? 'selected' : '';
			?>
				<option value="<?= $value->id; ?>" <?= $sel; ?>><?= strtoupper($value->code) ?></option>
			<?php } ?>
		</select>
	</div>
</div>

<div class="form-group row mb-3">
	<div class="col-md-2">
		<label for="max_stok">Maximum Stock <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control maskM" id="max_stok" name="max_stok" value="<?= $max_stok; ?>" placeholder="Maximum Stock">
	</div>
	<div class="col-md-2">
		<label for="min_stok">Minimum Stock <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control maskM" id="min_stok" name="min_stok" value="<?= $min_stok; ?>" placeholder="Minimum Stock">
	</div>
</div>

<div class="form-group row mb-3">
	<div class="col-md-2">
		<label for="photo" class="form-label mb-0">Upload MSDS</label>
	</div>

	<div class="col-md-10">
		<!-- wrapper -->
		<div class="d-flex flex-column gap-2">

			<!-- custom file input -->
			<div class="d-flex align-items-center gap-2 flex-wrap">
				<!-- input aslinya disembunyikan -->
				<input type="file" name="photo" id="photo" class="d-none" accept=".pdf,.jpg,.jpeg,.png">

				<!-- tombol pilih file -->
				<button type="button" class="btn btn-outline-warning" id="btnPickMsds">
					<i class="ti ti-upload me-1"></i> Choose File
				</button>

				<!-- nama file -->
				<span class="text-muted" id="msdsFileName">No file chosen</span>

				<!-- tombol clear -->
				<button type="button" class="btn btn-light border" id="btnClearMsds" style="display:none;">
					<i class="ti ti-x me-1"></i> Clear
				</button>
			</div>

			<!-- hint -->
			<small class="text-muted">
				Allowed: PDF/JPG/PNG. Max size 2MB.
			</small>

			<!-- existing file -->
			<?php if (!empty($file_msds)) : ?>
				<div class="d-flex align-items-center gap-2 flex-wrap">
					<span class="badge bg-light text-dark border">
						<i class="ti ti-file-description me-1"></i> Existing MSDS
					</span>
					<a href="<?= base_url() . $file_msds; ?>" target="_blank" class="btn btn-sm btn-success">
						<i class="ti ti-download me-1"></i> Download
					</a>
					<a href="<?= base_url() . $file_msds; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
						<i class="ti ti-eye me-1"></i> Preview
					</a>
				</div>
			<?php endif; ?>

		</div>
	</div>
</div>

<script>
	(function() {
		const input = document.getElementById('photo');
		const btnPick = document.getElementById('btnPickMsds');
		const btnClear = document.getElementById('btnClearMsds');
		const fileName = document.getElementById('msdsFileName');

		if (!input || !btnPick || !fileName) return;

		btnPick.addEventListener('click', function() {
			input.click();
		});

		input.addEventListener('change', function() {
			const name = (input.files && input.files.length) ? input.files[0].name : 'No file chosen';
			fileName.textContent = name;

			if (input.files && input.files.length) {
				btnClear.style.display = 'inline-flex';
			} else {
				btnClear.style.display = 'none';
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

<hr>

<div class="form-group row mb-3">
	<div class="col-md-2">
		<label>Dimension (L,W,H)</label>
	</div>
	<div class="col-md-3">
		<input type="text" class="form-control maskM getCub" id="length" name="length" value="<?= $length; ?>" placeholder="Length">
	</div>
	<div class="col-md-3">
		<input type="text" class="form-control maskM getCub" id="wide" name="wide" value="<?= $wide; ?>" placeholder="Wide">
	</div>
	<div class="col-md-3">
		<input type="text" class="form-control maskM getCub" id="high" name="high" value="<?= $high; ?>" placeholder="High">
	</div>
</div>

<div class="form-group row mb-3">
	<div class="col-md-2">
		<label for="cub">CBM</label>
	</div>
	<div class="col-md-3">
		<input type="text" class="form-control" id="cub" name="cub" placeholder="CBM" readonly value="<?= $cub; ?>">
	</div>
</div>

<div class="form-group row mb-3">
	<div class="col-md-2">
		<label for="id_supplier">Alternative Supplier</label>
	</div>
	<div class="col-md-6">
		<select id="id_supplier" name="id_supplier" class="form-control chosen-select" required>
			<option value="0">Select An Option</option>
			<?php foreach ($supplier as $value) {
				$sel = ($value->id == $id_supplier) ? 'selected' : '';
			?>
				<option value="<?= $value->id; ?>" <?= $sel; ?>><?= strtoupper(strtolower($value->nama)) ?></option>
			<?php } ?>
		</select>
	</div>
</div>

<!-- NOTE: pilih salah satu cara init:
   - DISARANKAN: init select2 di file list (ajax success) pakai dropdownParent modal.
   - Kalau tetap init di partial ini, pakai dropdownParent juga (di bawah).
-->
<script>
	$(document).ready(function() {
		$('.chosen-select').select2({
			width: '100%',
			dropdownParent: $('#dialog-popup')
		});
		$('.maskM').autoNumeric();
		$('#thickness').autoNumeric('init', {
			aSep: ',',
			aDec: '.',
			mDec: 3,
			mInt: 4,
			vMin: '0.000',
			vMax: '9999.999'
		});
	});

	function generateNama() {
		// Ambil nilai dari setiap dropdown dan input
		var jenisLogam = $("#code_lv1 option:selected").text().trim();
		var slithed = $("#code_lv2 option:selected").text().trim();
		var boron = $("#code_lv3 option:selected").text().trim();
		var thickness = $("#thickness").val().trim();
		var width = $("#width").val().trim();
		var coating = $("#id_coating option:selected").text().trim();
		var tensile = $("#id_tensile option:selected").text().trim();
		var warna = $("#warna").val().trim();

		// Gabungkan semua nilai sesuai format yang diinginkan
		var nama = jenisLogam + " " + slithed + " " + boron + " " + thickness + "-" + width + "-" + coating + " - " + tensile + " - " + warna;

		// Set hasil gabungan ke dalam kolom nama
		$("#nama").val(nama);
	}
</script>