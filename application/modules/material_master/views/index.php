<?php
$ENABLE_ADD     = has_permission('Material_Master.Add');
$ENABLE_MANAGE  = has_permission('Material_Master.Manage');
$ENABLE_VIEW    = has_permission('Material_Master.View');
$ENABLE_DELETE  = has_permission('Material_Master.Delete');
?>

<!-- DataTables Bootstrap 5 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<div class="card shadow-sm border-0">
	<div class="card-header bg-white">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

			<!-- FILTERS -->
			<div class="row g-2 flex-grow-1">
				<div class="col-md-3">
					<select name="level1" id="level1" class="form-control select2">
						<option value="0">METAL TYPE</option>
						<?php foreach ($get_level_1 as $key => $value) : ?>
							<option value="<?= $value['code_lv1']; ?>"><?= strtoupper($value['nama']); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="col-md-3">
					<select name="level2" id="level2" class="form-control select2">
						<option value="0">SLITHED/MOTHER COIL</option>
						<?php foreach ($get_level_2 as $key => $value) : ?>
							<option value="<?= $value['code_lv2']; ?>"><?= strtoupper($value['nama']); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="col-md-4">
					<select name="level3" id="level3" class="form-control select2">
						<option value="0">BORON/NON BORON</option>
						<?php foreach ($get_level_3 as $key => $value) : ?>
							<option value="<?= $value['code_lv3']; ?>"><?= strtoupper($value['nama']); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="col-md-4"></div>
			</div>

			<!-- ACTION BUTTONS -->
			<div class="d-flex gap-2 ms-auto">
				<?php if ($ENABLE_ADD) : ?>
					<a class="btn btn-md btn-success add" href="javascript:void(0)" title="Add">
						<i class="fa fa-plus me-1"></i>Add
					</a>
					<a class="btn btn-md btn-info" href="<?= base_url('material_master/download_excel'); ?>" target="_blank" title="Download">
						<i class="fa fa-excel me-1"></i>Excel
					</a>
				<?php endif; ?>
			</div>

		</div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table id="example1" class="table table-striped table-hover align-middle w-100">
				<thead class="table-light">
					<tr>
						<th>#</th>
						<th>Metal Type</th>
						<th>Slitted / Mother Coil</th>
						<th>Boron / Non Boron</th>
						<th>Material Name</th>
						<th>Status</th>
						<th width="7%">Action</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>

