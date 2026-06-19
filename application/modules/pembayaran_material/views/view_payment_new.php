<?php
$kode_supplier = [];
$nm_supplier = [];
foreach ($results['result_payment'] as $item) {
	$get_rec_invoice = $this->db->get_where('tr_invoice_po', ['id' => $item->no_doc])->row();

	if (!empty($get_rec_invoice)) {
		if (strpos($get_rec_invoice->no_po, 'TRS1') !== false) {
			$arr_no_incoming = str_replace(', ', ',', $get_rec_invoice->no_po);
			$get_no_po = $this->db
				->select('a.no_ipp')
				->from('tr_incoming_check a')
				->where_in('a.kode_trans', explode(',', $arr_no_incoming))
				->get()
				->result();

			$arr_no_po = [];
			foreach ($get_no_po as $item_no_po) {
				$arr_no_po[] = $item_no_po->no_ipp;
			}

			$arr_no_po = implode(',', $arr_no_po);
			$arr_no_po = str_replace(', ', ',', $arr_no_po);

			$get_no_surat = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $arr_no_po) . "')")->result();
			foreach ($get_no_surat as $item_no_surat) {
				$no_po[] = $item_no_surat->no_surat;
			}
		} else {
			$no_po[] = $get_rec_invoice->no_po;
		}
	}

	if (!empty($no_po)) {
		$get_nm_supplier = $this->db
			->select('b.kode_supplier, b.nama')
			->from('tr_purchase_order a')
			->join('new_supplier b', 'b.kode_supplier = a.id_suplier', 'left')
			->where_in('a.no_surat', $no_po)
			->group_by('b.kode_supplier')
			->get()
			->result();
		foreach ($get_nm_supplier as $item_supplier) {
			$kode_supplier[$item_supplier->kode_supplier] = $item_supplier->kode_supplier;
			$nm_supplier[] = $item_supplier->nama;
		}
	}
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
	.table thead th {
		background-color: #f8f9fa !important;
		color: #333 !important;
		font-weight: 600;
		vertical-align: middle;
	}

	/* Sinkronisasi style Chosen Plugin agar fit dengan form-control Bootstrap 5 */
	.chosen-container-single .chosen-single {
		height: 38px !important;
		line-height: 35px !important;
		background: #f8f9fa !important;
		border: 1px solid #dee2e6 !important;
		border-radius: 0.375rem !important;
	}

	.chosen-container-single .chosen-single div b {
		background-position: 0px 7px !important;
	}
