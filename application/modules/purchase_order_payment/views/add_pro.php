<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<input type="hidden" name="tipe_req" value="dp">
<input type="hidden" name="id_top" value="<?= $id_top ?>">
<input type="hidden" name="no_po" value="<?= $data_po['no_po'] ?>">
<input type="hidden" name="no_incoming" value="">

<div class="row g-3">

    <!-- Info PO -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nomor PO</label>
        <input type="text" name="nomor_po" class="form-control form-control-sm nomor_po"
            value="<?= $data_po['no_surat'] ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nama Supplier</label>
        <input type="text" name="nm_supplier" class="form-control form-control-sm"
            value="<?= $get_supplier['nama'] ?>" readonly>
    </div>

    <!-- Tanggal -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">Receive Invoice Date <span class="text-danger">*</span></label>
        <input type="text" name="invoice_date" class="form-control form-control-sm fp-date"
            placeholder="Pilih tanggal..." required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Invoice Date</label>
        <input type="text" name="invoice_date_real" class="form-control form-control-sm fp-date"
            placeholder="Pilih tanggal...">
    </div>

    <!-- Nomor Invoice & Persentase -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nomor Invoice <span class="text-danger">*</span></label>
        <input type="text" name="nomor_invoice" class="form-control form-control-sm" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Persentase Progress (%)</label>
        <input type="number" name="persen_dp" class="form-control form-control-sm persen_dp"
            step="0.01" value="<?= number_format($get_top->progress, 2) ?>" readonly>
    </div>

    <!-- DPP & Value Retensi -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">DPP</label>
        <input type="text" name="total_pembelian" class="form-control form-control-sm text-end total_pembelian"
            value="<?= number_format((($get_total_po['ttl_po'] - $nilai_disc) * $get_top->progress / 100), 2) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Value Progress</label>
        <input type="text" name="value_dp" class="form-control form-control-sm text-end value_dp"
            value="<?= number_format($get_top->nilai) ?>" readonly>
    </div>

    <!-- PPN & Disc -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nilai PPN</label>
        <input type="text" name="nilai_ppn" class="form-control form-control-sm text-end auto_num nilai_ppn"
            value="<?= number_format($nilai_ppn, 2) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nilai Disc</label>
        <input type="text" name="nilai_disc" class="form-control form-control-sm text-end auto_num nilai_disc"
            value="<?= number_format($nilai_disc, 2) ?>" readonly>
    </div>

    <!-- Currency & Kurs -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">Currency</label>
        <input type="text" name="currency" class="form-control form-control-sm"
            value="<?= $data_po['matauang'] ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Kurs</label>
        <input type="text" name="kurs" class="form-control form-control-sm text-end auto_num"
            placeholder="0">
    </div>

    <!-- Faktur Pajak -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nomor Faktur Pajak</label>
        <input type="text" name="nomor_faktur_pajak" class="form-control form-control-sm">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Tanggal Faktur Pajak</label>
        <input type="text" name="tanggal_faktur_pajak" class="form-control form-control-sm fp-date"
            placeholder="Pilih tanggal...">
    </div>

    <!-- Upload -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">Upload Invoice</label>
        <input type="file" name="upload_invoice" class="form-control form-control-sm">
    </div>

    <!-- Info Bank -->
    <div class="col-12">
        <hr class="my-1">
        <p class="fw-bold mb-2">Informasi Bank</p>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Bank</label>
        <input type="text" name="bank" class="form-control form-control-sm" placeholder="Nama Bank">
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">No. Rekening</label>
        <input type="text" name="no_bank" class="form-control form-control-sm" placeholder="Nomor Rekening">
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Nama Pemilik</label>
        <input type="text" name="nm_acc_bank" class="form-control form-control-sm" placeholder="Nama Pemilik Rekening">
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
    $(document).ready(function() {
        $('.auto_num').autoNumeric('init');

        // Init flatpickr
        $('#dialog-popup .fp-date').flatpickr({
            dateFormat: 'Y-m-d',
            allowInput: true
        });
    });

    $(document).on('change', '.persen_dp', function() {
        var total = parseFloat($('.total_pembelian').val().replace(/,/g, '') || 0);
        var persen = parseFloat($(this).val() || 0);
        $('.value_dp').val((total * persen / 100).toLocaleString());
    });

    $(document).on('change', '.value_dp', function() {
        var total = parseFloat($('.total_pembelian').val().replace(/,/g, '') || 0);
        var value = parseFloat($(this).val().replace(/,/g, '') || 0);
        $('.value_dp').val(value.toLocaleString());
        $('.persen_dp').val(((value / total) * 100).toFixed(2));
    });
</script>