<!-- Modal for Add/Edit -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form id="data_form" method="post" autocomplete="off" enctype="multipart/form-data">
				<div class="modal-header">
					<h4 class="modal-title" id="head_title">Material Master</h4>
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
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script type="text/javascript">
	// ============== MODAL: ADD / EDIT =================
	function initSelect2InModal() {
		// destroy dulu biar ga double init
		['#code_lv1', '#code_lv2', '#code_lv3', '#id_unit_packing', '#id_unit', '#id_unit_other', '#id_supplier'].forEach(function(sel) {
			if ($(sel).length && $(sel).hasClass('select2-hidden-accessible')) $(sel).select2('destroy');
		});

		$('.chosen-select').select2({
			width: '100%',
			dropdownParent: $('#dialog-popup')
		});
	}

	function initMaskInModal() {
		$('.maskM').autoNumeric();
		$('#thickness').autoNumeric('init', {
			aSep: ',',
			aDec: '.',
			mDec: 3,
			mInt: 4,
			vMin: '0.000',
			vMax: '9999.999'
		});
	}

	$(document).on('click', '.edit', function() {
		var id = $(this).data('id');
		$("#head_title").html("Material Master");
		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + '/add/' + id,
			success: function(data) {
				$("#dialog-popup").modal('show');
				$("#ModalView").html(data);

				initSelect2InModal();
				initMaskInModal();
			}
		});
	});

	$(document).on('click', '.add', function() {
		$("#head_title").html("Material Master");
		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + '/add/',
			success: function(data) {
				$("#dialog-popup").modal('show');
				$("#ModalView").html(data);

				initSelect2InModal();
				initMaskInModal();
			}
		});
	});

	// ============== CASCADE DROPDOWN (DI MODAL) =================
	$(document).on('change', '#code_lv1', function() {
		var code_lv1 = $("#code_lv1").val();

		$.ajax({
			url: siteurl + active_controller + '/get_list_level1',
			method: "POST",
			data: {
				code_lv1: code_lv1
			},
			dataType: 'json',
			success: function(res) {
				$('#code_lv2').html(res.option);
				$('#code_lv3').html("<option value='0'>Select Material Jenis</option>");

				// re-init select2 utk dropdown yang diganti
				if ($('#code_lv2').hasClass('select2-hidden-accessible')) $('#code_lv2').select2('destroy');
				if ($('#code_lv3').hasClass('select2-hidden-accessible')) $('#code_lv3').select2('destroy');

				$('#code_lv2, #code_lv3').select2({
					width: '100%',
					dropdownParent: $('#dialog-popup')
				});
			}
		});
	});

	$(document).on('change', '#code_lv2', function() {
		var code_lv1 = $("#code_lv1").val();
		var code_lv2 = $("#code_lv2").val();

		$.ajax({
			url: siteurl + active_controller + '/get_list_level3',
			method: "POST",
			data: {
				code_lv1: code_lv1,
				code_lv2: code_lv2
			},
			dataType: 'json',
			success: function(res) {
				$('#code_lv3').html(res.option);

				if ($('#code_lv3').hasClass('select2-hidden-accessible')) $('#code_lv3').select2('destroy');
				$('#code_lv3').select2({
					width: '100%',
					dropdownParent: $('#dialog-popup')
				});
			}
		});
	});

	$(document).on('change', '#code_lv3', function() {
		var code_lv1 = $("#code_lv1").val();
		var code_lv2 = $("#code_lv2").val();
		var code_lv3 = $("#code_lv3").val();

		$.ajax({
			url: siteurl + active_controller + '/get_list_level4_name',
			method: "POST",
			data: {
				code_lv1: code_lv1,
				code_lv2: code_lv2,
				code_lv3: code_lv3
			},
			dataType: 'json',
			success: function(res) {
				$('#nama').val(res.nama);
			}
		});
	});

	$(document).on('keyup', '.getCub', function() {
		get_cub();
	});

	// ============== SUBMIT FORM (FORM ADA DI MODAL WRAPPER) =================
	$(document).on('submit', '#data_form', function(e) {
		e.preventDefault();

		var code_lv1 = $('#code_lv1').val();
		var code_lv2 = $('#code_lv2').val();
		var code_lv3 = $('#code_lv3').val();
		var nama = $('#nama').val();

		var id_unit_packing = $('#id_unit_packing').val();
		var konversi = $('#konversi').val();
		var id_unit = $('#id_unit').val();
		var max_stok = $('#max_stok').val();
		var min_stok = $('#min_stok').val();

		if (code_lv1 == '0') {
			swal({
				title: "Error Message!",
				text: 'Material type not selected...',
				type: "warning"
			});
			return false;
		}
		if (code_lv2 == '0') {
			swal({
				title: "Error Message!",
				text: 'Material category not selected...',
				type: "warning"
			});
			return false;
		}
		if (code_lv3 == '0') {
			swal({
				title: "Error Message!",
				text: 'Material jenis not selected...',
				type: "warning"
			});
			return false;
		}
		if (nama == '') {
			swal({
				title: "Error Message!",
				text: 'Material Master is empty ...',
				type: "warning"
			});
			return false;
		}
		if (id_unit_packing == '0') {
			swal({
				title: "Error Message!",
				text: 'Packing Unit is empty ...',
				type: "warning"
			});
			return false;
		}
		if (konversi == '') {
			swal({
				title: "Error Message!",
				text: 'Conversion is empty ...',
				type: "warning"
			});
			return false;
		}
		if (id_unit == '0') {
			swal({
				title: "Error Message!",
				text: 'Unit Measurement is empty ...',
				type: "warning"
			});
			return false;
		}
		if (max_stok == '') {
			swal({
				title: "Error Message!",
				text: 'Maximum stok is empty ...',
				type: "warning"
			});
			return false;
		}
		if (min_stok == '') {
			swal({
				title: "Error Message!",
				text: 'Minimun stok is empty ...',
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

			var form_data = new FormData($('#data_form')[0]);

			$.ajax({
				type: 'POST',
				url: siteurl + active_controller + 'add',
				dataType: "json",
				data: form_data,
				processData: false,
				contentType: false,
				success: function(res) {
					if (res.status == '1') {
						swal({
							title: "Success",
							text: res.pesan,
							type: "success",
							timer: 1500,
							showConfirmButton: false
						}, function() {
							window.location.reload(true);
						})
					} else {
						swal({
							title: "Error",
							text: res.pesan,
							type: "error"
						})
					}
				},
				error: function() {
					swal({
						title: "Error",
						text: "Error proccess !",
						type: "error"
					})
				}
			})
		});

	})

	// ============== TOGGLE EDIT STATUS =================
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
						type: "success",
						timer: 1500,
						showConfirmButton: false
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

	// ============== DELETE =================
	$(document).on('click', '.delete', function(e) {
		e.preventDefault()
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
			},
			function() {
				$.ajax({
					type: 'POST',
					url: siteurl + active_controller + '/delete',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(res) {
						if (res.status == '1') {
							swal({
								title: "Success",
								text: res.pesan,
								type: "success",
								timer: 1500,
								showConfirmButton: false
							}, function() {
								window.location.reload(true);
							})
						} else {
							swal({
								title: "Error",
								text: res.pesan,
								type: "error"
							})
						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "Error proccess !",
							type: "error"
						})
					}
				})
			});

	})

	// ============== FILTER (HEADER) =================
	$(document).on('change', '#level1, #level2, #level3', function() {
		DataTables($('#level1').val(), $('#level2').val(), $('#level3').val());
	})

	// ============== INIT =================
	$(function() {
		$('.select2').select2({
			width: '100%'
		});

		DataTables($('#level1').val(), $('#level2').val(), $('#level3').val());
	});

	// ============== DATATABLES SERVER SIDE =================
	function DataTables(level1, level2, level3) {
		$('#example1').DataTable({
			processing: true,
			serverSide: true,
			stateSave: true,
			bAutoWidth: true,
			destroy: true,
			responsive: true,
			aaSorting: [
				[1, "asc"]
			],
			sPaginationType: "simple_numbers",
			iDisplayLength: 10,
			aLengthMenu: [
				[10, 20, 50, 100, 150],
				[10, 20, 50, 100, 150]
			],
			ajax: {
				url: siteurl + active_controller + '/data_side_material_master',
				type: "post",
				data: {
					level1: level1,
					level2: level2,
					level3: level3,
				},
				cache: false,
				error: function() {
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="7">No data found in the server</th></tr></tbody>');
				}
			}
		});
	}

	function get_cub() {
		var l = getNum($('#length').val().split(",").join(""));
		var w = getNum($('#wide').val().split(",").join(""));
		var h = getNum($('#high').val().split(",").join(""));
		var cub = (l * w * h) / 1000000000;

		$('#cub').val(cub.toFixed(7));
	}
</script>