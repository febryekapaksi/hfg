<?php
$ENABLE_ADD     = has_permission('Material_Type.Add');
$ENABLE_MANAGE  = has_permission('Material_Type.Manage');
$ENABLE_VIEW    = has_permission('Material_Type.View');
$ENABLE_DELETE  = has_permission('Material_Type.Delete');
?>

<style>
	.swal2-container {
        z-index: 99999 !important;
    }
</style>

<div class="card shadow-sm border-0">
	<div class="card-header bg-white d-flex align-items-center justify-content-between">
		<?php if ($ENABLE_ADD) : ?>
			<a href="javascript:void(0)" class="btn btn-success add">
				<i class="fa fa-plus me-1"></i> Add
			</a>
		<?php endif; ?>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table id="example1" class="table table-striped table-hover align-middle w-100">
				<thead class="table-light">
					<tr>
						<th>#</th>
						<th>Metal Type</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
				</thead>

				<tbody>
					<?php if (empty($result)) { ?>
						<!-- Handle empty result -->
						<?php } else {
						$numb = 0;
						foreach ($result as $record) {
							$numb++;
						?>
							<tr>
								<td><?= $numb; ?></td>
								<td><?= strtoupper($record->nama) ?></td>
								<td>
									<label class="toggle-switch">
										<input type="checkbox"
											class="toggle-status-checkbox"
											data-id="<?= $record->id ?>"
											data-status="<?= $record->status ?>"
											<?= $record->status == '1' ? 'checked' : '' ?>>
										<span class="toggle-slider"></span>
									</label>
								</td>
								<td>
									<?php if ($ENABLE_MANAGE) : ?>
										<a class="btn-icon btn-icon-edit edit" href="javascript:void(0)" data-id="<?= $record->id ?>">
											<i class="ti ti-edit"></i>
										</a>
									<?php endif; ?>

									<?php if ($ENABLE_DELETE) : ?>
										<a class="btn-icon btn-icon-delete delete" href="javascript:void(0)" data-id="<?= $record->id ?>">
											<i class="ti ti-trash"></i>
										</a>
									<?php endif; ?>
								</td>
							</tr>
					<?php }
					} ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- Modal for Add/Edit -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form id="data_form" autocomplete="off">
				<div class="modal-header">
					<h4 class="modal-title">Material Type</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" id="ModalView">
					<!-- Form content here -->
				</div>
				<div class="modal-footer">
					<button type="submit" name="save" class="btn btn-primary">
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

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
	$(document).ready(function() {
		// DataTables initialization
		$('#example1').DataTable({
			responsive: true,
			pageLength: 10,
			lengthMenu: [10, 25, 50, 100],
			language: {
				search: "Search:",
				lengthMenu: "Show _MENU_ entries",
				info: "Showing _START_ to _END_ of _TOTAL_ entries",
				paginate: {
					previous: "Prev",
					next: "Next"
				}
			}
		});

		// Edit and Add actions
		$(document).on('click', '.edit', function() {
			var id = $(this).data('id');
			$.ajax({
				type: 'POST',
				url: siteurl + active_controller + '/add/' + id,
				success: function(data) {
					$("#dialog-popup").modal('show')
					$('#ModalView').html(data);
				}
			});
		});

		$(document).on('click', '.add', function() {
			$.ajax({
				type: 'POST',
				url: siteurl + active_controller + '/add/',
				success: function(data) {
					$("#dialog-popup").modal('show')
					$('#ModalView').html(data);
				}
			});
		});

		$(document).on('submit', '#data_form', function(e) {
			e.preventDefault();
			var data = $(this).serialize();
			Swal.fire({
				title: "Are you sure?", text: "Data will be processed!", icon: "warning",
				showCancelButton: true, confirmButtonText: "Yes", cancelButtonText: "No"
			}).then(function(result) {
				if (result.isConfirmed) {
					$.ajax({
						type: 'POST', url: siteurl + active_controller + 'add', dataType: 'json', data: data,
						success: function(data) {
							if (data.status == '1') {
								Swal.fire({ title: "Success", text: data.pesan, icon: "success", timer: 1500, showConfirmButton: false })
									.then(function(){ window.location.reload(true); });
							} else {
								Swal.fire({ title: "Error", text: data.pesan, icon: "error", confirmButtonText: "OK" });
							}
						},
						error: function() { Swal.fire({ title: "Error", text: "Error processing!", icon: "error", confirmButtonText: "OK" }); }
					});
				}
			});
		});

		$(document).on('click', '.delete', function(e) {
			e.preventDefault();
			var id = $(this).data('id');
			Swal.fire({
				title: "Are you sure?", text: "Data will be deleted!", icon: "warning",
				showCancelButton: true, confirmButtonColor: "#dc3545", confirmButtonText: "Yes", cancelButtonText: "No"
			}).then(function(result) {
				if (result.isConfirmed) {
					$.ajax({
						type: 'POST', url: siteurl + active_controller + '/delete', dataType: 'json', data: { 'id': id },
						success: function(data) {
							if (data.status == '1') {
								Swal.fire({ title: "Success", text: data.pesan, icon: "success", timer: 1500, showConfirmButton: false })
									.then(function(){ window.location.reload(true); });
							} else {
								Swal.fire({ title: "Error", text: data.pesan, icon: "error", confirmButtonText: "OK" });
							}
						},
						error: function() { Swal.fire({ title: "Error", text: "Error processing!", icon: "error", confirmButtonText: "OK" }); }
					});
				}
			});
		});

		$(document).on('change', '.toggle-status-checkbox', function() {
			const id = $(this).data('id');
			const currentStatus = $(this).data('status');
			const checkbox = $(this);

			$.ajax({
				url: siteurl + active_controller + 'toggle_status',
				type: 'POST',
				data: { id: id, status: currentStatus },
				dataType: 'json',
				beforeSend: function() { checkbox.prop('disabled', true); },
				success: function(response) {
					if (response.status) {
						checkbox.data('status', response.new_status);
						checkbox.prop('checked', response.new_status == 1);
						Swal.fire({ title: "Success", text: response.message, icon: "success", timer: 1500, showConfirmButton: false });
					} else {
						checkbox.prop('checked', currentStatus == 1);
						Swal.fire({ title: "Error", text: response.message, icon: "error", confirmButtonText: "OK" });
					}
				},
				error: function(xhr, status, error) {
					checkbox.prop('checked', currentStatus == 1);
					console.error('Error:', error);
					Swal.fire({ title: "Error", text: "Error processing!", icon: "error", confirmButtonText: "OK" });
				},
				complete: function() { checkbox.prop('disabled', false); }
			});
		});

	});
</script>