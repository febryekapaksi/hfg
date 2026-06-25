<?php
$is_view = ($mode === 'view');
$ro      = $is_view ? 'readonly' : '';

// Normalisasi variabel ke 1 sumber supaya field tidak perlu if/else tiap baris
if ($is_view) {
    $no_surat    = $data['no_surat'];
    $nm_supplier = $data['nm_supplier'] ?? '-';
    $tipe        = $data['tipe'];
    $currency    = $data['matauang'] ?? 'IDR';
    $id_top      = $data['id_top'];
    $no_po       = $data['no_po'];
    $id_dp       = $data['id_dp'] ?? '';

    // nilai finansial
    $dpp_full  = $data['dpp'];   // di view, dpp sudah berisi sisa (yang disimpan)
    $sisa_dpp  = $data['dpp'];
    $nilai_dp  = $data['nilai_dp']  ?? 0;
    $nilai_ppn = $data['nilai_ppn'] ?? 0;
    $nilai_disc = $data['nilai_disc'] ?? 0;
    $sisa_nilai = $data['sisa_nilai'] ?? 0;
    $kurs      = $data['kurs']      ?? 0;

    // info DP sebelumnya (dari join)
    $data_dp = !empty($data['no_invoice_dp']) ? [
        'nomor_invoice' => $data['no_invoice_dp'],
        'value_dp'      => $data['nilai_dp'] ?? 0,
        'persen_dp'     => $data['persen_dp'] ?? 0,
    ] : null;

    // field form
    $nomor_invoice       = $data['nomor_invoice'];
    $invoice_date        = $data['invoice_date'];
    $invoice_date_real   = $data['invoice_date_real'] ?? '';
    $nomor_faktur_pajak  = $data['nomor_faktur_pajak'] ?? '';
    $tgl_faktur_pajak    = $data['tanggal_faktur_pajak'] ?? '';
    $bank                = $data['bank'];
    $no_bank             = $data['no_bank'];
    $nm_acc_bank         = $data['nm_acc_bank'];
    $file_invoice        = $data['file_invoice'] ?? null;
    $no_payment          = $data['no_payment'] ?? null;
    $status_payment      = $data['status_payment'] ?? null;
    $keterangan_top      = $data['keterangan_top'] ?? '-';
} else {
    $no_surat    = $data_po['no_surat'];
    $nm_supplier = $get_supplier['nama'] ?? '-';
    $currency    = $data_po['matauang'] ?? 'IDR';
    $id_top      = $data_po['id_top'];
    $no_po       = $data_po['no_po'];
    // $tipe, $id_dp, $data_dp, $dpp_full, $sisa_dpp, $nilai_dp, $nilai_ppn, $nilai_disc
    // sudah di-set dari controller form_il()

    $sisa_nilai        = $sisa_dpp + $nilai_ppn - $nilai_disc;
    $nomor_invoice     = '';
    $invoice_date      = '';
    $invoice_date_real = '';
    $nomor_faktur_pajak = '';
    $tgl_faktur_pajak  = '';
    $bank = $no_bank = $nm_acc_bank = '';
    $file_invoice = $no_payment = $status_payment = null;
    $kurs = 0;
}
?>

<input type="hidden" name="tipe_req" value="<?= $tipe ?>">
<input type="hidden" name="id_top" value="<?= $id_top ?>">
<input type="hidden" name="no_po" value="<?= $no_po ?>">
<input type="hidden" name="no_surat" value="<?= $no_surat ?>">
<input type="hidden" name="id_dp" value="<?= $id_dp ?? '' ?>">

