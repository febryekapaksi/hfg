<?php
$ENABLE_ADD = has_permission('Purchase_Order.Add');
$ENABLE_MANAGE = has_permission('Purchase_Order.Manage');
$ENABLE_VIEW = has_permission('Purchase_Order.View');
$ENABLE_DELETE = has_permission('Purchase_Order.Delete');
?>

<div class="card shadow-sm">
	<div class="card-header bg-white d-flex justify-content-between">
		<div>
			<?php if ($ENABLE_ADD) : ?>
				<a class="btn btn-success" href="<?= base_url('/purchase_order/addPurchaseorder/') ?>" title="Create PO">
					<i class="fa fa-plus"></i>&nbsp;Create PO
				</a>
			<?php endif; ?>
		</div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table id="example1" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>#</th>
						<th>No PO</th>
						<th>No PR</th>
						<th>No Incoming</th>
						<th style="min-width: 75px;">Tanggal PO</th>
						<th>Progress PO</th>
						<!-- <th>PO</th> -->
						<th>Vendor</th>
						<th>Harga PO</th>
						<th>Revisi</th>
						<th>Reject Reason</th>
						<?php if ($ENABLE_MANAGE) : ?>
							<th>Action</th>
						<?php endif; ?>
					</tr>
				</thead>

				<tbody>
					<?php if (empty($results)) { ?>
						<!-- Handle empty results -->
						<?php } else {
						$numb = 0;
						foreach ($results as $record) {
							$valid_edit = 1;
							$numb++;

							$no_pr = [];
							$get_no_pr =
								$this->db->query("
							SELECT
								b.no_pr as no_pr
							FROM
								material_planning_base_on_produksi_detail a
								LEFT JOIN material_planning_base_on_produksi b ON b.so_number = a.so_number
							WHERE
								a.id IN (SELECT aa.idpr FROM dt_trans_po aa WHERE aa.no_po = '" . $record->no_po . "' AND (aa.tipe IS NULL OR aa.tipe = 'pr material'))
							GROUP BY b.no_pr

							UNION ALL 

							SELECT
								b.no_pr as no_pr
							FROM
								rutin_non_planning_detail a
								JOIN rutin_non_planning_header b ON b.no_pengajuan = a.no_pengajuan
							WHERE
								a.id IN (SELECT aa.idpr FROM dt_trans_po aa WHERE aa.no_po = '" . $record->no_po . "' AND aa.tipe = 'pr depart')
							GROUP BY b.no_pr

							UNION ALL

							SELECT
								a.no_pr as no_pr
							FROM
								asset_planning a
							WHERE
								a.id IN (SELECT aa.idpr FROM dt_trans_po aa WHERE aa.no_po = '" . $record->no_po . "' AND aa.tipe = 'pr asset')
							GROUP BY a.no_pr")->result();

							foreach ($get_no_pr as $item_pr) {
								$no_pr[] = $item_pr->no_pr;
							}

							$no_pr = implode(', ', $no_pr);
						?>
							<tr>
								<td><?= $numb; ?></td>
								<td><?= $record->no_surat ?></td>
								<td><?= $no_pr ?></td>
								<td><?= $list_no_incoming[$record->no_po] ?></td>
								<td><?= date('d-M-Y', strtotime($record->tanggal)) ?></td>
								<td class="text-center">
									<?php
									if ($record->status == '1') {
										echo "<span class='badge bg-primary'>Waiting</span>";
									} elseif ($record->status == '2') {
										echo "<span class='badge bg-success'>Approved</span>";
									} elseif ($record->status == '3') {
										echo "<span class='badge bg-danger'>Rejected</span>";
									} else {
										echo "<span class='badge bg-gray'>Closed</span>";
									}
									?>
								</td>
								<td><?= $record->nm_supplier ?></td>
								<td class="text-right"><?= number_format($record->subtotal, 4, ',', '.') ?></td>
								<td class="text-center"><?= $record->revisi ?></td>
								<td><?= $record->reject_reason ?></td>
								<td>
									<?php if ($ENABLE_VIEW) : ?>
										<!-- <a class="btn btn-warning btn-sm" href="<?= base_url('/purchase_order/view_po/' . $record->no_po) ?>" title="View"><i class="fa fa-eye"></i></a> -->
										<!-- <a class="btn btn-primary btn-sm" href="<?= base_url('/purchase_order/print_po/' . $record->no_po) ?>" target="_blank" title="Print"><i class="fa fa-print"></i></a> -->
									<?php endif; ?>

									<?php if ($ENABLE_MANAGE && $valid_edit > 0) : ?>
										<a class="btn btn-info btn-sm" href="<?= base_url('/purchase_order/edit/' . $record->no_po) ?>" title="Edit"><i class="fa fa-edit"></i></a>
									<?php endif; ?>

									<?php if ($ENABLE_DELETE) : ?>
										<button type="button" class="btn btn-sm btn-danger close_po_modal" data-no_po="<?= $record->no_po ?>" data-no_surat="<?= $record->no_surat ?>" title="Close PO"><i class="fas fa-ban"></i></button>
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

<!-- Modal Dialogs -->
<!-- Modal for Closing PO -->
<div class="modal fade" id="dialog-rekap" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span> Customer Data Summary</h4>
			</div>
			<div class="modal-body" id="MyModalBody">...</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- DataTables Script -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
	$(document).ready(function() {
		$('#example1').DataTable();

		// Submit Close PO Form
		$(document).on('submit', '#CP-frm-data', function() {
			var data = new FormData($('#CP-frm-data')[0]);
			$.ajax({
				type: 'POST',
				url: siteurl + active_controller + 'close_po',
				data: data,
				dataType: 'json',
				processData: false,
				contentType: false,
				success: function(result) {
					if (result.status == 1) {
						swal({
							title: 'Success!',
							text: 'PO has been closed',
							type: 'success'
						});
					} else {
						swal({
							title: 'Failed!',
							text: 'PO has not been closed',
							type: 'warning'
						});
					}
				},
				error: function() {
					swal({
						title: 'Error!',
						text: 'Please try again later!',
						type: 'error'
					});
				}
			});
		});

		// Trigger Close PO using SweetAlert Input
		$(document).on('click', '.close_po_modal', function(e) {
			e.preventDefault();
			var no_po = $(this).data('no_po');
			var no_surat = $(this).data('no_surat');
			swal({
					title: "Close PO?",
					text: "Enter reason to close " + no_surat + ":",
					type: "input",
					showCancelButton: true,
					closeOnConfirm: false,
					animation: "slide-from-top",
					inputPlaceholder: "Write reason here...",
					showLoaderOnConfirm: true 
				},
				function(inputValue) {
					if (inputValue === false) return false;

					if (inputValue === "") {
						swal.showInputError("Close PO reason is required!");
						return false;
					}

					$.ajax({
						type: 'POST',
						url: siteurl + active_controller + 'close_po',
						data: {
							'no_po': no_po,
							'close_po_reason': inputValue 
						},
						dataType: 'json',
						success: function(result) {
							if (result.status == 1) {
								swal({
									title: 'Success!',
									text: 'PO has been closed.',
									type: 'success'
								}, function() {
									location.reload();
								});
							} else {
								swal({
									title: 'Failed!',
									text: 'PO has not been closed.',
									type: 'warning'
								});
							}
						},
						error: function() {
							swal({
								title: 'Error!',
								text: 'Please try again later!',
								type: 'error'
							});
						}
					});
				});
		});
	});
</script>