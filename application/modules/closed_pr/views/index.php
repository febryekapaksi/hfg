<?php
$ENABLE_ADD     = has_permission('Closed_PR.Add');
$ENABLE_MANAGE  = has_permission('Closed_PR.Manage');
$ENABLE_VIEW    = has_permission('Closed_PR.View');
$ENABLE_DELETE  = has_permission('Closed_PR.Delete');
?>

<div class="card shadow-sm border-0">
	<div class="card-header">
		<h5 class="mb-0">Closed Purchase Request</h5>
	</div>
	<!-- /.card-header -->

	<div class="card-body">
		<div class="table-responsive">
			<table id="example1" class="table table-bordered table-striped" width="100%">
				<thead>
					<tr>
						<th class="text-center">#</th>
						<th class="text-center">No. PR</th>
						<th class="text-center">Kategori PR</th>
						<th class="text-center">Request By</th>
						<th class="text-center">Request Date</th>
						<?php
						if ($ENABLE_VIEW) {
							echo '<th class="text-center">Action</th>';
						}
						?>
					</tr>
				</thead>
				<tbody>
					<?php
					$no = 1;
					foreach ($result as $item) {

						echo '<tr>';
						echo '<td class="text-center">' . $no . '</td>';
						echo '<td class="text-center">' . $item->no_pr . '</td>';
						echo '<td class="text-center">' . $item->kategori_pr . '</td>';
						echo '<td class="text-center">' . $item->request_by . '</td>';
						echo '<td class="text-center">' . $item->request_date . '</td>';
						if ($ENABLE_VIEW) {
							if ($item->kategori_pr == 'PR Department') {
								$view_detail = '<a href="' . base_url("/non_rutin/add/" . $item->id_pr . "/view") . '" class="btn btn-sm btn-info" target="_blank" title="View Detail PR"><i class="ti ti-eye"></i></a>';
							} else {
								if ($item->kategori_pr == 'PR Material') {
									$view_detail = '<a href="' . base_url("request_pr_material/detail_planning/" . $item->id_pr) . '" class="btn btn-sm btn-info" target="_blank" title="View Detail PR"><i class="ti ti-eye"></i></a>';
								} else {
									$view_detail = '<a href="' . base_url("request_pr_stok/detail_planning/" . $item->id_pr) . '" class="btn btn-sm btn-info" target="_blank" title="View Detail PR"><i class="ti ti-eye"></i></a>';
								}
							}
							$view_barang = '<button type="button" class="btn btn-sm btn-success view_barang_pr" title="View Barang PR" data-id_pr="' . $item->id_pr . '" data-no_pr="' . $item->no_pr . '" data-kategori_pr="' . $item->kategori_pr . '"><i class="ti ti-list"></i></button>';

							echo '<td class="text-center">' . $view_detail . ' ' . $view_barang . '</td>';
						}
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

<!-- Modal: List Material PR -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="modalListMaterialLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalListMaterialLabel">List Material PR</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="ModalView">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-dark" data-bs-dismiss="modal">
					<i class="ti ti-x me-1"></i> Close
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal: Closing PO -->
<div class="modal fade" id="dialog-popupCP" tabindex="-1" aria-labelledby="modalClosingPOLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalClosingPOLabel">Closing PO</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form action="" method="post" id="CP-frm-data">
				<div class="modal-body" id="ModalViewCP">
					...
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-dark" data-bs-dismiss="modal">
						<i class="ti ti-x me-1"></i> Cancel
					</button>
					<button type="submit" class="btn btn-danger">
						<i class="ti ti-lock me-1"></i> Close It!
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- page script -->
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('#example1').DataTable();
	});

	$(document).on('click', '.view_barang_pr', function() {
		var id_pr = $(this).data('id_pr');
		var kategori_pr = $(this).data('kategori_pr');
		var no_pr = $(this).data('no_pr');

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'view_barang_pr',
			data: {
				'id_pr': id_pr,
				'kategori_pr': kategori_pr,
				'no_pr': no_pr
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
					text: 'Please try again !',
					type: 'error'
				});
			}
		});
	});
</script>
