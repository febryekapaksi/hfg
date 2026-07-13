<div class="row g-3">

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nomor PO</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['no_surat'] ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nama Supplier</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['nm_supplier'] ?? '-' ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nomor Invoice</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['nomor_invoice'] ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Receive Invoice Date</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= date('d F Y', strtotime($data['invoice_date'])) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Invoice Date</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= !empty($data['invoice_date_real']) ? date('d F Y', strtotime($data['invoice_date_real'])) : '-' ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Persentase DP (%)</label>
        <input type="text" class="form-control form-control-sm text-end"
            value="<?= number_format($data['persen_dp'], 2) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">DPP</label>
        <input type="text" class="form-control form-control-sm text-end"
            value="<?= number_format($data['dpp'], 4) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Value DP</label>
        <input type="text" class="form-control form-control-sm text-end"
            value="<?= number_format($data['value_dp'], 4) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nilai PPN</label>
        <input type="text" class="form-control form-control-sm text-end"
            value="<?= number_format($data['nilai_ppn'], 2) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nilai Disc</label>
        <input type="text" class="form-control form-control-sm text-end"
            value="<?= number_format($data['nilai_disc'], 2) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Currency</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['matauang'] ?? 'IDR' ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Kurs</label>
        <input type="text" class="form-control form-control-sm text-end"
            value="<?= number_format($data['kurs'], 2) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nomor Faktur Pajak</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['nomor_faktur_pajak'] ?? '-' ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Tanggal Faktur Pajak</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= !empty($data['tanggal_faktur_pajak']) ? date('d F Y', strtotime($data['tanggal_faktur_pajak'])) : '-' ?>" readonly>
    </div>

    <?php if (!empty($data['file_invoice'])): ?>
    <div class="col-12">
        <label class="form-label fw-semibold">File Invoice</label><br>
        <a href="<?= base_url('uploads/invoice_dp/' . $data['file_invoice']) ?>"
            target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-file"></i> Lihat File
        </a>
    </div>
    <?php endif; ?>

    <div class="col-12"><hr class="my-1"><p class="fw-bold mb-2">Informasi Bank</p></div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Bank</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['bank'] ?>" readonly>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">No. Rekening</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['no_bank'] ?>" readonly>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Nama Pemilik</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['nm_acc_bank'] ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Status Payment</label><br>
        <?php
        if (empty($data['no_payment'])) {
            echo '<span class="badge bg-warning text-dark">Menunggu Pembayaran</span>';
        } elseif ($data['status_payment'] == 'payment') {
            echo '<span class="badge bg-success">Lunas</span>';
        } else {
            echo '<span class="badge bg-info">Dalam Proses</span>';
        }
        ?>
    </div>
    <?php if (!empty($data['no_payment'])): ?>
    <div class="col-md-6">
        <label class="form-label fw-semibold">No. Payment</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['no_payment'] ?>" readonly>
    </div>
    <?php endif; ?>

</div>