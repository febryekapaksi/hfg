<?php
$id         = isset($row->id) ? $row->id : '';
$nama_shift = isset($row->nama_shift) ? $row->nama_shift : '';
$keterangan = isset($row->keterangan) ? $row->keterangan : '';
?>

<input type="hidden" name="id" id="id" value="<?= $id; ?>">

<div class="row g-3">
	<div class="col-12">
		<label class="form-label">Shift Name <span class="text-danger">*</span></label>
		<input type="text" name="nama_shift" id="nama_shift" class="form-control"
			placeholder="e.g. Shift 1, Shift 2, Shift 3" value="<?= htmlspecialchars($nama_shift); ?>" required>
	</div>

	<div class="col-12">
		<label class="form-label">Description <small class="text-muted">(optional)</small></label>
		<textarea name="keterangan" id="keterangan" class="form-control" rows="3"
			placeholder="e.g. 07:00 - 15:00"><?= htmlspecialchars($keterangan); ?></textarea>
	</div>
</div>
