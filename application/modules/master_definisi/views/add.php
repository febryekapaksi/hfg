<?php
$ENABLE_ADD     = has_permission('Master_definisi.Add');
$ENABLE_MANAGE  = has_permission('Master_definisi.Manage');
$ENABLE_VIEW    = has_permission('Master_definisi.View');
$ENABLE_DELETE  = has_permission('Master_definisi.Delete');

$id       = (!empty($header[0]->id)) ? $header[0]->id : '';
$istilah  = (!empty($header[0]->istilah)) ? $header[0]->istilah : '';
$definisi = (!empty($header[0]->definisi)) ? $header[0]->definisi : '';
?>

<div class="form-group row mb-3">
	<div class="col-md-3">
		<label for="istilah" class="form-label mb-0">Istilah <span class="text-danger">*</span></label>
	</div>
	<div class="col-md-9">
		<input type="text" class="form-control" id="istilah" name="istilah" required placeholder="Masukkan istilah" value="<?= $istilah; ?>">
		<input type="hidden" class="form-control" id="id" name="id" value="<?= $id; ?>">
	</div>
</div>

<div class="form-group row mb-3">
	<div class="col-md-3">
		<label for="definisi" class="form-label mb-0">Definisi <span class="text-danger">*</span></label>
	</div>
	<div class="col-md-9">
		<textarea class="form-control" id="definisi" name="definisi" required placeholder="Masukkan definisi" rows="4"><?= $definisi; ?></textarea>
	</div>
</div>
