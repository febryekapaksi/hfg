<?php
$hide_table_jurnal_petty_cash = 'd-none';
// if (!empty($results['jurnal_refill_petty_cash'])) {
// 	$hide_table_jurnal_petty_cash = '';
// }

// Tentukan apakah ini PO import atau lokal
$is_import = false;
$kurs_receive_invoice = 0;
foreach ($results['result_payment'] as $item_check) {
	// Cek status LOI dari tr_purchase_order
	if (in_array($results['result_header']->tipe, ['invoice_dp', 'invoice_import', 'invoice_local'])) {
		// no_doc = no_po di tr_purchase_order, ids = id di tr_receive_invoice
		// Cek loi langsung dari tr_purchase_order menggunakan no_doc
		$get_po_loi = $this->db->select('loi')->get_where('tr_purchase_order', ['no_po' => $item_check->no_doc])->row();
		// Fallback: cek juga via no_surat jika tidak ketemu via no_po
		if (empty($get_po_loi)) {
			$get_po_loi = $this->db->select('loi')->get_where('tr_purchase_order', ['no_surat' => $item_check->no_doc])->row();
		}
		if (!empty($get_po_loi) && strtolower(trim($get_po_loi->loi)) === 'import') {
			$is_import = true;
		}
		// Fallback: jika tipe request_payment = invoice_import, anggap import
		if ($results['result_header']->tipe === 'invoice_import') {
			$is_import = true;
		}
		// Ambil kurs dari tr_receive_invoice menggunakan ids
		if (!empty($item_check->ids)) {
			$get_kurs_ri = $this->db->select('kurs')->get_where('tr_receive_invoice', ['id' => $item_check->ids])->row();
			if (!empty($get_kurs_ri) && $get_kurs_ri->kurs > 0) {
				$kurs_receive_invoice = (float)$get_kurs_ri->kurs;
			}
		}
	} else {
		// Untuk tipe lama (non invoice PO) — cek via tr_invoice_po -> tr_purchase_order
		$get_inv_po = $this->db->get_where('tr_invoice_po', ['id' => $item_check->no_doc])->row();
		if (!empty($get_inv_po) && !empty($get_inv_po->no_po)) {
			$no_po_check = $get_inv_po->no_po;
			if (strpos($no_po_check, 'TRS1') !== false) {
				$arr_inc = explode(',', str_replace(', ', ',', $no_po_check));
				$get_ipp = $this->db->select('no_ipp')->where_in('kode_trans', $arr_inc)->get('tr_incoming_check')->row();
				if (!empty($get_ipp)) {
					$no_po_check = $get_ipp->no_ipp;
				}
			}
			$get_po_loi2 = $this->db->select('loi')->get_where('tr_purchase_order', ['no_po' => $no_po_check])->row();
			if (!empty($get_po_loi2) && strtolower($get_po_loi2->loi) === 'import') {
				$is_import = true;
			}
		}
	}
}
$hide_ppn_pph_class = $is_import ? 'd-none' : '';
$hide_ppn_pph_style = $is_import ? 'style="display:none !important;"' : '';

$kode_supplier = [];
$nm_supplier = [];

