<?php
$ENABLE_ADD     = has_permission('Material_Category.Add');
$ENABLE_MANAGE  = has_permission('Material_Category.Manage');
$ENABLE_VIEW    = has_permission('Material_Category.View');
$ENABLE_DELETE  = has_permission('Material_Category.Delete');

$id       = (!empty($listData[0]->id)) ? $listData[0]->id : '';
$code_lv1 = (!empty($listData[0]->code_lv1)) ? $listData[0]->code_lv1 : '';
$code     = (!empty($listData[0]->code_lv2)) ? $listData[0]->code_lv2 : '';
$nama     = (!empty($listData[0]->nama)) ? $listData[0]->nama : '';
?>

<div class="form-group row mb-3">
	<div class="col-md-3">
		<label for="code_lv1">Metal Type <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-9">
		<select name="code_lv1" id="code_lv1" class="form-control chosen-select" required>
			<option value="0">Select Material Type</option>
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
		<label for="nama">Slithed/Mother Coil <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-9">
		<input type="hidden" class="form-control" id="id" name="id" value="<?= $id; ?>">
		<input type="hidden" class="form-control" id="code" name="code" value="<?= $code; ?>">
		<input type="text" class="form-control" id="nama" name="nama" required placeholder="Material Category" value="<?= $nama; ?>">
	</div>
</div>

<script>
	$(document).ready(function() {
		$('.chosen-select').select2({
			width: '100%'
		});
	});
</script>