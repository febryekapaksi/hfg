<?php
$ENABLE_ADD     = has_permission('Closed_PO.Add');
$ENABLE_MANAGE  = has_permission('Closed_PO.Manage');
$ENABLE_VIEW    = has_permission('Closed_PO.View');
$ENABLE_DELETE  = has_permission('Closed_PO.Delete');

?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>

<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="card">

	<!-- /.card-header -->
	<div class="card-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th width="5">#</th>
					<th>PO No.</th>
					<th>PR No.</th>
					<th>Incoming No.</th>
					<th>PO Date</th>
					<th>Vendor</th>
					<th>PO Amount</th>
					<th>Revision</th>
					<th>Close Reason</th>
					<?php if ($ENABLE_MANAGE) : ?>
						<th>Action</th>
					<?php endif; ?>
				</tr>
			</thead>

			<tbody>
				<?php if (empty($results)) {
				} else {

					$numb = 0;
					foreach ($results as $record) {
						$valid_edit = 1;
						$numb++;

						$no_pr = [];
						$get_no_pr = $this->db->query("
							SELECT
								b.no_pr as no_pr
							FROM
								material_planning_base_on_produksi_detail a
								JOIN material_planning_base_on_produksi b ON b.so_number = a.so_number
							WHERE
								a.id IN (SELECT aa.idpr FROM dt_trans_po aa WHERE aa.no_po = '" . $record->no_po . "' AND (aa.tipe IS NULL OR aa.tipe = ''))
							GROUP BY b.no_pr

							UNION ALL 

							SELECT
								b.no_pr as no_pr
							FROM
								rutin_non_planning_detail a
								JOIN rutin_non_planning_header b ON b.no_pengajuan = a.no_pengajuan
							WHERE
								a.id IN (SELECT aa.idpr FROM dt_trans_po aa WHERE aa.no_po = '" . $record->no_po . "' AND (aa.tipe IS NOT NULL OR aa.tipe <> ''))
							GROUP BY b.no_pr
						")->result();
						foreach ($get_no_pr as $item_pr) {
							$no_pr[] = $item_pr->no_pr;
						}

						$no_pr = implode(', ', $no_pr);

				?>
						<tr>
							<td><?= $numb; ?></td>
							<td><?= $record->no_surat ?></td>
							<td><?= $no_pr ?></td>
							<td><?= isset($list_no_incoming) && isset($list_no_incoming[$record->no_po]) ? $list_no_incoming[$record->no_po] : '-' ?></td>
							<td><?= date('d-M-Y', strtotime($record->tanggal)) ?></td>
							<td><?= $record->nm_supplier ?></td>
							<td class="text-right"><?= number_format($record->total_barang - $record->nilai_disc + $record->total_ppn + $record->taxtotal) ?></td>
							<td class="text-center"><?= $record->revisi ?></td>
							<td><?= $record->close_po_desc ?></td>
							<td style="padding-left:20px">
								<?php
								if ($ENABLE_VIEW) {
									echo '<a href="' . base_url("/closed_po/view_po/" . $record->no_po) . '" class="btn btn-sm btn-info" title="View Detail"><i class="fa fa-list"></i></a>';
								}
								?>
							</td>

						</tr>
				<?php }
				}  ?>
			</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->
<!-- Modal -->
<div class="modal modal-primary" id="dialog-rekap" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Customer Data Summary</h4>
			</div>
			<div class="modal-body" id="MyModalBody">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">
					<span class="glyphicon glyphicon-remove"></span> Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Quotation Data</h4>
			</div>
			<div class="modal-body" id="ModalView">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">
					<span class="glyphicon glyphicon-remove"></span> Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popupCP" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">Closing PO</h4>
			</div>
			<form action="" method="post" id="CP-frm-data">
				<div class="modal-body" id="ModalViewCP">
					...
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">
						<span class="glyphicon glyphicon-remove"></span> Cancel
					</button>
					<button type="submit" class="btn btn-sm btn-danger">Close It!</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		$('#example1').dataTable();
	});

	$(document).on('click', '.close_po_modal', function() {
		var no_po = $(this).data('no_po');

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'close_po_modal',
			data: {
				'no_po': no_po
			},
			cache: false,
			success: function(result) {
				$('#ModalViewCP').html(result);
				$('#dialog-popupCP').modal('show');
			},
			error: function(result) {
				Swal.fire({ title: 'Error !', text: 'Please try again later !', icon: 'error', confirmButtonText: 'OK' });
			}
		});
	});

	$(document).on('submit', '#CP-frm-data', function() {
		var data = new FormData($('#CP-frm-data')[0]);

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'close_po',
			data: data,
			cache: false,
			dataType: 'json',
			processData: false,
			contentType: false,
			success: function(result) {
				if (result.status == 1) {
					Swal.fire({ title: 'Success !', text: 'PO has been closed', icon: 'success', timer: 1500, showConfirmButton: false })
						.then(function(){ location.reload(); });
				} else {
					Swal.fire({ title: 'Failed !', text: 'PO has not been closed', icon: 'warning', confirmButtonText: 'OK' });
				}
			},
			error: function(result) {
				Swal.fire({ title: 'Error !', text: 'Please try again later !', icon: 'error', confirmButtonText: 'OK' });
			}
		});
	});

	$(document).on('click', '.edit', function(e) {
		var id = $(this).data('no_penawaran');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'penawaran/EditHeader/' + id,
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('click', '.cetak', function(e) {
		var id = $(this).data('no_penawaran');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'xtes/cetak' + id,
			success: function(data) {

			}
		})
	});

	$(document).on('click', '.view', function() {
		var id = $(this).data('no_po');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'purchase_order/Lihat/' + id,
			data: {
				'id': id
			},
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});
	$(document).on('click', '.add', function() {
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Add Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'penawaran/addHeader',
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});


	// DELETE DATA
	$(document).on('click', '.Approve', function(e) {
		e.preventDefault()
		var id = $(this).data('no_po');
		Swal.fire({
			title: "Are you sure?",
			text: "PO will be approved.",
			icon: "warning",
			showCancelButton: true,
			confirmButtonText: "Yes, Approve!",
			cancelButtonText: "Cancel"
		}).then(function(result) {
			if (result.isConfirmed) {
				$.ajax({
					type: 'POST',
					url: siteurl + 'purchase_order/Approved',
					dataType: "json",
					data: { 'id': id },
					success: function(result) {
						if (result.status == '1') {
							Swal.fire({ title: "Success", text: "P.R Approved.", icon: "success", timer: 1500, showConfirmButton: false })
								.then(function(){ window.location.reload(true); });
						} else {
							Swal.fire({ title: "Error", text: "Data error. Failed to approve data", icon: "error", confirmButtonText: "OK" });
						}
					},
					error: function() {
						Swal.fire({ title: "Error", text: "Data error. Ajax request failed", icon: "error", confirmButtonText: "OK" });
					}
				});
			}
		});
	})

	$(function() {
		// $('#example1 thead tr').clone(true).appendTo( '#example1 thead' );
		// $('#example1 thead tr:eq(1) th').each( function (i) {
		// var title = $(this).text();
		//alert(title);
		// if (title == "#" || title =="Action" ) {
		// $(this).html( '' );
		// }else{
		// $(this).html( '<input type="text" />' );
		// }

		// $( 'input', this ).on( 'keyup change', function () {
		// if ( table.column(i).search() !== this.value ) {
		// table
		// .column(i)
		// .search( this.value )
		// .draw();
		// }else{
		// table
		// .column(i)
		// .search( this.value )
		// .draw();
		// }
		// } );
		// } );


		$("#form-area").hide();
	});


	//Delete

	function PreviewPdf(id) {
		param = id;
		tujuan = 'customer/print_request/' + param;

		$(".modal-body").html('<iframe src="' + tujuan + '" frameborder="no" width="570" height="400"></iframe>');
	}

	function PreviewRekap() {
		tujuan = 'customer/rekap_pdf';
		$(".modal-body").html('<iframe src="' + tujuan + '" frameborder="no" width="100%" height="400"></iframe>');
	}
</script>