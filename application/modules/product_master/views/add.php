<?php
$id              = (!empty($listData[0]->id))              ? $listData[0]->id              : '';
$code_lv1        = (!empty($listData[0]->code_lv1))        ? $listData[0]->code_lv1        : '';
$code_lv2        = (!empty($listData[0]->code_lv2))        ? $listData[0]->code_lv2        : '';
$code_lv3        = (!empty($listData[0]->code_lv3))        ? $listData[0]->code_lv3        : '';
$code_lv4        = (!empty($listData[0]->code_lv4))        ? $listData[0]->code_lv4        : '';
$nama            = (!empty($listData[0]->nama))            ? $listData[0]->nama            : '';
$retail          = (!empty($listData[0]->retail))          ? $listData[0]->retail          : '';
$code            = (!empty($listData[0]->code))            ? $listData[0]->code            : '';
$trade_name      = (!empty($listData[0]->trade_name))      ? $listData[0]->trade_name      : '';
$max_stok        = (!empty($listData[0]->max_stok))        ? $listData[0]->max_stok        : '';
$min_stok        = (!empty($listData[0]->min_stok))        ? $listData[0]->min_stok        : '';
$moq             = (!empty($listData[0]->moq))             ? $listData[0]->moq             : '';
$id_unit_packing = (!empty($listData[0]->id_unit_packing)) ? $listData[0]->id_unit_packing : '';
$konversi        = (!empty($listData[0]->konversi))        ? $listData[0]->konversi        : '';
$id_unit         = (!empty($listData[0]->id_unit))         ? $listData[0]->id_unit         : '';
$panjang         = (!empty($listData[0]->panjang))         ? $listData[0]->panjang         : '';
$tebal           = (!empty($listData[0]->tebal))           ? $listData[0]->tebal           : '';
$length          = (!empty($listData[0]->length))          ? $listData[0]->length          : '';
$wide            = (!empty($listData[0]->wide))            ? $listData[0]->wide            : '';
$high            = (!empty($listData[0]->high))            ? $listData[0]->high            : '';
$weight          = (!empty($listData[0]->weight))          ? $listData[0]->weight          : '';
$cub             = (!empty($listData[0]->cub))             ? $listData[0]->cub             : '';
$file_msds       = (!empty($listData[0]->file_msds))       ? $listData[0]->file_msds       : '';
$status1         = (!empty($listData[0]->status) && $listData[0]->status == '1') ? 'checked' : '';
$status2         = (!empty($listData[0]->status) && $listData[0]->status == '0') ? 'checked' : '';
?>

<input type="hidden" id="id" name="id" value="<?= $id; ?>">
<input type="hidden" id="code_lv4" name="code_lv4" value="<?= $code_lv4; ?>">

<div class="row mb-3">
	<div class="col-md-2">
		<label class="col-form-label">Product Type <span class="text-danger">*</span></label>
	</div>
	<div class="col-md-10">
		<select name="code_lv1" id="code_lv1" class="form-select chosen-select">
			<option value="0">Select Product Type</option>
			<?php foreach ($listLevel1 as $key => $value) : ?>
				<option value="<?= $value['code_lv1']; ?>"
					<?= ($code_lv1 == $value['code_lv1']) ? 'selected' : ''; ?>>
					<?= strtoupper($value['nama']); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
</div>

<div class="row mb-3">
	<div class="col-md-2">
		<label class="col-form-label">Product Category <span class="text-danger">*</span></label>
	</div>
	<div class="col-md-10">
		<select name="code_lv2" id="code_lv2" class="form-select chosen-select">
			<option value="0">Select Product Category</option>
			<?php if (!empty($id) && !empty($listLevel2)) : ?>
				<?php foreach ($listLevel2 as $key => $value) : ?>
					<option value="<?= $value['code_lv2']; ?>"
						<?= ($code_lv2 == $value['code_lv2']) ? 'selected' : ''; ?>>
						<?= strtoupper($value['nama']); ?>
					</option>
				<?php endforeach; ?>
			<?php endif; ?>
		</select>
	</div>
</div>

<div class="row mb-3">
	<div class="col-md-2">
		<label class="col-form-label">Product Jenis <span class="text-danger">*</span></label>
	</div>
	<div class="col-md-10">
		<select name="code_lv3" id="code_lv3" class="form-select chosen-select">
			<option value="0">Select Product Jenis</option>
			<?php if (!empty($id) && !empty($listLevel3)) : ?>
				<?php foreach ($listLevel3 as $key => $value) : ?>
					<option value="<?= $value['code_lv3']; ?>"
						<?= ($code_lv3 == $value['code_lv3']) ? 'selected' : ''; ?>>
						<?= strtoupper($value['nama']); ?>
					</option>
				<?php endforeach; ?>
			<?php endif; ?>
		</select>
	</div>
</div>

<hr>

<div class="row mb-3">
	<div class="col-md-2">
		<label class="col-form-label">Product Master <span class="text-danger">*</span></label>
	</div>
	<div class="col-md-10">
		<input type="text" class="form-control" id="nama" name="nama"
			required placeholder="Product Master Name" value="<?= $nama; ?>">
	</div>
</div>

<div class="row mb-3">
	<div class="col-md-2">
		<label class="col-form-label">Panjang</label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control maskM" id="panjang" name="panjang"
			value="<?= $panjang; ?>" placeholder="Panjang">
	</div>
	<div class="col-md-2">
		<label class="col-form-label">Tebal</label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control maskM" id="tebal" name="tebal"
			value="<?= $tebal; ?>" placeholder="Tebal">
	</div>
