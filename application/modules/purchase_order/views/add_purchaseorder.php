<?php
$ENABLE_ADD     = has_permission('Purchase_Request.Add');
$ENABLE_MANAGE  = has_permission('Purchase_Request.Manage');
$ENABLE_VIEW    = has_permission('Purchase_Request.View');
$ENABLE_DELETE  = has_permission('Purchase_Request.Delete');

$noPrList = [];
if (!empty($results['headerso'])) {
	foreach ($results['headerso'] as $h) {
		$noPrList[] = $h->no_pr;
	}
}
?>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
<div class="card shadow-sm">

	<div class="card-body">
		<form id="data-form" method="post">
			<input type="hidden" name="so_number" value="<?= implode(',', $results['param']) ?>">
			<input type="hidden" class="count_all_prod" value="<?= count($results['getitemso']) ?>">
			<input type="hidden" name="no_pr" value="<?= implode(',', $noPrList) ?>">
			<input type="hidden" id="h_subtotal" name="subtotal" value="0">
			<input type="hidden" id="h_totalinppn" name="totalinppn" value="0">
			<input type="hidden" id="h_hargatotal" name="hargatotal" value="0">
			<input type="hidden" id="h_diskontotal" name="diskontotal" value="0">
			<input type="hidden" id="h_diskonkhusus" name="diskonkhusus" value="0">
			<input type="hidden" id="h_kirim" name="kirim" value="0">
			<input type="hidden" id="h_ppn" name="ppn" value="0">
			<input type="hidden" id="h_persenppn" name="persenppn" value="0">
			<input type="hidden" id="h_persendisc" name="persendisc" value="0">
			<input type="hidden" id="h_totaldisc" name="totaldisc" value="0">
			<input type="hidden" id="h_expect_tanggal" name="expect_tanggal" value="">
			<input type="hidden" id="h_taxtotal" name="taxtotal" value="0">
			<input type="hidden" id="h_dpp" name="dpp" value="0">
			<div class="col-sm-12">
				<div class="form-group row mb-3">
					<!-- <div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="id_customer">Supplier</label>
									</div>
									<div class="col-md-8">
										<select id="id_suplier" name="id_suplier" class='form-control input-md chosen-select' onchange="get_lokasi()" required>
											<option value="">--Select--</option>
											<?php foreach ($results['supplier'] as $supplier) { ?>
												<option value="<?= $supplier->id_suplier ?>"><?= strtoupper(strtolower($supplier->name_suplier)) ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div> -->
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Local / Import</label>
							</div>
							<div class="col-md-8" id="ubahloi">
								<select id="loi" name="loi" class="form-control select2" onchange="get_kurs()" required>
									<option value="">--Select--</option>
									<option value="Import">Import</option>
									<option value="Lokal">Lokal</option>
								</select>
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Department</label>
							</div>
							<div class="col-md-8" id="ubahloi">
								<select id="select_department" name="dept[]" class="form-control select2" multiple required>
									<?php
									foreach ($results['list_department'] as $item) {
										echo '<option value="' . $item->id . '">' . strtoupper($item->nama) . '</option>';
									}
									?>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group row mb-3">
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="customer">NO.PO</label>
							</div>
							<div class="col-md-8">
								<input type="text" class="form-control" id="no_po" required name="no_po" readonly placeholder="ID PO">
								<input type="hidden" class="form-control" id="no_surat" name="no_surat" readonly placeholder="No.PO">
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="customer">Delivery Date</label>
							</div>
							<div class="col-md-8">
								<input type="text" name="delivery_date" id="delivery_date" required class="form-control">
							</div>
						</div>
					</div>
				</div>
				<div class="form-group row mb-3">
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="customer">PO Date</label>
							</div>
							<div class="col-md-8">
								<input type="text" class="form-control" id="tanggal" value="" onkeyup required name="tanggal">
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Currency</label>
							</div>
							<div class="col-md-8">
								<select id="select_curr" name="matauang" class="form-control select2" required>
									<option value="">- Currency -</option>
									<?php foreach ($results['mata_uang'] as $mata_uang) {
										$selected = '';
									?>
										<option value="<?= $mata_uang->kode ?>" <?= $selected; ?>><?= strtoupper(strtolower($mata_uang->kode)) ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
					</div>
				</div>
				<!-- <div class="form-group row mb-3">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">Expect Date</label>
									</div>
									<div class="col-md-8">
										<input type="date" class="form-control" id="expect_tanggal" required name="expect_tanggal"  >
									</div>
								</div>
							</div>
						</div> -->
				<div class="form-group row mb-3">
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="customer">Payment Term</label>
							</div>
							<div class="col-md-8">
								<select id="term" name="term" class="form-control select2" required>
									<option value="">-- Select --</option>
									<?php foreach ($results['term'] as $term): ?>
										<option value="<?= htmlspecialchars($term->id) ?>">
											<?= htmlspecialchars($term->name) ?>
										</option>
									<?php endforeach; ?>
								</select>
								<!-- <input type="text" class="form-control" id="term" onkeyup required name="term"> -->
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Supplier</label>
							</div>
							<div class="col-md-8">
								<select id="supplier" name="supplier" class="form-control select2" required>
									<option value="">- Supplier -</option>
									<?php foreach ($results['list_supplier'] as $supplier) {
										$selected = '';
									?>
										<option value="<?= $supplier->kode_supplier ?>" data-address="<?= $supplier->address ?>" <?= $selected; ?>><?= strtoupper(strtolower($supplier->nama)) ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group row mb-3" hidden>
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Price Method</label>
							</div>
							<div class="col-md-8">
								<select id="cif" name="cif" class="form-control select2" required>
									<option value="">--Select--</option>
									<option value="CIF">CIF</option>
									<option value="FOB">FOB</option>
									<option value="LOCO">LOCO</option>
									<option value="DDU">DDU</option>
									<option value="FRANCO">FRANCO</option>
								</select>
							</div>
						</div>
					</div>
				</div>

				<div class="form-group row mb-3">
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Remarks</label>
							</div>
							<div class="col-md-8">
								<textarea name="keterangan" id="" class="form-control"></textarea>
							</div>
						</div>
					</div>
					<div class="col-sm-6" hidden>
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Alamat</label>
							</div>
							<div class="col-md-8">
								<textarea name="delivery_address" id="" class="form-control"></textarea>
							</div>
						</div>
					</div>
				</div>


				<div class="table-responsive mb-3">
					<div class="form-check form-switch mb-3">
						<input class="form-check-input text-end form-control" type="checkbox" id="show_tax" name="show_tax" value="Y">
						<label class="form-check-label" for="show_tax"><b>Gunakan Pajak (PPn)</b></label>
					</div>
					<table class='table table-bordered table-striped'>
						<thead>
							<tr class='bg-blue'>
								<th width='5%' class="text-center">
									<input type="checkbox" id="select_all" checked>
								</th>
								<th style="min-width: 200px;">Item</th>
								<th style="min-width: 200px;">Nama Lain</th>
								<th style="min-width: 150px;">HS Code</th>
								<th style="min-width: 150px;">Kuota Internal</th>
								<th style="min-width: 100px;" hidden>Width</th>
								<th style="min-width: 100px;" hidden>Length</th>
								<th style="min-width: 150px;">Qty PR</th>
								<th style="min-width: 150px;">PO Qty</th>
								<th style="min-width: 100px;">Unit Measurement</th>
								<th style="min-width: 75px;">Unit Packing</th>
								<th style="min-width: 100px;" hidden>Rate LME</th>
								<th style="min-width: 100px;" hidden>Alloy Price</th>
								<th style="min-width: 100px;" hidden>Fab Cost</th>
								<th style="min-width: 150px;">Unit Price</th>
								<th style="min-width: 100px;" hidden>Disc %</th>
								<th style="min-width: 100px;" hidden>Shipping Cost</th>
								<th style="min-width: 150px;">Total Price</th>
								<th style="min-width: 150px;">Discount Value</th>
								<!-- <th style="min-width: 100px;">Nilai PPN</th> -->
								<th style="min-width: 150px;">Sub Total</th>
								<th style="min-width: 150px;">Deskripsi Item</th>
								<th style="min-width: 150px;">Sisa Kuota</th>
							</tr>
						</thead>
						<tbody>
							<?php
							// if ($results['getitemso']) {
							$n = 1;
							// print_r($results['getitemso']);
							// exit;
							$key = 0;

							foreach ($results['getitemso'] as $value) {

								$get_trans_po = $this->db->get_where('dt_trans_po', ['idpr' => $value->id])->num_rows();

								$get_qty_all_po = $this->db->query("SELECT IF(SUM(a.qty) IS NOT NULL, SUM(a.qty), 0) AS qty_all_po FROM dt_trans_po a WHERE a.idpr = '" . $value->id . "'")->row();

								// echo '<tr><td>' . $value->nm_material . '</td></tr>';

								$no = $n++;
								$key++;
								// $disabled = ($loi == 'Import') ? '' : 'readonly';
								// $disabled2 = ($loi == 'Import') ? 'readonly' : '';
								// $idmat = $value->idmaterial;
								// $harga 	= $this->db->query("SELECT * FROM ms_product_pricelist WHERE id_category3 = '$idmat'")->row();

								// $stock = $this->db->query("SELECT * FROM stock_material WHERE id_category3 = '$idmat'")->row();

								// $avl 	 =	$stock->qty_free;
								$disabled = '';
								$po    = (!empty($value->qty)) ? $value->qty : 0;

								$harga_beli = (!empty($harga->harga_beli)) ? $harga->harga_beli : 0;

								$total = $harga_beli * $po;


								// if ($value->status_app !== 'Y') {

								// $get_po = $this->db->get_where('dt_trans_po', ['idpr' => $value->id])->row();
								// // if ($get_po->qty == null || $get_po->qty > $value->propose_purchase) {
								// $status = "<div class='badge bg-green'>Done PO</div>";
								// if ($get_po->qty == null || $get_po->qty > $value->propose_purchase) {
								// 	$status = "<div class='badge bg-red'>Outstanding PO</div>";
								// }
								$nm_material1 = (!empty($value->nm_material1)) ? $value->nm_material1 : '';

								$no_pr = (!empty($value->no_pr)) ? $value->no_pr : '';
								$id_material = (!empty($value->id_material)) ? $value->id_material : '';
								$width = (!empty($value->width)) ? $value->width : 0;
								$length = (!empty($value->length)) ? $value->length : 0;
								$total_weight = (!empty($value->totalweight)) ? $value->totalweight : 0;

								echo "
												<tr>
													<td class='text-center'>
														<input type='checkbox' name='dt[" . $key . "][checked_point]' class='checked_point' data-no='" . $key . "' value='" . $key . "'  checked>
													</td>
													
													<td>  
													" . $value->nm_material . $nm_material1 . "
														<input type='hidden' class='form-control input-sm' id='dt_idpr_" . $key . "' name='dt[" . $key . "][idpr]' value='" . $value->id . "'>
														<input type='hidden' id='dt_tipe_pr_" . $key . "' name='dt[" . $key . "][tipe_pr]' value='" . $value->tipe_pr . "'>
														<input type='hidden' class='form-control input-sm' id='dt_no_pr_" . $key . "' name='dt[" . $key . "][no_pr]' value='" . $no_pr . "'>
														<input type='hidden' class='form-control input-sm' id='dt_idmaterial_" . $key . "' name='dt[" . $key . "][idmaterial]' value='" . $id_material . "'>
														<input type='hidden' class='form-control input-sm' id='dt_namamaterial_" . $key . "' name='dt[" . $key . "][namamaterial]' value='" . $value->nm_material . $nm_material1 . "'>
														<input type='hidden' class='form-control input-sm' id='dt_panjang_" . $key . "' name='dt[" . $key . "][panjang]'>
														<input type='hidden' class='form-control input-sm' id='dt_lebar_" . $key . "' name='dt[" . $key . "][lebar]'>
														<input type='hidden' class='form-control input-sm ch_diskon' id='dt_ch_diskon_" . $key . "'>
														<input type='hidden' class='form-control input-sm ch_pajak' id='dt_ch_pajak_" . $key . "'>
														<input type='hidden' class='form-control input-sm ch_jumlah' id='dt_ch_jumlah_" . $key . "'>
														<input type='hidden' class='form-control input-sm ch_ppn' id='dt_ch_ppn_" . $key . "'>
													</td>

													<td>" . $value->nm_lain . "</td>

													<td>
														<input type='hidden' class='form-control input-sm'  id='dt_hscode" . $key . "' name='dt[" . $key . "][hscode]' value='" . $value->hscode . "' readonly>
														<input type='text' class='form-control input-sm'  id='dt_local_code" . $key . "' name='dt[" . $key . "][local_code]' value='" . $value->local_code . "' readonly>
													</td>
													<td><input type='text' class='form-control input-sm autoNumeric3' id='dt_kuotainternal" . $key . "' name='dt[" . $key . "][kuota_internal]' value='" . $value->kuota_internal . "' readonly></td>

													<td hidden>
														<select class='form-control input-sm' id='dt_ppn_" . $key . "' name='dt[" . $key . "][ppn]' onchange='CariPPN(" . $key . ")'>
															<option value=''>SELECT</option>
															<option value='Y'>Y</option>
															<option value='N'>N</option>
														</select>
													</td>
												  
												  	<td hidden><input type='text' class='form-control input-sm' name='dt[" . $key . "][kode_barang]' id='dt_kode_barang_" . $key . "' value='" . $value->code . $value->code1 . "' readonly></td>
												  	<td><input type='text' class='form-control input-sm auto_num text-center' id='dt_pr_" . $key . "' name='dt[" . $key . "][pr]' value='" . ($value->propose_purchase - $get_qty_all_po->qty_all_po)  . "' readonly ></td>
														
													<td hidden><input type='text' class='form-control input-sm' name='dt[" . $key . "][description]' id='dt_description_" . $key . "' value=''></td>
												  	<td hidden><input type='hidden' class='form-control input-sm autoNumeric' name='dt[" . $key . "][width]' id='dt_width_" . $key . "'  value='" . $width . "'></td>
													<td hidden><input type='hidden' class='form-control input-sm autoNumeric' name='dt[" . $key . "][length]' id='dt_length_" . $key . "'  value='" . $length . "'></td>
													
													<td>
														<input type='hidden' class='form-control input-sm autoNumeric' name='dt[" . $key . "][totalweight]' id='dt_totalweight_" . $key . "' value='" . $total_weight . "'  onkeyup='HitAmmount(" . $key . ")'>
														<input type='text' class='form-control input-sm auto_num text-center' id='dt_qty_" . $key . "' name='dt[" . $key . "][qty]' value='" . ($value->propose_purchase - $get_qty_all_po->qty_all_po) . "' onkeyup='HitAmmount(" . $key . ")'>
													</td>
													<td class='text-center'>" . ucfirst($value->unit_measure) . "</td>
													<td class='text-center'>" . ucfirst($value->packing_unit) . ucfirst($value->packing_unit2) . "</td>
													
													<td hidden>
														<select class='form-control input-sm' id='dt_ratelme_" . $key . "' name='dt[" . $key . "][ratelme]' onchange='CariPrice(" . $key . ")'>
															<option value=''>-Select-</option>
															<option value='Hari Ini'>Today</option>
															<option value='H-10'>H-10</option>
															<option value='H-30'>H-30</option>
														</select>
													</td>
													<td hidden><input type='text' class='form-control input-sm autoNumeric3' id='dt_alloyprice_" . $key . "' " . $disabled . " data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' name='dt[" . $key . "][alloyprice]' onkeyup='HitAmmount(" . $key . ")'></td>
													<td hidden><input type='text' class='form-control input-sm autoNumeric3' id='dt_fabcost_" . $key . "' " . $disabled . " name='dt[" . $key . "][fabcost]' onkeyup='HitAmmount(" . $key . ")'></td>
													
													<td><input type='text' class='form-control input-sm text-end auto_num_3dec' id='dt_hargasatuan_" . $key . "' name='dt[" . $key . "][hargasatuan]' onkeyup='HitAmmount(" . $key . ")' value=''></td>
													
													<td hidden><input type='text' class='form-control input-sm autoNumeric pajak' id='dt_pajak_" . $key . "' name='dt[" . $key . "][pajak]' onkeyup='HitAmmount(" . $key . ")'></td>
													<td hidden><input type='text' class='form-control input-sm autoNumeric3' id='dt_diskon_" . $key . "' " . $disabled . " name='dt[" . $key . "][diskon]' onkeyup='HitAmmount(" . $key . ")'></td>
												
												  	<td><input type='text' class='form-control input-sm text-end ch_jumlah_ex' id='dt_jumlahharga_" . $key . "' readonly name='dt[" . $key . "][jumlahharga]' value='" . number_format($total, 4, '.', ',') . "'></td>
													<td>
														<div class='input-group input-group-sm mb-2'>
															<input type='text' name='dt[" . $key . "][disc_persen]' class='form-control input-sm auto_num disc_persen'
																id='disc_persen_" . $key . "' data-key='" . $key . "' placeholder='Persen Disc (%)'>
															<span class='input-group-text'>%</span>
														</div>
														<div class='input-group input-group-sm'>
															<input type='text' name='dt[" . $key . "][disc_num]' class='form-control input-sm auto_num disc_num'
																id='disc_num_" . $key . "' data-key='" . $key . "' placeholder='Nilai Disc (Rp)'>
															<span class='input-group-text'>Rp</span>
														</div>
													</td>

													<td hidden>
														<input type='text' class='form-control auto_num input-sm ch_ppn cng_nilai_ppn' id='dt_nilai_ppn_" . $key . "' name='dt[" . $key . "][nilai_ppn]' data-key='" . $key . "' placeholder='Nilai PPN' readonly>
														<input type='text' class='form-control input-sm ch_per_ppn cng_persen_ppn' id='dt_persen_ppn_" . $key . "' name='dt[" . $key . "][persen_ppn]' data-key='" . $key . "' placeholder='Persen PPN' readonly>
													</td>
													
													<td><input type='text' class='form-control input-sm text-end ch_jumlah_ex2' id='dt_totalharga_" . $key . "' readonly name='dt[" . $key . "][totalharga]' value='" . number_format($total, 4, '.', ',') . "'></td>
													<td><input type='text' class='form-control input-sm' id='dt_note_" . $key . "' name='dt[" . $key . "][note]'></td>
													<td><input type='text' class='form-control input-sm text-end sisa_kuota autoNumeric3' id='dt_sisa_kuota_" . $key . "' name='dt[" . $key . "][sisa_kuota]'></td>
											 	</tr>
													";
							}
							?>
						</tbody>
						<tfoot>
							<tr>
								<td class="text-end" colspan="12"><b>Total</b></th>
								<td colspan="2">
									<input readonly type="text" class="form-control text-end auto_num_4dec" id="totalinppn" onkeyup required name="totalinppn">
								</td>
							</tr>
							<tr hidden>
								<td class="text-end" colspan="12"><b>Diskon Khusus</b></th>
								<td colspan="2">
									<input type="text" class="form-control text-end auto_num" id="diskonkhusus" onblur="cariTotal()" name="diskonkhusus">
								</td>
							</tr>
							<tr class="row-pajak" style="display: none;" hidden>
								<td class="text-end" colspan="12"><b>Total (Exclude PPn)</b></td>
								<td colspan="2">
									<input readonly type="text" class="form-control text-end autoNumeric" id="totalexppn" name="totalexppn">
								</td>
							</tr>
							<tr class="row-pajak" style="display: none;">
								<td class="text-end" colspan="12"><b>DPP</b></td>
								<td colspan="2">
									<input readonly type="text" class="form-control text-end autoNumeric" id="dpp" name="dpp">
								</td>
							</tr>
							<tr class="row-pajak" style="display: none;">
								<td class="text-end" colspan="12"><b>PPn</b></td>
								<td colspan="2">
									<input readonly type="text" class="form-control text-end autoNumeric" id="ppn" name="ppn">
								</td>
							</tr>
							<!-- <tr>
										<td class="text-end" colspan="12"><b>Keterangan</b></td>
										<td colspan="2">
											<textarea name="note" id="" class="form-control"></textarea>
										</td>
									</tr> -->
							<tr hidden>
								<td class="text-end" colspan="12"><b>Shipping Cost</b></td>
								<td colspan="2">
									<input type="hidden" class="form-control" id="taxtotal" onkeyup required name="taxtotal">
									<input type="text" class="form-control auto_num text-end" id="kirim" onblur="cariTotal()" required name="kirim">
								</td>
							</tr>
							<tr>
								<td class="text-end" colspan="12"><b>Total Order</b></td>
								<td colspan="2">
									<input readonly type="text" class="form-control text-end auto_num_4dec" id="subtotal" onkeyup required name="subtotal">
								</td>
							</tr>
						</tfoot>
					</table>
				</div>


				<div class="form-group row mb-3" hidden>
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="customer">Expect Date</label>
							</div>
							<div class="col-md-8">
								<input type="text" class="form-control" id="expect_tanggal" required name="expect_tanggal" readonly>
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-2">
								<label for="customer">Note</label>
							</div>
							<div class="col-md-10">
								<input type="text" class="form-control" id="note_ket" name="note_ket">
							</div>
						</div>
					</div>
				</div>
				<div class="form-group row mb-3" hidden>
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="id_customer">Sub Total Unit Price</label>
								</div>
								<div class="col-md-8" id="ForHarga">
									<input readonly type="text" class="form-control" id="hargatotal" onkeyup required name="hargatotal">
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="id_customer">Keterangan</label>
								</div>
								<div class="col-md-8" id="ForHarga">
									<textarea name="note" id="" class="form-control" disabled></textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group row mb-3" hidden>
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="persendisc" class="control-label">Total Discount</label>
								</div>

								<div class="col-md-8" id="ForHarga">
									<!-- Persentase -->
									<div class="input-group" style="margin-bottom:6px;">
										<input type="text" class="form-control auto_num" id="persendisc" name="persendisc" placeholder="Persen Disc (%)" required onkeyup="cariTotal()" onblur="cariTotal()">
										<span class="input-group-addon">%</span>
									</div>

									<!-- Nominal -->
									<div class="input-group">
										<span class="input-group-addon">Rp</span> <!-- atau $ -->
										<input type="text" class="form-control auto_num text-right" id="totaldisc" name="totaldisc" placeholder="Nilai Disc" required onkeyup="cariTotal()" onblur="cariTotal()">
									</div>
								</div>
							</div>

						</div>
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="id_customer">Shipping Cost</label>
								</div>
								<div class="col-md-8" id="ForTax">
									<input type="hidden" class="form-control" id="taxtotal" onkeyup required name="taxtotal">
									<input type="text" class="form-control auto_num" id="kirim" onblur="cariTotal()" required disabled>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group row mb-3" hidden>
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="persenppn" class="control-label">Total PPN</label>
								</div>

								<div class="col-md-8" id="ForHarga">
									<!-- Persentase PPN -->
									<div class="input-group" style="margin-bottom:6px;">
										<input type="text"
											class="form-control auto_num" id="persenppn" name="persenppn" placeholder="Persen PPN (%)" required onkeyup="cariTotal()" onblur="cariTotal()">
										<span class="input-group-addon">%</span>
									</div>

									<!-- Nilai PPN -->
									<div class="input-group">
										<span class="input-group-addon">Rp</span> <!-- atau $ -->
										<input type="text"
											class="form-control auto_num" id="totalppn" name="totalppn" placeholder="Nilai PPN" required onkeyup="cariTotal()" onblur="cariTotal()">
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="id_customer">Total Order</label>
								</div>
								<div class="col-md-8" id="ForSum">
									<input readonly type="text" class="form-control" id="subtotal" disabled>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group row mb-3" hidden>
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="id_customer">Discount</label>
								</div>
								<div class="col-md-8" id="ForDiskon">
									<input readonly type="text" class="form-control" id="diskontotal" onkeyup required name="diskontotal">
								</div>
							</div>
						</div>
					</div>
				</div>
				<hr>
				<div class="form-group row mb-3">
					<div class="col-sm-12">
						<input type="hidden" name="num_top" class="num_top">
						<button type="button" class="btn btn-sm btn-primary add_top mb-3">
							<i class="fa fa-plus"></i> Add TOP
						</button>
						<table class="table table-bordered">
							<thead class="bg-blue">
								<tr>
									<th class="text-center">Group TOP</th>
									<th class="text-center">Progress (%)</th>
									<th class="text-center">Value</th>
									<th class="text-center">Remarks</th>
									<th class="text-center">Payment Type</th>
									<th class="text-center">Due Date</th>
									<th class="text-center">Action</th>
								</tr>
							</thead>
							<tbody class="list_tbody_top">

							</tbody>
						</table>
					</div>
				</div>

				<div class="text-center">
					<button type="submit" class="btn btn-success" name="save" id="simpan-com"><i class="fa fa-save"></i> Save</button>
				</div>

			</div>
		</form>
	</div>
</div>

<div class="modal fade" id="modal_lc" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header bg-info">
				<h5 class="modal-title text-white">Detail Letter of Credit (LC)</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="modal_row_index_hidden">
				<input type="hidden" id="modal_id_supplier_hidden">

				<div class="row">
					<div class="col-md-6">
						<div class="form-group mb-2">
							<label>Supplier</label>
							<input type="text" id="modal_supplier_name" class="form-control" readonly>
						</div>
						<div class="form-group mb-2">
							<label>No. Credit</label>
							<input type="text" id="no_credit" class="form-control" placeholder="Input No. Credit...">
						</div>
						<div class="form-group mb-2">
							<label>Issue Date</label>
							<input type="date" id="issue_date" class="form-control">
						</div>
						<div class="form-group mb-2">
							<label>Expiry Date</label>
							<input type="date" id="expiry_date" class="form-control">
						</div>
						<div class="form-group mb-2">
							<label>Supplier Address</label>
							<textarea id="modal_supplier_address" class="form-control" rows="3" readonly></textarea>
						</div>
					</div>

					<div class="col-md-6">
						<div class="form-group mb-2">
							<label>Value Contract</label>
							<input type="number" id="value_contract" class="form-control" step="0.01">
						</div>
						<div class="form-group row mb-2">
							<div class="col-6">
								<label>Tolerance + (%)</label>
								<input type="number" id="tolerance_plus" class="form-control">
							</div>
							<div class="col-6">
								<label>Tolerance - (%)</label>
								<input type="number" id="tolerance_minus" class="form-control">
							</div>
						</div>
						<div class="form-group row mb-2">
							<div class="col-6">
								<label>Type Of LC</label>
								<select id="type_of_lc" class="form-control">
									<option value="At Sight">At Sight</option>
									<option value="Usen">Usen</option>
								</select>
							</div>
							<div class="col-6">
								<div id="group_usen_until" style="display:none;">
									<label class="text-danger">Valid Usen Until</label>
									<input type="date" id="valid_usen_until" class="form-control">
								</div>
							</div>
						</div>

						<div class="form-group mb-2">
							<label>Bank Sender / Receiver</label>
							<div class="row">
								<div class="col-6"><input type="text" id="bank_sender" class="form-control" placeholder="Sender"></div>
								<div class="col-6"><input type="text" id="bank_receiver" class="form-control" placeholder="Receiver"></div>
							</div>
						</div>
						<div class="form-group mb-2">
							<label>Latest Date Of Shipment</label>
							<input type="date" id="latest_shipment" class="form-control">
						</div>
						<div class="form-group mb-2">
							<label>No. Sales Contract</label>
							<input type="text" id="no_sales_contract" class="form-control">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Close</button>
				<button type="button" id="btn_save_lc" class="btn btn-md btn-primary"><i class="fas fa-save"></i> Save Detail LC</button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url = '<?php echo base_url(); ?>';
	var active_controller = '<?php echo ($this->uri->segment(1)); ?>';
	var num_top = getNum($('.num_top').val());

	function syncHiddenFields(formData) {
		// Update hidden fields dari visible input
		$('#h_subtotal').val($('#subtotal').val() || '0');
		$('#h_totalinppn').val($('#totalinppn').val() || '0');
		$('#h_hargatotal').val($('#hargatotal').val() || '0');
		$('#h_diskontotal').val($('#diskontotal').val() || '0');
		$('#h_diskonkhusus').val($('#diskonkhusus').val() || '0');
		$('#h_kirim').val($('#kirim').val() || '0');
		$('#h_ppn').val($('#ppn').val() || '0');
		$('#h_persenppn').val($('#persenppn').val() || '0');
		$('#h_persendisc').val($('#persendisc').val() || '0');
		$('#h_totaldisc').val($('#totaldisc').val() || '0');

		// expect_tanggal — set default hari ini jika kosong
		var exp = $('#expect_tanggal').val();
		if (!exp) exp = new Date().toISOString().slice(0, 10);
		$('#h_expect_tanggal').val(exp);
	}

	$(document).ready(function() {
		TotalSemua()
		checkShowTax()

		if ($('#expect_tanggal').val() === '') {
			var today = new Date().toISOString().slice(0, 10);
			$('#expect_tanggal').val(today);
			$('#h_expect_tanggal').val(today);
		}

		$('input[id^="dt_qty_"]').each(function() {
			let id = $(this).attr('id').replace('dt_qty_', '');
			HitAmmount(id);
		});

		$('.select2').select2({
			width: '100%'
		});

		$('.auto_num').autoNumeric('init');
		$('.auto_num_3dec').autoNumeric('init', {
			mDec: 3,
			vMin: 0
		});
		$('.auto_num_4dec').autoNumeric('init', {
			mDec: 4,
			vMin: 0
		});
		$('[id^="dt_hargasatuan_"]').autoNumeric('init', {
			mDec: 3,
			vMin: 0
		});

		var max_fields2 = 10; //maximum input boxes allowed
		var wrapper2 = $(".input_fields_wrap2"); //Fields wrapper
		var add_button2 = $(".add_field_button2"); //Add button ID	


		$(document).on('click', '.checked_point', function() {
			var no = $(this).data('no');

			if ($(this).prop('checked') === true) {
				$('input[name="dt[' + no + '][description]"]').prop('readonly', false);
				$('input[name="dt[' + no + '][description]"]').val('');

				$('input[name="dt[' + no + '][qty]"]').prop('readonly', false);
				$('input[name="dt[' + no + '][qty]"]').val('');

				$('input[name="dt[' + no + '][hargasatuan]"]').prop('readonly', false);
				$('input[name="dt[' + no + '][hargasatuan]"]').val('');

				$('input[name="dt[' + no + '][ppn]"]').prop('disabled', false);
				$('input[name="dt[' + no + '][ppn]"]').val('');

				$('input[name="dt[' + no + '][jumlahharga]"]').val('0');

				$('input[name="dt[' + no + '][disc_persen]"]').prop('readonly', false);
				$('input[name="dt[' + no + '][disc_persen]"]').val('0');

				$('input[name="dt[' + no + '][disc_num]"]').prop('readonly', false);
				$('input[name="dt[' + no + '][disc_num]"]').val('0');

				$('input[name="dt[' + no + '][nilai_ppn]"]').val('0');

				$('input[name="dt[' + no + '][persen_ppn]"]').val('0');

				$('input[name="dt[' + no + '][totalharga]"]').val('0');

				$('input[name="dt[' + no + '][note]"]').prop('readonly', false);
				$('input[name="dt[' + no + '][note]"]').val('');
			} else {
				$('input[name="dt[' + no + '][description]"]').prop('readonly', true);
				$('input[name="dt[' + no + '][description]"]').val('');

				$('input[name="dt[' + no + '][qty]"]').prop('readonly', true);
				$('input[name="dt[' + no + '][qty]"]').val('');

				$('input[name="dt[' + no + '][hargasatuan]"]').prop('readonly', true);
				$('input[name="dt[' + no + '][hargasatuan]"]').val('');

				$('input[name="dt[' + no + '][ppn]"]').prop('disabled', true);
				$('input[name="dt[' + no + '][ppn]"]').val('');

				$('input[name="dt[' + no + '][jumlahharga]"]').val('0');

				$('input[name="dt[' + no + '][disc_persen]"]').prop('readonly', true);
				$('input[name="dt[' + no + '][disc_persen]"]').val('0');

				$('input[name="dt[' + no + '][disc_num]"]').prop('readonly', true);
				$('input[name="dt[' + no + '][disc_num]"]').val('0');

				$('input[name="dt[' + no + '][nilai_ppn]"]').val('0');

				$('input[name="dt[' + no + '][persen_ppn]"]').val('0');

				$('input[name="dt[' + no + '][totalharga]"]').val('0');

				$('input[name="dt[' + no + '][note]"]').prop('readonly', true);
				$('input[name="dt[' + no + '][note]"]').val('');
			}

			TotalSemua()
		});

		// $('#select_all').change(function() {
		// 	var count_all_prod = $('.count_all_prod').val();
		// 	// If the checkbox being changed is checked
		// 	if ($(this).prop('checked')) {
		// 		// Set all checkboxes to checked
		// 		$('input[type="checkbox"]').prop('checked', true);
		// 		for (var i = 1; i <= count_all_prod; i++) {
		// 			$('input[name="dt[' + i + '][description]"]').prop('readonly', false);
		// 			$('input[name="dt[' + i + '][description]"]').val('');

		// 			$('input[name="dt[' + i + '][qty]"]').prop('readonly', false);
		// 			$('input[name="dt[' + i + '][qty]"]').val('');

		// 			$('input[name="dt[' + i + '][hargasatuan]"]').prop('readonly', false);
		// 			$('input[name="dt[' + i + '][hargasatuan]"]').val('');

		// 			$('input[name="dt[' + i + '][ppn]"]').prop('disabled', false);
		// 			$('input[name="dt[' + i + '][ppn]"]').val('');

		// 			$('input[name="dt[' + i + '][jumlahharga]"]').val('0');

		// 			$('input[name="dt[' + i + '][disc_persen]"]').prop('readonly', false);
		// 			$('input[name="dt[' + i + '][disc_persen]"]').val('0');

		// 			$('input[name="dt[' + i + '][disc_num]"]').prop('readonly', false);
		// 			$('input[name="dt[' + i + '][disc_num]"]').val('0');

		// 			$('input[name="dt[' + i + '][nilai_ppn]"]').val('0');

		// 			$('input[name="dt[' + i + '][persen_ppn]"]').val('0');

		// 			$('input[name="dt[' + i + '][totalharga]"]').val('0');

		// 			$('input[name="dt[' + i + '][note]"]').prop('readonly', false);
		// 			$('input[name="dt[' + i + '][note]"]').val('');
		// 		}
		// 	} else {
		// 		// If the checkbox being changed is unchecked, uncheck all checkboxes
		// 		$('input[type="checkbox"]').prop('checked', false);
		// 		for (var i = 1; i <= count_all_prod; i++) {
		// 			$('input[name="dt[' + i + '][description]"]').prop('readonly', true);
		// 			$('input[name="dt[' + i + '][description]"]').val('');

		// 			$('input[name="dt[' + i + '][qty]"]').prop('readonly', true);
		// 			$('input[name="dt[' + i + '][qty]"]').val('');

		// 			$('input[name="dt[' + i + '][hargasatuan]"]').prop('readonly', true);
		// 			$('input[name="dt[' + i + '][hargasatuan]"]').val('');

		// 			$('input[name="dt[' + i + '][ppn]"]').prop('disabled', true);
		// 			$('input[name="dt[' + i + '][ppn]"]').val('');

		// 			$('input[name="dt[' + i + '][jumlahharga]"]').val('0');

		// 			$('input[name="dt[' + i + '][disc_persen]"]').prop('readonly', true);
		// 			$('input[name="dt[' + i + '][disc_persen]"]').val('0');

		// 			$('input[name="dt[' + i + '][disc_num]"]').prop('readonly', true);
		// 			$('input[name="dt[' + i + '][disc_num]"]').val('0');

		// 			$('input[name="dt[' + i + '][nilai_ppn]"]').val('0');

		// 			$('input[name="dt[' + i + '][persen_ppn]"]').val('0');

		// 			$('input[name="dt[' + i + '][totalharga]"]').val('0');

		// 			$('input[name="dt[' + i + '][note]"]').prop('readonly', true);
		// 			$('input[name="dt[' + i + '][note]"]').val('');
		// 		}
		// 	}
		// });

		// Ganti fungsi di dalam $('#select_all').change(...)
		$('#select_all').change(function() {
			var isChecked = $(this).prop('checked');

			// Set semua checkbox item
			$('.checked_point').prop('checked', isChecked);

			if (isChecked) {
				// ✅ Loop cukup SEKALI per row, update semua field sekaligus
				$('.checked_point').each(function() {
					var no = $(this).data('no');
					var $row = $(this).closest('tr');

					// Enable semua input dalam row ini sekaligus
					$row.find('input[name*="[qty]"], input[name*="[hargasatuan]"], input[name*="[description]"], input[name*="[note]"], input[name*="[disc_persen]"], input[name*="[disc_num]"]')
						.prop('readonly', false).val('');

					$row.find('input[name*="[ppn]"]').prop('disabled', false).val('');

					// Reset nilai kalkulasi
					$row.find('input[name*="[jumlahharga]"], input[name*="[nilai_ppn]"], input[name*="[persen_ppn]"], input[name*="[totalharga]"]').val('0');
					$row.find('input[name*="[disc_persen]"], input[name*="[disc_num]"]').val('0');
				});
			} else {
				$('.checked_point').each(function() {
					var $row = $(this).closest('tr');

					$row.find('input[name*="[qty]"], input[name*="[hargasatuan]"], input[name*="[description]"], input[name*="[note]"], input[name*="[disc_persen]"], input[name*="[disc_num]"]')
						.prop('readonly', true).val('');

					$row.find('input[name*="[ppn]"]').prop('disabled', true).val('');

					$row.find('input[name*="[jumlahharga]"], input[name*="[nilai_ppn]"], input[name*="[persen_ppn]"], input[name*="[totalharga]"]').val('0');
				});
			}

			// ✅ Panggil TotalSemua SEKALI saja di akhir, bukan per-item
			TotalSemua();
		});

		$(document).on('change', '#id_suplier', function() {
			let id_suplier = $('#id_suplier').val();
			$.ajax({
				type: "POST",
				url: siteurl + 'purchase_order/getPR',
				data: {
					'id_suplier': id_suplier
				},
				cache: false,
				dataType: 'json',
				success: function(data) {
					$('#no_pr').html(data.option).trigger("chosen:updated");
				}
			});
		});

		$(document).on('change', '#no_pr', function() {
			let loi = $('#loi').val();
			let no_pr = $(this).val();
			$.ajax({
				type: "POST",
				url: siteurl + 'purchase_order/AddMaterial_Direct',
				data: {
					'loi': loi,
					'no_pr': no_pr
				},
				cache: false,
				dataType: 'json',
				success: function(data) {
					$('#data_request').html(data.list_mat);
					$(".bilangan-desimal").maskMoney();
					$('.autoNumeric3').autoNumeric('init', {
						vMin: 0
					});
					$('.autoNumeric').autoNumeric();
					$('#expect_tanggal').val(data.min_date);
				}
			});
		});

		$(document).on('click', '.hapus_baris', function() {
			$(this).parent().parent().remove();
			SumDel();
		});

		$('#simpan-com').click(function(e) {
			e.preventDefault();
			var deskripsi = $('#deskripsi').val();
			var tanggal = $('#tanggal').val();
			var loi = $('#loi').val();
			var term = $('#term').val();
			var cif = $('#cif').val();
			var pajak = $('.pajak').val();
			var supplier = $('#supplier').val();
			var department = $('#select_department').val();
			var delivery_date = $('#delivery_date').val()
			var currency = $('#select_curr').val()

			var ttl_persen_top = 0;
			var detail_lc_all = [];
			$('.input_progress').each(function() {
				var progress = $(this).val();
				if (progress !== '') {
					progress = progress.split(',').join('');
					progress = parseFloat(progress);

					ttl_persen_top += progress;
				}
			});

			var data, xhr;
			if (loi == '' || loi == null) {
				swal("Warning", "Form cannot be empty", "error");
				return false;
			} else if (tanggal == '' || tanggal == null) {
				swal("Warning", "Date cannot be empty", "error");
				return false;
			} else if (supplier == '' || supplier == null) {
				swal("Warning", "Supplier is required", "error");
				return false;
			} else if (select_department == '' || select_department == null) {
				swal("Warning", "Department is required", "error");
				return false;
			} else if (delivery_date == '' || delivery_date == null) {
				swal("Warning", "Delivery Date is required", "error");
				return false;
			} else if (currency == '' || currency == null) {
				swal("Warning", "Currency is required", "error");
				return false;
			} else if (ttl_persen_top > 100) {
				swal("Warning", "Total TOP tidak boleh lebih dari 100%", "error");
				return false;
			} else {
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
							// syncHiddenFields();
							var formData = new FormData($('#data-form')[0]);

							syncHiddenFields(formData);

							console.log('subtotal dikirim:', formData.get('subtotal'));

							$('.list_tbody_top tr').each(function(index) {
								var row = $(this);
								var lcData = row.attr('data-lc_detail');
								if (lcData) {
									formData.append('detail_lc[' + (index + 1) + ']', lcData);
								}
							});

							var baseurl = siteurl + 'purchase_order/SaveAll';
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
										window.location.href = base_url + active_controller;
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
			}
		});

		$(document).on('click', '#btn_save_lc', function() {
			let rowIndex = $('#modal_row_index_hidden').val();

			let targetRow = $('.list_tbody_top tr').eq(rowIndex);

			let dataLC = {
				no_credit: $('#no_credit').val(),
				issue_date: $('#issue_date').val(),
				expiry_date: $('#expiry_date').val(),
				value_contract: $('#value_contract').val(),
				type_of_lc: $('#type_of_lc').val(),
				valid_usen_until: $('#valid_usen_until').val(),
				bank_sender: $('#bank_sender').val(),
				bank_receiver: $('#bank_receiver').val(),
				latest_shipment: $('#latest_shipment').val(),
				no_sales_contract: $('#no_sales_contract').val()
			};

			targetRow.attr('data-lc_detail', JSON.stringify(dataLC));

			targetRow.css('background-color', '#e8f5e9');
			targetRow.find('label[for="lc"]').html('LC <i class="fa fa-check-circle text-success"></i>');

			swal("Success", "LC data has been saved in this row.", "success");
			$('#modal_lc').modal('hide');
		});

		$('.list_tbody_top tr').each(function(i) {
			console.log("Baris " + i + " data-lc: ", $(this).attr('data-lc_detail'));
		});

		$(document).on('change', '.cng_nilai_ppn', function() {
			var key = $(this).data('key');
			var nilai = $("#dt_nilai_ppn_" + key).val();
			var nilai = nilai.split(',').join('');
			var nilai = parseFloat(nilai);
			var nilai = nilai.toFixed(2);

			var hargasatuan = $("#dt_hargasatuan_" + key).val();
			var hargasatuan = hargasatuan.split(',').join('');
			var hargasatuan = parseFloat(hargasatuan);

			var qty = $("#dt_qty_" + key).val();
			var qty = qty.split(',').join('');
			var qty = parseFloat(qty);

			if (qty <= 0 || hargasatuan <= 0) {
				var nilai_persen = 0;
			} else {
				var nilai_persen = parseFloat(nilai / (hargasatuan * qty) * 100);
			}

			$("#dt_persen_ppn_" + key).val(nilai_persen);

			HitAmmount(key);
		});

		$(document).on('change', '.cng_persen_ppn', function() {
			var key = $(this).data('key');

			var persen = $("#dt_persen_ppn_" + key).val();
			persen = persen.split(',').join('');
			persen = parseFloat(persen);

			var hargasatuan = $("#dt_hargasatuan_" + key).val();
			hargasatuan = hargasatuan.split(',').join('');
			hargasatuan = parseFloat(hargasatuan);

			var qty = $("#dt_qty_" + key).val();
			qty = qty.split(',').join('');
			qty = parseFloat(qty);

			var nilai_ppn = parseFloat((hargasatuan * qty) * persen / 100);

			$("#dt_nilai_ppn_" + key).autoNumeric('set', nilai_ppn.toFixed(2));


			HitAmmount(key);
		});

		$(document).on('change', '.disc_persen', function() {
			var key = $(this).data('key');
			var disc_persen = getNum($(this).val().split(',').join(''));
			var hargasatuan = getNum($('#dt_hargasatuan_' + key).val().split(',').join(''));
			var qty = getNum($('#dt_qty_' + key).val().split(',').join(''));

			var disc_num = ((hargasatuan * qty) * disc_persen / 100);
			$('#disc_num_' + key).val(number_format(disc_num, 2));

			HitAmmount(key);
		});

		$(document).on('change', '.disc_num', function() {
			var key = $(this).data('key');
			var disc_num = getNum($(this).val().split(',').join(''));
			var hargasatuan = getNum($('#dt_hargasatuan_' + key).val().split(',').join(''));
			var qty = getNum($('#dt_qty_' + key).val().split(',').join(''));

			var disc_persen = parseFloat(disc_num / (hargasatuan * qty) * 100);
			$('#disc_persen_' + key).val(disc_persen);

			HitAmmount(key);
		});

		$(document).on('keyup', '#persendisc', function() {
			var total = getNum($("#hargatotal").val().split(",").join(""));
			var persen_disc = getNum($(this).val().split(",").join(""));

			var disc = (total * persen_disc / 100);

			$("#totaldisc").val(number_format(disc, 2));
			cariTotal();
		});

		$(document).on('keyup', '#diskonkhusus', function() {
			var total = getNum($("#totalinppn").val().split(",").join(""));
			var nilai = getNum($(this).val().split(",").join(""));
			var selisih = total - nilai

			var exppn = selisih / 1.11
			var dpp = (exppn) * 11 / 12
			var ppn = 12 / 100 * (dpp)
			var subtotal = exppn + ppn

			$("#totalexppn").val(number_format(exppn, 2));
			$("#ppn").val(number_format(ppn, 2));
			$("#subtotal").val(number_format(subtotal, 4));

			cariTotal()
		});

		$(document).on('keyup', '#totaldisc', function() {
			var total = getNum($("#hargatotal").val().split(",").join(""));
			var disc = getNum($("#totaldisc").val().split(",").join(""));

			var persen_disc = (disc / total * 100);
			$("#persendisc").val(number_format(persen_disc, 2));

			cariTotal();
		});

		$(document).on('click', '.add_top', function() {
			$.ajax({
				type: "POST",
				url: siteurl + active_controller + '/add_top_po',
				cache: false,
				dataType: 'JSON',
				success: function(result) {
					num_top++;
					var Rows = '<tr class="top_' + num_top + '">';

					Rows += '<td class="">';
					Rows += '<select class="form-control" name="group_top_' + num_top + '">';
					Rows += result.list_top_group;
					Rows += '</select>';
					Rows += '</td>';

					Rows += '<td class="">';
					Rows += '<input type="text" class="form-control form-control-sm input_progress progress_' + num_top + ' auto_num" name="progress_' + num_top + '" data-no="' + num_top + '">';
					Rows += '</select>';
					Rows += '</td>';

					Rows += '<td class="text-right">';
					Rows += '<input type="text" class="form-control form-control-sm nilai_top nilai_top_' + num_top + ' auto_num_4dec" name="nilai_top_' + num_top + '" data-no="' + num_top + '">';
					Rows += '</td>';

					Rows += '<td class="">';
					Rows += '<textarea name="keterangan_top_' + num_top + '" class="form-control form-control-sm"></textarea>';
					Rows += '</td>';

					Rows += '<td class="">';
					Rows += '<div class="form-check">';
					Rows += '<input class="form-check-input check_bayar" type="radio" id="lc_' + num_top + '" name="tipe_bayar_' + num_top + '" value="lc" required>';
					Rows += '<label class="form-check-label" for="lc_' + num_top + '">LC</label>';
					Rows += '</div>';
					Rows += '<div class="form-check">';
					Rows += '<input class="form-check-input check_bayar" type="radio" id="tt_' + num_top + '" name="tipe_bayar_' + num_top + '" value="tt" required>';
					Rows += '<label class="form-check-label" for="tt_' + num_top + '">TT</label>';
					Rows += '</div>';
					Rows += '</td>';

					Rows += '<td class="">';
					Rows += '<input type="date" class="form-control form-control-sm" name="jatuh_tempo_' + num_top + '">';
					Rows += '</td>';

					Rows += '<td class="text-center">';
					Rows += '<button type="button" class="btn btn-sm btn-danger del_top" data-top_no="' + num_top + '"><i class="fa fa-trash"></i></button>';
					Rows += '</td>';

					Rows += '</tr>';


					$('.num_top').val(num_top);
					$('.list_tbody_top').append(Rows);
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

		$(document).on('change', '.check_bayar', function() {
			let tipe = $(this).val();

			if (tipe === 'lc') {
				let idSupplier = $('#supplier').val();
				let namaSupplier = $('#supplier option:selected').text();
				let alamatSupplier = $('#supplier option:selected').data('address');

				if (idSupplier === "") {
					swal("Attention", "Please select a Supplier in the header form first!", "warning");
					$(this).prop('checked', false);
					return false;
				}

				$('#modal_supplier_name').val(namaSupplier);
				$('#modal_id_supplier_hidden').val(idSupplier);
				$('#modal_supplier_address').val(alamatSupplier);

				let rowIndex = $(this).closest('tr').index();
				$('#modal_row_index_hidden').val(rowIndex);
				$('#modal_lc').modal('show');
			}
		});

		$(document).on('change', '#type_of_lc', function() {
			if ($(this).val() === 'Usen') {
				$('#group_usen_until').show();
			} else {
				$('#group_usen_until').hide();
				$('#valid_usen_until').val(''); // reset jika pilih At Sight
			}
		});

		$(document).on('change', '.input_progress', function() {
			var no = $(this).data('no');
			// Group TOP dihitung dari TOTAL (jumlah item, sebelum PPN & ongkir), bukan Total Order
			var total = getNum($('#totalinppn').val().split(',').join(''));

			var progress = $(this).val();
			if (progress == '' || progress == null) {
				progress = 0;
			} else {
				progress = progress.split(',').join('');
				progress = parseFloat(progress);
			}

			// Validasi: progress per baris & total akumulasi tidak boleh > 100%
			progress = validasiProgressTop(no, progress);

			var nilai_top = (total * progress / 100);

			$(this).val(number_format(progress, 2));
			$('.nilai_top_' + no).val(number_format(nilai_top, 4));
		});

		$(document).on('change', '.nilai_top', function() {
			var no = $(this).data('no');

			// Group TOP dihitung dari TOTAL (jumlah item, sebelum PPN & ongkir), bukan Total Order
			var total = getNum($('#totalinppn').val().split(',').join(''));

			var nilai_top = $(this).val();
			if (nilai_top == '' || nilai_top == null) {
				nilai_top = 0;
			} else {
				nilai_top = nilai_top.split(',').join('');
				nilai_top = parseFloat(nilai_top);
			}

			var progress = (total > 0) ? (nilai_top / total * 100) : 0;

			// Validasi: progress per baris & total akumulasi tidak boleh > 100%
			var progress_valid = validasiProgressTop(no, progress);
			if (progress_valid !== progress) {
				progress = progress_valid;
				nilai_top = (total * progress / 100);
			}

			$(this).val(number_format(nilai_top, 4));
			$('.progress_' + no).val(number_format(progress, 2));
		});

		$(document).on('click', '.del_top', function() {
			var top_no = $(this).data('top_no');

			$('.top_' + top_no).remove();
		});

		$('#show_tax').on('change', function() {
			checkShowTax();
		});

	});

	function checkShowTax() {
		cariTotal();

		if ($('#show_tax').is(':checked')) {
			$('.row-pajak').show();
		} else {
			$('.row-pajak').hide();
		}
	}

	function addmaterial() {
		var jumlah = $('#data_request').find('tr').length;
		var id_suplier = $("#id_suplier").val();
		var loi = $("#loi").val();
		var angka = jumlah + 1;
		if (id_suplier == '' || id_suplier == null || loi == '' || loi == null) {
			swal("Warning", "Please select a Supplier first", "error");
			return false;
		} else {
			$.ajax({
				type: "GET",
				url: siteurl + 'purchase_order/AddMaterial',
				data: "jumlah=" + jumlah + "&id_suplier=" + id_suplier + "&loi=" + loi,
				success: function(html) {
					$("#data_request").append(html);
					$(".bilangan-desimal").maskMoney();
					$(".chosen-select").chosen();
					$('.autoNumeric3').autoNumeric('init', {
						vMin: 0
					});
				}
			});
			$.ajax({
				type: "GET",
				url: siteurl + 'purchase_order/UbahImport',
				data: "loi=" + loi,
				success: function(html) {
					$("ubahloi").html(html);
				}
			});
		}
	}

	function HitungHarga(id) {
		var dt_qty = $("#dt_qty_" + id).val();
		var dt_width = $("#dt_width_" + id).val();
		var dt_hargasatuan = $("#dt_hargasatuan_" + id).val();
		// $.ajax({
		// type:"GET",
		// url:siteurl+'purchase_order/HitungHarga',
		// data:"dt_hargasatuan="+dt_hargasatuan+"&dt_qty="+dt_qty+"&id="+id,
		// success:function(html){
		// $("#jumlahharga_"+id).html(html);
		// }
		// });
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/TotalWeight',
			data: "dt_width=" + dt_width + "&dt_qty=" + dt_qty + "&id=" + id,
			success: function(html) {
				$("#totalwidth_" + id).html(html);
			}
		});
		TotalSemua()
	}

	function CariPrice(id) {
		var dt_ratelme = $("#dt_ratelme_" + id).val();
		var dt_idmaterial = $("#dt_idmaterial_" + id).val();
		if (dt_idmaterial == '' || dt_idmaterial == null) {
			swal("Warning", "Please select a Material first", "error");
			return false;
		} else {
			$.ajax({
				type: "GET",
				url: siteurl + 'purchase_order/CariPrice',
				data: "dt_ratelme=" + dt_ratelme + "&dt_idmaterial=" + dt_idmaterial + "&id=" + id,
				success: function(html) {
					$("#dt_alloyprice_" + id).val(html);
				}
			});
		}
	}

	function CariPPN(id) {
		var ppn = $("#dt_ppn_" + id).val();

		// console.log(ppn)

		var harga = getNum($("#dt_jumlahharga_" + id).val().split(",").join(""));
		var disc_persen = getNum($("#disc_persen_" + id).val().split(",").join(""));
		var disc_num = getNum($("#disc_num_" + id).val().split(",").join(""));
		if (disc_num == '' || disc_num == 0) {
			disc_num = (harga * disc_persen / 100);
		}

		harga = (harga - disc_num);
		var dt_idmaterial = $("#dt_idmaterial_" + id).val();


		// if (dt_idmaterial == '' || dt_idmaterial == null) {
		// 	swal("Warning", "Please select a Material first", "error");
		// 	return false;
		// } else {


		// }
		if (ppn == '' || ppn == 'N') {
			$("#dt_nilai_ppn_" + id).attr('readonly', true);
			$("#dt_persen_ppn_" + id).attr('readonly', true);

			$("#dt_nilai_ppn_" + id).val('0');
			$("#dt_persen_ppn_" + id).val('0');
			HitAmmount(id);
		} else {
			$.ajax({
				type: "GET",
				url: siteurl + 'purchase_order/CariPPN',
				data: "harga=" + harga + "&id=" + id,
				success: function(html) {
					$("#dt_nilai_ppn_" + id).attr('readonly', false);
					$("#dt_persen_ppn_" + id).attr('readonly', false);

					$("#dt_nilai_ppn_" + id).val(html);
					$("#dt_persen_ppn_" + id).val(11);
					HitAmmount(id);
				}
			});
		}
	}

	function get_kurs() {
		// var loi = $("#loi").val();
		// $.ajax({
		// 	type: "GET",
		// 	url: siteurl + 'purchase_order/FormInputKurs',
		// 	data: "loi=" + loi,
		// 	success: function(html) {
		// 		$("#input_kurs").html(html);
		// 	}
		// });
	}


	function HitungUP(id) {
		var alloyprice = $("#dt_alloyprice_" + id).val();
		var fabcost = $("#dt_fabcost_" + id).val();
		var diskon = $("#dt_diskon_" + id).val();
		var pajak = $("#dt_pajak_" + id).val();
		var qty = $("#dt_qty_" + id).val();
		var hargasatuan = $("#dt_hargasatuan_" + id).val();
		var dt_width = $("#dt_totalwidth_" + id).val();

		var loi = $("#loi").val();
		// console.log(dt_width)
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/HitungUP',
			data: "fabcost=" + fabcost + "&alloyprice=" + alloyprice + "&hargasatuan=" + hargasatuan + "&loi=" + loi,
			success: function(html) {
				// $("#dt_hargasatuan_"+id).val(html); 
				HitAmmount(id)
			}
		});
		// $.ajax({
		// type:"GET",
		// url:siteurl+'purchase_order/Hitjumlah',
		// data:"fabcost="+fabcost+"&alloyprice="+alloyprice+"&pajak="+pajak+"&diskon="+diskon+"&qty="+qty+"&hargasatuan="+hargasatuan+"&loi="+loi+"&dt_width="+dt_width,
		// success:function(html){
		// $("#dt_jumlahharga_"+id).val(html); 
		// }
		// });		
	}

	function HitungUPIm(id) {
		var alloyprice = $("#dt_alloyprice_" + id).val();
		var fabcost = $("#dt_fabcost_" + id).val();
		var diskon = $("#dt_diskon_" + id).val();
		var pajak = $("#dt_pajak_" + id).val();
		var qty = $("#dt_qty_" + id).val();
		var hargasatuan = $("#dt_hargasatuan_" + id).val();
		var dt_width = $("#dt_totalwidth_" + id).val();

		var loi = $("#loi").val();
		// console.log(dt_width)
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/HitungUP',
			data: "fabcost=" + fabcost + "&alloyprice=" + alloyprice + "&hargasatuan=" + hargasatuan + "&loi=" + loi,
			success: function(html) {
				// $("#dt_hargasatuan_"+id).val(html); 
				$('.autoNumeric3').autoNumeric('init', {
					vMin: 0
				});
				HitAmmount(id)
			}
		});
		// $.ajax({
		// type:"GET",
		// url:siteurl+'purchase_order/Hitjumlah',
		// data:"fabcost="+fabcost+"&alloyprice="+alloyprice+"&pajak="+pajak+"&diskon="+diskon+"&qty="+qty+"&hargasatuan="+hargasatuan+"&loi="+loi+"&dt_width="+dt_width,
		// success:function(html){
		// $("#dt_jumlahharga_"+id).val(html); 
		// }
		// });		
	}

	function CariProperties(id) {
		var idpr = $("#dt_idpr_" + id).val();
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariIdMaterial',
			data: "idpr=" + idpr + "&id=" + id,
			success: function(html) {
				$("#idmaterial_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariNamaMaterial',
			data: "idpr=" + idpr + "&id=" + id,
			success: function(html) {
				$("#namaterial_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariPanjangMaterial',
			data: "idpr=" + idpr + "&id=" + id,
			success: function(html) {
				$("#panjang_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariLebarMaterial',
			data: "idpr=" + idpr + "&id=" + id,
			success: function(html) {
				$("#lebar_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariDescripitionMaterial',
			data: "idpr=" + idpr + "&id=" + id,
			success: function(html) {
				$("#description_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariQtyMaterial',
			data: "idpr=" + idpr + "&id=" + id,
			success: function(html) {
				$("#qty_" + id).html(html);
			}
		});
		// $.ajax({
		// type:"GET",
		// url:siteurl+'purchase_order/CariweightMaterial',
		// data:"idpr="+idpr+"&id="+id,
		// success:function(html){
		// $("#width_"+id).html(html);
		// }
		// });
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariTweightMaterial',
			data: "idpr=" + idpr + "&id=" + id,
			success: function(html) {
				$("#totalwidth_" + id).html(html);
			}
		});

		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariWidthMaterial',
			data: "idpr=" + idpr + "&id=" + id,
			success: function(html) {
				$("#width_" + id).html(html);
			}
		});

		var a;
		var ArrList = [];
		for (a = 1; a <= 100; a++) {
			var dataid = $('#dt_idpr_' + a).val();
			ArrList.push(dataid);
		}
		$.ajax({
			type: "POST",
			url: siteurl + 'purchase_order/getDateExp',
			data: {
				'id_pr': ArrList
			},
			dataType: 'json',
			success: function(data) {
				$('#expect_tanggal').val(data.minimal)
			}
		});
	}

	function LockMaterial(id) {
		var idpr = $("#dt_idpr_" + id).val();
		var idmaterial = $("#dt_idmaterial_" + id).val();
		var namaterial = $("#dt_namamaterial_" + id).val();
		var description = $("#dt_description_" + id).val();
		var qty = $("#dt_qty_" + id).val();
		var width = $("#dt_width_" + id).val();
		var totalwidth = $("#dt_totalweight_" + id).val();
		var hargasatuan = $("#dt_hargasatuan_" + id).val();
		var diskon = $("#dt_diskon_" + id).val();
		var pajak = $("#dt_pajak_" + id).val();
		var ratelme = $("#dt_ratelme_" + id).val();
		var alloyprice = $("#dt_alloyprice_" + id).val();
		var fabcost = $("#dt_fabcost_" + id).val();
		var panjang = $("#dt_panjang_" + id).val();
		var lebar = $("#dt_lebar_" + id).val();
		var jumlahharga = $("#dt_jumlahharga_" + id).val();
		var note = $("#dt_note_" + id).val();
		var subtotal = $("#subtotal").val();
		var hargatotal = $("#hargatotal").val();
		var diskontotal = $("#diskontotal").val();
		var taxtotal = $("#taxtotal").val();
		if (qty == '' || qty == null || hargasatuan == '' || hargasatuan == null) {
			swal("Warning", "Form cannot be empty", "error");
			return false;
		} else {
			$.ajax({
				type: "GET",
				url: siteurl + 'purchase_order/LockMatrial',
				data: "idpr=" + idpr + "&id=" + id + "&idmaterial=" + idmaterial + "&width=" + width + "&ratelme=" + ratelme + "&alloyprice=" + alloyprice + "&fabcost=" + fabcost + "&panjang=" + panjang + "&lebar=" + lebar + "&totalwidth=" + totalwidth + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
				success: function(html) {
					$("#trmaterial_" + id).html(html);
				}
			});
			$.ajax({
				type: "GET",
				url: siteurl + 'purchase_order/CariTHarga',
				data: "idpr=" + idpr + "&id=" + id + "&hargatotal=" + hargatotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
				success: function(html) {
					$("#ForHarga").html(html);
				}
			});
			$.ajax({
				type: "GET",
				url: siteurl + 'purchase_order/CariTDiskon',
				data: "idpr=" + idpr + "&id=" + id + "&diskontotal=" + diskontotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
				success: function(html) {
					$("#ForDiskon").html(html);
				}
			});
			$.ajax({
				type: "GET",
				url: siteurl + 'purchase_order/CariTPajak',
				data: "idpr=" + idpr + "&id=" + id + "&taxtotal=" + taxtotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
				success: function(html) {
					$("#ForTax").html(html);
				}
			});
			$.ajax({
				type: "GET",
				url: siteurl + 'purchase_order/CariTSum',
				data: "idpr=" + idpr + "&id=" + id + "&hargatotal=" + hargatotal + "&diskontotal=" + diskontotal + "&taxtotal=" + taxtotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
				success: function(html) {
					$("#ForSum").html(html);
				}
			});
		}
	}

	function CancelItem(id) {
		var idpr = $("#dt_idpr_" + id).val();
		var idmaterial = $("#dt_idmaterial_" + id).val();
		var namaterial = $("#dt_namamaterial_" + id).val();
		var description = $("#dt_description_" + id).val();
		var qty = $("#dt_qty_" + id).val();
		var hargasatuan = $("#dt_hargasatuan_" + id).val();
		var diskon = $("#dt_diskon_" + id).val();
		var pajak = $("#dt_pajak_" + id).val();
		var jumlahharga = $("#dt_jumlahharga_" + id).val();
		var note = $("#dt_note_" + id).val();
		var subtotal = $("#subtotal").val();
		var hargatotal = $("#hargatotal").val();
		var diskontotal = $("#diskontotal").val();
		var taxtotal = $("#taxtotal").val();
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariMinHarga',
			data: "idpr=" + idpr + "&id=" + id + "&hargatotal=" + hargatotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
			success: function(html) {
				$("#ForHarga").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariMinDiskon',
			data: "idpr=" + idpr + "&id=" + id + "&diskontotal=" + diskontotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
			success: function(html) {
				$("#ForDiskon").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariMinPajak',
			data: "idpr=" + idpr + "&id=" + id + "&taxtotal=" + taxtotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
			success: function(html) {
				$("#ForTax").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariMinSum',
			data: "idpr=" + idpr + "&id=" + id + "&hargatotal=" + hargatotal + "&diskontotal=" + diskontotal + "&taxtotal=" + taxtotal + "&idmaterial=" + idmaterial + "&namaterial=" + namaterial + "&description=" + description + "&qty=" + qty + "&hargasatuan=" + hargasatuan + "&diskon=" + diskon + "&pajak=" + pajak + "&jumlahharga=" + jumlahharga + "&note=" + note,
			success: function(html) {
				$("#ForSum").html(html);
			}
		});
		$('#data_request #trmaterial_' + id).remove();
	}

	function HapusItem(id) {}

	function HitungHarga2(id) {
		var dt_qty = $("#dt_qty_" + id).val();
		var dt_width = $("#dt_totalwidth_" + id).val();
		var dt_hargasatuan = $("#dt_hargasatuan_" + id).val();
		console.log(dt_width);
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/HitungHarga',
			data: "dt_hargasatuan=" + dt_hargasatuan + "&dt_qty=" + dt_qty + "&id=" + id + "&dt_width=" + dt_width,
			success: function(html) {
				$("#jumlahharga_" + id).html(html);
			}
		});

	}

	function get_lokasi() {
		var supplier = $("#id_suplier").val();
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_order/CariLokasi',
			data: "supplier=" + supplier,
			success: function(html) {
				$("#loi").html(html);
				get_kurs();
			}

		});


	}

	// Validasi progress Group TOP tidak boleh > 100% (per baris maupun akumulasi seluruh baris)
	function validasiProgressTop(no, progress) {
		if (isNaN(progress) || progress < 0) progress = 0;

		// Batas per baris
		if (progress > 100) {
			swal("Peringatan", "Progress tidak boleh lebih dari 100%.", "warning");
			return 100;
		}

		// Total progress baris lain (selain baris yang sedang diubah)
		var totalLain = 0;
		$('.input_progress').each(function() {
			if ($(this).data('no') == no) return; // lewati baris ini
			var p = getNum(($(this).val() || '').split(',').join(''));
			totalLain += p;
		});

		var sisa = 100 - totalLain;
		if (sisa < 0) sisa = 0;

		if ((totalLain + progress) > 100) {
			swal("Peringatan", "Total progress semua Group TOP tidak boleh lebih dari 100%. Sisa yang tersedia: " + number_format(sisa, 2) + "%.", "warning");
			return sisa;
		}

		return progress;
	}

	function HitAmmount(id) {
		// --- Input per baris ---
		var qty = getNum($("#dt_qty_" + id).val().split(",").join(""));
		var hargasatuan = getNum($("#dt_hargasatuan_" + id).val().split(",").join(""));
		var persen_ppn = getNum($("#dt_persen_ppn_" + id).val().split(",").join(""));
		var disc_persen = getNum($('#disc_persen_' + id).val().split(',').join(''));
		var disc_num = getNum($('#disc_num_' + id).val().split(',').join(''));

		// Jumlah kotor (harga satuan x qty) -> kolom "Total Harga" (jumlahharga)
		var jumlah = hargasatuan * qty;

		// --- Diskon efektif (satu sumber kebenaran) ---
		// Prioritas: jika user isi nominal diskon (disc_num) > 0, pakai nominal itu
		// lalu turunkan persennya. Jika tidak, hitung nominal dari persen diskon.
		var diskon_efektif;
		if (disc_num > 0) {
			diskon_efektif = disc_num;
			disc_persen = (jumlah > 0) ? (diskon_efektif / jumlah * 100) : 0;
		} else {
			diskon_efektif = jumlah * disc_persen / 100;
			disc_num = diskon_efektif;
		}

		var jumlah_w_disc = jumlah - diskon_efektif;

		// PPN dihitung dari jumlah setelah diskon
		var nilai_ppn = jumlah_w_disc * persen_ppn / 100;

		// Total baris (Sub Total) = setelah diskon + ppn. INI yang dilihat user & disimpan.
		var totalharga = jumlah_w_disc + nilai_ppn;

		// Sisa kuota
		var kuota_internal = getNum($("#dt_kuotainternal" + id).val().split(",").join(""));
		var sisa_kuota = kuota_internal - qty;
		$("#dt_sisa_kuota_" + id).val(number_format(sisa_kuota, 2));

		if (sisa_kuota < 0) {
			$('#dt_sisa_kuota_' + id).css('color', 'red');
		} else {
			$('#dt_sisa_kuota_' + id).css('color', 'black');
		}

		// --- Tulis balik nilai baris ---
		$("#dt_nilai_ppn_" + id).val(number_format(nilai_ppn, 2));
		$("#dt_jumlahharga_" + id).val(number_format(jumlah, 4));
		$("#dt_totalharga_" + id).val(number_format(totalharga, 4));
		$("#disc_persen_" + id).val(number_format(disc_persen, 2));
		$("#disc_num_" + id).val(number_format(disc_num, 2));

		// Field legacy tetap disinkronkan agar tidak menyeret nilai lama
		$("#dt_ch_diskon_" + id).val(diskon_efektif);
		$("#dt_ch_pajak_" + id).val(nilai_ppn);
		$("#dt_ch_jumlah_" + id).val(totalharga);

		// Hitung ulang total footer dari basis baris yang sama
		cariTotal();
	}

	// cariTotal() = satu-satunya sumber kebenaran untuk total footer.
	// Selalu MENJUMLAHKAN ULANG seluruh baris (bukan membaca nilai lama).
	function cariTotal() {
		// 1. Jumlahkan Sub Total semua baris item (kelas ch_jumlah_ex2 = #dt_totalharga_*)
		var totalItem = 0;
		$(".ch_jumlah_ex2").each(function() {
			totalItem += getNum($(this).val().split(",").join(""));
		});

		var diskonKhusus = getNum($("#diskonkhusus").val().split(",").join(""));
		var kirim = getNum($("#kirim").val().split(",").join(""));

		// 2. Total (sebelum pajak & ongkir) = total item - diskon khusus
		var baseSetelahDiskon = Math.max(0, totalItem - diskonKhusus);

		var pajakAktif = $('#show_tax').is(':checked');
		var dpp = 0;
		var ppn = 0;
		var totalPlusPajak = baseSetelahDiskon;

		if (pajakAktif) {
			// Rumus sesuai Excel: DPP = base * 11/12 ; PPn = DPP * 12%
			dpp = baseSetelahDiskon * (11 / 12);
			ppn = dpp * 0.12;
			totalPlusPajak = baseSetelahDiskon + ppn;
		}

		// 3. Total Order = (base + ppn) + biaya kirim
		var grandtotal = totalPlusPajak + kirim;

		// 4. Tulis ke footer
		$("#totalinppn").val(number_format(totalItem, 4));
		$("#totalexppn").val(number_format(baseSetelahDiskon, 4));
		$("#dpp").val(number_format(dpp, 2));
		$("#ppn").val(number_format(ppn, 2));
		$("#subtotal").val(number_format(grandtotal, 4));

		// Field hidden yang dibaca backend
		$("#hargatotal").val(number_format(totalItem, 4));
		$("#taxtotal").val(number_format(kirim, 2));
	}

	function SumDel() {
		var SUM_JML = 0
		var SUM_DIS = 0
		var SUM_PJK = 0
		var SUM_JMX = 0

		$(".ch_diskon").each(function() {
			SUM_DIS += Number($(this).val());
		});

		$(".ch_pajak").each(function() {
			SUM_PJK += Number($(this).val());
		});

		$(".ch_jumlah").each(function() {
			SUM_JML += Number($(this).val());
		});

		$(".ch_jumlah_ex").each(function() {
			SUM_JMX += Number($(this).val().split(",").join(""));
		});

		$("#hargatotal").val(number_format(SUM_JMX, 2));
		$("#diskontotal").val(number_format(SUM_DIS, 2));
		$("#taxtotal").val(number_format(SUM_PJK, 2));
		$("#subtotal").val(number_format(SUM_JML, 4));

		// Hitung ulang footer dengan rumus pajak yang benar setelah baris dihapus.
		cariTotal();
	}

	function TotalSemua() {
		var SUM_JML = 0
		var SUM_DIS = 0
		var SUM_PJK = 0
		var SUM_JMX = 0

		$(".ch_diskon").each(function() {
			SUM_DIS += Number($(this).val());
		});

		$(".ch_pajak").each(function() {
			SUM_PJK += Number($(this).val());
		});

		$(".ch_jumlah").each(function() {
			SUM_JML += Number($(this).val());
		});

		$(".ch_jumlah_ex2").each(function() {
			SUM_JMX += Number($(this).val().split(",").join(""));
		});

		$("#hargatotal").val(number_format(SUM_JMX, 2));
		$("#totalinppn").val(number_format(SUM_JMX, 4));
		$("#diskontotal").val(number_format(SUM_DIS, 2));
		$("#taxtotal").val(number_format(SUM_PJK, 2));
		$("#subtotal").val(number_format(SUM_JMX, 4));

		// Hitung ulang footer (DPP/PPN/Subtotal) dengan rumus pajak yang benar.
		// cariTotal() adalah satu-satunya sumber kebenaran total footer.
		cariTotal();
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

	function getNum(val) {
		if (isNaN(val) || val == '') {
			return 0;
		}
		return parseFloat(val);
	}
</script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
	$(function() {
		$('#tanggal, #delivery_date').datepicker({
			dateFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
		});
	});
</script>

<!-- html trash -->