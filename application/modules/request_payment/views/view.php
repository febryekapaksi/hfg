<style>
    .bank-transfer-box {
        background-color: #f8f9fa;
        border-radius: 0.375rem;
    }
</style>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        
        <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2">
            <i class="fa fa-file-text-o me-2"></i>Informasi PO & Supplier
        </h6>
        
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-muted">Nomor PO</label>
                <input type="text" name="nomor_po" class="form-control bg-light nomor_po" value="<?= $data_invoice['id'] ?>" readonly>
            </div>
            
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-muted">Nama Supplier</label>
                <input type="text" name="nm_supplier" class="form-control bg-light" value="<?= $data_invoice['nm_supplier'] ?>" readonly>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-muted">Receive Invoice Date</label>
                <input type="date" name="invoice_date" class="form-control bg-light" value="<?= $data_invoice['invoice_date'] ?>" readonly>
            </div>
            
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-muted">Invoice Date Real</label>
                <input type="date" name="invoice_date_real" class="form-control bg-light invoice_date_real" value="<?= $data_invoice['invoice_date_real'] ?>" readonly>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-muted">Nomor Invoice</label>
                <input type="text" name="nomor_invoice" class="form-control bg-light nomor_invoice" value="<?= $data_invoice['invoice_no'] ?>" readonly>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-muted">Nomor Faktur Pajak</label>
                <input type="text" name="nomor_faktur_pajak" class="form-control bg-light nomor_faktur_pajak" value="<?= $data_invoice['no_faktur_pajak'] ?>" readonly>
            </div>
            
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-muted">Tanggal Faktur Pajak</label>
                <input type="date" name="tanggal_faktur_pajak" class="form-control bg-light tanggal_faktur_pajak" value="<?= $data_invoice['tanggal_faktur_pajak'] ?>" readonly>
            </div>
        </div>


        <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2">
            <i class="fa fa-calculator me-2"></i>Rincian Nilai & Dokumen
        </h6>
        
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-muted">Currency</label>
                <input type="text" name="currency" class="form-control bg-light" value="<?= $data_invoice['curr'] ?>" readonly>
            </div>
            
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-muted">Kurs</label>
                <input type="text" name="kurs" class="form-control bg-light text-end auto_num" value="<?= number_format($data_invoice['kurs'], 2) ?>" readonly>
            </div>
            
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-muted">Persentase DP</label>
                <div class="input-group">
                    <input type="text" name="persen_dp" class="form-control bg-light text-end persen_dp" value="<?= number_format($data_invoice['persen_dp'], 2) ?>" readonly>
                    <span class="input-group-text bg-light small">%</span>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-muted">DPP (Total Pembelian)</label>
                <input type="text" name="total_pembelian" class="form-control bg-light text-end total_pembelian" value="<?= number_format($data_invoice['total_pembelian'], 2) ?>" readonly>
            </div>
            
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-muted">Nilai Disc</label>
                <input type="text" name="nilai_disc" class="form-control bg-light text-end auto_num nilai_disc" value="<?= number_format($nilai_disc, 2) ?>" readonly>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-muted">Nilai PPN</label>
                <input type="text" name="nilai_ppn" class="form-control bg-light text-end auto_num nilai_ppn" value="<?= number_format($nilai_ppn, 2) ?>" readonly>
            </div>
            
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-muted">Value DP</label>
                <input type="text" name="value_dp" class="form-control bg-light text-end fw-bold text-success value_dp" value="<?= number_format($nilai_top, 2) ?>" readonly>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-muted">Upload Invoice</label>
                <div class="input-group">
                    <input type="file" name="upload_invoice" class="form-control form-control-sm upload_invoice">
                    <?php if(!empty($data_invoice['link_doc']) && file_exists($data_invoice['link_doc'])): ?>
                        <a href="<?= base_url($data_invoice['link_doc']) ?>" class="btn btn-sm btn-primary" target="_blank">
                            <i class="fa fa-download"></i> Download
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>


        <div class="bank-transfer-box p-3 border">
            <span class="d-block fw-bold text-dark mb-3 small">
                <i class="fa fa-university me-1"></i> Informasi Bank Transfer Vendor
            </span>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label text-muted m-0 small">Bank</label>
                    <input type="text" name="bank" class="form-control bg-white" value="<?= $data_invoice['bank'] ?>" readonly>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label text-muted m-0 small">No. Rekening</label>
                    <input type="text" name="no_bank" class="form-control bg-white" value="<?= $data_invoice['no_bank'] ?>" readonly>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label text-muted m-0 small">Atas Nama (Acc)</label>
                    <input type="text" name="nm_acc_bank" class="form-control bg-white" value="<?= $data_invoice['nm_acc_bank'] ?>" readonly>
                </div>
            </div>
        </div>

    </div>
</div>