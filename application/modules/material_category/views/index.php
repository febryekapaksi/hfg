<?php
$ENABLE_ADD     = has_permission('Material_Category.Add');
$ENABLE_MANAGE  = has_permission('Material_Category.Manage');
$ENABLE_VIEW    = has_permission('Material_Category.View');
$ENABLE_DELETE  = has_permission('Material_Category.Delete');
?>

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
						<th>Slitted / Mother Coil</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
				</thead>

				<tbody>
					<?php
					if (!empty($result)) {
						$numb = 0;
						foreach ($result as $record) {
							$numb++;
							$material_type = (!empty($get_level_1[$record->code_lv1]['nama'])) ? $get_level_1[$record->code_lv1]['nama'] : '';
					?>
							<tr>
								<td><?= $numb; ?></td>
								<td><?= strtoupper($material_type) ?></td>
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
										<a class="btn-icon btn-icon-edit edit" href="javascript:void(0)" data-id="<?= $record->id ?>" title="Edit">
											<i class="ti ti-edit"></i>
										</a>
									<?php endif; ?>

									<?php if ($ENABLE_DELETE) : ?>
										<a class="btn-icon btn-icon-delete delete" href="javascript:void(0)" data-id="<?= $record->id ?>" title="Delete">
											<i class="ti ti-trash"></i>
										</a>
									<?php endif; ?>
								</td>
							</tr>
					<?php
						}
					}
					?>
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
					<h4 class="modal-title" id="head_title">Material Category</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" id="ModalView">
					<!-- ajax content -->
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

		// Edit
		$(document).on('click', '.edit', function() {
			var id = $(this).data('id');
			$("#head_title").html("Material Category");
			$.ajax({
				type: 'POST',
				url: siteurl + active_controller + '/add/' + id,
				success: function(data) {
					$("#dialog-popup").modal('show');
					$('#ModalView').html(data);
					$('#code_lv1').select2({
						width: '100%',
						dropdownParent: $('#dialog-popup')
					});
				}
			});
		});

		// Add
		$(document).on('click', '.add', function() {
			$("#head_title").html("Material Category");
			$.ajax({
				type: 'POST',
				url: siteurl + active_controller + '/add/',
				success: function(data) {
					$("#dialog-popup").modal('show');
					$('#ModalView').html(data);
					$('#code_lv1').select2({
						width: '100%',
						dropdownParent: $('#dialog-popup')
					});
				}
			});
		});

		// Submit form
		$(document).on('submit', '#data_form', function(e) {
			e.preventDefault();

			var data = $(this).serialize();
			var code_lv1 = $('#code_lv1').val();

			if (code_lv1 == '0') {
				swal({
					title: "Error Message!",
					text: 'Material type not selected...',
					type: "warning"
				});
				return false;
			}

			swal({
				title: "Are you sure?",
				text: "Data will be processed!",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Yes",
				cancelButtonText: "No",
				closeOnConfirm: false
			}, function() {
				$.ajax({
					type: 'POST',
					url: siteurl + active_controller + 'add',
					dataType: 'json',
					data: data,
					success: function(res) {
						if (res.status == '1') {
							swal({
								title: "Success",
								text: res.pesan,
								type: "success"
							}, function() {
								window.location.reload(true);
							});
						} else {
							swal({
								title: "Error",
								text: res.pesan,
								type: "error"
							});
						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "Error processing!",
							type: "error"
						});
					}
				});
			});
		});

		// Delete
		$(document).on('click', '.delete', function(e) {
			e.preventDefault();
			var id = $(this).data('id');

			swal({
				title: "Are you sure?",
				text: "Data will be deleted!",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Yes",
				cancelButtonText: "No",
				closeOnConfirm: false
			}, function() {
				$.ajax({
					type: 'POST',
					url: siteurl + active_controller + '/delete',
					dataType: 'json',
					data: {
						'id': id
					},
					success: function(res) {
						if (res.status == '1') {
							swal({
								title: "Success",
								text: res.pesan,
								type: "success"
							}, function() {
								window.location.reload(true);
							});
						} else {
							swal({
								title: "Error",
								text: res.pesan,
								type: "error"
							});
						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "Error processing!",
							type: "error"
						});
					}
				});
			});
		});

		// Toggle Status
		$(document).on('change', '.toggle-status-checkbox', function() {
			const id = $(this).data('id');
			const currentStatus = $(this).data('status');
			const checkbox = $(this);

			$.ajax({
				url: siteurl + active_controller + 'toggle_status',
				type: 'POST',
				data: {
					id: id,
					status: currentStatus
				},
				dataType: 'json',
				beforeSend: function() {
					checkbox.prop('disabled', true);
				},
				success: function(response) {
					const ok = (response.status === true || response.status === 1 || response.status === '1');

					if (ok) {
						const newStatus = (response.new_status !== undefined) ? response.new_status : (currentStatus == 1 ? 0 : 1);
						const msg = response.message || response.pesan || "Status updated.";

						checkbox.data('status', newStatus);
						checkbox.prop('checked', newStatus == 1);

						swal({
							title: "Success",
							text: msg,
							type: "success"
						});
					} else {
						const msg = response.message || response.pesan || "Failed to update status.";
						checkbox.prop('checked', currentStatus == 1);

						swal({
							title: "Error",
							text: msg,
							type: "error"
						});
					}
				},
				error: function() {
					checkbox.prop('checked', currentStatus == 1);
					swal({
						title: "Error",
						text: "Error processing!",
						type: "error"
					});
				},
				complete: function() {
					checkbox.prop('disabled', false);
				}
			});
		});

	});
</script>