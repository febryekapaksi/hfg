<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
	<div class="card shadow-sm border-0">
		<div class="card-header">
			<div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
				<!-- Left: Tanggal & Tingkat PR -->
				<div class="d-flex flex-wrap align-items-end gap-3">
					<div>
						<label for="tgl_butuh" class="form-label fw-bold mb-1">Tanggal Dibutuhkan</label>
						<?php
						$tgl_now = date('Y-m-d');
						$tgl_next_month = date('Y-m-' . '20', strtotime('+1 month', strtotime($tgl_now)));
						echo form_input(array('id' => 'tgl_butuh', 'name' => 'tgl_butuh', 'class' => 'form-control text-center datepicker changeSaveDate', 'readonly' => 'readonly', 'placeholder' => 'Tanggal Dibutuhkan'), $tgl_next_month);
						?>
					</div>
					<div>
						<label for="tingkat_pr" class="form-label fw-bold mb-1">Tingkat PR</label>
						<select name="tingkat_pr" id="tingkat_pr" class="form-select tingkat_pr">
							<option value="1">Normal</option>
							<option value="2">Urgent</option>
						</select>
					</div>
				</div>

				<!-- Right: Action Buttons -->
				<div class="d-flex flex-wrap gap-2">
					<button type="button" class="btn btn-primary" id="autoUpdate">
						<i class="ti ti-refresh me-1"></i> Update Otomatis
					</button>
					<button type="button" class="btn btn-danger" id="autoDelete">
						<i class="ti ti-trash me-1"></i> Clear Propose Request
					</button>
				</div>
			</div>
		</div>
		<!-- /.card-header -->

		<div class="card-body">
			<!-- Filter Category & Budget -->
			<div class="row mb-3">
				<div class="col-md-4">
					<label for="category" class="form-label fw-bold">Category</label>
					<select name='category' id='category' class='form-select select2'>
						<?php
						foreach ($category as $val => $valx) {
							$selected = ($valx['id'] == '8') ? 'selected' : '';
							echo "<option value='" . $valx['id'] . "' " . $selected . " data-nm_category='" . strtoupper($valx['nm_category']) . "'>" . strtoupper($valx['nm_category']) . "</option>";
						}
						?>
					</select>
				</div>
				<div class="col-md-4">
					<label for="" class="form-label fw-bold">Budget</label>
					<input type="text" class="form-control text-end autoNumeric0 nilai_budget" value="" readonly>
				</div>
				<div class="col-md-4">
					<label for="" class="form-label fw-bold">Pengajuan</label>
					<input type="text" class="form-control text-end nilai_pengajuan" value="" readonly>
				</div>
			</div>

			<!-- Table -->
			<div class="table-responsive">
				<table class="table table-bordered table-striped" id="example1" width='100%'>
					<thead>
						<tr class="table-primary">
							<th class="text-center" width='4%'>#</th>
							<th class="text-center">Nama Barang</th>
							<th class="text-center">Kebutuhan 1 Bulan</th>
							<th class="text-center">Stock</th>
							<th class="text-center">Max Stock</th>
							<th class="text-center">Propose Purchase</th>
							<th class="text-center">Unit</th>
							<th class="text-center">Keterangan</th>
							<th class="text-center">Price Reference</th>
							<th class="text-center">Total Price</th>
						</tr>
					</thead>
					<tbody></tbody>
					<tfoot>
						<tr class="table-primary">
							<th colspan="9" class="text-center fw-bold">Total Price Pengajuan</th>
							<th class="text-end total-price fw-bold">0</th>
						</tr>
					</tfoot>
				</table>
			</div>

			<!-- Action Buttons -->
			<div class="d-flex justify-content-end gap-2 mt-3">
				<button type="button" class="btn btn-success" id="saveRequest">
					<i class="ti ti-device-floppy me-1"></i> Purchase Request
				</button>
				<button type="button" class="btn btn-dark" id="back">
					<i class="ti ti-arrow-left me-1"></i> Back
				</button>
			</div>
		</div>
		<!-- /.card-body -->
	</div>
	<!-- /.card -->
</form>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<style>
	.datepicker {
		cursor: pointer;
	}
