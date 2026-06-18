<?php
$ENABLE_ADD     = has_permission('Material_Jenis.Add');
$ENABLE_MANAGE  = has_permission('Material_Jenis.Manage');
$ENABLE_VIEW    = has_permission('Material_Jenis.View');
$ENABLE_DELETE  = has_permission('Material_Jenis.Delete');

$id       = (!empty($listData[0]->id)) ? $listData[0]->id : '';
$code_lv1 = (!empty($listData[0]->code_lv1)) ? $listData[0]->code_lv1 : '';
$code_lv2 = (!empty($listData[0]->code_lv2)) ? $listData[0]->code_lv2 : '';
$code     = (!empty($listData[0]->code_lv3)) ? $listData[0]->code_lv3 : ''; // FIX: tadi salah ambil
$nama     = (!empty($listData[0]->nama)) ? $listData[0]->nama : '';
?>

<div class="form-group row mb-3">
	<div class="col-md-3">
		<label for="code_lv1">Metal Type <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-9">
		<select name="code_lv1" id="code_lv1" class="form-control chosen-select" required>
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
	<div class="col-md-3">
		<label for="code_lv2">Material Category <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-9">
		<select name="code_lv2" id="code_lv2" class="form-control chosen-select" required>
			<?php
			// kalau edit + listLevel2 ada, tampilkan list
			if (!empty($id) && !empty($listLevel2)) {
				echo "<option value='0'>Select Material Category</option>";
				foreach ($listLevel2 as $key => $value) {
					$selected = ($code_lv2 == $value['code_lv2']) ? 'selected' : '';
					echo "<option value='" . $value['code_lv2'] . "' " . $selected . ">" . strtoupper($value['nama']) . "</option>";
				}
			} else {
				// mode add: nanti diisi via ajax ketika pilih code_lv1
				echo "<option value='0'>Select Material Category</option>";
			}
			?>
		</select>
	</div>
</div>

<div class="form-group row mb-3">
	<div class="col-md-3">
		<label for="nama">Material Sub-type <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-9">
		<input type="hidden" class="form-control" id="id" name="id" value="<?= $id; ?>">
		<input type="hidden" class="form-control" id="code" name="code" value="<?= $code; ?>">
		<input type="text" class="form-control" id="nama" name="nama" required placeholder="Material Sub-type" value="<?= $nama; ?>">
	</div>
</div>