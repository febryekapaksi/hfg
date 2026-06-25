<?php
$is_view = ($mode === 'view');
$ro      = $is_view ? 'readonly' : '';

if ($is_view) {
    $no_surat    = $data['no_surat'];
    $nm_supplier = $data['nm_supplier'] ?? '-';
    $currency    = $data['currency'] ?? 'IDR';
    $id_top      = $data['id_top'] ?? '';
    $no_po       = $data['no_po'];
    $id_dp       = $data['id_dp'] ?? '';
    $id_ros_val  = $data['id_ros'] ?? '';
    $sisa_tagihan_val = (float)($data['sisa_nilai'] ?? 0);
    $kurs        = (float)($data['kurs'] ?? 0);
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
    $total_dp_rupiah_val = 0;
} else {
    $no_surat    = $data_po['no_surat'];
    $nm_supplier = $get_supplier['nama'] ?? '-';
    $currency_val = $currency ?? 'IDR';
    $id_top_val  = $id_top ?? '';
    $no_po       = $data_po['no_po'];
    $id_dp_val   = $id_dp ?? '';
    $id_ros_val  = $id_ros ?? '';
    $sisa_tagihan_val = $sisa_tagihan ?? 0;
    $total_dp_rupiah_val = $total_dp_rupiah ?? 0;
    $kurs        = 0;
    $nomor_invoice = '';
    $invoice_date = '';
    $invoice_date_real = '';
    $nomor_faktur_pajak = '';
    $tgl_faktur_pajak = '';
    $bank = $no_bank = $nm_acc_bank = '';
    $file_invoice = $no_payment = $status_payment = null;
}
?>

<input type="hidden" name="tipe_req" value="<?= $tipe ?>">
<input type="hidden" name="id_top" value="<?= $is_view ? $id_top : ($id_top_val ?? '') ?>">
<input type="hidden" name="no_po" value="<?= $no_po ?>">
<input type="hidden" name="no_surat" value="<?= $no_surat ?>">
<input type="hidden" name="id_dp" value="<?= $is_view ? $id_dp : ($id_dp_val ?? '') ?>">
<input type="hidden" name="id_ros" value="<?= $id_ros_val ?>">
<input type="hidden" name="nilai_ppn" value="0">
<input type="hidden" name="nilai_disc" value="0">

<div class="row g-3">

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nomor PO</label>
        <input type="text" class="form-control form-control-sm" value="<?= $no_surat ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nama Supplier</label>
        <input type="text" class="form-control form-control-sm" value="<?= $nm_supplier ?>" readonly>
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
        <label class="form-label fw-semibold">Tipe</label>
        <input type="text" class="form-control form-control-sm" value="<?= ucfirst($tipe) ?>" readonly>
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
        <label class="form-label fw-semibold">Currency</label>
        <input type="text" name="currency" class="form-control form-control-sm"
            value="<?= $is_view ? $currency : $currency_val ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">
            Kurs <?php if (!$is_view): ?><span class="text-danger">*</span><?php endif; ?>
        </label>
        <input type="text" name="kurs" id="input_kurs_il"
            class="form-control form-control-sm text-end <?= !$is_view ? 'auto_num' : '' ?>"
            value="<?= $kurs > 0 ? number_format($kurs, 2) : '' ?>"
            placeholder="<?= !$is_view ? 'Masukkan kurs' : '' ?>"
            <?= $ro ?>>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Sisa Tagihan (<?= $is_view ? $currency : $currency_val ?>)</label>
        <input type="text" name="sisa_nilai" id="sisa_tagihan_display"
            class="form-control form-control-sm text-end"
            value="<?= number_format($sisa_tagihan_val, 2) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Jumlah Invoice (IDR)</label>
        <input type="text" id="jumlah_invoice_idr"
            class="form-control form-control-sm text-end"
            value="0.00" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Total DP Sebelumnya (IDR)</label>
        <input type="text" class="form-control form-control-sm text-end"
            value="<?= number_format($is_view ? 0 : $total_dp_rupiah_val, 2) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Outstanding (IDR)</label>
        <input type="text" id="outstanding_il"
            class="form-control form-control-sm text-end"
            value="0.00" readonly>
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
                echo '<span class="badge bg-info">Dalam Proses</span>';
            }
            ?>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">No. Payment</label>
            <input type="text" class="form-control form-control-sm" value="<?= $no_payment ?>" readonly>
        </div>
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
        $('.auto_num').autoNumeric('init', {aSep: ',', aDec: '.', mDec: 2});

        var sisaTagihan = <?= (float)$sisa_tagihan_val ?>;

        function formatNumber(num) {
            return num.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        function hitungSemua() {
            var kursRaw = $('#input_kurs_il').autoNumeric('get');
            var kurs = parseFloat(kursRaw) || 0;

            var jumlahIdr = sisaTagihan * kurs;
            var outstanding = 0 - jumlahIdr; // outstanding negatif jika ada tagihan

            // Outstanding = sisa tagihan dikurangi jumlah IDR
            // Karena ini form input baru, outstanding = 0 (belum ada pembayaran sebelumnya untuk invoice ini)
            // Tapi kalau kurs lebih tinggi → jumlah IDR lebih besar → outstanding bisa minus
            outstanding = sisaTagihan * kurs; // ini jumlah yang harus dibayar
            // Tidak ada yang dikurangi karena ini form baru

            $('#jumlah_invoice_idr').val(formatNumber(jumlahIdr));
            $('#outstanding_il').val(formatNumber(jumlahIdr));
        }

        $('#input_kurs_il').on('change keyup', function() {
            hitungSemua();
        });

        hitungSemua();
    });
</script>
<?php endif; ?>
