<?php
$ENABLE_ADD     = has_permission('Request_Payment_Approval.Add');
$ENABLE_MANAGE  = has_permission('Request_Payment_Approval.Manage');
$ENABLE_VIEW    = has_permission('Request_Payment_Approval.View');
$ENABLE_DELETE  = has_permission('Request_Payment_Approval.Delete');

if ($type == 'expense') {
	$keterangan = $header->informasi;
	$no_doc = $header->no_doc;
	$tgl_doc = $header->tgl_doc;
	$bank_id = $header->bank_id;
	$accnumber = $header->accnumber;
	$accname = $header->accname;
} elseif ($type == 'kasbon') {
	$keterangan = $header->keperluan;
	$no_doc = $header->no_doc;
	$tgl_doc = $header->tgl_doc;
	$bank_id = $header->bank_id;
	$accnumber = $header->accnumber;
	$accname = $header->accname;
} elseif ($type == 'transportasi') {
	$keterangan = 'Transportasi';
	$no_doc = $header->no_doc;
	$tgl_doc = $header->tgl_doc;
	$bank_id = $header->bank_id;
	$accnumber = $header->accnumber;
	$accname = $header->accname;
} elseif ($type == 'nonpo') {
	$keterangan = $header->info;
	$no_doc = $header->no_doc;
	$tgl_doc = $header->tanggal_doc;
	$bank_id = $header->bank_id;
	$accnumber = $header->accnumber;
	$accname = $header->accname;
} elseif ($type == 'periodik') {
	$keterangan = $header->keterangan;
	$no_doc = $header->no_doc;
	$tgl_doc = $header->tanggal;
	$bank_id = $header->bank_id;
	$accnumber = $header->accnumber;
	$accname = $header->accname;
} elseif ($type == 'direct_payment') {
	$keterangan = $header->deskripsi;
	$no_doc = $header->no_doc;
	$tgl_doc = $header->tgl_doc;
	$bank_id = $header->bank;
	$accnumber = $header->bank_number;
	$accname = $header->bank_account;
} elseif (in_array($type, ['invoice_dp', 'invoice_import', 'invoice_local'])) {
	$tipe_label = str_replace(['invoice_dp', 'invoice_import', 'invoice_local'], ['Invoice DP', 'Invoice Import', 'Invoice Local'], $type);
	$keterangan = $tipe_label . ' - ' . $header->no_surat . ' - ' . $header->nomor_invoice;
	$no_doc = $header->no_po;
	$no_surat = $header->no_surat;
	$tgl_doc = $header->invoice_date;
	$bank_id = $header->bank;
	$accnumber = $header->no_bank;
	$accname = $header->nm_acc_bank;
}
?>

<style>
	.table thead th {
		background-color: #f8f9fa !important;
		color: #333 !important;
		vertical-align: middle;
		font-weight: 600;
	}

	.inner-sub-table td {
		padding: 0.25rem 0;
		border: none !important;
		background: transparent !important;
	}
</style>

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form')); ?>

<input type="hidden" name="id" value="<?= $header->id; ?>">
<input type="hidden" name="tipe" value="<?= $type; ?>">
<input type="hidden" name="tingkat_approval" value="2">
<?php if (in_array($type, ['invoice_dp', 'invoice_import', 'invoice_local'])) : ?>
	<input type="hidden" name="no_doc" value="<?= $no_doc; ?>">
<?php endif; ?>

