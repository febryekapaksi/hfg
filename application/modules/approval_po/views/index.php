<?php
$ENABLE_ADD = has_permission('Approval_PR_Material.Add');
$ENABLE_MANAGE = has_permission('Approval_PR_Material.Manage');
$ENABLE_VIEW = has_permission('Approval_PR_Material.View');
$ENABLE_DELETE = has_permission('Approval_PR_Material.Delete');
?>


<div class="card shadow-sm">
	<div class="card-body">
		<!-- Filters -->
		<!-- <div class="row mb-3">
			<div class="col-md-3 mb-3">
				<label for="product" class="form-label">Product Type</label>
				<select name="product" id="product" class="form-select" onchange="filterData()">
					<option value="0">All Product Type</option>
					<?php foreach (get_list_inventory_lv1('product') as $val => $valx) { ?>
						<option value="<?= $valx['code_lv1'] ?>"><?= strtoupper($valx['nama']) ?></option>
					<?php } ?>
				</select>
			</div>

			<div class="col-md-3 mb-3">
				<label for="costcenter" class="form-label">Costcenter</label>
				<select name="costcenter" id="costcenter" class="form-select" onchange="filterData()">
					<option value="0">All Costcenter</option>
					<?php foreach (get_costcenter() as $val => $valx) { ?>
						<option value="<?= $valx['id_costcenter'] ?>"><?= strtoupper($valx['nama_costcenter']) ?></option>
					<?php } ?>
				</select>
			</div>
		</div> -->

		<!-- Data Table -->
		<div class="table-responsive">
			<table id="example1" class="table table-bordered table-striped" width="100%">
				<thead>
					<tr>
						<th class="text-center">#</th>
						<th class="text-center">No. PO</th>
						<th class="text-center">PO Date</th>
						<th class="text-center">Created By</th>
						<th class="text-center">Created Date</th>
						<th class="text-center">Action</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>

<!-- Modal Popup -->
<div class="modal fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" style="width: 90%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				<h4 class="modal-title" id="myModalLabel">Default</h4>
			</div>
			<div class="modal-body" id="ModalView">...</div>
		</div>
	</div>
</div>


<script>
	// Initialize DataTable
	$(document).ready(function() {
		var product = $("#product").val();
		var costcenter = $("#costcenter").val();
		loadDataTable(costcenter, product);

		// Filter Change Event
		$(document).on('change', '#costcenter, #product', function() {
			var costcenter = $("#costcenter").val();
			var product = $("#product").val();
			loadDataTable(costcenter, product);
		});
	});

	// Function to load the DataTable with filtered data
	function loadDataTable(costcenter = null, product = null) {
		var dataTable = $('#example1').DataTable({
			"processing": true,
			"serverSide": true,
			"stateSave": true,
			"fixedHeader": true,
			"autoWidth": false,
			"destroy": true,
			"searching": true,
			"responsive": true,
			"aaSorting": [
				[1, "desc"]
			],
			"columnDefs": [{
				"targets": 'no-sort',
				"orderable": false,
			}],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [
				[10, 20, 50, 100, 150],
				[10, 20, 50, 100, 150]
			],
			"ajax": {
				url: siteurl + active_controller + 'data_side_approval_pr_material',
				type: "post",
				data: {
					'costcenter': costcenter,
					'product': product
				},
				cache: false,
				error: function() {
					$(".my-grid-error").html("");
					$("#example1").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#example1_processing").css("display", "none");
				}
			}
		});
	}

	// Handle the Detail Button Click
	$(document).on('click', '.detail', function() {
		var so_number = $(this).data('so_number');
		$("#head_title").html("<b>Detail</b>");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + 'detail',
			data: {
				'so_number': so_number
			},
			success: function(data) {
				$("#dialog-popup").modal('show');
				$("#ModalView").html(data);
			}
		});
	});

	// Handle the Approve Button Click
	$(document).on('click', '.Approve', function(e) {
		e.preventDefault();
		var id = $(this).data('no_po');
		swal({
			title: "Are you sure?",
			text: "This PO will be approved.",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-info",
			confirmButtonText: "Yes, Approve!",
			cancelButtonText: "Cancel",
			closeOnConfirm: false
		}, function() {
			$.ajax({
				type: 'POST',
				url: siteurl + 'purchase_order/Approved',
				dataType: "json",
				data: { 'id': id },
				success: function(result) {
					if (result.status == '1') {
						swal({ title: "Success", text: "PO Approved", type: "success" }, function() { window.location.reload(true); });
					} else {
						swal({ title: "Error", text: "Error approving PO", type: "error" });
					}
				},
				error: function() {
					swal({ title: "Error", text: "Request failed", type: "error" });
				}
			});
		});
	});

	// Handle the Booking Process
	$(document).on('click', '.booking', function(e) {
		e.preventDefault();
		var so_number = $(this).data('so_number');
		swal({
			title: "Are you sure?",
			text: "Process Booking Material & PR!",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-info",
			confirmButtonText: "Yes!",
			cancelButtonText: "Cancel",
			closeOnConfirm: false
		}, function() {
			$.ajax({
				type: 'POST',
				url: base_url + active_controller + 'process_booking',
				dataType: "json",
				data: { 'so_number': so_number },
				success: function(result) {
					if (result.status == '1') {
						swal({ title: "Success", text: result.pesan, type: "success" }, function() { window.location.reload(true); });
					} else {
						swal({ title: "Error", text: result.pesan, type: "error" });
					}
				},
				error: function() {
					swal({ title: "Error", text: "Request failed", type: "error" });
				}
			});
		});
	});
</script>