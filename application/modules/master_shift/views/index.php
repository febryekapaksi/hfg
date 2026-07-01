<?php
$ENABLE_ADD    = has_permission('Master_Shift.Add');
$ENABLE_MANAGE = has_permission('Master_Shift.Manage');
$ENABLE_DELETE = has_permission('Master_Shift.Delete');
?>

<div class="card shadow-sm border-0">
	<div class="card-header bg-white">
		<div class="d-flex flex-column flex-md-row gap-2 align-items-md-center">
			<div class="d-flex gap-2 flex-wrap">
				<?php if ($ENABLE_ADD) : ?>
					<button type="button" class="btn btn-success" id="btn-add">
						<i class="fa fa-plus me-1"></i> Add Data
					</button>
				<?php endif; ?>
			</div>
			<div class="ms-md-auto"></div>
		</div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table id="table_shift" class="table table-striped table-hover align-middle w-100">
				<thead class="table-light">
					<tr>
						<th style="width:60px;">#</th>
						<th>Shift Name</th>
						<th>Description</th>
						<th>Last By</th>
						<th class="text-center">Last Date</th>
						<th style="width:120px;">Action</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal-form" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-md modal-dialog-scrollable">
		<div class="modal-content">
			<form id="form-shift" method="post" autocomplete="off">
				<div class="modal-header">
					<h5 class="modal-title" id="modal-title">
						<i class="ti ti-clock me-2"></i> Shift Data
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" id="modal-body">
					<!-- form loaded here via AJAX -->
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary" id="btn-save">
						<i class="ti ti-device-floppy me-1"></i> Save
					</button>
					<button type="button" class="btn btn-dark" data-bs-dismiss="modal">
						<i class="ti ti-x me-1"></i> Cancel
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
var base_url = '<?= base_url(); ?>';
var controller = 'master_shift';

$(document).ready(function() {
	// Init DataTable
	var table = $('#table_shift').DataTable({
		processing: true,
		serverSide: true,
		stateSave: true,
		destroy: true,
		responsive: true,
		aaSorting: [[1, "asc"]],
		iDisplayLength: 10,
		aLengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
		ajax: {
			url: base_url + controller + '/data_side',
			type: "POST"
		}
	});

	// Add Data
	$('#btn-add').on('click', function() {
		$('#modal-title').html("<i class='ti ti-plus me-2'></i> Add Shift");
		loadForm();
	});

	// Edit Data
	$(document).on('click', '.edit', function() {
		var id = $(this).data('id');
		$('#modal-title').html("<i class='ti ti-edit me-2'></i> Edit Shift");
		loadForm(id);
	});

	// Load form into modal
	function loadForm(id) {
		$.ajax({
			type: 'POST',
			url: base_url + controller + '/form',
			data: { id: id || '' },
			success: function(html) {
				$('#modal-body').html(html);
				var modal = new bootstrap.Modal(document.getElementById('modal-form'));
				modal.show();
			}
		});
	}

	// Submit form
	$('#form-shift').on('submit', function(e) {
		e.preventDefault();

		var nama = $('#nama_shift').val();
		if (!nama || !nama.trim()) {
			swal({ title: "Warning!", text: "Shift Name is required.", type: "warning" });
			return;
		}

		swal({
			title: "Save data?",
			text: "Please make sure the data is correct.",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-primary",
			confirmButtonText: "Yes, Save!",
			cancelButtonText: "Cancel",
			closeOnConfirm: true,
			closeOnCancel: false
		}, function(isConfirm) {
			if (!isConfirm) {
				swal("Cancelled", "Process cancelled.", "error");
				return;
			}

			var formData = new FormData($('#form-shift')[0]);

			$.ajax({
				url: base_url + controller + '/save',
				type: "POST",
				data: formData,
				dataType: "json",
				processData: false,
				contentType: false,
				beforeSend: function() {
					$('#btn-save').prop('disabled', true).html('<i class="ti ti-loader me-1"></i> Saving...');
				},
				success: function(res) {
					if (res.status == 1) {
						swal({ title: "Success!", text: res.pesan, type: "success", timer: 2000, showConfirmButton: false });
						bootstrap.Modal.getInstance(document.getElementById('modal-form')).hide();
						table.ajax.reload(null, false);
					} else {
						swal({ title: "Failed!", text: res.pesan, type: "warning" });
					}
				},
				error: function() {
					swal({ title: "Error!", text: "An error occurred. Please try again.", type: "error" });
				},
				complete: function() {
					$('#btn-save').prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i> Save');
				}
			});
		});
	});

	// Delete Data
	$(document).on('click', '.delete', function() {
		var id = $(this).data('id');

		swal({
			title: "Delete data?",
			text: "This action cannot be undone!",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "Yes, Delete!",
			cancelButtonText: "Cancel",
			closeOnConfirm: false
		}, function() {
			$.ajax({
				type: 'POST',
				url: base_url + controller + '/hapus',
				dataType: "json",
				data: { id: id },
				success: function(res) {
					if (res.status == 1) {
						swal({ title: "Success", text: res.pesan, type: "success" }, function() {
							table.ajax.reload(null, false);
						});
					} else {
						swal({ title: "Failed", text: res.pesan, type: "error" });
					}
				},
				error: function() {
					swal({ title: "Error", text: "Server request failed.", type: "error" });
				}
			});
		});
	});
});
</script>