</div>

<div class="row mb-3">
	<div class="col-md-2">
		<label class="col-form-label">Product Retail</label>
	</div>
	<div class="col-md-10">
		<input type="text" class="form-control" id="retail" name="retail"
			placeholder="Product Retail" value="<?= $retail; ?>">
	</div>
</div>

<hr>

<div class="row mb-3">
	<div class="col-md-2">
		<label class="col-form-label">Product Code</label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control" id="code" name="code"
			value="<?= $code; ?>" placeholder="Product Code">
		<small>
			<span style="cursor:pointer;" class="text-primary" id="updateManualCode">
				Update Code Program
			</span>
		</small>
	</div>
	<div class="col-md-2">
		<label class="col-form-label">Trade Name</label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control" id="trade_name" name="trade_name"
			value="<?= $trade_name; ?>" placeholder="Trade Name">
	</div>
</div>

<div class="row mb-3">
	<div class="col-md-2">
		<label class="col-form-label">Packing Unit / Conversion</label>
	</div>
	<div class="col-md-2">
		<select id="id_unit_packing" name="id_unit_packing" class="form-select chosen-select">
			<option value="0">Select Unit</option>
			<?php foreach ($satuan_packing as $value) : ?>
				<option value="<?= $value->id; ?>"
					<?= ($value->id == $id_unit_packing) ? 'selected' : ''; ?>>
					<?= strtoupper($value->code); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="col-md-2">
		<input type="text" id="konversi" name="konversi"
			class="form-control maskM" placeholder="Conversion"
			value="<?= $konversi; ?>">
	</div>
	<div class="col-md-2">
		<label class="col-form-label">Unit Measurement</label>
	</div>
	<div class="col-md-2">
		<select id="id_unit" name="id_unit" class="form-select chosen-select">
			<option value="0">Select Unit</option>
			<?php foreach ($satuan as $value) : ?>
				<option value="<?= $value->id; ?>"
					<?= ($value->id == $id_unit) ? 'selected' : ''; ?>>
					<?= strtoupper($value->code); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
</div>

<div class="row mb-3">
	<div class="col-md-2">
		<label class="col-form-label">Maximum Stock</label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control maskM" id="max_stok" name="max_stok"
			value="<?= $max_stok; ?>" placeholder="Maximum Stock">
	</div>
	<div class="col-md-2">
		<label class="col-form-label">Minimum Stock</label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control maskM" id="min_stok" name="min_stok"
			value="<?= $min_stok; ?>" placeholder="Minimum Stock">
	</div>
</div>

<div class="row mb-3">
	<div class="col-md-2">
		<label class="col-form-label">Upload MSDS</label>
	</div>
	<div class="col-md-4">
		<input type="file" name="photo" id="photo" class="form-control">
		<?php if (!empty($file_msds)) : ?>
			<a href="<?= base_url() . $file_msds; ?>" target="_blank" class="text-info mt-1 d-inline-block">
				<i class="fa fa-download me-1"></i>Download File
			</a>
		<?php endif; ?>
	</div>
	<div class="col-md-2">
		<label class="col-form-label">MOQ</label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control maskM" id="moq" name="moq"
			value="<?= $moq; ?>" placeholder="MOQ">
	</div>
</div>

<hr>

<div class="row mb-3">
	<div class="col-md-2">
		<label class="col-form-label">Dimensi (P, L, T)</label>
	</div>
	<div class="col-md-3">
		<input type="text" class="form-control maskM getCub" id="length" name="length"
			value="<?= $length; ?>" placeholder="Panjang">
	</div>
	<div class="col-md-3">
		<input type="text" class="form-control maskM getCub" id="wide" name="wide"
			value="<?= $wide; ?>" placeholder="Lebar">
	</div>
	<div class="col-md-3">
		<input type="text" class="form-control maskM getCub" id="high" name="high"
			value="<?= $high; ?>" placeholder="Tinggi">
	</div>
</div>

<div class="row mb-3">
	<div class="col-md-2">
		<label class="col-form-label">Berat/unit</label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control" id="weight" name="weight"
			value="<?= $weight; ?>" placeholder="Berat per unit">
	</div>
</div>

<div class="row mb-3">
	<div class="col-md-2">
		<label class="col-form-label">CBM</label>
	</div>
	<div class="col-md-4">
		<input type="text" class="form-control" id="cub" name="cub"
			placeholder="CBM" readonly value="<?= $cub; ?>">
	</div>
</div>

<?php if (!empty($id)) : ?>
	<div class="row mb-3">
		<div class="col-md-2">
			<label class="col-form-label">Status</label>
		</div>
		<div class="col-md-10">
			<div class="d-flex gap-4 pt-2">
				<label>
					<input type="radio" name="status" value="1" <?= $status1; ?>> Aktif
				</label>
				<label>
					<input type="radio" name="status" value="0" <?= $status2; ?>> Non-Aktif
				</label>
			</div>
		</div>
	</div>
<?php endif; ?>

<script>
	$(document).ready(function() {
		// Init select2 dengan dropdownParent agar tidak tertutup overlay modal
		$('#dialog-popup').find('.chosen-select').select2({
			width: '100%',
			dropdownParent: $('#dialog-popup')
		});

		// Init autoNumeric
		$('#dialog-popup').find('.maskM').autoNumeric('init');
	});
</script>