</style>
<script>
	$(document).ready(function() {
		var category = $("#category").val();
		DataTables(category);

		$('.select2').select2();

		$(document).on('click', '#back', function() {
			window.location.href = siteurl + active_controller;
		});

		$(document).on('change', '#category', function() {
			var category = $("#category").val();
			DataTables(category);
			hitungBudget();
			hitungPengajuan();
		});

		$('.autoNumeric2').autoNumeric('init', {
			mDec: '2',
			aPad: false
		});

		$('.datepicker').datepicker({
			dateFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
		});

		hitungBudget();
		hitungPengajuan();
	});

	$(document).on('click', '#autoUpdate', function() {
		var inventory = $('#category').val();
		var nm_category = $('#category').find(':selected').data('nm_category');

		if (inventory == '0') {
			swal({
				title: "Error Message!",
				text: 'Filter category terlebih dahulu ...',
				type: "warning"
			});
			return false;
		}
		swal({
				title: "Are you sure?",
				text: "Update otomatis Category " + nm_category + " !",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Yes, Process it!",
				cancelButtonText: "No, cancel process!",
				closeOnConfirm: false,
				closeOnCancel: false
			},
			function(isConfirm) {
				if (isConfirm) {
					$.ajax({
						url: base_url + active_controller + '/auto_update_rutin/' + inventory,
						type: "POST",
						cache: false,
						dataType: 'json',
						success: function(data) {
							if (data.status == 1) {
								swal({
									title: "Save Success!",
									text: data.pesan,
									type: "success",
									timer: 7000
								});
								DataTables(inventory);
								hitungPengajuan();
							} else if (data.status == 0) {
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

	$(document).on('click', '#autoDelete', function() {
		var id_category = $('#category').val();
		var nm_category = $('#category').find(':selected').data('nm_category');

		if (id_category == '0') {
			swal({
				title: "Error Message!",
				text: 'Pilih Category Terlebih Dahulu ...',
				type: "warning"
			});
			return false;
		}
		swal({
				title: "Are you sure?",
				text: "Clear Propose Category " + nm_category + " !",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Yes, Process it!",
				cancelButtonText: "No, cancel process!",
				closeOnConfirm: false,
				closeOnCancel: false
			},
			function(isConfirm) {
				if (isConfirm) {
					$.ajax({
						url: base_url + active_controller + '/clear_update_reorder/' + id_category,
						type: "POST",
						cache: false,
						dataType: 'json',
						success: function(data) {
							if (data.status == 1) {
								swal({
									title: "Save Success!",
									text: data.pesan,
									type: "success",
									timer: 7000
								});
								DataTables(id_category);
								hitungBudget();
								hitungPengajuan();
							} else if (data.status == 0) {
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

	$(document).on('change', '.changeSave', function() {
		var id = $(this).data('id');
		var inventory = $('#category').val();
		var max_propose = $(this).data('max_propose');
		var qty_satuan = $(this).val();

		var nomor = $(this).data('no');
		var id_material = $(this).data('id');
		var purchase = $('#purchase_' + nomor).val().split(",").join("");
		if (purchase > max_propose) {
			swal({
				type: 'warning',
				title: 'Peringatan !',
				text: 'Nilai propose tidak boleh lebih dari Max Stock !',
				showCancelButton: false,
				allowoOutsideClick: false
			});
			purchase = max_propose;
		}

		var tanggal = $('#tgl_butuh').val();
		var satuan = $('#satuan_' + nomor).val();
		var info = $('#info_' + nomor).val();

		$.ajax({
			url: base_url + active_controller + 'save_reorder_change',
			type: "POST",
			data: {
				"id_material": id_material,
				"purchase": purchase,
				"tanggal": tanggal,
				"info": info,
				"satuan": satuan
			},
			cache: false,
			dataType: 'json',
			success: function(data) {
				DataTables(inventory);
				hitungPengajuan();
			},
			error: function() {
				console.log('error connection serve !');
			}
		});
	});

	function check_inputed_qty_stock() {
		var deferred = $.Deferred();
		$.ajax({
			url: siteurl + active_controller + 'check_inputed_qty_stock',
			method: 'POST',
			dataType: 'json',
			success: function(response) {
				deferred.resolve(response.jumlah_data);
			},
			error: function(error) {
				deferred.reject(error);
			}
		});
		return deferred.promise();
	}

	$(document).on('click', '#saveRequest', function() {
		var category = $('#category').val();
		var tingkat_pr = $('.tingkat_pr').val();

		var nilai_budget = $('.nilai_budget').val();
		if (nilai_budget !== '') {
			nilai_budget = nilai_budget.split(',').join('');
			nilai_budget = parseFloat(nilai_budget);
		} else {
			nilai_budget = 0;
		}

		var nilai_pengajuan = $('.nilai_pengajuan').val();
		if (nilai_pengajuan !== '') {
			nilai_pengajuan = nilai_pengajuan.split(',').join('');
			nilai_pengajuan = parseFloat(nilai_pengajuan);
		} else {
			nilai_pengajuan = 0;
		}

		if (category == '0') {
			swal({
				title: "Error Message!",
				text: 'Pilih Category Terlebih Dahulu ...',
				type: "warning"
			});
			return false;
		}

		check_inputed_qty_stock().then(function(data) {
			if (data > 0) {
				swal({
						title: "Are you sure?",
						text: "Membuat semua Propose berdasarkan Category !!!",
						type: "warning",
						showCancelButton: true,
						confirmButtonClass: "btn-danger",
						confirmButtonText: "Yes, Process it!",
						cancelButtonText: "No, cancel process!",
						closeOnConfirm: false,
						closeOnCancel: false
					},
					function(isConfirm) {
						if (isConfirm) {
							var nilai_budget = $('.nilai_budget').val();
							if (nilai_budget !== '') {
								nilai_budget = nilai_budget.split(',').join('');
								nilai_budget = parseFloat(nilai_budget);
							} else {
								nilai_budget = 0;
							}

							var nilai_pengajuan = $('.nilai_pengajuan').val();
							if (nilai_pengajuan !== '') {
								nilai_pengajuan = nilai_pengajuan.split(',').join('');
								nilai_pengajuan = parseFloat(nilai_pengajuan);
							} else {
								nilai_pengajuan = 0;
							}

							$.ajax({
								url: base_url + active_controller + '/save_reorder_all',
								type: "POST",
								data: {
									'category': category,
									'tingkat_pr': tingkat_pr,
									'nilai_budget': nilai_budget,
									'nilai_pengajuan': nilai_pengajuan
								},
								cache: false,
								dataType: 'json',
								success: function(data) {
									if (data.status == 1) {
										swal({
											title: "Save Success!",
											text: data.pesan,
											type: "success",
											timer: 7000
										});
										window.location.href = base_url + active_controller;
									} else if (data.status == 0) {
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
			} else {
				swal({
					title: 'Warning !',
					text: 'Please fill qty pack at least at 1 product !',
					type: 'warning'
				});
			}
		}).fail(function(error) {
			swal({
				title: 'Error !',
				text: 'Please try again later !',
				type: 'error'
			});
		});
	});

	$(document).on('change', '.changeSaveDate', function() {
		var tanggal = $('#tgl_butuh').val();
		var id_category = $('#category').val();

		if (id_category == '0') {
			swal({
				title: "Error Message!",
				text: 'Pilih Category Terlebih Dahulu ...',
				type: "warning"
			});
			return false;
		}

		$.ajax({
			url: base_url + active_controller + '/save_reorder_change_date',
			type: "POST",
			data: {
				"tanggal": tanggal,
				"id_category": id_category,
			},
			cache: false,
			dataType: 'json',
			success: function(data) {
				console.log(data.pesan);
			},
			error: function() {
				console.log('error connection serve !');
			}
		});
	});

	$(document).on('change', '.input_qty_packing', function() {
		var id = $(this).data('id');
		var qty_packing = $(this).val();
		if (qty_packing == '' || qty_packing == null) {
			qty_packing = 0;
		} else {
			qty_packing = qty_packing.split(',').join('');
			qty_packing = parseFloat(qty_packing);
		}

		var konversi = $(this).data('konversi');
		if (konversi == '' || konversi == 0 || konversi == null) {
			konversi = 1;
		}

		var nilai = (qty_packing * konversi);
		$('.purchase_' + id).val(nilai.toLocaleString());
	});

	$(document).on('change', '.input_qty_satuan', function() {
		var id = $(this).data('id');
		var max_propose = $(this).data('max_propose');
		var qty_satuan = $(this).val();
		if (qty_satuan == '' || qty_satuan == null) {
			qty_satuan = 0;
		} else {
			qty_satuan = qty_satuan.split(',').join('');
			qty_satuan = parseFloat(qty_satuan);
		}

		if (qty_satuan > max_propose) {
			qty_satuan = max_propose;
		}

		var konversi = $(this).data('konversi');
		if (konversi == '' || konversi == 0 || konversi == null) {
			konversi = 1;
		}

		var nilai = (qty_satuan / konversi);
		$('.purchase_pack_' + id).val(nilai.toLocaleString());
	});

	function hitungBudget() {
		var category = $('#category').val();
		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'hitung_budget',
			data: {
				'category': category
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				$('.nilai_budget').val(number_format(result.nilai_budget));
			}
		});
	}

	function hitungPengajuan() {
		var category = $('#category').val();
		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'hitung_pengajuan',
			data: {
				'category': category
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				$('.nilai_pengajuan').val(number_format(result.nilai_pengajuan));
			}
		});
	}

	function DataTables(category = null) {
		var dataTable = $('#example1').DataTable({
			"processing": true,
			"serverSide": true,
			"stateSave": true,
			"autoWidth": true,
			"destroy": true,
			"responsive": true,
			"aaSorting": [
				[2, "asc"]
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
				url: base_url + active_controller + '/server_side_reorder_point_new',
				type: "post",
				data: function(d) {
					d.category = category;
				},
				cache: false,
				dataSrc: function(json) {
					var total_price = json.total_price;
					$('#example1 tfoot .total-price').text(number_format(total_price));
					return json.data;
				},
				error: function() {
					$(".my-grid-error").html("");
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#my-grid_processing").css("display", "none");
				}
			}
		});
	}

	function number_format(number, decimals, dec_point, thousands_sep) {
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function(n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}
</script>
