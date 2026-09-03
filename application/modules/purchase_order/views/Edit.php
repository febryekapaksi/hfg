<?php
$ENABLE_ADD     = has_permission('Purchase_Request.Add');
$ENABLE_MANAGE  = has_permission('Purchase_Request.Manage');
$ENABLE_VIEW    = has_permission('Purchase_Request.View');
$ENABLE_DELETE  = has_permission('Purchase_Request.Delete');
?>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
<div class="card">

	<div class="card-body">
		<form id="data-form" method="post">
			<input type="hidden" name="no_po" value="<?= $results['get_po']->no_po ?>">
			<input type="hidden" name="no_pr" value="<?= $results['get_po']->no_pr ?>">
			<div class="col-sm-12">
				<div class="form-group row mb-3">
					<!-- <div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="id_customer">Supplier</label>
									</div>
									<div class="col-md-8">
										<select id="id_suplier" name="id_suplier" class='form-control input-md chosen-select' onchange="get_lokasi()" required>
											<option value="">--Pilih--</option>
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
									<option value="">--Pilih--</option>
									<option value="Import" <?= (isset($results['get_po']) && $results['get_po']->loi == 'Import') ? 'selected' : null ?>>Import</option>
									<option value="Lokal" <?= (isset($results['get_po']) && $results['get_po']->loi == 'Lokal') ? 'selected' : null ?>>Lokal</option>
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
									<option value="">--Pilih--</option>
									<?php
									foreach ($results['list_department'] as $item) {
										$selected = '';
										if ($results['get_po']->id_dept !== '') {
											foreach (explode(',', $results['get_po']->id_dept) as $data_po_dept) {
												if ($data_po_dept == $item->id) {
													$selected = 'selected';
												}
											}
										}
										echo '<option value="' . $item->id . '" ' . $selected . '>' . strtoupper($item->nama) . '</option>';
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
								<input type="hidden" class="form-control" id="no_po" required name="no_po" readonly placeholder="ID PO" value="<?= $results['get_po']->no_po ?>">
								<input type="text" class="form-control" id="no_surat" required name="no_surat" readonly placeholder="No.PO" value="<?= $results['get_po']->no_surat ?>">
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="customer">Delivery Date</label>
							</div>
							<div class="col-md-8">
								<input type="text" name="delivery_date" id="delivery_date" class="form-control" value="<?= $results['get_po']->delivery_date ?>" required>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group row mb-3">
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="customer">Tanggal PO</label>
							</div>
							<div class="col-md-8">
								<input type="text" class="form-control" id="tanggal" value="<?= $results['get_po']->tanggal ?>" onkeyup required name="tanggal">
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Currency</label>
							</div>
							<div class="col-md-8">
								<select id="select_curr" name="matauang" class='form-control select2' required>
									<option value="">- Currency -</option>
									<?php foreach ($results['mata_uang'] as $mata_uang) {
										$selected = '';
										if ($results['get_po']->matauang == $mata_uang->kode) {
											$selected = 'selected';
										}
									?>
										<option value="<?= $mata_uang->kode ?>" <?= $selected; ?>><?= strtoupper(strtolower($mata_uang->kode)) ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
					</div>
				</div>

				<div class="form-group row mb-3">
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="customer">Payment Term</label>
							</div>
							<div class="col-md-8">
								<select id="term" name="term" class="form-control select2" required>
									<option value="">-- Pilih --</option>
									<?php foreach ($results['term'] as $term): ?>
										<option value="<?= htmlspecialchars($term->id) ?>" <?= ($results['get_po']->term == ($term->id)) ? 'selected' : '' ?>>
											<?= htmlspecialchars($term->name) ?>
										</option>
									<?php endforeach; ?>
								</select>
								<!-- <input type="text" class="form-control" id="term" onkeyup required name="term" value="<?= $results['get_po']->term ?>"> -->
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Supplier</label>
							</div>
							<div class="col-md-8">
								<select id="supplier" name="supplier" class='form-control select2' required>
									<option value="">- Supplier -</option>
									<?php foreach ($results['list_supplier'] as $supplier) {
										$selected = '';
										if ($supplier->kode_supplier == $results['get_po']->id_suplier) {
											$selected = 'selected';
										}
									?>
										<option value="<?= $supplier->kode_supplier ?>" data-address="<?= $supplier->address ?>" <?= $selected; ?>><?= strtoupper(strtolower($supplier->nama)) ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
					</div>

				</div>

				<div class="form-group row mb-3">
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Keterangan</label>
							</div>
							<div class="col-md-8">
								<textarea name="keterangan" id="" class="form-control"><?= $results['get_po']->note ?></textarea>
							</div>
						</div>
					</div>
					<div class="col-sm-6" hidden>
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Alamat</label>
							</div>
							<div class="col-md-8">
								<textarea name="delivery_address" id="" class="form-control"><?= $results['get_po']->delivery_address ?></textarea>
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
								<select id="cif" name="cif" class="form-control select" required>
									<option value="">--Pilih--</option>
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


				<div class="table-responsive mb-3">
					<div class="form-check form-switch mb-3">
						<input class="form-check-input text-end form-control"
							type="checkbox"
							id="show_tax"
							name="show_tax"
							value="Y"
							<?= ($results['get_po']->show_tax == 'Y') ? 'checked' : ''; ?>>
						<label class="form-check-label" for="show_tax"><b>Gunakan Pajak (PPn)</b></label>
					</div>
					<table class='table table-bordered table-striped'>
						<thead>
							<tr class='bg-blue'>
								<th style="min-width: 200px;">Item</th>
								<th style="min-width: 200px;">Nama Lain</th>
								<th style="min-width: 150px;">HS Code</th>
								<th style="min-width: 150px;">Kuota Internal</th>
								<th style="min-width: 100px;" hidden>Width</th>
								<th style="min-width: 100px;" hidden>Length</th>
								<th style="min-width: 100px;">Qty PR</th>
								<th style="min-width: 100px;">PO Qty</th>
								<th style="min-width: 100px;">Unit Measurement</th>
								<th style="min-width: 75px;">Unit Packing</th>
								<th style="min-width: 100px;" hidden>Rate LME</th>
								<th style="min-width: 100px;" hidden>Alloy Price</th>
								<th style="min-width: 100px;" hidden>Fab Cost</th>
								<th style="min-width: 150px;">Harga Satuan</th>
								<th style="min-width: 100px;" hidden>Disc %</th>
								<th style="min-width: 100px;" hidden>Biaya Kirim</th>
								<th style="min-width: 150px;">Total Harga</th>
								<th style="min-width: 150px;">Nilai Discount</th>
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
							// print_r($value . "<br>");
							$key = 0;
							foreach ($results['getitemso'] as $value) {

								$get_trans_po = $this->db->get_where('dt_trans_po', ['idpr' => $value->id])->num_rows();

								// echo '<tr><td>' . $value->nm_material . '</td></tr>';

								$no = $n++;
								$key++;

								$disabled = '';
								// $disabled = ($loi == 'Import') ? '' : 'readonly';
								// $disabled2 = ($loi == 'Import') ? 'readonly' : '';
								// $idmat = $value->idmaterial;
								// $harga 	= $this->db->query("SELECT * FROM ms_product_pricelist WHERE id_category3 = '$idmat'")->row();

								// $stock = $this->db->query("SELECT * FROM stock_material WHERE id_category3 = '$idmat'")->row();

								// $avl 	 =	$stock->qty_free;
								$po    = $value->qty;

								$total = $value->hargasatuan * $value->qty;


								// if ($value->status_app !== 'Y') {

								$get_po = $this->db->get_where('dt_trans_po', ['idpr' => $value->idpr])->row();
								// if ($get_po->qty == null || $get_po->qty > $value->propose_purchase) {
								$status = "<div class='badge bg-green'>Done PO</div>";
								if ($get_po->qty == null || $get_po->qty > $value->propose_purchase) {
									$status = "<div class='badge bg-red'>Outstanding PO</div>";
								}

								$idpr = (!empty($value->idpr)) ? $value->idpr : '';
								$no_pr = (!empty($value->no_pr)) ? $value->no_pr : '';
								$tipe_pr = (!empty($value->tipe_pr)) ? $value->tipe_pr : '';
								$id_material = (!empty($value->idmaterial)) ? $value->idmaterial : '';
								$nm_material1 = (!empty($value->nm_material1)) ? $value->nm_material1 : '';
								$description = (!empty($value->description)) ? $value->description : '';
								$width = (!empty($value->width)) ? $value->width : 0;
								$length = (!empty($value->length)) ? $value->length : 0;
								$total_weight = (!empty($value->totalweight)) ? $value->totalweight : 0;
								echo "
													<tr>
														<td>  " . $value->nm_material . $value->nm_material1 . "
																<input type='hidden' id='dt_id_" . $key . "' name='dt[" . $key . "][id]' value='" . $value->id . "'>
																<input type='hidden' class='form-control input-sm' id='dt_idpr_" . $key . "' name='dt[" . $key . "][idpr]' value='" . $idpr . "'>
																<input type='hidden' class='form-control input-sm' id='dt_no_pr_" . $key . "' name='dt[" . $key . "][no_pr]' value='" . $no_pr . "'>
																<input type='hidden' class='form-control input-sm' id='dt_tipe_pr_" . $key . "' name='dt[" . $key . "][tipe_pr]' value='" . $tipe_pr . "'>
																<input type='hidden' class='form-control input-sm' id='dt_idmaterial_" . $key . "' name='dt[" . $key . "][idmaterial]' value='" . $id_material . "'>
																<input type='hidden' class='form-control input-sm' id='dt_namamaterial_" . $key . "' name='dt[" . $key . "][namamaterial]' value='" . $value->nm_material . $value->nm_material1 . "'>
																<input type='hidden' class='form-control input-sm' id='dt_panjang_" . $key . "' name='dt[" . $key . "][panjang]'>
																<input type='hidden' class='form-control input-sm' id='dt_lebar_" . $key . "' name='dt[" . $key . "][lebar]'>
									
																<input type='hidden' class='form-control input-sm ch_diskon' id='dt_ch_diskon_" . $key . "' value='" . $value->nilai_disc . "'>
																<input type='hidden' class='form-control input-sm ch_pajak' id='dt_ch_pajak_" . $key . "'>
																<input type='hidden' class='form-control input-sm ch_jumlah' id='dt_ch_jumlah_" . $key . "' value='" . $value->harga_total . "'>
																<input type='hidden' class='form-control input-sm ch_ppn' id='dt_ch_ppn_" . $key . "' value='" . $value->ppn . "'>
														</td>

														<td>" . $value->nm_lain . "</td>

														<td>
															<input type='hidden' class='form-control input-sm'  id='dt_hscode" . $key . "' name='dt[" . $key . "][hscode]' value='" . $value->hscode . "'>
															<input type='text' class='form-control input-sm'  id='dt_local_code" . $key . "' name='dt[" . $key . "][local_code]' value='" . $value->local_code . "' readonly>
														</td>
														<td><input type='text' class='form-control input-sm autoNumeric3' id='dt_kuotainternal" . $key . "' name='dt[" . $key . "][kuota_internal]' value='" . $value->kuota_internal . "' readonly></td>
											  
														<td hidden><input type='text' class='form-control input-sm' name='dt[" . $key . "][kode_barang]' id='dt_kode_barang_" . $key . "' value='" . $value->code . $value->code1 . "' readonly></td>
													
														<td hidden><input type='text' class='form-control input-sm' name='dt[" . $key . "][description]' id='dt_description_" . $key . "' value='" . $description . "'></td>
													
														<td><input type='text' class='form-control text-center input-sm' id='dt_pr_" . $key . "' name='dt[" . $key . "][pr]' value='" . $value->propose_purchase  . "' readonly ></td>
													
														<td hidden><input type='hidden' class='form-control input-sm autoNumeric' name='dt[" . $key . "][width]' id='dt_width_" . $key . "'  value='" . $width . "'></td>
														<td hidden><input type='hidden' class='form-control input-sm autoNumeric' name='dt[" . $key . "][length]' id='dt_length_" . $key . "'  value='" . $length . "'></td>
														<td>
															<input type='hidden' class='form-control input-sm autoNumeric' name='dt[" . $key . "][totalweight]' id='dt_totalweight_" . $key . "' value='" . $total_weight . "'  onkeyup='HitAmmount(" . $key . ")'>
															<input type='text' class='form-control text-center input-sm' id='dt_qty_" . $key . "' name='dt[" . $key . "][qty]' value='" . $value->qty . "' onkeyup='HitAmmount(" . $key . ")'>
														</td>
														<td class='text-center'>" . ucfirst($value->unit_measure) . "</td>
														<td class='text-center'>" . ucfirst($value->packing_unit) . ucfirst($value->packing_unit2) . "</td>
														<td hidden>
															<select class='form-control input-sm' id='dt_ratelme_" . $key . "' name='dt[" . $key . "][ratelme]' onchange='CariPrice(" . $key . ")'>
																<option value=''>-Pilih-</option>
																<option value='Hari Ini'>Hari ini</option>
																<option value='H-10'>H-10</option>
																<option value='H-30'>H-30</option>
															</select>
														</td>
														<td hidden><input type='text' class='form-control input-sm autoNumeric3' id='dt_alloyprice_" . $key . "' " . $disabled . " data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' name='dt[" . $key . "][alloyprice]' onkeyup='HitAmmount(" . $key . ")'></td>
														<td hidden><input type='text' class='form-control input-sm autoNumeric3' id='dt_fabcost_" . $key . "' " . $disabled . " name='dt[" . $key . "][fabcost]' onkeyup='HitAmmount(" . $key . ")'></td>
														<td><input type='text' class='form-control text-right input-sm auto_num_3dec' id='dt_hargasatuan_" . $key . "' name='dt[" . $key . "][hargasatuan]' onkeyup='HitAmmount(" . $key . ")' value='" . $value->hargasatuan . "'></td>
														<td hidden>
															<select class='form-control input-sm' id='dt_ppn_" . $key . "' name='dt[" . $key . "][ppn]' onchange='CariPPN(" . $key . ")'>
																<option value=''>SELECT</option>
																<option value='Y' " . (($value->ppn > 0) ? 'selected' : null) . ">Y</option>
																<option value='N' " . (($value->ppn <= 0) ? 'selected' : null) . ">N</option>
															</select>
														</td>
														<td hidden><input type='text' class='form-control input-sm autoNumeric pajak' id='dt_pajak_" . $key . "' name='dt[" . $key . "][pajak]' onkeyup='HitAmmount(" . $key . ")'></td>
														<td hidden><input type='text' class='form-control input-sm autoNumeric3' id='dt_diskon_" . $key . "' " . $disabled . " name='dt[" . $key . "][diskon]' onkeyup='HitAmmount(" . $key . ")'></td>
													
														<td><input type='text' class='form-control input-sm ch_jumlah_ex text-right auto_num' id='dt_jumlahharga_" . $key . "' readonly name='dt[" . $key . "][jumlahharga]' value='" . number_format($total, 4, '.', ',') . "'></td>
														
														<td>
															<div class='input-group input-group-sm mb-2'>
																<input type='text' name='dt[" . $key . "][disc_persen]' class='form-control input-sm auto_num disc_persen'
																	id='disc_persen_" . $key . "' data-key='" . $key . "' value='" . $value->persen_disc . "'>
																<span class='input-group-text'>%</span>
															</div>
															<div class='input-group input-group-sm'>
																<input type='text' name='dt[" . $key . "][disc_num]' class='form-control input-sm auto_num disc_num'
																	id='disc_num_" . $key . "' data-key='" . $key . "' value='" . $value->nilai_disc . "'>
																<span class='input-group-text'>Rp</span>
															</div>
														</td>
														<td hidden>
														<input type='text' class='form-control auto_num input-sm ch_ppn cng_nilai_ppn' id='dt_nilai_ppn_" . $key . "' name='dt[" . $key . "][nilai_ppn]' data-key='" . $key . "' placeholder='Nilai PPN' " . (($value->ppn > 0) ? '' : 'readonly') . " value='" . (($value->ppn > 0) ? $value->ppn : null) . "'>
														<input type='text' class='form-control input-sm ch_per_ppn cng_persen_ppn' id='dt_persen_ppn_" . $key . "' name='dt[" . $key . "][persen_ppn]' data-key='" . $key . "' placeholder='Persen PPN' " . (($value->ppn > 0) ? '' : 'readonly') . " value='" . (($value->ppn > 0) ? $value->ppn_persen : null) . "'>
														</td>
														<td><input type='text' class='form-control input-sm text-right auto_num ch_jumlah_ex2' id='dt_totalharga_" . $key . "' readonly name='dt[" . $key . "][totalharga]' value='" . number_format($total - $value->nilai_disc + $value->ppn, 4, '.', ',') . "'></td>
														<td><input type='text' class='form-control input-sm' id='dt_note_" . $key . "' name='dt[" . $key . "][note]'></td>																
														<td><input type='text' class='form-control input-sm text-end sisa_kuota autoNumeric3' id='dt_sisa_kuota_" . $key . "' name='dt[" . $key . "][sisa_kuota]' value='" . ($value->kuota_internal - $value->qty) . "' readonly></td>
										 			</tr>
												";
							}
							?>
						</tbody>
						<tfoot>
							<tr>
								<td class="text-end" colspan="11"><b>Total</b></th>
								<td colspan="2">
									<input readonly type="text" class="form-control auto_num_4dec text-end" id="totalinppn" value="<?= number_format($results['get_po']->total_include_ppn, 4, '.', ',') ?>" onkeyup required name="totalinppn">
								</td>
							</tr>
							<tr hidden>
								<td class="text-end" colspan="11"><b>Diskon Khusus</b></th>
								<td colspan="2">
									<input type="text" class="form-control text-end auto_num" id="diskonkhusus" value="<?= $results['get_po']->diskon_khusus ?>" onblur="cariTotal()" name="diskonkhusus">
								</td>
							</tr>
							<tr class="row-pajak" style="display: none;" hidden>
								<td class="text-end" colspan="11"><b>Total (Exclude PPn)</b></td>
								<td colspan="2">
									<input readonly type="text" class="form-control text-end autoNumeric" id="totalexppn" name="totalexppn">
								</td>
							</tr>
							<tr class="row-pajak" style="display: none;">
								<td class="text-end" colspan="11"><b>DPP</b></td>
								<td colspan="2">
									<input readonly type="text" class="form-control text-end autoNumeric" id="dpp" name="dpp">
								</td>
							</tr>
							<tr class="row-pajak" style="display: none;">
								<td class="text-end" colspan="11"><b>PPn</b></td>
								<td colspan="2">
									<input readonly type="text" class="form-control text-end autoNumeric" id="ppn" name="ppn">
								</td>
							</tr>
							<tr>
								<td class="text-end" colspan="11"><b>Biaya Kirim</b></td>
								<td colspan="2">
									<input type="hidden" class="form-control" id="taxtotal" onkeyup required name="taxtotal">
									<input type="text" class="form-control auto_num text-end" id="kirim" value="<?= $results['get_po']->taxtotal ?>" onblur="cariTotal()" required name="kirim">
								</td>
							</tr>
							<tr>
								<td class="text-end" colspan="11"><b>Total Order</b></td>
								<td colspan="2">
									<input readonly type="text" class="form-control text-end auto_num_4dec" id="subtotal" value="<?= number_format($results['get_po']->subtotal, 4, '.', ',') ?>" onkeyup required name="subtotal">
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
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Sub Total (Exclude PPN & Disc)</label>
							</div>
							<div class="col-md-8" id="ForHarga">
								<input readonly type="text" class="form-control" id="hargatotal" onkeyup required name="hargatotal">
							</div>
						</div>
					</div>
				</div>
				<div class="form-group row mb-3" hidden>
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="id_customer">Total Discount</label>
								</div>
								<div class="col-md-1 text-right">
									<span>(%)</span> <br><br>
									<span>($)</span>
								</div>
								<div class="col-md-7" id="ForHarga">
									<input type="text" class="form-control auto_num" id="persendisc" onkeyup required name="persendisc" onblur="cariTotal()" placeholder="Persen Disc (%)" value="<?= $results['get_po']->persen_disc ?>">
									<input type="text" class="form-control auto_num" id="totaldisc" onkeyup required name="totaldisc" onblur="cariTotal()" placeholder="Nilai Disc" value="<?= $results['get_po']->nilai_disc ?>">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group row mb-3" hidden>
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Total PPN</label>
							</div>
							<div class="col-md-1 text-right">
								<span>(%)</span> <br><br>
								<span>($)</span>
							</div>
							<div class="col-md-7" id="ForHarga">
								<input type="text" class="form-control auto_num" id="persenppn" onkeyup required name="persenppn" onblur="cariTotal()" placeholder="Persen PPN (%)" value="<?= $results['get_po']->total_ppn_persen ?>">
								<input type="text" class="form-control auto_num" id="totalppn" onkeyup required name="totalppn" onblur="cariTotal()" placeholder="Nilai PPN" value="<?= $results['get_po']->total_ppn ?>" disabled>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group row mb-3" hidden>
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
				<!-- <div class="col-sm-12">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="id_customer">Biaya Kirim</label>
								</div>
								<div class="col-md-8" id="ForTax">
									<input type="hidden" class="form-control" id="taxtotal" onkeyup required name="taxtotal">
									<input type="text" class="form-control auto_num" id="kirim" onblur="cariTotal()" required name="kirim" value="<?= $results['get_po']->taxtotal; ?>">
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-4">
									<label for="id_customer">Total Order</label>
								</div>
								<div class="col-md-8" id="ForSum">
									<input readonly type="text" class="form-control" id="subtotal" onkeyup required name="subtotal">
								</div>
							</div>
						</div>
					</div> -->
				<hr>
				<div class="form-group row mb-3">
					<div class="col-sm-12">
						<input type="hidden" name="num_top" class="num_top" value="<?= $results['num_po'] ?>">
						<button type="button" class="btn btn-sm btn-primary add_top mb-3">
							<i class="fa fa-plus"></i> Add TOP
						</button>
						<table class="table table-bordered">
							<thead class="bg-blue">
								<tr>
									<th class="text-center">Group TOP</th>
									<th class="text-center">Progress (%)</th>
									<th class="text-center">Value</th>
									<th class="text-center">Keterangan</th>
									<th class="text-center">Tipe Pembayaran</th>
									<th class="text-center">Jatuh Tempo</th>
									<th class="text-center">Action</th>
								</tr>
							</thead>
							<tbody class="list_tbody_top">
								<?php
								$no = 1;
								foreach ($results['list_top'] as $item_top) {
									$checked_lc = ($item_top->tipe_bayar == 'lc') ? 'checked' : '';
									$checked_tt = ($item_top->tipe_bayar == 'tt') ? 'checked' : '';
									$display_btn_lc = ($item_top->tipe_bayar == 'lc') ? '' : 'display:none;';

									echo '<tr class="top_' . $no . '">';

									echo '<td>';
									echo '<select name="group_top_' . $no . '" class="form-control form-control-sm">';
									foreach ($results['list_group_top'] as $item_group_top) {
										$selected = '';
										if ($item_group_top->id == $item_top->group_top) {
											$selected = 'selected';
										}
										echo '<option value="' . $item_group_top->id . '" ' . $selected . '>' . strtoupper($item_group_top->name) . '</option>';
									}
									echo '</select>';
									echo '</td>';

									echo '<td>';
									echo '<input type="text" class="form-control form-control-sm input_progress progress_' . $no . ' auto_num" name="progress_' . $no . '" data-no="' . $no . '" value="' . number_format($item_top->progress, 2) . '">';
									echo '</td>';

									echo '<td class="text-right">';
									echo '<input type="text" class="form-control form-control-sm nilai_top nilai_top_' . $no . ' auto_num_4dec" name="nilai_top_' . $no . '" data-no="' . $no . '" value="' . number_format($item_top->nilai, 4, '.', ',') . '">';
									echo '</td>';

									echo '<td>';
									echo '<textarea name="keterangan_top_' . $no . '" class="form-control form-control-sm">' . $item_top->keterangan . '</textarea>';
									echo '</td>';

									echo '<td>';
									echo '<div class="form-check">';
									echo '<input class="form-check-input check_bayar" type="radio" id="lc_' . $no . '" name="tipe_bayar_' . $no . '" value="lc" ' . $checked_lc . ' data-no="' . $no . '">';
									echo '<label class="form-check-label" for="lc_' . $no . '">LC</label>';
									echo '</div>';
									echo '<div class="form-check">';
									echo '<input class="form-check-input check_bayar" type="radio" id="tt_' . $no . '" name="tipe_bayar_' . $no . '" value="tt" ' . $checked_tt . ' data-no="' . $no . '">';
									echo '<label class="form-check-label" for="tt_' . $no . '">TT</label>';
									echo '</div>';

									// Tombol untuk buka modal LC
									echo '<button type="button" class="btn btn-sm btn-outline-primary btn_view_lc" id="btn_lc_' . $no . '" style="' . $display_btn_lc . '" data-no="' . $no . '"><i class="fas fa-eye"></i> Detail LC</button>';

									// --- INPUT HIDDEN UNTUK DATA LC (DARI TABEL tr_po_detail_lc) ---
									echo '<input type="hidden" name="no_credit_' . $no . '" value="' . $item_top->no_credit . '">';
									echo '<input type="hidden" name="issue_date_' . $no . '" value="' . $item_top->issue_date . '">';
									echo '<input type="hidden" name="expiry_date_' . $no . '" value="' . $item_top->expiry_date . '">';
									echo '<input type="hidden" name="value_contract_' . $no . '" value="' . $item_top->value_contract . '">';
									echo '<input type="hidden" name="tolerance_plus_' . $no . '" value="' . $item_top->tolerance_plus . '">';
									echo '<input type="hidden" name="tolerance_minus_' . $no . '" value="' . $item_top->tolerance_minus . '">';
									echo '<input type="hidden" name="type_of_lc_' . $no . '" value="' . $item_top->type_of_lc . '">';
									echo '<input type="hidden" name="valid_usen_until_' . $no . '" value="' . $item_top->valid_usen_until . '">';
									echo '<input type="hidden" name="bank_sender_' . $no . '" value="' . $item_top->bank_sender . '">';
									echo '<input type="hidden" name="bank_receiver_' . $no . '" value="' . $item_top->bank_receiver . '">';
									echo '<input type="hidden" name="latest_shipment_' . $no . '" value="' . $item_top->latest_shipment . '">';
									echo '<input type="hidden" name="no_sales_contract_' . $no . '" value="' . $item_top->no_sales_contract . '">';
									echo '</td>';

									echo '<td class="">';
									echo '<input type="date" class="form-control form-control-sm" name="jatuh_tempo_' . $no . '" value="' . $item_top->jatuh_tempo . '">';
									echo '</td>';

									echo '<td class="text-center">';
									echo '<button type="button" class="btn btn-sm btn-danger del_top" data-top_no="' . $no . '"><i class="fa fa-trash"></i></button>';
									echo '</td>';

									echo '</tr>';

									$no++;
								}
								?>
							</tbody>
						</table>

					</div>
				</div>
				<div class="text-center">
					<button type="submit" class="btn btn-success" name="save" id="simpan-com"><i class="fa fa-save"></i> Simpan</button>
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

<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url = '<?php echo base_url(); ?>';
	var active_controller = '<?php echo ($this->uri->segment(1)); ?>';
	var num_top = getNum($('.num_top').val());
	$(document).ready(function() {

		TotalSemua()
		cariTotal()
		checkShowTax()
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

		var max_fields2 = 10; //maximum input boxes allowed
		var wrapper2 = $(".input_fields_wrap2"); //Fields wrapper
		var add_button2 = $(".add_field_button2"); //Add button ID	

		$('.datepicker').datepicker({
			dateFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
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
				swal("Warning", "Form Tidak Boleh Kosong :)", "error");
				return false;
			} else if (tanggal == '' || tanggal == null) {
				swal("Warning", "Tanggal Tidak Boleh Kosong :)", "error");
				return false;
			} else if (supplier == '' || supplier == null) {
				swal("Warning", "Supplier tidak boleh kosong  :)", "error");
				return false;
			} else if (select_department == '' || select_department == null) {
				swal("Warning", "Department tidak boleh kosong  :)", "error");
				return false;
			} else if (delivery_date == '' || delivery_date == null) {
				swal("Warning", "Delivery Date tidak boleh kosong  :)", "error");
				return false;
			} else if (currency == '' || currency == null) {
				swal("Warning", "Currency tidak boleh kosong  :)", "error");
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
							var formData = new FormData($('#data-form')[0]);
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

			swal("Berhasil", "Data LC telah tersimpan di baris ini.", "success");
			$('#modal_lc').modal('hide');
		});

		$('.list_tbody_top tr').each(function(i) {
			console.log("Baris " + i + " data-lc: ", $(this).attr('data-lc_detail'));
		});

		$(document).on('click', '.btn_view_lc', function() {
			let no = $(this).data('no'); // Ambil nomor urut baris
			let row = $(this).closest('tr'); // Ambil nomor urut dari ID tombol

			// Mapping Data dari Hidden Input ke Modal
			$('#modal_row_index_hidden').val(no);
			$('#no_credit').val(row.find('input[name="no_credit_' + no + '"]').val());
			$('#issue_date').val(row.find('input[name="issue_date_' + no + '"]').val());
			$('#expiry_date').val(row.find('input[name="expiry_date_' + no + '"]').val());
			$('#value_contract').val(row.find('input[name="value_contract_' + no + '"]').val());
			$('#tolerance_plus').val(row.find('input[name="tolerance_plus_' + no + '"]').val());
			$('#tolerance_minus').val(row.find('input[name="tolerance_minus_' + no + '"]').val());
			$('#type_of_lc').val(row.find('input[name="type_of_lc_' + no + '"]').val()).trigger('change');
			$('#valid_usen_until').val(row.find('input[name="valid_usen_until_' + no + '"]').val());
			$('#bank_sender').val(row.find('input[name="bank_sender_' + no + '"]').val());
			$('#bank_receiver').val(row.find('input[name="bank_receiver_' + no + '"]').val());
			$('#latest_shipment').val(row.find('input[name="latest_shipment_' + no + '"]').val());
			$('#no_sales_contract').val(row.find('input[name="no_sales_contract_' + no + '"]').val());

			// Info Supplier
			$('#modal_supplier_name').val($('#supplier option:selected').text());
			$('#modal_supplier_address').val($('#supplier option:selected').data('address'));

			$('#modal_lc').modal('show');
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

			var disc_persen = (disc_num / (hargasatuan * qty) * 100);
			$('#disc_persen_' + key).val(number_format(disc_persen, 2));

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
					Rows += '<select class="form-control form-control-sm" name="group_top_' + num_top + '">';
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
					swal("Perhatian", "Silakan pilih Supplier terlebih dahulu di header form!", "warning");
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
			var subtotal = $('#subtotal').val();
			if (subtotal == '' || subtotal == null) {
				subtotal = 0;
			} else {
				subtotal = subtotal.split(',').join('');
				subtotal = parseFloat(subtotal);
			}

			var progress = $(this).val();
			if (progress == '' || progress == null) {
				progress = 0;
			} else {
				progress = progress.split(',').join('');
				progress = parseFloat(progress);
			}

			var nilai_top = (subtotal * progress / 100);

			// Gunakan number_format dengan 4 digit
			$('.nilai_top_' + no).val(number_format(nilai_top, 4));
			// Jangan timpa $(this) di sini
		});

		$(document).on('change', '.nilai_top', function() {
			var no = $(this).data('no');

			var subtotal = $('#subtotal').val();
			if (subtotal == '' || subtotal == null) {
				subtotal = 0;
			} else {
				subtotal = subtotal.split(',').join('');
				subtotal = parseFloat(subtotal);
			}

			var nilai_top = $(this).val();
			if (nilai_top == '' || nilai_top == null) {
				nilai_top = 0;
			} else {
				nilai_top = nilai_top.split(',').join('');
				nilai_top = parseFloat(nilai_top);
			}

			var progress = (nilai_top / subtotal * 100);

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
			swal("Warning", "Silahkan Pilih Supplier Terlebih Dahulu :)", "error");
			return false;
		} else {
			$.ajax({
				type: "GET",
				url: siteurl + 'purchase_order/AddMaterial',
				data: "jumlah=" + jumlah + "&id_suplier=" + id_suplier + "&loi=" + loi,
				success: function(html) {
					$("#data_request").append(html);
					$(".bilangan-desimal").maskMoney();
					$(".chosen-select").select2({
						width: '100%'
					});
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
	}

	function CariPrice(id) {
		var dt_ratelme = $("#dt_ratelme_" + id).val();
		var dt_idmaterial = $("#dt_idmaterial_" + id).val();
		if (dt_idmaterial == '' || dt_idmaterial == null) {
			swal("Warning", "Silahkan Pilih Material Terlebih Dahulu :)", "error");
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
		// 	swal("Warning", "Silahkan Pilih Material Terlebih Dahulu :)", "error");
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

	// function SumDel() {
	// 	var SUM_JML = 0
	// 	var SUM_DIS = 0
	// 	var SUM_PJK = 0
	// 	var SUM_JMX = 0

	// 	$(".ch_diskon").each(function() {
	// 		SUM_DIS += Number($(this).val());
	// 	});

	// 	$(".ch_pajak").each(function() {
	// 		SUM_PJK += Number($(this).val());
	// 	});

	// 	$(".ch_jumlah").each(function() {
	// 		SUM_JML += Number($(this).val());
	// 	});

	// 	$(".ch_jumlah_ex").each(function() {
	// 		SUM_JMX += Number($(this).val().split(",").join(""));
	// 	});

	// 	$("#hargatotal").val(number_format(SUM_JMX, 2));
	// 	$("#diskontotal").val(number_format(SUM_DIS, 2));
	// 	$("#taxtotal").val(number_format(SUM_PJK, 2));
	// 	$("#subtotal").val(number_format(SUM_JML, 2));

	// }

	function TotalSemua() {
		var SUM_JML = 0
		var SUM_DIS = 0
		var SUM_PJK = 0
		var SUM_JMX = 0
		var SUM_JMXX = 0
		var SUM_PPN = 0;

		var totaldisc = Number($('#totaldisc').val());
		var totalppn = Number($('#totalppn').val());

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
			SUM_JMXX += Number($(this).val().split(",").join(""));
		});
		$(".disc_num").each(function() {
			SUM_JMX += Number($(this).val().split(",").join(""));
		});
		$(".ch_ppn").each(function() {
			SUM_JMX -= Number($(this).val().split(",").join(""));
			SUM_JMXX -= Number($(this).val().split(",").join(""));

			SUM_PPN += Number($(this).val().split(",").join(""));
		});
		SUM_JMXX += Number($("#totalppn").val().split(",").join(""));
		var biaya_kirim = Number($("#kirim").val().split(",").join(""));



		// alert(SUM_JMX);

		$("#hargatotal").val(number_format(SUM_JMX, 2));
		$("#diskontotal").val(number_format(SUM_DIS, 2));
		$("#taxtotal").val(number_format(SUM_PJK, 2));
		$("#subtotal").val(number_format((SUM_JMX - totaldisc + totalppn + biaya_kirim), 4));

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