</style>

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<form action="" id="frm-data" enctype="multipart/form-data">
	<input type="hidden" name="id_payment" class="id_payment" value="<?= $results['id_payment'] ?>">

	<div class="card border-0 shadow-sm mb-4">

		<div class="card-header bg-white border-0 pt-4 px-4">
			<h5 class="fw-bold text-dark mb-3 border-bottom pb-2"><i class="fa fa-file-text-o me-2"></i>Summary Pembayaran Material</h5>

			<div class="row g-3">
				<div class="col-12 col-md-6">
					<div class="mb-3">
						<label class="form-label fw-semibold small text-muted">Tanggal Bayar</label>
						<input type="date" name="tgl_bayar" class="form-control bg-light tgl_bayar" value="<?= $results['result_header']->tgl_bayar ?>" readonly>
					</div>

					<div class="mb-3">
						<label class="form-label fw-semibold small text-muted">Keterangan Pembayaran</label>
						<textarea name="keterangan_pembayaran" class="form-control bg-light keterangan_pembayaran" rows="3" readonly><?= $results['result_header']->keterangan_pembayaran ?></textarea>
					</div>

					<div class="mb-3">
						<label class="form-label fw-semibold small text-muted">Mata Uang</label>
						<select name="mata_uang" class="form-select bg-light mata_uang" disabled>
							<option value="">- Mata Uang -</option>
							<?php foreach ($results['list_mata_uang'] as $item_mata_uang) {
								$selected = ($item_mata_uang->kode == $results['result_header']->mata_uang) ? 'selected' : '';
								echo '<option value="' . $item_mata_uang->kode . '" ' . $selected . '>' . $item_mata_uang->kode . '</option>';
							} ?>
						</select>
					</div>
				</div>

				<div class="col-12 col-md-6">
					<div class="mb-3">
						<label class="form-label fw-semibold small text-muted">Nama Supplier</label>
						<input type="hidden" name="supplier_input" class="supplier_input" value="<?= implode(',', $kode_supplier) ?>">
						<input type="hidden" name="nm_supplier_input" class="nm_supplier_input" value="<?= implode(',', $nm_supplier) ?>">
						<select name="supplier" class="form-select bg-light supplier" disabled>
							<option value="">- Supplier Name -</option>
							<?php foreach ($results['list_supplier'] as $item_supplier) {
								$selected = (isset($kode_supplier[$item_supplier->kode_supplier])) ? 'selected' : '';
								echo '<option value="' . $item_supplier->kode_supplier . '" ' . $selected . '>' . $item_supplier->nama . '</option>';
							} ?>
						</select>
					</div>

					<div class="mb-3">
						<label class="form-label fw-semibold small text-muted">Metode Bank Pengirim</label>
						<select name="bank" class="form-select bg-light bank" disabled>
							<option value="">- Bank -</option>
							<?php foreach ($results['list_bank'] as $item_bank) {
								$selected = ($item_bank->no_perkiraan == $results['result_header']->coa_bank) ? 'selected' : '';
								echo '<option value="' . $item_bank->no_perkiraan . '" ' . $selected . '>' . $item_bank->nama . '</option>';
							} ?>
						</select>
					</div>

					<div class="mb-3">
						<label class="form-label fw-semibold small text-muted">Request Payment Bank</label>
						<div class="input-group">
							<span class="input-group-text bg-light fw-semibold small">Rp</span>
							<input type="text" name="payment_bank" class="form-control bg-light text-end input_payment_bank auto_num fw-bold text-primary" value="<?= number_format($results['result_header']->payment_bank, 2) ?>" readonly>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="card-body px-4">
			<div class="table-responsive">
				<table class="table table-striped table-hover table-bordered align-middle w-100" id="mytabledata">
					<thead class="text-center">
						<tr>
							<th>Supplier</th>
							<th>Nomor Dokumen</th>
							<th>Payment Bank</th>
							<th colspan="2">Tipe & Nilai PPh</th>
							<th>PPN</th>
							<th>Total Alokasi Payment</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$total_payment = 0;
						$total_ppn = 0;
						$total_pph = 0;
						$total_payment_bank = 0;
						$total_selisih = 0;
						$no = 1;
						foreach ($results['result_payment'] as $item) {
							$get_rec_invoice = $this->db->get_where('tr_invoice_po', ['id' => $item->no_doc])->row();
							$nilai_utuh = 0;
							$persen_progress = 1;

							if (!empty($get_rec_invoice) && $get_rec_invoice->id_top !== '') {
								$get_top = $this->db->get_where('tr_top_po', ['id' => $get_rec_invoice->id_top])->row();
								if (!empty($get_top)) {
									$persen_progress = $get_top->progress;
								}
							}
							if (!empty($get_rec_invoice)) {
								if (strpos($get_rec_invoice->no_po, 'TRS1') !== false) {
									$arr_no_incoming = str_replace(', ', ',', $get_rec_invoice->no_po);
									$get_no_po = $this->db->select('a.no_ipp')->from('tr_incoming_check a')->where_in('a.kode_trans', explode(',', $arr_no_incoming))->get()->result();
									$arr_no_po = [];
									foreach ($get_no_po as $item_no_po) {
										$arr_no_po[] = $item_no_po->no_ipp;
									}
									$arr_no_po = implode(',', $arr_no_po);
									$arr_no_po = str_replace(', ', ',', $arr_no_po);

									$get_no_surat = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $arr_no_po) . "')")->result();
									foreach ($get_no_surat as $item_no_surat) {
										$no_po[] = $item_no_surat->no_surat;
									}

									$get_incoming_check_detail = $this->db->select('a.qty_order, b.hargasatuan')->from('tr_incoming_check_detail a')->join('dt_trans_po b', 'b.id = a.id_po_detail', 'left')->where_in('a.kode_trans', $arr_no_incoming)->get()->result();
									foreach ($get_incoming_check_detail as $item_detail) {
										$nilai_utuh += ($item_detail->hargasatuan * $item_detail->qty_order);
									}
								} else {
									$no_po[] = $get_rec_invoice->no_po;
									$get_nilai_utuh = $this->db->select('a.hargatotal')->from('tr_purchase_order a')->where('a.no_surat', $get_rec_invoice->no_po)->get()->result();
									foreach ($get_nilai_utuh as $item_nilai_utuh) {
										$nilai_utuh += $item_nilai_utuh->hargatotal;
									}
								}
							}

							if (!empty($no_po)) {
								$get_nm_supplier = $this->db->select('b.nama as nm_supplier')->from('tr_purchase_order a')->join('new_supplier b', 'b.kode_supplier = a.id_suplier', 'left')->where_in('a.no_surat', $no_po)->group_by('b.nama')->get()->result();
								foreach ($get_nm_supplier as $item_supplier) {
									$nm_supplier[] = $item_supplier->nm_supplier;
								}
							}

							$nm_supplier = implode(', ', $nm_supplier);
							$nilai_ppn = (($nilai_utuh * $persen_progress / 100) * 11 / 100);
							if ($nilai_ppn <= 0) {
								$nilai_ppn = $item->total_ppn;
							}
							$nilai_pph = $item->total_pph;

							$selected_pph_23 = ($item->tipe_pph == 'PPH 23') ? 'selected' : '';
							$selected_pph_22 = ($item->tipe_pph == 'PPH 22') ? 'selected' : '';
						?>
							<tr>
								<td><?= $nm_supplier; ?></td>
								<td class="text-center fw-semibold">
									<input type="hidden" name="dt[<?= $no ?>][id_payment]" value="<?= $item->id ?>">
									<?= $item->no_doc; ?>
								</td>
								<td class="text-end fw-semibold">
									<input type="hidden" class="jumlah_col_<?= $item->id ?>">
									<input type="hidden" class="payment_bank_<?= $item->id ?>" value="<?= $item->jumlah ?>">
									<?= number_format($item->jumlah, 2); ?>
								</td>
								<td style="width: 12%;">
									<select name="dt[<?= $no ?>][tipe_pph]" class="form-select form-select-sm bg-light" disabled>
										<option value="1" <?= $selected_pph_23 ?>>PPH 23</option>
										<option value="2" <?= $selected_pph_22 ?>>PPH 22</option>
									</select>
								</td>
								<td>
									<input type="hidden" class="nilai_utuh_<?= $item->id ?>" value="<?= $nilai_utuh ?>">
									<input type="hidden" class="persen_progress_<?= $item->id ?>" value="<?= $persen_progress ?>">
									<input type="text" class="form-control form-control-sm text-end" name="dt[<?= $no ?>][nilai_pph]" data-id="<?= $item->id ?>" value="<?= number_format($item->total_pph, 2) ?>" readonly>
								</td>
								<td class="text-end">
									<input type="hidden" name="dt[<?= $no ?>][nilai_ppn]" class="nilai_ppn_<?= $item->id ?>" value="<?= $nilai_ppn ?>">
									<?= number_format($nilai_ppn, 2); ?>
								</td>
								<td class="text-end fw-bold text-success payment_col_<?= $item->id ?>"><?= number_format($item->jumlah - $nilai_pph + $nilai_ppn, 2); ?></td>
							</tr>
						<?php
							$total_payment += ($item->jumlah - $nilai_pph + $nilai_ppn);
							$total_ppn += ($nilai_ppn);
							$total_payment_bank += ($item->jumlah);
							$total_pph += ($item->total_pph);
							$total_selisih += $item->selisih;
							$no++;
						}
						?>
					</tbody>

					<tfoot class="table-light align-middle">
						<tr>
							<td colspan="5" class="border-0"></td>
							<td class="fw-semibold">Total Payment</td>
							<td class="text-end fw-bold text-primary"><?= number_format($total_payment, 2) ?></td>
						</tr>
						<tr>
							<td colspan="5" class="border-0"></td>
							<td class="fw-semibold">Selisih</td>
							<td class="text-end selisih_col fw-mono"><?= number_format($total_selisih, 2) ?></td>
						</tr>
						<tr>
							<td colspan="5" class="border-0"></td>
							<td class="fw-semibold">Bank Charge</td>
							<td>
								<input type="text" name="bank_charge" class="form-control form-control-sm text-end auto_num bank_charge bg-light" value="<?= number_format($results['bank_charge'], 2) ?>" readonly>
							</td>
						</tr>
						<tr>
							<td colspan="5" class="border-0"></td>
							<td class="fw-semibold">Total PPh</td>
							<td class="text-end total_pph_col text-danger"><?= number_format($total_pph, 2) ?></td>
						</tr>
						<tr>
							<td colspan="5" class="border-0"></td>
							<td class="fw-semibold">Total PPN</td>
							<td class="text-end text-muted"><?= number_format($total_ppn, 2) ?></td>
						</tr>
						<tr class="table-warning fw-bold">
							<td colspan="5" class="border-0"></td>
							<td>Kontrol Status</td>
							<td class="text-end kontrol_col <?= (($results['result_header']->payment_bank - $total_payment) != 0) ? 'text-danger' : 'text-success' ?>"><?= number_format($results['result_header']->payment_bank - $total_payment, 2) ?></td>
						</tr>
					</tfoot>
				</table>
			</div>

			<input type="hidden" name="total_pph" class="total_pph" value="<?= $total_pph ?>">
			<input type="hidden" name="total_payment" class="total_payment" value="<?= $total_payment ?>">
			<input type="hidden" name="total_ppn" class="total_ppn" value="<?= $total_ppn ?>">
			<input type="hidden" name="total_payment_bank" class="total_payment_bank" value="<?= $total_payment_bank ?>">
			<input type="hidden" name="kontrol" class="kontrol" value="<?= ($results['result_header']->payment_bank - $total_payment - $results['bank_charge'] - $total_ppn + $total_pph) ?>">
		</div>

		<div class="card-footer bg-white border-0 pb-4 px-4 d-flex justify-content-between align-items-center">
			<div>
				<?php if (file_exists('assets/expense/' . $results['result_header']->link_doc) && $results['result_header']->link_doc !== '') : ?>
					<a href="<?= base_url('assets/expense/' . $results['result_header']->link_doc) ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fa fa-download me-1"></i> Download Berkas Dokumen</a>
				<?php endif; ?>
			</div>
			<div>
				<a href="<?= base_url() ?>pembayaran_material/payment_list" class="btn btn-warning btn-sm text-dark px-3 fw-semibold"><i class="fa fa-reply me-1"></i> Kembali</a>
			</div>
		</div>

	</div>