<div class="row g-3">

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nomor PO</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $no_surat ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nama Supplier</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $nm_supplier ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Tipe</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= ucfirst($tipe) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Currency</label>
        <input type="text" name="currency" class="form-control form-control-sm"
            value="<?= $currency ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">
            Receive Invoice Date <?php if (!$is_view): ?><span class="text-danger">*</span><?php endif; ?>
        </label>
        <input type="text" name="invoice_date"
            class="form-control form-control-sm <?= !$is_view ? 'fp-date' : '' ?>"
            value="<?= !empty($invoice_date) ? date('d M Y', strtotime($invoice_date)) : '' ?>"
            placeholder="<?= !$is_view ? 'Pilih tanggal...' : '' ?>"
            <?= $ro ?> <?= !$is_view ? 'required' : '' ?>>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Invoice Date</label>
        <input type="text" name="invoice_date_real"
            class="form-control form-control-sm <?= !$is_view ? 'fp-date' : '' ?>"
            value="<?= !empty($invoice_date_real) ? date('d M Y', strtotime($invoice_date_real)) : '' ?>"
            placeholder="<?= !$is_view ? 'Pilih tanggal...' : '' ?>"
            <?= $ro ?>>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">
            Nomor Invoice <?php if (!$is_view): ?><span class="text-danger">*</span><?php endif; ?>
        </label>
        <input type="text" name="nomor_invoice" class="form-control form-control-sm"
            value="<?= $nomor_invoice ?>"
            placeholder="<?= !$is_view ? 'Nomor Invoice' : '' ?>"
            <?= $ro ?> <?= !$is_view ? 'required' : '' ?>>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">
            Kurs
            <?php if (!$is_view && $currency !== 'IDR'): ?>
                <span class="text-danger">*</span>
            <?php endif; ?>
        </label>
        <input type="text" name="kurs"
            class="form-control form-control-sm text-end <?= !$is_view ? 'auto_num' : '' ?>"
            value="<?= $kurs > 0 ? number_format($kurs, 2) : '' ?>"
            placeholder="<?= (!$is_view && $currency !== 'IDR') ? 'Wajib diisi' : '0' ?>"
            <?= $ro ?>>
    </div>

    <?php if (!empty($data_dp)): ?>
        <div class="col-12">
            <div class="alert alert-info py-2 mb-0">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted d-block">No. Invoice DP</small>
                        <strong><?= $data_dp['nomor_invoice'] ?></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Nilai DP</small>
                        <strong><?= $currency ?> <?= number_format($data_dp['value_dp'], 2) ?></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Persentase DP</small>
                        <strong><?= number_format($data_dp['persen_dp'], 2) ?>%</strong>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-md-6">
        <label class="form-label fw-semibold">
            DPP Total
            <?php if (!empty($data_dp) && !$is_view): ?>
                <span class="text-muted fw-normal">(sebelum dikurangi DP)</span>
            <?php endif; ?>
        </label>
        <input type="text" class="form-control form-control-sm text-end"
            value="<?= number_format($dpp_full, 2) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">
            <?= (!empty($data_dp)) ? 'Sisa DPP (setelah DP)' : 'DPP' ?>
        </label>
        <input type="text" name="dpp"
            class="form-control form-control-sm text-end <?= !empty($data_dp) ? 'bg-warning-subtle' : '' ?>"
            value="<?= number_format($sisa_dpp, 2) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nilai DP</label>
        <input type="text" class="form-control form-control-sm text-end"
            value="<?= number_format($nilai_dp, 2) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nilai PPN</label>
        <input type="text" name="nilai_ppn" class="form-control form-control-sm text-end"
            value="<?= number_format($nilai_ppn, 2) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nilai Disc</label>
        <input type="text" name="nilai_disc" class="form-control form-control-sm text-end"
            value="<?= number_format($nilai_disc, 2) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Sisa Nilai</label>
        <input type="text" name="sisa_nilai" class="form-control form-control-sm text-end"
            value="<?= number_format($sisa_nilai, 2) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nomor Faktur Pajak</label>
        <input type="text" name="nomor_faktur_pajak" class="form-control form-control-sm"
            value="<?= $nomor_faktur_pajak ?>"
            placeholder="<?= !$is_view ? 'Masukkan nomor faktur pajak' : '' ?>"
            <?= $ro ?>>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Tanggal Faktur Pajak</label>
        <input type="text" name="tanggal_faktur_pajak"
            class="form-control form-control-sm <?= !$is_view ? 'fp-date' : '' ?>"
            value="<?= !empty($tgl_faktur_pajak) ? date('d M Y', strtotime($tgl_faktur_pajak)) : '' ?>"
            placeholder="<?= !$is_view ? 'Pilih tanggal...' : '' ?>"
            <?= $ro ?>>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Upload Invoice</label>
        <?php if ($is_view && !empty($file_invoice)): ?>
            <br>
            <a href="<?= base_url('uploads/invoice_il/' . $file_invoice) ?>"
                target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="fa fa-file"></i> Lihat File
            </a>
        <?php elseif ($is_view): ?>
            <input type="text" class="form-control form-control-sm" value="-" readonly>
        <?php else: ?>
            <input type="file" name="upload_invoice" class="form-control form-control-sm"
                accept=".pdf,.jpg,.jpeg,.png">
        <?php endif; ?>
    </div>

    <?php if ($is_view): ?>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Status Payment</label><br>
            <?php
            if (empty($no_payment)) {
                echo '<span class="badge bg-warning text-dark">Menunggu Pembayaran</span>';
            } elseif ($status_payment == 2) {
                echo '<span class="badge bg-success">Lunas</span>';
            } else {
                echo '<span class="badge bg-info text-dark">Dalam Proses</span>';
            }
            ?>
        </div>
        <?php if (!empty($no_payment)): ?>
            <div class="col-md-6">
                <label class="form-label fw-semibold">No. Payment</label>
                <input type="text" class="form-control form-control-sm"
                    value="<?= $no_payment ?>" readonly>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="col-12">
        <hr class="my-1">
        <p class="fw-bold mb-2">Informasi Bank</p>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">
            Bank <?php if (!$is_view): ?><span class="text-danger">*</span><?php endif; ?>
        </label>
        <input type="text" name="bank" class="form-control form-control-sm"
            value="<?= $bank ?>"
            placeholder="<?= !$is_view ? 'Nama Bank' : '' ?>"
            <?= $ro ?> <?= !$is_view ? 'required' : '' ?>>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">
            No. Rekening <?php if (!$is_view): ?><span class="text-danger">*</span><?php endif; ?>
        </label>
        <input type="text" name="no_bank" class="form-control form-control-sm"
            value="<?= $no_bank ?>"
            placeholder="<?= !$is_view ? 'Nomor Rekening' : '' ?>"
            <?= $ro ?> <?= !$is_view ? 'required' : '' ?>>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">
            Nama Pemilik <?php if (!$is_view): ?><span class="text-danger">*</span><?php endif; ?>
        </label>
        <input type="text" name="nm_acc_bank" class="form-control form-control-sm"
            value="<?= $nm_acc_bank ?>"
            placeholder="<?= !$is_view ? 'Nama Pemilik Rekening' : '' ?>"
            <?= $ro ?> <?= !$is_view ? 'required' : '' ?>>
    </div>

</div>

<?php if (!$is_view): ?>
    <script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
    <script>
        $(document).ready(function() {
            flatpickr('.fp-date', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd M Y',
                allowInput: false,
                locale: 'id'
            });
            $('.auto_num').autoNumeric('init', {
                aSep: ',',
                aDec: '.',
                mDec: 2
            });
        });
    </script>
<?php endif; ?>