<div class="row g-3 mb-4">
	<div class="col-12 col-md-6">
		<div class="card h-100 border-0 shadow-sm">
			<div class="card-body">
				<h6 class="fw-bold text-secondary mb-3 border-bottom pb-2"><i class="fa fa-file-text me-2"></i>Detail Dokumen</h6>

				<div class="mb-3">
					<label class="form-label fw-semibold small text-muted">Nomor Dokumen</label>
					<?php if (in_array($type, ['invoice_dp', 'invoice_import', 'invoice_local'])) : ?>
						<input type="text" class="form-control bg-light" readonly value="<?= $no_surat ?? $no_doc; ?>">
					<?php else : ?>
						<input type="text" name="no_doc" class="form-control bg-light" readonly value="<?= $no_doc; ?>">
					<?php endif; ?>
				</div>

				<div class="mb-3">
					<label class="form-label fw-semibold small text-muted">Keterangan / Keperluan</label>
					<input type="text" name="informasi" class="form-control bg-light" readonly value="<?= ($keterangan) ?: ''; ?>">
				</div>

				<!-- <div class="row g-2">
					<div class="col-6">
						<label class="form-label fw-semibold small text-muted">Biaya Admin Bank</label>
						<input type="text" name="admin_bank" class="form-control bg-light text-end" readonly value="<?= number_format(($data_req_payment['admin_bank']), 2) ?>">
					</div>
					<div class="col-6">
						<label class="form-label fw-semibold small text-muted">Link Dokumen</label>
						<div class="d-block">
							<?php if (!empty($data_req_payment['link_doc']) && file_exists('./assets/expense/' . $data_req_payment['link_doc'])) : ?>
								<a href="<?= base_url('assets/expense/' . $data_req_payment['link_doc']) ?>" class="btn btn-primary btn-sm w-100 h-100 d-flex align-items-center justify-content-center" target="_blank">
									<i class="fa fa-download me-2"></i> Download Lampiran
								</a>
							<?php else : ?>
								<span class="text-muted small d-block pt-2">Tidak ada berkas</span>
							<?php endif; ?>
						</div>
					</div>
				</div> -->
			</div>
		</div>
	</div>

	<div class="col-12 col-md-6">
		<div class="card h-100 border-0 shadow-sm">
			<div class="card-body d-flex flex-column justify-content-between">
				<div>
					<h6 class="fw-bold text-secondary mb-3 border-bottom pb-2"><i class="fa fa-info-circle me-2"></i>Informasi Tambahan</h6>

					<div class="row g-2 mb-3">
						<div class="col-6">
							<label class="form-label fw-semibold small text-muted">Nama Bank Asal</label>
							<input type="text" class="form-control bg-light" readonly value="<?= $data_req_payment['bank_name']; ?>">
						</div>
						<div class="col-6">
							<label class="form-label fw-semibold small text-muted">Tanggal Dokumen</label>
							<input type="text" class="form-control bg-light" readonly value="<?= $tgl_doc; ?>">
						</div>
					</div>
				</div>

				<div class="bg-light p-3 rounded border border-warning">
					<!-- <label class="form-label fw-bold text-danger small mb-1"><i class="fa fa-exclamation-triangle me-1"></i> Alasan Penolakan (Wajib diisi jika Reject)</label>
					<textarea name="reject_reason" class="form-control reject_reason" rows="2" placeholder="Tulis alasan penolakan di sini..."></textarea> -->
				</div>
			</div>
		</div>
	</div>
</div>

