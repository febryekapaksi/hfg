<?php
$ENABLE_ADD     = has_permission('Request_Payment.Add');
$ENABLE_MANAGE  = has_permission('Request_Payment.Manage');
$ENABLE_DELETE  = has_permission('Request_Payment.Delete');
$ENABLE_VIEW    = has_permission('Request_Payment.View');
?>

<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
	.table-responsive-container {
		max-height: 500px;
		overflow-y: auto;
	}

	.sticky-header th {
		position: sticky !important;
		top: 0 !important;
		z-index: 10;
		background-color: #198754 !important;
		color: white !important;
		font-weight: bold;
		vertical-align: middle;
	}

	.chosen-container-single .chosen-single {
		height: 38px !important;
		line-height: 32px !important;
		background: #fff !important;
		border: 1px solid #dee2e6 !important;
		border-radius: 0.375rem !important;
	}

	.swal2-container {
		z-index: 99999 !important;
	}
</style>

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<form action="<?= $this->uri->uri_string() ?>" id="frm_data" name="frm_data" class="needs-validation" enctype="multipart/form-data" novalidate>
	<div class="card shadow-sm border-0 mb-4">
		<div class="card-header bg-transparent border-0 d-flex justify-content-end gap-2 pt-3 pe-3">
			<a href="<?= base_url('request_payment/download_excel_request_payment') ?>" class="btn btn-sm btn-success">
				<i class="fa fa-download me-1"></i> Excel
			</a>
			<button type="button" class="btn btn-sm btn-outline-danger" onclick="reset_data();">
				<i class="fa fa-refresh me-1"></i> Reset
			</button>
		</div>

		<div class="card-body">
			<input type="hidden" name="" class="actived_tab" value="transport">

			<div class="table-responsive table-responsive-container mb-4">
				<table id="table_req_payment" class="table table-striped table-hover table-bordered align-middle w-100 mb-0">
					<thead class="sticky-header text-center">
						<tr>
							<th style="width: 5%">No.</th>
							<th>No. Dokumen</th>
							<th>Request By</th>
							<th>Tanggal</th>
							<th>Keperluan</th>
							<th>Kategori</th>
							<th>Nilai Pengajuan</th>
							<th style="width: 15%">Tanggal Pembayaran</th>
							<th style="width: 10%">Action</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>

			<div class="row g-3 align-items-end">
				<div class="col-12 col-md-6">
					<div class="form-group">
						<label for="reject_reason" class="form-label fw-semibold small text-muted">Reject Reason</label>
						<textarea name="reject_reason" id="reject_reason" class="form-control" rows="2" placeholder="Tulis alasan jika menolak pengajuan..."></textarea>
					</div>
				</div>
				<div class="col-12 col-md-6 text-end d-flex justify-content-md-end justify-content-start gap-2">
					<button type="button" class="btn btn-danger" onclick="reject_req_payment()">
						<i class="fa fa-close me-1"></i> Reject
					</button>
					<button type="submit" name="save" class="btn btn-primary" id="submit">
						<i class="fa fa-save me-1"></i> Update
					</button>
				</div>
			</div>

		</div>
	</div>
