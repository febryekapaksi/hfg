<?php
$pembeda = substr($header[0]['so_number'], 0, 1);
$due_date = (!empty($header[0]['due_date'])) ? date('d F Y', strtotime($header[0]['due_date'])) : '-';
$tgl_dibutuhkan = (!empty($header[0]['tgl_dibutuhkan'])) ? date('d F Y', strtotime($header[0]['tgl_dibutuhkan'])) : '-';
?>

<div class="col-md-12">
	<form id="data-form" method="post" autocomplete="off">
		<input type="hidden" name='so_number' id='so_number' value='<?= $header[0]['so_number']; ?>'>

		<div class="form-group row mb-3">
			<div class="col-md-6">
				<label>No. Request/SO</label>
				<input type="text" class="form-control" value="<?= $header[0]['so_number']; ?>" readonly>
			</div>
			<div class="col-md-6">
				<label>Due Date SO</label>
				<input type="text" class="form-control" value="<?= $due_date; ?>" readonly>
			</div>
		</div>

		<div class="form-group row mb-3">
			<div class="col-md-6">
				<label>No. PR</label>
				<input type="text" class="form-control" value="<?= $header[0]['no_pr']; ?>" readonly>
			</div>
			<div class="col-md-6">
				<label>Required Date</label>
				<input type="text" class="form-control" value="<?= $tgl_dibutuhkan; ?>" readonly>
			</div>
		</div>

		<div class="form-group row mb-3">
			<div class="col-md-6">
				<label>Customer</label>
				<input type="text" class="form-control" value="<?= $header[0]['name_customer']; ?>" readonly>
			</div>
			<div class="col-md-6">
				<label>PR Priority</label>
				<input type="text" class="form-control" value="<?= ($header[0]['tingkat_pr'] == 2) ? 'Urgent' : 'Normal' ?>" readonly>
			</div>
		</div>

		<div class="table-responsive">
			<table class="table table-striped table-bordered table-hover table-condensed">
				<thead class="thead-light">
					<tr class="bg-blue">
						<th class="text-center">#</th>
						<th class="text-center">Material Name</th>
						<?php if ($pembeda == 'SO') { ?>
							<th class="text-center">Estimation (Kg)</th>
							<th class="text-center">Stock Free (Kg)</th>
							<th class="text-center">Use Stock (Kg)</th>
							<th class="text-center">Remaining Free Stock (Kg)</th>
						<?php } ?>
						<th class="text-center">Min Stock</th>
						<th class="text-center">Max Stock</th>
						<th class="text-center">Min Order</th>
						<th class="text-center">Qty PR</th>
						<th class="text-center">Qty Rev</th>
						<th class="text-center">Note</th>
						<th class="text-center">#</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($detail as $key => $value) {
						$key++;
						$nm_material = $value['nama'];
						$stock_free = $value['stock_free'];
						$use_stock = $value['use_stock'];
						$sisa_free = $stock_free - $use_stock;
						$propose = $value['propose_purchase'];

						echo "<tr>";
						echo "<td class='text-center'>" . $key . "</td>";
						echo "<td class='text-left'>" . $nm_material . "
                                    <input type='hidden' name='detail[" . $key . "][id]' value='" . $value['id'] . "'>
                                  </td>";
						if ($pembeda == 'SO') {
							echo "<td class='text-right'>" . number_format($value['qty_order'], 5) . "</td>";
							echo "<td class='text-right'>" . number_format($stock_free, 5) . "</td>";
							echo "<td class='text-right'>" . number_format($use_stock, 5) . "</td>";
							echo "<td class='text-right'>" . number_format($sisa_free, 5) . "</td>";
						}
						echo "<td class='text-right'>" . number_format($value['min_stok'], 2) . "</td>";
						echo "<td class='text-right'>" . number_format($value['max_stok'], 2) . "</td>";
						echo "<td class='text-right'>" . number_format(0, 2) . "</td>";
						echo "<td class='text-right'>" . number_format($propose, 2) . "</td>";
						echo "<td class='text-center'>" . number_format($value['propose_rev'], 2) . "</td>";
						echo "<td class='text-left'>" . $value['note'] . "</td>";

						if ($value['status_app'] == 'N') {
							echo "<td class='text-center'><span class='badge bg-blue text-bold'>Waiting Process</span></td>";
						}
						if ($value['status_app'] == 'Y') {
							echo "<td class='text-center'><span class='badge bg-green text-bold'>Approved</span></td>";
						}
						if ($value['status_app'] == 'D') {
							echo "<td class='text-center'><span class='badge bg-red text-bold'>Rejected</span></td>";
						}

						echo "</tr>";
					}
					?>
				</tbody>
			</table>
		</div>

		<div class="form-group row">
			<div class="text-center">
				<button type="button" class="btn btn-secondary" id="back"><i class="fa fa-reply"></i> Back</button>
			</div>
		</div>
	</form>
</div>

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" style="width: 70%;">
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

<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script type="text/javascript">
	$(document).ready(function() {
		$('.datepicker').datepicker({
			dateFormat: 'dd-M-yy'
		});

		$('.autoNumeric5').autoNumeric('init', {
			mDec: '5',
			aPad: false
		});

		$('.chosen-select').select2();

		// Kembali button action
		$(document).on('click', '#back', function() {
			window.location.href = base_url + active_controller;
		});
	});
</script>