if (!empty($results['result_header']->id_supplier)) {
	$kode_supplier[$results['result_header']->id_supplier] = $results['result_header']->id_supplier;
}
if (!empty($results['result_header']->nm_supplier)) {
	$nm_supplier[] = $results['result_header']->nm_supplier;
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
	td {
		padding: 5px 5px 5px 5px;
	}

	.d-none {
		display: none;
	}
</style>

<input type="hidden" name="id_payment" class="id_payment" value="<?= $results['id_payment'] ?>">
<div class="box box-primary">
	<div class="box-header">
		<table class="" style="width: 100%;" border="0">
			<tr>
				<td width="15%" style="">Tgl Bayar</td>
				<td width="5%" class="text-center">:</td>
				<td width="25%">
					<input type="text" name="tgl_bayar" id="tgl_bayar" readonly disabled class="form-control form-control-sm tgl_bayar" value="<?= $results['result_header']->tgl_bayar ?>" placeholder="Pilih tanggal">
				</td>
				<td width="15%" style="">Supplier</td>
				<td width="5%" class="text-center">:</td>
				<td width="25%">
					<input type="hidden" name="supplier_input" class="supplier_input" value="<?= implode(',', $kode_supplier) ?>">
					<input type="hidden" name="nm_supplier_input" class="nm_supplier_input" value="<?= implode(',', $nm_supplier) ?>">
					<select name="supplier" id="" class="form-control form-control-sm supplier" disabled>
						<option value="">- Supplier Name -</option>
						<?php
						foreach ($results['list_supplier'] as $item_supplier) {
							$selected = (isset($kode_supplier[$item_supplier->kode_supplier])) ? 'selected' : '';
							echo '<option value="' . $item_supplier->kode_supplier . '" ' . $selected . '>' . $item_supplier->nama . '</option>';
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td width="15%" style="">Keterangan Pembayaran</td>
				<td width="5%" class="text-center">:</td>
				<td width="25%">
					<textarea name="keterangan_pembayaran" readonly id="" class="form-control form-control-sm keterangan_pembayaran"><?= $results['result_header']->keterangan_pembayaran ?></textarea>
				</td>
				<td width="15%" style="">Pilih Bank</td>
				<td width="5%" class="text-center">:</td>
				<td width="25%">
					<select name="bank" id="" class="form-control form-control-sm bank" disabled onchange="set_jurnal_refill('<?= $results['id_payment'] ?>')">
						<option value="">- Bank -</option>
						<?php
						foreach ($results['list_bank'] as $item_bank) {
							echo '<option value="' . $item_bank->no_perkiraan . '" ' . ($item_bank->no_perkiraan == $results['result_header']->coa_bank ? 'selected' : '') . '>' . $item_bank->no_perkiraan . ' - ' . $item_bank->nama . '</option>';
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td width="15%" style="">Mata Uang</td>
				<td width="5%" class="text-center">:</td>
				<td width="25%">
					<select name="mata_uang" id="" class="form-control form-control-sm mata_uang" disabled data-placeholder="- Pilih Mata Uang -">
						<option value="">- Mata Uang -</option>
						<?php
						foreach ($results['list_mata_uang'] as $item_mata_uang) {
							echo '<option value="' . $item_mata_uang->kode . '" ' . ($item_mata_uang->kode == $results['result_header']->mata_uang ? 'selected' : '') . '>' . $item_mata_uang->kode . '</option>';
						}
						?>
					</select>
				</td>
				<td width="15%" style="">Nilai Bank</td>
				<td width="5%" class="text-center">:</td>
				<td width="25%">
					<input type="text" name="payment_bank" id="" class="form-control form-control-sm text-right input_payment_bank auto_num" value="<?= number_format($results['result_header']->payment_bank, 2) ?>" readonly>
				</td>
			</tr>
			<tr>
				<td width="15%" style="">Kurs</td>
				<td width="5%" class="text-center">:</td>
				<td width="25%">
					<input type="text" name="kurs_payment" id="" class="form-control form-control-sm text-right auto_num kurs_payment_input" value="<?= number_format($results['result_header']->kurs_payment, 2) ?>" readonly disabled placeholder="Pilih mata uang dulu">
				</td>
				<td width="15%" style="">Nilai Bank IDR</td>
				<td width="5%" class="text-center">:</td>
				<td width="25%">
					<input type="text" name="nilai_bank_idr" id="" class="form-control form-control-sm text-right nilai_bank_idr" value="<?= number_format($results['result_header']->dibayar_idr, 2) ?>" readonly readonly style="background-color: #e9ecef;">
				</td>
			</tr>
		</table>
	</div>
	<div class="box-body" style="margin-bottom: 10px;">
		<table class="table table-bordered table-striped" id="mytabledata" width='100%'>
			<thead>
				<tr class='bg-blue'>
					<th class="text-center">Supplier</th>
					<th class="text-center">Nomor Dokumen</th>
					<th class="text-center">Invoice</th>
					<th class="text-center" colspan="2" <?= $hide_ppn_pph_style ?>>PPH</th>
					<th class="text-center" <?= $hide_ppn_pph_style ?>>PPN</th>
					<th class="text-center">DPP</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$total_payment = 0;
				$total_ppn = 0;
				$total_pph = 0;
				$total_payment_bank = 0;
				$ttl_bank_charge = 0;
				$no = 1;

				foreach ($results['result_payment'] as $item) {
					$nm_supplier_row = $results['result_header']->nm_supplier ?? '';

					$nilai_ppn = $item->nilai_ppn ?? 0;
					$nilai_pph = $item->nilai_pph ?? 0;

					// DPP diambil langsung dari payment_approve_details.total_bayar_idr
					$total_bayar_idr = $item->total_bayar_idr ?? 0;
					echo '<tr>';
					echo '<td class="text-center">' . $nm_supplier_row . '</td>';
					echo '<td class="text-center">
						<input type="hidden" name="dt[' . $no . '][id_payment]" value="' . $item->id . '">
						<input type="hidden" name="dt[' . $no . '][kurs_invoice]" value="' . ($item->kurs_invoice ?? 1) . '">
						<input type="hidden" name="dt[' . $no . '][no_doc]" value="' . ($item->no_doc ?? '') . '">
						<input type="hidden" name="dt[' . $no . '][no_surat]" value="' . ($item->no_surat ?? '') . '">
						<input type="hidden" name="dt[' . $no . '][jumlah]" value="' . $item->nilai_invoice . '">
						<input type="hidden" name="dt[' . $no . '][ids]" value="' . ($item->id ?? '') . '">
						<input type="hidden" class="jumlah_asli_' . $item->id . '" value="' . $item->nilai_invoice . '">
						' . ($item->no_surat ?? $item->no_doc) . '</td>';
					echo '<td class="text-right req_payment_col_' . $item->id . '">
						<input type="hidden" class="jumlah_col_' . $item->id . '">
						<input type="hidden" class="payment_bank_' . $item->id . '" value="' . $item->nilai_invoice . '">
						' . number_format($item->nilai_invoice, 2) . '
					</td>';

					// PPH column
					echo '<td ' . $hide_ppn_pph_style . '>';
					echo '<select name="dt[' . $no . '][tipe_pph]" class="form-control form-control-sm chosen" disabled>';
					$tipe_pph = $item->tipe_pph ?? '';
					echo '<option value="' . $tipe_pph . '">' . $tipe_pph . '</option>';
					echo '</select>';
					echo '</td>';
					echo '<td ' . $hide_ppn_pph_style . '>';
					echo '<input type="text" class="form-control form-control-sm text-right auto_num nilai_pph" readonly name="dt[' . $no . '][nilai_pph]" value="' . $nilai_pph . '">';
					echo '</td>';

					// PPN column
					echo '<td class="text-right" ' . $hide_ppn_pph_style . '>';
					echo '<input type="text" name="dt[' . $no . '][nilai_ppn]" class="form-control form-control-sm text-right auto_num nilai_ppn" readonly value="' . $nilai_ppn . '">';
					echo '</td>';

					// DPP column
					echo '<td class="text-right payment_col_' . $item->id . '">' . number_format($total_bayar_idr, 2) . '</td>';
					echo '</tr>';

					$total_payment += $total_bayar_idr;
					$total_ppn += $nilai_ppn;
					$total_payment_bank += $item->nilai_invoice;
					$no++;
				}
				?>
			</tbody>
			<tbody>
				<?php $footer_colspan = $is_import ? 2 : 5; ?>
				<tr>
					<td colspan="<?= $footer_colspan ?>"></td>
					<td>Subtotal</td>
					<td class="text-right total_payment_col">
						<?= number_format($total_payment, 2) ?>
					</td>
				</tr>
				<tr class="ppn_footer_row" <?= $hide_ppn_pph_style ?>>
					<td colspan="5"></td>
					<td>PPN</td>
					<td class="text-right total_ppn_col"><?= number_format($results['result_header']->total_ppn, 2) ?></td>
				</tr>
				<tr class="pph_footer_row" <?= $hide_ppn_pph_style ?>>
					<td colspan="5"></td>
					<td>PPH</td>
					<td class="text-right total_pph_col">
						<?= number_format($results['result_header']->total_pph, 2) ?>
					</td>
				</tr>
				<tr>
					<td colspan="<?= $footer_colspan ?>"></td>
					<td>Bank Charge</td>
					<td>
						<input type="text" name="bank_charge" id="" class="form-control form-control-sm text-right auto_num bank_charge" readonly value="<?= number_format($results['result_header']->bank_charge, 2) ?>" readonly>
					</td>
				</tr>
				<tr>
					<td colspan="<?= $footer_colspan ?>"></td>
					<td><strong>Grand Total Payment</strong></td>
					<td class="text-right grand_total_payment_col"><strong><?= number_format($results['result_header']->grand_total_payment, 2) ?></strong></td>
				</tr>
				<tr class="selisih_kurs_row">
					<td colspan="<?= $footer_colspan ?>"></td>
					<td>Selisih Kurs</td>
					<td class="text-right selisih_kurs_col"><?= number_format(abs($results['result_header']->selisih_kurs_idr), 2) ?>
					</td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="total_pph" class="total_pph" value="<?= $total_pph ?>">
		<input type="hidden" name="total_payment" class="total_payment" value="<?= $total_payment ?>">
		<input type="hidden" name="total_ppn" class="total_ppn" value="<?= $total_ppn ?>">
		<input type="hidden" name="total_payment_bank" class="total_payment_bank" value="<?= $total_payment_bank ?>">
		<input type="hidden" name="kontrol" class="kontrol" value="<?= number_format($results['result_header']->payment_bank, 2) ?>" readonly>
		<input type="hidden" class="kurs_receive_invoice" value="<?= $kurs_receive_invoice ?>">
		<input type="hidden" class="is_import" value="<?= $is_import ? '1' : '0' ?>">

		<div class="col-md-4">
			<div class="form-group">
				<?php if (isset($results['result_header']->link_doc) && $results['result_header']->link_doc != '') { ?><a href="<?= base_url('assets/expense/' . $results['result_header']->link_doc) ?>" class="btn btn-sm btn-primary" target="_blank"><i class="fa fa-download"></i> Download Dokumen</a><?php } ?>
			</div>
		</div>
	</div>

	<div class="box-footer">
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">

				<a href="<?= base_url() ?>pembayaran_material/payment_list" class="btn btn-warning btn-sm"><i class="fa fa-reply">&nbsp;</i>Kembali</a>
			</div>
		</div>
	</div>

</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script>
	set_jurnal();
	set_jurnal_refill();

	$(document).ready(function() {
		// Init Flatpickr untuk tanggal bayar
		flatpickr('#tgl_bayar', {
			dateFormat: 'Y-m-d',
			defaultDate: '<?= $results['result_header']->tgl_bayar ?>',
			allowInput: true
		});

		// $('.supplier').chosen();
		$('.bank').chosen({
			width: '100%'
		});
		$('select[name="mata_uang"]').select2({
			width: '100%',
			placeholder: '- Pilih Mata Uang -',
			allowClear: true
		});
		$('.pph').chosen({
			width: '100%'
		});

		$('select[name="mata_uang"]').on('change', function() {
			var mata_uang = $(this).val();
			var kurs_input = $('input[name="kurs_payment"]');

			if (!mata_uang || mata_uang === '') {
				// Belum pilih — kosongkan dan disable
				kurs_input.val('').prop('disabled', true)
					.attr('placeholder', 'Pilih mata uang dulu');
			} else if (mata_uang.toUpperCase() === 'IDR') {
				// IDR — set 1 dan tetap disable
				kurs_input.val('1').prop('disabled', true)
					.attr('placeholder', '');
			} else {
				// Non-IDR — enable dan minta input kurs
				kurs_input.val('').prop('disabled', false)
					.attr('placeholder', 'Masukkan kurs')
					.focus();
			}

			recalculate_all_by_kurs();
		});

		$('.auto_num').autoNumeric();

		// $.ajax({
		// 	type: "POST",
		// 	url: siteurl + active_controller + 'used_choosed_payment',
		// 	cache: false,
		// 	success: function(result) {

		// 	}
		// });
	});

	function getNum(val) {
		if (isNaN(val) || val == '') {
			return 0;
		}
		return parseFloat(val);
	}

	function number_format(number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
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
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
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
		var total_payment_bank = $('.input_payment_bank').val();
		if (total_payment_bank !== '' && total_payment_bank !== undefined) {
			total_payment_bank = total_payment_bank.split(',').join('');
			total_payment_bank = parseFloat(total_payment_bank) || 0;
		} else {
			total_payment_bank = 0;
		}
		var bank_charge = $('.bank_charge').val();
		if (bank_charge !== '' && bank_charge !== undefined) {
			bank_charge = bank_charge.split(',').join('');
			bank_charge = parseFloat(bank_charge) || 0;
		} else {
			bank_charge = 0;
		}

		// Kurs untuk hitung nilai bank IDR
		var kurs_val = $('input[name="kurs_payment"]').val();
		if (kurs_val !== '' && kurs_val !== undefined) {
			kurs_val = kurs_val.split(',').join('');
			kurs_val = parseFloat(kurs_val) || 0;
		} else {
			kurs_val = 0;
		}
		if (kurs_val <= 0) kurs_val = 1;

		var nilai_bank_idr = total_payment_bank * kurs_val;

		// Grand Total = Subtotal + PPN - PPH + Bank Charge
		var grand_total = total_payment + total_ppn - total_pph + bank_charge;

		// Kontrol = Nilai Bank IDR - Grand Total Payment
		var kontrol = parseFloat((nilai_bank_idr - grand_total).toFixed(2));

		$('.kontrol').val(kontrol);
	}

	function set_jurnal() {
		var id_payment = $('.id_payment').val();
		var payment_bank = $('.input_payment_bank').val()
		var bank_charge = $('.bank_charge').val();
		var bank = $('.bank').val();
		var nilai_pph = $('.total_pph').val();
		var nilai_ppn = $('.total_ppn').val();

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'set_jurnal',
			data: {
				'id_payment': id_payment,
				'payment_bank': payment_bank,
				'bank_charge': bank_charge,
				'bank': bank,
				'nilai_pph': nilai_pph,
				'nilai_ppn': nilai_ppn
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				$('.tbody_jurnal').html(result.hasil_jurnal);
				$('.th_ttl_debit_jurnal').html(number_format(result.ttl_debit));
				$('.th_ttl_kredit_jurnal').html(number_format(result.ttl_kredit));
			}
		})
	}

	function set_jurnal_refill() {
		// var id_payment = $('.id_payment').val();
		// var bank = $('.bank').val();

		// $.ajax({
		// 	type: 'post',
		// 	url: siteurl + active_controller + 'set_jurnal_refill',
		// 	data: {
		// 		'id_payment': id_payment,
		// 		'bank': bank
		// 	},
		// 	cache: false,
		// 	dataType: 'json',
		// 	success: function(result) {
		// 		$('.tbody_jurnal_refill_pettycash').html(result.hasil);
		// 		$('.ttl_debit_refill').html(number_format(result.ttl_debit));
		// 		$('.ttl_kredit_refill').html(number_format(result.ttl_kredit));
		// 	}
		// });
	}

	$(document).on('change', '.change_nilai_pph', function() {
		recalculate_all_by_kurs();
	});

	$(document).on('change', '.change_nilai_ppn', function() {
		recalculate_all_by_kurs();
	});

	$(document).on('change', '.input_payment_bank', function() {
		recalculate_all_by_kurs();
	});

	$(document).on('change keyup', '.input_payment_bank', function() {
		recalculate_all_by_kurs();
	});

	$(document).on('change', '.bank_charge', function() {
		recalculate_all_by_kurs();
	});

	// Saat kurs diubah, recalculate semua nominal × kurs
	$(document).on('change keyup', 'input[name="kurs_payment"]', function() {
		recalculate_all_by_kurs();
	});

	function recalculate_all_by_kurs() {
		var kurs_val = $('input[name="kurs_payment"]').val();
		if (kurs_val !== '' && kurs_val !== undefined) {
			kurs_val = kurs_val.split(',').join('');
			kurs_val = parseFloat(kurs_val) || 0;
		} else {
			kurs_val = 0;
		}
		if (kurs_val <= 0) kurs_val = 1;

		var total_req_payment = 0;

		// Loop semua row: Request Payment = jumlah_asli × kurs
		$('[class*="jumlah_asli_"]').each(function() {
			var className = $(this).attr('class');
			var id = className.replace('jumlah_asli_', '');
			var jumlah_asli = parseFloat($(this).val()) || 0;
			var jumlah_idr = jumlah_asli * kurs_val;

			// Update Request Payment column — tidak diubah, tetap nilai asli
			// $('.req_payment_col_' + id).html(number_format(jumlah_idr, 2));
			$('.payment_bank_' + id).val(jumlah_idr);

			total_req_payment += jumlah_idr;
		});

		// PPh, PPn, Bank Charge — langsung dari input user (IDR, TIDAK dikali kurs)
		var is_import = parseInt($('.is_import').val()) || 0;
		var total_pph = 0;
		var total_ppn = 0;

		if (!is_import) {
			$('.nilai_pph').each(function() {
				var val = $(this).val().split(',').join('');
				total_pph += parseFloat(val) || 0;
			});

			$('.nilai_ppn').each(function() {
				var val = $(this).val().split(',').join('');
				total_ppn += parseFloat(val) || 0;
			});
		}

		var bank_charge = parseFloat($('.bank_charge').val().split(',').join('')) || 0;

		// DPP per row = (jumlah_asli × kurs) - ppn row
		$('[class*="jumlah_asli_"]').each(function() {
			var className = $(this).attr('class');
			var id = className.replace('jumlah_asli_', '');
			var jumlah_asli = parseFloat($(this).val()) || 0;
			var jumlah_idr = jumlah_asli * kurs_val;

			var ppn_row = parseFloat($('.nilai_ppn_' + id).val().split(',').join('')) || 0;
			var dpp = jumlah_idr - ppn_row;
			$('.payment_col_' + id).html(number_format(dpp, 2));
		});

		// Subtotal = total_req_payment (sudah × kurs)
		var subtotal = total_req_payment;

		// Update hidden values
		$('.total_payment').val(subtotal);
		$('.total_ppn').val(total_ppn);
		$('.total_pph').val(total_pph);
		$('.total_payment_bank').val(total_req_payment);

		// Update display
		$('.total_pph_col').html(number_format(total_pph, 2));
		$('.total_ppn_col').html(number_format(total_ppn, 2));
		$('.total_payment_col').html(number_format(subtotal, 2));

		// Grand Total Payment = Subtotal + PPN - PPH + Bank Charge
		var grand_total = subtotal + total_ppn - total_pph + bank_charge;
		$('.grand_total_payment_col').html('<strong>' + number_format(grand_total, 2) + '</strong>');

		// Selisih Kurs: bandingkan Nilai Bank IDR dengan nilai berdasarkan kurs_receive_invoice
		var kurs_receive = parseFloat($('.kurs_receive_invoice').val()) || 0;
		var nilai_bank_input = parseFloat($('.input_payment_bank').val().split(',').join('')) || 0;
		var nilai_bank_idr = nilai_bank_input * kurs_val;
		$('.nilai_bank_idr').val(number_format(nilai_bank_idr, 2));

		var selisih_kurs = 0;
		if (kurs_receive > 0 && nilai_bank_input > 0) {
			var nilai_kurs_receive = nilai_bank_input * kurs_receive;
			selisih_kurs = nilai_bank_idr - nilai_kurs_receive;
		}
		$('.selisih_kurs_col').html(number_format(selisih_kurs, 2));

		hitung_kontrol();
	};
	$(document).on('change', '.bank', function() {
		set_jurnal();
	})

	$(document).on('submit', '#frm-data', function(e) {
		e.preventDefault();

		// Re-hitung kontrol sebelum validasi
		hitung_kontrol();

		var kontrol = $('.kontrol').val();
		if (kontrol == '' || kontrol == undefined) {
			kontrol = 0;
		} else {
			kontrol = kontrol.split(',').join('');
			kontrol = parseFloat(kontrol) || 0;
		}

		var mata_uang = $('select[name="mata_uang"]').val();
		var bank = $('select[name="bank"]').val();
		var kurs_payment = $('input[name="kurs_payment"]').val();

		var payment_bank = $('.input_payment_bank').val();
		if (payment_bank !== '') {
			payment_bank = payment_bank.split(',').join('');
			payment_bank = parseFloat(payment_bank) || 0;
		} else {
			payment_bank = 0;
		}

		// Toleransi floating point: kontrol harus mendekati 0 (selisih < 1)
		if (payment_bank <= 0) {
			swal({
				title: 'Warning !',
				text: 'Maaf, Nilai bank harus diisi dan tidak boleh 0!',
				type: 'warning'
			});

			return false;
		}
		if (bank == '') {
			swal({
				title: 'Warning !',
				text: 'Maaf, Bank wajib diisi!',
				type: 'warning'
			});

			return false;
		}

		if (mata_uang == '') {
			swal({
				title: 'Warning !',
				text: 'Maaf, Mata Uang tidak boleh kosong!',
				type: 'warning'
			});

			return false;
		}

		if (kurs_payment == '') {
			swal({
				title: 'Warning !',
				text: 'Maaf, Kurs payment tidak bbisa kosong!',
				type: 'warning'
			});

			return false;
		}

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

					var formData = new FormData($('#frm-data')[0]);

					// Tentukan endpoint berdasarkan tipe payment
					var tipe_payment = '<?= $results['result_header']->tipe ?? '' ?>';
					var baseurl;
					if (tipe_payment === 'invoice_import') {
						baseurl = siteurl + active_controller + 'save_payment_import';
					} else {
						baseurl = siteurl + active_controller + 'save_payment_po';
					}
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
									timer: 5000,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
								window.location.href = base_url + active_controller + 'payment_list';
							} else {

								if (data.status == 2) {
									swal({
										title: "Save Failed!",
										text: data.pesan,
										type: "warning",
										timer: 5000,
										showCancelButton: false,
										showConfirmButton: false,
										allowOutsideClick: false
									});
								} else {
									swal({
										title: "Save Failed!",
										text: data.pesan,
										type: "warning",
										timer: 5000,
										showCancelButton: false,
										showConfirmButton: false,
										allowOutsideClick: false
									});
								}

							}
						},
						error: function() {

							swal({
								title: "Error Message !",
								text: 'An Error Occured During Process. Please try again..',
								type: "warning",
								timer: 5000,
								showCancelButton: false,
								showConfirmButton: false,
								allowOutsideClick: false
							});
						}
					});
				} else {
					swal("Cancelled", "Data can be process again :)", "error");
					return false;
				}
			});
	});
</script>