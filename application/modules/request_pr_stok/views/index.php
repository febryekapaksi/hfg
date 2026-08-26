<?php
$ENABLE_ADD     = has_permission('PR_Stok.Add');
$ENABLE_MANAGE  = has_permission('PR_Stok.Manage');
$ENABLE_VIEW    = has_permission('PR_Stok.View');
$ENABLE_DELETE  = has_permission('PR_Stok.Delete');
?>

<div class="card shadow-sm border-0">
	<div class="card-header d-flex align-items-center justify-content-between">
		<h5 class="mb-0">Daftar Purchase Request</h5>
		<?php if ($ENABLE_ADD) : ?>
			<a class="btn btn-success" href="<?= base_url('request_pr_stok/add_new') ?>" title="Add">
				<i class="ti ti-plus me-1"></i> Add PR
			</a>
		<?php endif; ?>
	</div>
	<!-- /.card-header -->

	<div class="card-body">
		<!-- Filter (hidden, dipertahankan untuk fungsionalitas) -->
		<div class="row mb-3" hidden>
			<div class="col-md-3">
				<label class="form-label fw-bold">Product Type</label>
				<select name='product' id='product' class='form-select select2'>
					<option value='0'>All Product Type</option>
					<?php
					foreach (get_list_inventory_lv1('product') as $val => $valx) {
						echo "<option value='" . $valx['code_lv1'] . "'>" . strtoupper($valx['nama']) . "</option>";
					}
					?>
				</select>
			</div>
		</div>
		<div class="row mb-3" hidden>
			<div class="col-md-3">
				<label class="form-label fw-bold">Costcenter</label>
				<select name='costcenter' id='costcenter' class='form-select select2'>
					<option value='0'>All Costcenter</option>
					<?php
					foreach (get_costcenter() as $val => $valx) {
						echo "<option value='" . $valx['id_costcenter'] . "'>" . strtoupper($valx['nama_costcenter']) . "</option>";
					}
					?>
				</select>
			</div>
		</div>

		<!-- Table -->
		<div class="table-responsive">
			<table id="example1" class="table table-bordered table-striped" width='100%'>
				<thead>
					<tr>
						<th class="text-center">#</th>
						<th class="text-center">No. PR</th>
						<th class="text-center">Kategori PR</th>
						<th class="text-center">Nama Barang</th>
						<th class="text-center" style="min-width: 8% !important;">Qty (Pack)</th>
						<th class="text-center">Dibutuhkan</th>
						<th class="text-center">Status</th>
						<th class="text-center">Request By</th>
						<th class="text-center">Request Date</th>
						<th class="text-center">Option</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$no = 1;
					foreach ($result as $row) {
						$get_detail_pr = $this->db->get_where('material_planning_base_on_produksi_detail', ['so_number' => $row->so_number])->result();

						$nm_detail = '';
						$qty_detail = '';
						foreach ($get_detail_pr as $item) {
							$this->db->select('a.stock_name, b.code');
							$this->db->from('accessories a');
							$this->db->join('ms_satuan b', 'b.id = a.id_unit_gudang', 'left');
							$this->db->where('a.id', $item->id_material);
							$get_stok_data = $this->db->get()->row();

							if (!empty($get_stok_data)) {
								$nm_detail = $nm_detail . $get_stok_data->stock_name . '<br>';
								$qty_detail = $qty_detail . number_format($item->propose_purchase, 2) . ' ' . ucfirst($get_stok_data->code) . '<br>';
							}
						}

						$kategori_pr = [];
						$this->db->select('c.nm_category as kategori');
						$this->db->from('material_planning_base_on_produksi_detail a');
						$this->db->join('accessories b', 'b.id = a.id_material', 'left');
						$this->db->join('accessories_category c', 'c.id = b.id_category', 'left');
						$this->db->where('a.so_number', $row->so_number);
						$this->db->group_by('c.id');
						$get_kategori_pr = $this->db->get()->result();
						foreach ($get_kategori_pr as $item_kategori_pr) {
							$kategori_pr[] = $item_kategori_pr->kategori;
						}

						if (!empty($kategori_pr)) {
							$kategori_pr = implode(', ', $kategori_pr);
						} else {
							$kategori_pr = '';
						}

						echo '<tr>';
						echo '<td class="text-center">' . $no . '</td>';
						echo '<td>' . strtoupper($row->no_pr) . '</td>';
						echo '<td>' . strtoupper($kategori_pr) . '</td>';
						echo '<td>' . $nm_detail . '</td>';
						echo '<td class="text-end">' . $qty_detail . '</td>';
						echo '<td>' . date('d F Y', strtotime($row->tgl_dibutuhkan)) . '</td>';

						$getCheck = $this->db->get_where('material_planning_base_on_produksi_detail', array('so_number' => $row->so_number, 'status_app' => 'N'))->result();

						$valid_edit = 1;
						if (($row->sts_reject1 !== null || $row->sts_reject2 !== null || $row->sts_reject3 !== null) && $row->rejected == 1) {
							if ($row->sts_reject1 == "1") :
								$warna = "danger";
								$sts = "Rejected By Head";
							elseif ($row->sts_reject2 == "1") :
								$warna = "danger";
								$sts = "Rejected By Cost Control";
							elseif ($row->sts_reject3 == "1") :
								$warna = "danger";
								$sts = "Rejected By Management";
							endif;

							$warna = 'danger';
							$sts = 'Rejected';
						} else {
							if ($row->app_1 == null && $row->app_2 == null && $row->app_3 == null) :
								$warna = "primary";
								$sts = "Waiting Approval";
							else :
								if ($row->sts_app == "Y") :
									$warna = "success";
									$sts = "Approved";
								else :
									$warna = "primary";
									$sts = "Waiting Approval";
								endif;
							endif;
						}

						if (COUNT($getCheck) <= 0) {
							$sts = 'Approved';
							$warna = 'success';
							$valid_edit = 0;
						}

						echo '<td><span class="badge bg-' . $warna . '">' . $sts . '</span></td>';
						echo '<td class="text-center">' . $row->request_by . '</td>';
						echo '<td class="text-center">' . $row->request_date . '</td>';

						$view = "<a href='" . site_url($this->uri->segment(1)) . '/detail_planning/' . $row->so_number . "' class='btn btn-sm btn-warning' title='Detail PR'><i class='ti ti-eye'></i></a>";
						$edit = "";
						$print = '<a href="' . site_url($this->uri->segment(1)) . '/PrintH2/' . $row->so_number . '" class="btn btn-sm btn-info" title="Print PR" target="_blank"><i class="ti ti-download"></i></a>';

						$close = '';
						if ($ENABLE_DELETE) {
							$close = '<button type="button" class="btn btn-sm btn-danger close_pr_modal" data-so_number="' . $row->so_number . '" title="Close PR"><i class="ti ti-x"></i></button>';
						}

						echo '<td class="text-center">' . $view . ' ' . $edit . ' ' . $print . ' ' . $close . '</td>';
						echo '</tr>';

						$no++;
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<!-- /.card-body -->
</div>

<!-- Modal Close PR -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="modalClosePRLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalClosePRLabel">Closing PR</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form action="" method="post" id="frm-data">
				<div class="modal-body" id="ModalView">
					...
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-dark" data-bs-dismiss="modal">
						<i class="ti ti-x me-1"></i> Cancel
					</button>
					<button type="submit" class="btn btn-danger">
						<i class="ti ti-lock me-1"></i> Close PR
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		$('.select2').select2();

		var product = $("#product").val();
		var costcenter = $("#costcenter").val();
		DataTables(costcenter, product);

		$(document).on('change', '#costcenter', function() {
			var costcenter = $("#costcenter").val();
			var product = $("#product").val();
			DataTables(costcenter, product);
		});

		$(document).on('change', '#product', function() {
			var costcenter = $("#costcenter").val();
			var product = $("#product").val();
			DataTables(costcenter, product);
		});
	});

	$(document).on('click', '.close_pr_modal', function() {
		var so_number = $(this).data('so_number');

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + 'close_pr_modal',
			data: {
				'so_number': so_number
			},
			cache: false,
			success: function(result) {
				$('#ModalView').html(result);
				var modal = new bootstrap.Modal(document.getElementById('dialog-popup'));
				modal.show();
			},
			error: function(result) {
				swal({
					title: 'Error !',
					text: 'Please try again later !',
					type: 'error'
				});
			}
		});
	});

	$(document).on('click', '.close_pr', function() {
		var so_number = $(this).data('so_number');

		swal({
			title: 'Are you sure to close this PR ?',
			showCancelButton: true,
			confirmButtonText: 'Close',
			confirmButtonColor: 'red',
			type: 'warning'
		}, function(onConfirm) {
			if (onConfirm) {
				$.ajax({
					type: 'POST',
					url: siteurl + active_controller + 'close_pr',
					data: {
						'so_number': so_number
					},
					cache: false,
					dataType: 'json',
					success: function(result) {
						if (result.status == '1') {
							swal({
								title: 'Success !',
								text: 'PR has been closed',
								type: 'success'
							}, function(onConfirm) {
								location.reload(true);
							});
						} else {
							swal({
								title: 'Failed !',
								text: 'PR has not been closed',
								type: 'warning'
							});
						}
					},
					error: function(result) {
						swal({
							title: 'Error !',
							text: 'Please try again later !',
							type: 'error'
						});
					}
				});
			}
		});
	});

	$(document).on('submit', '#frm-data', function(e) {
		e.preventDefault();

		var data = new FormData($('#frm-data')[0]);
		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'close_pr',
			data: data,
			cache: false,
			dataType: 'json',
			processData: false,
			contentType: false,
			success: function(result) {
				if (result.status == '1') {
					swal({
						title: 'Success !',
						text: 'PR has been closed',
						type: 'success'
					}, function(onConfirm) {
						location.reload(true);
					});
				} else {
					swal({
						title: 'Failed !',
						text: 'PR has not been closed',
						type: 'warning'
					});
				}
			},
			error: function(result) {
				swal({
					title: 'Error !',
					text: 'Please try again later !',
					type: 'error'
				});
			}
		});
	});

	$(document).on('click', '.detail', function() {
		var so_number = $(this).data('so_number');
		$("#head_title").html("<b>Detail</b>");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + 'detail',
			data: {
				'so_number': so_number,
			},
			success: function(data) {
				$('#ModalView').html(data);
				var modal = new bootstrap.Modal(document.getElementById('dialog-popup'));
				modal.show();
			}
		});
	});

	// DELETE DATA
	$(document).on('click', '.booking', function(e) {
		e.preventDefault();
		var so_number = $(this).data('so_number');

		swal({
				title: "Anda Yakin?",
				text: "Process Booking Material & PR !",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: base_url + active_controller + 'process_booking',
					dataType: "json",
					data: {
						'so_number': so_number
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: result.pesan,
									type: "success"
								},
								function() {
									window.location.reload(true);
								});
						} else {
							swal({
								title: "Error",
								text: result.pesan,
								type: "error"
							});
						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "Data error. Gagal request Ajax",
							type: "error"
						});
					}
				});
			});
	});

	function DataTables(costcenter = null, product = null) {
		var dataTable = $('#example1').DataTable();
	}
</script>
