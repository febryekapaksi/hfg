<?php
$ENABLE_ADD     = has_permission('Material_Type.Add');
$ENABLE_MANAGE  = has_permission('Material_Type.Manage');
$ENABLE_VIEW    = has_permission('Material_Type.View');
$ENABLE_DELETE  = has_permission('Material_Type.Delete');

$id = (!empty($listData[0]->id)) ? $listData[0]->id : '';
$code = (!empty($listData[0]->code_lv1)) ? $listData[0]->code_lv1 : '';
$nama = (!empty($listData[0]->nama)) ? $listData[0]->nama : '';
?>

<div class="form-group row">
	<div class="col-md-3">
		<label for="nama">Metal Type <span class='text-danger'>*</span></label>
	</div>
	<div class="col-md-9">
		<input type="hidden" class="form-control" id="id" name="id" value='<?= $id; ?>'>
		<input type="hidden" class="form-control" id="code" name="code" value='<?= $code; ?>'>
		<input type="text" class="form-control" id="nama" name="nama" required placeholder="Material Type" value='<?= $nama; ?>'>
	</div>
</div>