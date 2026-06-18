<?php
// Detail Approval
// $alasan_reject1 = (!empty($header)) ? $header[0]['reject_reason1'] : '';
// $alasan_reject2 = (!empty($header)) ? $header[0]['reject_reason2'] : '';
$alasan_reject3 = (!empty($header)) ? $header[0]['reject_reason3'] : '';

// $keterangan_1 = (!empty($header)) ? $header[0]['keterangan_1'] : '';
// $keterangan_2 = (!empty($header)) ? $header[0]['keterangan_2'] : '';
$keterangan_3 = (!empty($header)) ? $header[0]['keterangan_3'] : '';

// $status1 = '';
// $status2 = '';
$status3 = '';
if (!empty($header)) {
	// if ($header[0]['app_1'] == '1') {
	// 	$status1 = '<div class="badge bg-green">Approved</div>';
	// } else {
	// 	if ($header[0]['sts_reject1'] == '1') {
	// 		$status1 = '<div class="badge bg-red">Rejected</div>';
	// 	}
	// }

	// if ($header[0]['app_2'] == '1') {
	// 	$status2 = '<div class="badge bg-green">Approved</div>';
	// } else {
	// 	if ($header[0]['sts_reject2'] == '1') {
	// 		$status2 = '<div class="badge bg-red">Rejected</div>';
	// 	}
	// }

	if ($header[0]['app_3'] == '1') {
		$status3 = '<div class="badge bg-green">Approved</div>';
	} else {
		if ($header[0]['sts_reject3'] == '1') {
			$status3 = '<div class="badge bg-red">Rejected</div>';
		}
	}
}