<div class="card border-0 shadow-sm mb-4">
	<div class="card-body">
		<div class="table-responsive">
			<table id="mytabledata" class="table table-striped table-hover table-bordered align-middle mb-0">
				<thead class="text-center">
					<tr>
						<th style="width: 4%;">#</th>
						<th>Barang / Jasa</th>
						<th style="width: 12%;">Tgl. Transaksi</th>
						<th style="width: 6%;">Qty</th>
						<th style="width: 8%;">Currency</th>
						<th style="width: 35%;">Rincian Nominal Pembayaran</th>
						<th style="width: 8%;">Berkas</th>
						<th style="width: 8%;">
							<div class="form-check d-flex justify-content-center m-0">
								<input class="form-check-input master_check" type="checkbox" id="checkAll" checked>
								<label class="form-check-label small ms-1 fw-bold" for="checkAll">Pilih</label>
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (!empty($details)) {
						$n = $gTotal = 0;
						foreach ($details as $dtl) : $n++;
							$coa = (isset($dtl->coa)) ? $dtl->coa : '';
							$nm_coa = (isset($list_coa[$coa]) && $coa !== '') ? $list_coa[$coa] : '';

							// TIPE 1: EXPENSE
							if ($type == 'expense') :
								$harga = $dtl->harga;
								if (isset($dtl->id_kasbon) && $dtl->id_kasbon !== '') {
									$harga = $dtl->kasbon * -1;
								}
								$gTotal += ($data_req_payment['jumlah'] + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']);
					?>
								<tr>
									<td class="text-center"><?= $n; ?></td>

									<td><?= $dtl->deskripsi; ?> <?= (isset($dtl->id_kasbon) && $dtl->id_kasbon !== '') ? "<span class='badge bg-warning text-dark'>Kasbon</span>" : null ?></td>
									<td class="text-center"><?= $dtl->tanggal; ?></td>
									<td class="text-center"><?= $dtl->qty; ?></td>
									<td class="text-center small"><?= $data_req_payment['currency']; ?></td>
									<td>
										<table class="w-100 inner-sub-table small">
											<tr>
												<td>Pengajuan</td>
												<td class="text-end fw-semibold"><?= number_format($data_req_payment['jumlah'], 2) ?></td>
											</tr>
											<tr>
												<td>Nilai PPh</td>
												<td class="text-end text-danger">- <?= number_format($data_req_payment['total_pph'], 2) ?></td>
											</tr>
											<tr>
												<td>Bank Charge</td>
												<td class="text-end text-muted">+ <?= number_format($data_req_payment['admin_bank'], 2) ?></td>
											</tr>
											<tr class="border-top">
												<td><b>Net Payment</b></td>
												<td class="text-end fw-bold text-success"><?= number_format(($data_req_payment['jumlah'] + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']), 2) ?></td>
											</tr>
										</table>
									</td>
									<td class="text-center">
										<?php
										$get_ros = $this->db->get_where('tr_ros', ['id' => $dtl->no_doc])->row_array();
										$get_invoice = $this->db->get_where('tr_invoice_po', ['id' => $dtl->no_doc])->row_array();
										if (!empty($get_ros) && file_exists($get_ros['link_doc'])) {
											echo '<a href="' . base_url('./' . $get_ros['link_doc']) . '" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fa fa-download"></i></a>';
										} else if (!empty($get_invoice) && file_exists($get_invoice['link_doc'])) {
											echo '<a href="' . base_url('./' . $get_invoice['link_doc']) . '" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fa fa-download"></i></a>';
										} else if (file_exists('./assets/expense/' . $dtl->doc_file) && $dtl->doc_file !== '') {
											echo '<a href="' . base_url('./assets/expense/') . $dtl->doc_file . '" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fa fa-download"></i></a>';
										} else {
											echo '-';
										}
										?>
									</td>
									<td class="text-center">
										<input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="form-check-input check_item">
									</td>
								</tr>

								<?php elseif ($type == 'kasbon') :
								if ($kasbon_pr == '1') { ?>
									<tr>
										<td class="text-center"><?= $n; ?></td>

										<td><?= $dtl->keperluan; ?></td>
										<td class="text-center"><?= $dtl->tgl_doc; ?></td>
										<td class="text-center">-</td>
										<td class="text-center small"><?= $data_req_payment['currency']; ?></td>

										<td class="text-center"><a href="<?= base_url('assets/expense/') . $dtl->doc_file; ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fa fa-download"></i></a></td>
										<td class="text-center">
											<?php if ($dtl->status == '2') : ?>
												<input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="form-check-input check_item">
											<?php elseif ($dtl->status == '3') : ?><span class="badge bg-warning text-dark">Process</span>
											<?php elseif ($dtl->status == '4') : ?><span class="badge bg-success">PAID</span>
											<?php else : ?><span class="badge bg-secondary">Undefined</span><?php endif; ?>
										</td>
									</tr>
									<?php foreach ($data_detail_pr_kasbon as $detail_kasbon_pr) : ?>
										<tr class="table-light">
											<td></td>
											<td></td>
											<td><small class="text-muted"><i class="fa fa-caret-right me-1"></i> <?= $detail_kasbon_pr->nm_material ?></small></td>
											<td></td>
											<td class="text-center"><?= number_format($detail_kasbon_pr->qty) ?></td>
											<td class="text-center small"><?= $data_req_payment['currency'] ?></td>
											<td>
												<table class="w-100 inner-sub-table small">
													<tr>
														<td>Harga</td>
														<td class="text-end"><?= number_format($detail_kasbon_pr->total_harga) ?></td>
													</tr>
													<tr>
														<td>Admin Bank</td>
														<td class="text-end text-muted">+ <?= number_format($data_req_payment['admin_bank']) ?></td>
													</tr>
												</table>
											</td>
											<td></td>
											<td></td>
										</tr>
									<?php $gTotal += $detail_kasbon_pr->total_harga;
									endforeach; ?>
								<?php } else {
									$gTotal += ($dtl->jumlah_kasbon + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']); ?>
									<tr>
										<td class="text-center"><?= $n; ?></td>

										<td><?= $dtl->keperluan; ?></td>
										<td class="text-center"><?= $dtl->tgl_doc; ?></td>
										<td class="text-center">1</td>
										<td class="text-center small"><?= $data_req_payment['currency']; ?></td>
										<td>
											<table class="w-100 inner-sub-table small">
												<tr>
													<td>Pengajuan</td>
													<td class="text-end fw-semibold"><?= number_format($data_req_payment['jumlah'], 2) ?></td>
												</tr>
												<tr>
													<td>Nilai PPh</td>
													<td class="text-end text-danger">- <?= number_format($data_req_payment['total_pph'], 2) ?></td>
												</tr>
												<tr>
													<td>Bank Charge</td>
													<td class="text-end text-muted">+ <?= number_format($data_req_payment['admin_bank'], 2) ?></td>
												</tr>
												<tr class="border-top">
													<td><b>Net Payment</b></td>
													<td class="text-end fw-bold text-success"><?= number_format(($data_req_payment['jumlah'] + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']), 2) ?></td>
												</tr>
											</table>
										</td>
										<td class="text-center"><a href="<?= base_url('assets/expense/') . $dtl->doc_file; ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fa fa-download"></i></a></td>
										<td class="text-center">
											<?php if ($dtl->status == '2') : ?>
												<input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="form-check-input check_item">
											<?php elseif ($dtl->status == '3') : ?><span class="badge bg-warning text-dark">Process</span>
											<?php elseif ($dtl->status == '4') : ?><span class="badge bg-success">PAID</span>
											<?php else : ?><span class="badge bg-secondary">Undefined</span><?php endif; ?>
										</td>
									</tr>
								<?php } ?>

							<?php elseif ($type == 'transportasi') :
								$gTotal += ($dtl->jumlah_kasbon + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']); ?>
								<tr>
									<td class="text-center"><?= $n; ?></td>
									<td class="text-muted text-center small">-</td>
									<td><?= $dtl->keperluan; ?></td>
									<td class="text-center"><?= $dtl->tgl_doc; ?></td>
									<td class="text-center">1</td>
									<td class="text-center small"><?= $data_req_payment['currency']; ?></td>
									<td>
										<table class="w-100 inner-sub-table small">
											<tr>
												<td>Nilai Pengajuan</td>
												<td class="text-end fw-semibold"><?= number_format($dtl->jumlah_kasbon, 2) ?></td>
											</tr>
											<tr>
												<td>Nilai PPh</td>
												<td class="text-end text-danger">- <?= number_format($data_req_payment['total_pph'], 2) ?></td>
											</tr>
											<tr>
												<td>Bank Charge</td>
												<td class="text-end text-muted">+ <?= number_format($data_req_payment['admin_bank'], 2) ?></td>
											</tr>
											<tr class="border-top">
												<td><b>Net Payment</b></td>
												<td class="text-end fw-bold text-success"><?= number_format(($dtl->jumlah_kasbon + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']), 2) ?></td>
											</tr>
										</table>
									</td>
									<td class="text-center">
										<?php if (file_exists('./assets/expense/' . $dtl->doc_file) && $dtl->doc_file !== '') : ?>
											<a href="<?= base_url('./assets/expense/') . $dtl->doc_file; ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fa fa-download"></i></a>
										<?php else: echo '-';
										endif; ?>
									</td>
									<td class="text-center">
										<?php if ($dtl->status == '1') : ?>
											<input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="form-check-input check_item">
										<?php elseif ($dtl->status == '2') : ?><span class="badge bg-warning text-dark">Process</span>
										<?php elseif ($dtl->status == '3') : ?><span class="badge bg-success">PAID</span>
										<?php else : ?><span class="badge bg-secondary">Undefined</span><?php endif; ?>
									</td>
								</tr>

							<?php elseif ($type == 'nonpo') :
								$gTotal += ($dtl->total_request + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']); ?>
								<tr>
									<td class="text-center"><?= $n; ?></td>

									<td><?= $dtl->deskripsi; ?></td>
									<td class="text-center"><?= $dtl->tgl_pr; ?></td>
									<td class="text-center">1</td>
									<td class="text-center small"><?= $data_req_payment['currency']; ?></td>
									<td>
										<table class="w-100 inner-sub-table small">
											<tr>
												<td>Nilai Pengajuan</td>
												<td class="text-end fw-semibold"><?= number_format($dtl->total_request, 2) ?></td>
											</tr>
											<tr>
												<td>Nilai PPh</td>
												<td class="text-end text-danger">- <?= number_format($data_req_payment['total_pph'], 2) ?></td>
											</tr>
											<tr>
												<td>Bank Charge</td>
												<td class="text-end text-muted">+ <?= number_format($data_req_payment['admin_bank'], 2) ?></td>
											</tr>
											<tr class="border-top">
												<td><b>Net Payment</b></td>
												<td class="text-end fw-bold text-success"><?= number_format(($dtl->total_request + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']), 2) ?></td>
											</tr>
										</table>
									</td>
									<td class="text-center">
										<?php if (file_exists('./assets/expense/' . $dtl->doc_file) && $dtl->doc_file !== '') : ?>
											<a href="<?= base_url('./assets/expense/') . $dtl->doc_file; ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fa fa-download"></i></a>
										<?php else: echo '-';
										endif; ?>
									</td>
									<td class="text-center">
										<input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="form-check-input check_item">
									</td>
								</tr>

							<?php elseif ($type == 'periodik') :
								$gTotal += ($data_req_payment['jumlah'] + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']); ?>
								<tr>
									<td class="text-center"><?= $n; ?></td>

									<td><?= $dtl->keterangan; ?></td>
									<td class="text-center"><?= $dtl->tanggal; ?></td>
									<td class="text-center">1</td>
									<td class="text-center small"><?= $data_req_payment['currency']; ?></td>
									<td>
										<table class="w-100 inner-sub-table small">
											<tr>
												<td>Nilai Pengajuan</td>
												<td class="text-end fw-semibold"><?= number_format($data_req_payment['jumlah'], 2) ?></td>
											</tr>
											<tr>
												<td>Nilai PPh</td>
												<td class="text-end text-danger">- <?= number_format($data_req_payment['total_pph'], 2) ?></td>
											</tr>
											<tr>
												<td>Bank Charge</td>
												<td class="text-end text-muted">+ <?= number_format($data_req_payment['admin_bank'], 2) ?></td>
											</tr>
											<tr class="border-top">
												<td><b>Net Payment</b></td>
												<td class="text-end fw-bold text-success"><?= number_format(($data_req_payment['jumlah'] + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']), 2) ?></td>
											</tr>
										</table>
									</td>
									<td class="text-center">
										<?php if (file_exists('./assets/bayar_rutin/' . $dtl->doc_file) && $dtl->doc_file !== '') : ?>
											<a href="<?= base_url('./assets/bayar_rutin/') . $dtl->doc_file; ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fa fa-download"></i></a>
										<?php else: echo '-';
										endif; ?>
									</td>
									<td class="text-center">
										<input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="form-check-input check_item">
									</td>
								</tr>
							<?php endif;

							// TIPE 6: DIRECT PAYMENT
							if ($type == 'direct_payment') { ?>
								<tr>
									<td class="text-center"><?= $n; ?></td>

									<td><?= $dtl->deskripsi; ?></td>
									<td class="text-center"><?= $dtl->tgl_doc; ?></td>
									<td class="text-end fw-semibold"><?= number_format($dtl->grand_total, 2) ?></td>
									<td class="text-center small"><?= $data_req_payment['currency']; ?></td>
									<td class="text-end fw-bold text-success"><?= number_format($dtl->grand_total, 2) ?></td>
									<td class="text-center"><a href="<?= base_url('assets/expense/') . $data_req_payment['link_doc']; ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fa fa-download"></i></a></td>
									<td class="text-center">
										<?php if ($dtl->sts == '2') : ?>
											<input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="form-check-input check_item">
										<?php elseif ($dtl->sts == '3') : ?><span class="badge bg-warning text-dark">Process</span>
										<?php elseif ($dtl->sts == '4') : ?><span class="badge bg-success">PAID</span>
										<?php else : ?><span class="badge bg-secondary">Undefined</span><?php endif; ?>
									</td>
								</tr>
							<?php $gTotal += $dtl->grand_total;
							}

							// TIPE 7: INVOICE PO (DP/IMPORT/LOCAL)
							if (in_array($type, ['invoice_dp', 'invoice_import', 'invoice_local'])) {
								$kurs_val = (float)($dtl->kurs ?? 1);
								if ($kurs_val <= 0) $kurs_val = 1;

								$nilai_invoice = ($type == 'invoice_dp')
									? ((float)($dtl->nilai_invoice ?? 0) + (float)($dtl->nilai_ppn ?? 0)) * $kurs_val
									: ((float)($dtl->nilai_invoice ?? 0) + (float)($dtl->nilai_ppn ?? 0)) * $kurs_val;

								$gTotal += $nilai_invoice;
							?>
								<tr>
									<td class="text-center"><?= $n; ?></td>

									<td><?= $data_req_payment['keperluan'] ?? ($dtl->nomor_invoice ?? '-'); ?></td>
									<td class="text-center"><?= $dtl->invoice_date; ?></td>
									<td class="text-center">1</td>
									<td class="text-center small"><?= $dtl->currency ?? 'IDR'; ?></td>
									<td>
										<table class="table table-sm mb-0 w-100 small inner-sub-table">
											<?php if ($type == 'invoice_dp') : ?>
												<tr>
													<td>Value DP</td>
													<td class="text-center" style="width:10px">:</td>
													<td class="text-end fw-semibold"><?= number_format($dtl->jumlah_rupiah ?? 0, 2) ?></td>
												</tr>
											<?php else : ?>
												<tr>
													<td>Sisa Nilai</td>
													<td class="text-center" style="width:10px">:</td>
													<td class="text-end fw-semibold"><?= number_format($dtl->sisa_nilai ?? 0, 2) ?></td>
												</tr>
											<?php endif; ?>
											<tr>
												<td>PPN</td>
												<td class="text-center">:</td>
												<td class="text-end"><?= number_format($dtl->nilai_ppn ?? 0, 2) ?></td>
											</tr>
											<tr>
												<td>Kurs</td>
												<td class="text-center">:</td>
												<td class="text-end"><?= number_format($kurs_val, 2) ?></td>
											</tr>
											<tr class="fw-bold">
												<td>Total (IDR)</td>
												<td class="text-center">:</td>
												<td class="text-end text-success"><?= number_format($nilai_invoice, 2) ?></td>
											</tr>
										</table>
									</td>
									<td class="text-center">
										<?php if (!empty($dtl->file_invoice)) : ?>
											<?php
											$file_path_dp = FCPATH . 'uploads/invoice_dp/' . $dtl->file_invoice;
											$file_path_il = FCPATH . 'uploads/invoice_il/' . $dtl->file_invoice;
											if (file_exists($file_path_dp)) : ?>
												<a href="<?= base_url('uploads/invoice_dp/' . $dtl->file_invoice); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa fa-download"></i></a>
											<?php elseif (file_exists($file_path_il)) : ?>
												<a href="<?= base_url('uploads/invoice_il/' . $dtl->file_invoice); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa fa-download"></i></a>
											<?php else : ?>
												-
											<?php endif; ?>
										<?php else : ?>
											-
										<?php endif; ?>
									</td>
									<td class="text-center">
										<input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="form-check-input check_item">
									</td>
								</tr>
					<?php }
						endforeach;
					}  ?>
				</tbody>
				<tfoot>
					<tr class="table-primary align-middle fw-bold">
						<td colspan="6" class="text-end text-uppercase fs-6">Grand Total :</td>
						<td class="text-end fs-5"><?= number_format($gTotal, 2); ?></td>
						<td colspan="2"></td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

<div class="row g-3">
	<div class="col-12 col-md-5">
		<div class="card border-0 shadow-sm">
			<div class="card-body p-3">
				<span class="d-block fw-bold text-dark mb-2 small"><i class="fa fa-university me-1"></i> Informasi Rekening Penerima Transfer :</span>
				<table class="table table-sm table-bordered m-0 small align-middle">
					<tr>
						<td class="bg-light text-muted fw-semibold ps-2" style="width: 35%;">Nama Bank</td>
						<td class="ps-2 fw-bold"><?= $bank_id ?></td>
					</tr>
					<tr>
						<td class="bg-light text-muted fw-semibold ps-2">Nomor Rekening</td>
						<td class="ps-2 fw-mono text-primary"><?= $accnumber ?></td>
					</tr>
					<tr>
						<td class="bg-light text-muted fw-semibold ps-2">Nama Pemilik Akun</td>
						<td class="ps-2"><?= $accname ?></td>
					</tr>
				</table>
			</div>
		</div>
	</div>

	<div class="col-12 col-md-7 d-flex align-items-end justify-content-end gap-2 mb-4">
		<a href="<?= base_url($this->uri->segment(1) . '/list_approve_management'); ?>" class="btn btn-secondary px-3"><i class="fa fa-reply me-1"></i> Kembali</a>
		<!-- <button type="button" class="btn btn-danger px-3" id="reject"><i class="fa fa-close me-1"></i> Tolak (Reject)</button> -->
		<button type="button" class="btn btn-success px-4" id="process"><i class="fa fa-save me-1"></i> Setujui (Approve)</button>
	</div>
</div>

<?= form_close() ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">
	var url_save = siteurl + 'request_payment/save_approval';
	var url_reject = siteurl + 'request_payment/reject_approval';

	if (typeof $.fn.divide !== 'undefined') {
		$('.divide').divide();
	}

	// Event Handler Master Checkbox (Pilih Semua)
	$(document).on('click', '.master_check', function() {
		$('.check_item').prop('checked', $(this).is(':checked'));
	});

	$(document).on('click', '.check_item', function() {
		// Hitung total checkbox item yang ada
		var total_checkbox = $('.check_item').length;
		// Hitung berapa checkbox item yang sedang di-centang
		var total_checked = $('.check_item:checked').length;

		// Jika jumlah yang dicentang sama dengan total item, set master_check jadi true (checked)
		if (total_checkbox === total_checked) {
			$('.master_check').prop('checked', true);
		} else {
			$('.master_check').prop('checked', false);
		}
	});

	// Validasi & Eksekusi Aksi APPROVE (PROCESS)
	$(document).on('click', '#process', function(e) {
		if ($("#bank_coa").val() == "0") {
			Swal.fire({
				icon: 'warning',
				title: 'Perhatian!',
				text: 'Bank tidak boleh kosong'
			});
			return false;
		}

		if (!$('.check_item').is(':checked')) {
			Swal.fire({
				icon: 'warning',
				title: 'Pilih Item!',
				text: 'Silakan pilih minimal satu item yang akan disetujui!'
			});
			return false;
		}

		Swal.fire({
			title: 'Anda Yakin?',
			text: 'Seluruh item terpilih akan disetujui dalam sistem!',
			icon: 'info',
			showCancelButton: true,
			confirmButtonColor: '#198754',
			confirmButtonText: 'Ya, Setujui!',
			cancelButtonText: 'Batal'
		}).then((result) => {
			if (result.isConfirmed) {
				var formdata = new FormData($('#frm_data')[0]);
				$.ajax({
					url: url_save,
					dataType: "json",
					type: 'POST',
					data: formdata,
					processData: false,
					contentType: false,
					success: function(msg) {
						if (msg['save'] == '1') {
							Swal.fire({
								icon: 'success',
								title: 'Sukses!',
								text: 'Data pengajuan berhasil disetujui',
								timer: 1500,
								showConfirmButton: false
							});
							window.location.href = siteurl + active_controller + 'list_approve_management';
						} else {
							Swal.fire({
								icon: 'error',
								title: 'Gagal!',
								text: 'Sistem gagal menyetujui pengajuan.'
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error AJAX!',
							text: 'Gagal menghubungi server, silakan hubungi tim IT.'
						});
					}
				});
			}
		});
	});

	// Validasi & Eksekusi Aksi REJECT
	$(document).on('click', '#reject', function(e) {
		if (!$('.check_item').is(':checked')) {
			Swal.fire({
				icon: 'warning',
				title: 'Pilih Item!',
				text: 'Silakan pilih minimal satu item yang akan ditolak!'
			});
			return false;
		}

		var reject_reason = $.trim($('.reject_reason').val());
		if (reject_reason === '') {
			Swal.fire({
				icon: 'warning',
				title: 'Alasan Kosong!',
				text: 'Kolom Alasan Penolakan wajib diisi untuk melakukan penolakan data!'
			});
			$('.reject_reason').focus();
			return false;
		}

		Swal.fire({
			title: 'Anda Yakin Menolak?',
			text: 'Item terpilih akan dikembalikan dengan status ditolak!',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#dc3545',
			confirmButtonText: 'Ya, Tolak!',
			cancelButtonText: 'Batal'
		}).then((result) => {
			if (result.isConfirmed) {
				var formdata = new FormData($('#frm_data')[0]);
				$.ajax({
					url: url_reject,
					dataType: "json",
					type: 'POST',
					data: formdata,
					processData: false,
					contentType: false,
					success: function(msg) {
						if (msg['save'] == '1') {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil!',
								text: 'Data pengajuan telah ditolak.',
								timer: 1500,
								showConfirmButton: false
							});
							window.location.href = siteurl + active_controller + 'list_approve_management';
						} else {
							Swal.fire({
								icon: 'error',
								title: 'Gagal!',
								text: 'Gagal memproses penolakan.'
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error AJAX!',
							text: 'Gagal memproses pengajuan melalui server.'
						});
					}
				});
			}
		});
	});
</script>