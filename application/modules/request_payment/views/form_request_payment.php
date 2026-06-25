<input type="hidden" name="id_receive"  value="<?= $data_receive['id'] ?>">
<input type="hidden" name="tipe_rp"     value="<?= $tipe_rp ?>">
<input type="hidden" name="tipe"        value="<?= $tipe ?>">

<div class="row g-3">

    {{-- Info Invoice --}}
    <div class="col-12">
        <div class="alert alert-light border mb-0 py-2">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted d-block">Tipe</small>
                    <strong><?= $tipe_label ?></strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Nomor PO</small>
                    <strong><?= $data_receive['no_surat'] ?></strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Supplier</small>
                    <strong><?= $get_supplier['nama'] ?? '-' ?></strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">No. Invoice</small>
                    <strong><?= $data_receive['nomor_invoice'] ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nilai Pembayaran</label>
        <input type="text" name="jumlah" class="form-control form-control-sm text-end"
            value="<?= number_format($jumlah, 2) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Total + PPN</label>
        <input type="text" name="jumlah_total" class="form-control form-control-sm text-end"
            value="<?= number_format($jumlah_total, 2) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Currency</label>
        <input type="text" name="currency" class="form-control form-control-sm"
            value="<?= $data_receive['currency'] ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Kurs</label>
        <input type="text" name="kurs" class="form-control form-control-sm text-end"
            value="<?= number_format($data_receive['kurs'], 2) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Bank</label>
        <input type="text" name="bank_id" class="form-control form-control-sm"
            value="<?= $data_receive['bank'] ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">No. Rekening</label>
        <input type="text" name="accnumber" class="form-control form-control-sm"
            value="<?= $data_receive['no_bank'] ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nama Pemilik Rekening</label>
        <input type="text" name="accname" class="form-control form-control-sm"
            value="<?= $data_receive['nm_acc_bank'] ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Tanggal Rencana Pembayaran <span class="text-danger">*</span></label>
        <input type="text" name="tanggal" id="tanggal_rencana"
            class="form-control form-control-sm fp-date" placeholder="Pilih tanggal..." required>
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Keperluan</label>
        <input type="text" name="keperluan" class="form-control form-control-sm"
            value="Pembayaran <?= $tipe_label ?> - <?= $data_receive['no_surat'] ?> - <?= $data_receive['nomor_invoice'] ?>">
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Upload Dokumen</label>
        <input type="file" name="link_doc" class="form-control form-control-sm"
            accept=".pdf,.jpg,.jpeg,.png">
    </div>

</div>

<script>
$(document).ready(function () {
    flatpickr('#tanggal_rencana', {
        dateFormat  : 'Y-m-d',
        altInput    : true,
        altFormat   : 'd M Y',
        allowInput  : false,
        locale      : 'id'
    });
});
</script>