$tgl_dibutuhkan = (!empty($header[0]['tgl_dibutuhkan'])) ? date('d F Y', strtotime($header[0]['tgl_dibutuhkan'])) : '';
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<div class="card">
	<div class="card-body">
		<form id="data-form" method="post" autocomplete="off"><br>
			<input type="hidden" name='so_number' id='so_number' value='<?= $header[0]['so_number']; ?>'>
			<div class="form-group row">
				<div class="col-md-12 mb-2">
					<div class="table-responsive">
						<table class='table no-border' width='100%'>
							<tr>
								<td width='20%'>No Request / SO</td>
								<td width='1%'>:</td>
								<td width='29%'><?= $header[0]['so_number']; ?></td>
								<td width='20%'>Required Date </td>
								<td width='1%'>:</td>
								<td width='29%'>
									<input type="date" name="tgl_dibutuhkan" id="" class="form-control input-sm" value="<?= $header[0]['tgl_dibutuhkan'] ?>">
								</td>
							</tr>
							<tr>
								<td>No. PR</td>
								<td>:</td>
								<td><?= $header[0]['no_pr']; ?></td>
								<td>PR Priority</td>
								<td>:</td>
								<td>
									<select name="tingkat_pr" id="" class="form-control input-sm">
										<option value="1" <?= ($header[0]['tingkat_pr'] == 1) ? 'selected' : null  ?>>Normal</option>
										<option value="2" <?= ($header[0]['tingkat_pr'] == 2) ? 'selected' : null  ?>>Urgent</option>
									</select>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<div class="col-md-12 mb-2">
					<div class="table-responsive">
						<table class="table no-border">
							<thead>
								<tr>
									<th class="text-center">Approval By</th>
									<th class="text-center">Status</th>
									<th class="text-center">Rejection Reason</th>
									<th class="text-center">Remarks</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="text-center">Management</td>
									<td class="text-center">
										<?= $status3 ?>
									</td>
									<td>
										<input type="text" name="reject_reason3" id="" class="form-control" value="<?= $alasan_reject3 ?>" readonly>
									</td>
									<td>
										<input type="text" name="keterangan_3" id="" class="form-control" value="<?= $keterangan_3 ?>">
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="col-md-12 mb-2">
					<div class="table-responsive">
						<table class='table table-striped table-bordered table-hover table-condensed datatable' width='100%'>
							<thead class='thead'>
								<tr class='bg-blue'>
									<th class='text-center th'>#</th>
									<th class='text-center th'>Material Name</th>
									<th class='text-center th'>Alias Name</th>
									<th class='text-center th'>Min Stock</th>
									<th class='text-center th'>Max Stock</th>
									<th class='text-center th'>Min Order</th>
									<th class='text-center th'>Qty PR</th>
									<th class='text-center th'>Notes</th>
									<th class='text-center th'>Action</th>
								</tr>
							</thead>
							<tbody id="list_non_pr">
								<?php
								$no_non_pr = 1;
								foreach ($list_material_non_pr as $material_non_pr => $item) :
									$disabled_add = '';

									echo '<tr>';
									echo '<td class="text-center">' . $no_non_pr . '</td>';
									echo '<td>' . $item['nama'] . '</td>';
									echo '<td>' . $item['trade_name'] . '</td>';
									echo '<td class="text-right">' . number_format($item['min_stok'], 2) . '</td>';
									echo '<td class="text-right">' . number_format($item['max_stok'], 2) . '</td>';
									echo '<td class="text-right">' . number_format(0, 2) . '</td>';
									echo '<td><input type="text" class="form-control form-control-sm autoNumeric2 nmat_qty_pr_' . $item['code_lv4'] . '" data-id_material="' . $item['code_lv4'] . '"></td>';
									echo '<td><input type="text" class="form-control form-control-sm nmat_notes_' . $item['code_lv4'] . '" data-id_material="' . $item['code_lv4'] . '"></td>';
									echo '<td class="text-center"><button type="button" class="btn btn-sm btn-success add_material_pr add_material_pr_' . $item['code_lv4'] . '" data-id_material="' . $item['code_lv4'] . '"><i class="fa fa-plus"></i></button></td>';
									echo '</tr>';

									$no_non_pr++;
								endforeach;
								?>
							</tbody>
						</table>
					</div>
				</div>
				<div class="col-md-12 mb-2">
					<div class="table-responsive">
						<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
							<thead class='thead'>
								<tr class='bg-blue'>
									<th class='text-center th'>#</th>
									<th class='text-center th'>Material Name</th>
									<th class='text-center th'>Alias Name</th>
									<th class='text-center th'>Min Stock</th>
									<th class='text-center th'>Max Stock</th>
									<th class='text-center th'>Min Order</th>
									<th class='text-center th'>Qty PR</th>
									<th class='text-center th'>#</th>
									<th class='text-center th'>Notes</th>
									<th class='text-center th'>Action</th>
								</tr>
							</thead>
							<tbody id="list_pr_detail">
								<?php
								foreach ($detail as $key => $value) {
									$key++;
									$get_material = $this->db->select('nama, trade_name')->get_where('new_inventory_4', ['code_lv4' => $value['id_material']])->row_array();
									$nm_material 	= (!empty($get_material)) ? $get_material['nama'] : '';
									$nm_lain 	= (!empty($get_material)) ? $get_material['trade_name'] : '';
									$stock_free 	= $value['stock_free'];
									$use_stock 		= $value['use_stock'];
									$sisa_free 		= $stock_free - $use_stock;
									$propose 		= $value['propose_purchase'];

									if ($propose > 0) {
										echo "<tr>";
										echo "<td class='text-center'>" . $key . "</td>";
										echo "	<td class='text-left'>" . $nm_material . "</td>";
										echo "	<td class='text-left'>" . $nm_lain . "</td>";
										echo "<td class='text-right min_stok'>" . number_format($value['min_stok'], 2) . "</td>";
										echo "<td class='text-right max_stok'>" . number_format($value['max_stok'], 2) . "</td>";
										echo "<td class='text-right min_order'>" . number_format(0, 2) . "</td>";
										if ($value['status_app'] == 'N') {
											echo "<td align='center'>";
											echo "<input type='hidden' name='detail[" . $key . "][id]' value='" . $value['id'] . "'>";
											echo "<input type='text' name='detail[" . $key . "][qty]' class='form-control input-sm text-center qty_pr_" . $value['id'] . " autoNumeric2' style='width:100px;' value='" . $propose . "'>";
											echo "</td>";
											echo "<td class='text-center'><span class='badge rounded-pill bg-primary text-bold'>Waiting Process</span></td>";
										}
										if ($value['status_app'] == 'Y') {
											echo "<td class='text-center'>" . number_format($propose, 2) . "</td>";
											echo "<td class='text-center'><span class='badge rounded-pill bg-success text-bold'>Approved</span></td>";
										}
										if ($value['status_app'] == 'D') {
											echo "<td class='text-center'>" . number_format($propose, 2) . "</td>";
											echo "<td class='text-center'><span class='badge rounded-pill bg-danger text-bold'>Rejected</span></td>";
										}
										echo "<td class='text-center'><input type='text' class='form-control notes_" . $value['id'] . "' name='detail[" . $key . "][note]' value='" . $value['note'] . "'></td>";
										echo '<td class="text-center">
										<button type="button" class="btn btn-sm btn-warning edit_detail" data-id="' . $value['id'] . '"><i class="fa fa-edit"></i></button>
										<button type="button" class="btn btn-sm btn-danger del_detail" data-id="' . $value['id'] . '"><i class="fa fa-trash"></i></button>
									</td>';
										echo "</tr>";
									}
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<div class="form-group row">
				<div class="text-center">
					<button type="button" class="btn btn-md btn-secondary" name="back" id="back"><i class="fa fa-reply"></i> Back</button>
					<button type="button" class="btn btn-md btn-success" name="save" id="save"><i class="fa fa-edit"></i> Update</button>
				</div>
			</div>
		</form>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" style='width:70%;'>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Detail Data</h4>
			</div>
			<div class="modal-body" id="ModalView">
				...
			</div>
		</div>
	</div>
</div>


<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script type="text/javascript">
	var base_url = '<?php echo base_url(); ?>';
	var active_controller = '<?php echo ($this->uri->segment(1)); ?>';

	var so_number = $("#so_number").val();

	$(document).ready(function() {
		$('.datatable').dataTable();

		$('.datepicker').datepicker({
			dateFormat: 'dd-M-yy'
		});
		$('.autoNumeric5').autoNumeric('init', {
			mDec: '5',
			aPad: false
		})
		$('.autoNumeric2').autoNumeric('init', {
			mDec: '2',
			aPad: false
		})
		$('.chosen-select').select2()

		//back
		$(document).on('click', '#back', function() {
			window.location.href = base_url + active_controller
		});

		$('#save').click(function(e) {
			e.preventDefault();

			swal({
					title: "Are you sure?",
					text: "You will not be able to process again this data!",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-danger",
					confirmButtonText: "Yes, Process it!",
					cancelButtonText: "No, cancel process!",
					closeOnConfirm: true,
					closeOnCancel: false
				},
				function(isConfirm) {
					if (isConfirm) {
						var formData = new FormData($('#data-form')[0]);
						var baseurl = siteurl + active_controller + '/process_update_all';
						$.ajax({
							url: baseurl,
							type: "POST",
							data: formData,
							cache: false,
							dataType: 'json',
							processData: false,
							contentType: false,
							success: function(data) {
								if (data.status == 1) {
									swal({
										title: "Save Success!",
										text: data.pesan,
										type: "success",
										timer: 7000
									});
									window.location.href = base_url + active_controller
								} else {
									swal({
										title: "Save Failed!",
										text: data.pesan,
										type: "warning",
										timer: 7000
									});
								}
							},
							error: function() {

								swal({
									title: "Error Message !",
									text: 'An Error Occured During Process. Please try again..',
									type: "warning",
									timer: 7000
								});
							}
						});
					} else {
						swal("Cancelled", "Data can be process again :)", "error");
						return false;
					}
				});
		});

		function refresh_pr_detail() {
			var so_number = $("#so_number").val();

			$.ajax({
				type: "POST",
				url: siteurl + active_controller + '/refresh_pr_detail',
				data: {
					'so_number': so_number
				},
				cache: false,
				success: function(result) {
					$("#list_pr_detail").html(result);

					$('.datepicker').datepicker({
						dateFormat: 'dd-M-yy'
					});
					$('.autoNumeric5').autoNumeric('init', {
						mDec: '5',
						aPad: false
					})
					$('.autoNumeric2').autoNumeric('init', {
						mDec: '2',
						aPad: false
					})
					$('.chosen-select').select2()
				},
				error: function(result) {

				}
			});
		}

		function refresh_material() {
			$.ajax({
				type: "POST",
				url: siteurl + active_controller + "/get_refresh_material",
				data: {
					"so_number": so_number
				},
				cache: false,
				success: function(result) {
					$(".list_non_pr").html(result);
				}
			});
		}

		$(document).on("click", ".edit_detail", function() {
			var id = $(this).data('id');
			var so_number = $("#so_number").val();
			var qty_pr = $(".qty_pr_" + id).val();
			if (qty_pr !== '') {
				qty_pr = qty_pr.split(",").join("");
				qty_pr = parseFloat(qty_pr);
			} else {
				var qty_pr = 0;
			}

			var notes = $(".notes_" + id).val();

			$.ajax({
				type: "POST",
				url: siteurl + active_controller + '/edit_detail',
				data: {
					'id': id,
					'so_number': so_number,
					'qty_pr': qty_pr,
					'notes': notes
				},
				cache: false,
				dataType: 'json',
				success: function(result) {
					if (result.status == '1') {
						swal({
								title: 'Edit Success !',
								text: 'PR Material has been updated !',
								type: 'success'
							},
							function(isConfirm) {
								if (isConfirm) {
									refresh_pr_detail()
								}
							}
						);
					} else {
						swal({
							title: 'Edit Failed !',
							text: 'PR Material has not been updated !',
							type: 'error'
						});
					}
				},
				error: function(result) {
					swal({
						title: 'Edit Failed !',
						text: 'PR Material has not been updated !',
						type: 'error'
					});
				}
			});
		});

		$(document).on("click", ".del_detail", function() {
			var id = $(this).data('id');

			swal({
					title: "Are you sure?",
					text: "This data will permanently deleted !",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-danger",
					confirmButtonText: "Yes, Delete it!",
					cancelButtonText: "No, cancel delete!",
					closeOnConfirm: true,
					closeOnCancel: false
				},
				function(isConfirm) {
					if (isConfirm) {
						$.ajax({
							type: "POST",
							url: siteurl + active_controller + '/del_detail',
							data: {
								'id': id
							},
							cache: false,
							dataType: 'json',
							success: function(result) {
								if (result.status == '1') {
									swal({
										title: 'Success !',
										text: 'The data has been deleted !',
										type: 'success'
									}, function(isConfirm) {
										if (isConfirm) {
											refresh_pr_detail();
										}
									});
								} else {
									swal({
										title: 'Failed !',
										text: 'The data has not been deleted !',
										type: 'error'
									});
								}
								refresh_pr_detail(so_number);
							},
							error: function(result) {

							}
						});
					} else {
						swal("Cancelled", "Data can be process again :)", "error");
						return false;
					}
				});
		});

		$(document).on("click", ".add_material_pr", function() {
			var so_number = $("#so_number").val();
			var id_material = $(this).data('id_material');
			var qty_pr = $(".nmat_qty_pr_" + id_material).val();
			if (qty_pr == "" || qty_pr == null) {
				qty_pr = 0;
			} else {
				qty_pr = qty_pr.split(",").join("");
				qty_pr = parseFloat(qty_pr);
			}
			var notes = $(".nmat_notes_" + id_material).val();

			$.ajax({
				type: "POST",
				url: siteurl + active_controller + '/add_material',
				data: {
					'so_number': so_number,
					'id_material': id_material,
					'qty_pr': qty_pr,
					'notes': notes
				},
				cache: false,
				dataType: "json",
				beforeSend: function(result) {
					$(".add_material_pr_" + id_material).html("<i class='fa fa-spinner fa-spin'></i>");
				},
				success: function(result) {
					refresh_pr_detail();
					$(".add_material_pr_" + id_material).html("<i class='fa fa-plus'></i>");
					$(".add_material_pr_" + id_material).prop("disabled", true);
				},
				error: function(result) {
					swal({
						title: "Sorry !",
						text: "Please try again next time !",
						type: "error"
					});
					$(".add_material_pr_" + id_material).html("<i class='fa fa-plus'></i>");
				}
			})
		});
	});
</script>