</form>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
	$(document).ready(function() {
		DataTables();
		load_all_party();
	});

	function load_all_party() {
		if (typeof $.fn.autoNumeric !== 'undefined') {
			$(".divide").autoNumeric('init');
		}

		$(".select2").select2({
			width: '100%'
		});
		$('.vendor').chosen({
			width: '100%'
		});
		$('.tipe').chosen({
			width: '100%'
		});

		initFlatpickr();
	}

	function initFlatpickr() {
		if ($(".tanggal").length > 0) {
			$(".tanggal").flatpickr({
				altInput: true,
				altFormat: "d M Y",
				dateFormat: "Y-m-d",
				allowInput: false
			});
		}
	}

	function cektotal() {
		var total_req = 0;
		$('.dtlloop').each(function() {
			if (this.checked) {
				var ids = $(this).val();
				total_req += Number($("#jumlah_" + ids).val());
			}
		});
		$("#total_req").autoNumeric('set', total_req);
	}

	function hitung_net_payment(no) {
		var nilai_pengajuan = $('.nilai_pengajuan_' + no).val() || '0';
		nilai_pengajuan = parseFloat(nilai_pengajuan.split(',').join('')) || 0;

		var admin_charge = $('.admin_charge_' + no).val() || '0';
		admin_charge = parseFloat(admin_charge.split(',').join('')) || 0;

		var nilai_pph = $('.nilai_pph_' + no).val() || '0';
		nilai_pph = parseFloat(nilai_pph.split(',').join('')) || 0;

		var net_payment = (nilai_pengajuan + admin_charge - nilai_pph);
		$('.net_payment_' + no).val(net_payment.toLocaleString());
	}

	function reset_data() {
		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'reset_choosed_req_payment',
			cache: false,
			success: function(result) {
				DataTables();
			}
		});
	}

	function reject_req_payment() {
		var reject_reason = $('#reject_reason').val();

		if (reject_reason == '') {
			Swal.fire({
				icon: 'warning',
				title: 'Warning !',
				text: 'Reject Reason masih kosong !',
				timer: 3000,
				showConfirmButton: false
			});
			return false;
		}

		Swal.fire({
			icon: 'warning',
			title: 'Are you sure ?',
			text: 'Selected data will be rejected !',
			showCancelButton: true,
			confirmButtonText: 'Yes, Reject it!',
			cancelButtonText: 'Cancel'
		}).then((next) => {
			if (next.isConfirmed) {
				$.ajax({
					type: 'post',
					url: siteurl + active_controller + 'reject_req_payment',
					cache: false,
					data: {
						'reject_reason': reject_reason
					},
					dataType: 'json',
					success: function(result) {
						if (result.status == '1') {
							Swal.fire({
								icon: 'success',
								title: 'Success !',
								text: result.msg,
								timer: 2000,
								showConfirmButton: false
							}).then(() => {
								DataTables();
							});
						} else {
							Swal.fire({
								icon: 'warning',
								title: 'Failed !',
								text: result.msg
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error !',
							text: 'Please try again later !'
						});
					}
				});
			}
		});
	}

	$(document).on('click', '.pilih_data', function() {
		var val_pilih = $(this).val();
		var kategori = $(this).data('kategori');
		var isChecked = $(this).is(':checked');
		var wdo = isChecked ? 1 : 0;

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'added_pilih_data',
			data: {
				'id': val_pilih,
				'kategori': kategori,
				'wdo': wdo
			},
			cache: false,
			error: function() {
				Swal.fire({
					icon: 'error',
					title: 'Error !',
					text: 'Please try again later !'
				});
			}
		});
	});

	// Save/Update Form Handler
	$('#frm_data').on('submit', function(e) {
		e.preventDefault();

		Swal.fire({
			icon: 'warning',
			title: 'Are you sure ?',
			text: 'The data you choose will be processed !',
			showCancelButton: true,
			confirmButtonText: 'Yes, Process it!',
			cancelButtonText: 'Cancel'
		}).then((next) => {
			if (next.isConfirmed) {
				var formdata = $('#frm_data').serialize();
				$.ajax({
					type: 'post',
					url: siteurl + active_controller + 'save_request_payment',
					data: formdata,
					dataType: 'json',
					cache: false,
					success: function(result) {
						if (result.status == '1') {
							Swal.fire({
								icon: 'success',
								title: 'Success !',
								text: result.msg,
								timer: 2000,
								showConfirmButton: false
							}).then(() => {
								DataTables();
							});
						} else {
							Swal.fire({
								icon: 'warning',
								title: 'Failed !',
								text: result.msg
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error !',
							text: 'Please try again later !'
						});
					}
				});
			}
		});
	});

	function DataTables() {
		$('#table_req_payment').dataTable({
			serverSide: true,
			processing: true,
			stateSave: true,
			paging: true,
			destroy: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_data_req_payment',
				dataType: 'json'
			},
			columns: [{
					data: 'no',
					className: 'text-center'
				},
				{
					data: 'no_dokumen',
					className: 'text-center'
				},
				{
					data: 'request_by'
				},
				{
					data: 'tanggal',
					className: 'text-center'
				},
				{
					data: 'keperluan'
				},
				{
					data: 'kategori',
					className: 'text-center'
				},
				{
					data: 'nilai_pengajuan',
					className: 'text-end'
				},
				{
					data: 'tanggal_pembayaran',
					className: 'text-center'
				},
				{
					data: 'action',
					className: 'text-center'
				}
			],
			drawCallback: function() {
				initFlatpickr();
			}
		});
	}
</script>