</form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script>
	$(document).ready(function() {
		if (typeof $.fn.chosen !== 'undefined') {
			$('.bank, .mata_uang, .pph').chosen({
				width: '100%'
			});
		}
		if (typeof $.fn.autoNumeric !== 'undefined') {
			$('.auto_num').autoNumeric();
		}

		$.ajax({
			type: "POST",
			url: siteurl + active_controller + 'used_choosed_payment',
			cache: false,
			success: function(result) {}
		});
	});

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

	function hitung_kontrol() {
		var total_payment = parseFloat($('.total_payment').val()) || 0;
		var total_pph = parseFloat($('.total_pph').val()) || 0;
		var total_ppn = parseFloat($('.total_ppn').val()) || 0;

		var total_payment_bank = $('.input_payment_bank').val() || '0';
		total_payment_bank = parseFloat(total_payment_bank.split(',').join('')) || 0;

		var bank_charge = $('.bank_charge').val() || '0';
		bank_charge = parseFloat(bank_charge.split(',').join('')) || 0;

		var kontrol = parseFloat(total_payment_bank - total_payment - bank_charge - total_ppn - total_pph);

		$('.kontrol_col').html(number_format(kontrol, 2));
		$('.kontrol').val(kontrol);

		// Ubah warna text indikator kontrol status agar dinamis sewaktu PPh/Bank diubah
		if (kontrol == 0) {
			$('.kontrol_col').removeClass('text-danger').addClass('text-success');
		} else {
			$('.kontrol_col').removeClass('text-success').addClass('text-danger');
		}
	}

	$(document).on('change', '.change_nilai_pph', function() {
		var id = $(this).data('id');
		var payment_bank = parseFloat($('.payment_bank_' + id).val()) || 0;
		var nilai_ppn = parseFloat($('.nilai_ppn_' + id).val()) || 0;

		var nilai_pph = $(this).val() || '0';
		nilai_pph = parseFloat(nilai_pph.split(',').join('')) || 0;

		var ttl_pph = 0;
		$('.nilai_pph').each(function() {
			var pph = $(this).val() || '0';
			ttl_pph += parseFloat(pph.split(',').join('')) || 0;
		});

		$('.total_pph').val(ttl_pph);
		$('.total_pph_col').html(number_format(ttl_pph, 2));

		var nilai_payment = (payment_bank - nilai_ppn + nilai_pph);
		$('.payment_col_' + id).html(number_format(nilai_payment, 2));

		hitung_kontrol();
	});

	$(document).on('change', '.input_payment_bank', function() {
		var nilai_payment_bank = $(this).val() || '0';
		nilai_payment_bank = parseFloat(nilai_payment_bank.split(',').join('')) || 0;

		var total_payment = parseFloat($('.total_payment').val()) || 0;
		var selisih = parseFloat(total_payment - nilai_payment_bank);

		$('.selisih_col').html(number_format(selisih, 2));
		hitung_kontrol();
	});

	$(document).on('change', '.bank_charge', function() {
		hitung_kontrol();
	});

	$(document).on('submit', '#frm-data', function(e) {
		e.preventDefault();
		var kontrol = parseFloat($('.kontrol').val()) || 0;

		if (kontrol !== 0) {
			Swal.fire({
				icon: 'warning',
				title: 'Perhatian!',
				text: 'Maaf, Pastikan nilai Kontrol Status harus 0.00 sebelum data dibayarkan!'
			});
			return false;
		}

		Swal.fire({
			title: 'Apakah Anda Yakin?',
			text: 'Data pembayaran material ini akan segera diproses ke database dan tidak dapat diubah kembali!',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#198754',
			cancelButtonColor: '#dc3545',
			confirmButtonText: 'Ya, Proses Sekarang!',
			cancelButtonText: 'Batal'
		}).then((result) => {
			if (result.isConfirmed) {
				var formData = new FormData($('#frm-data')[0]);
				var baseurl = siteurl + active_controller + 'save_payment';

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
							Swal.fire({
								icon: 'success',
								title: 'Berhasil Disimpan!',
								text: data.pesan,
								confirmButtonColor: '#198754'
							}).then(() => {
								window.location.href = siteurl + active_controller + 'payment_list';
							});
						} else {
							Swal.fire({
								icon: 'error',
								title: 'Gagal Menyimpan!',
								text: data.pesan
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error AJAX!',
							text: 'Terjadi kegagalan komunikasi data dengan server. Silakan coba kembali.'
						});
					}
				});
			} else if (result.dismiss === Swal.DismissReason.cancel) {
				Swal.fire({
					icon: 'info',
					title: 'Dibatalkan',
					text: 'Proses pembayaran material ditangguhkan.',
					timer: 1500,
					showConfirmButton: false
				});
			}
		});
	});
</script>