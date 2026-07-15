<?php $is_view = ($mode === 'view'); ?>

<input type="hidden" name="tipe_req" value="dp">
<input type="hidden" name="id_top" value="<?= $is_view ? $data['id_top']   : $data_po['id_top'] ?>">
<input type="hidden" name="no_po" value="<?= $is_view ? $data['no_po']    : $data_po['no_po'] ?>">
<input type="hidden" name="no_surat" value="<?= $is_view ? $data['no_surat'] : $data_po['no_surat'] ?>">

<?php
$d = $is_view ? $data : array_merge($data_po, [
    'nm_supplier'          => $get_supplier['nama'] ?? '-',
    'nomor_invoice'        => '',
    'invoice_date'         => '',
    'invoice_date_real'    => '',
    'nomor_faktur_pajak'   => '',
    'tanggal_faktur_pajak' => '',
    'bank'                 => '',
    'no_bank'              => '',
    'nm_acc_bank'          => '',
    'kurs'                 => '',
    'jumlah_rupiah'        => 0,
    'jumlah_po'            => 0,
    'no_payment'           => null,
    'status_payment'       => null,
    'file_invoice'         => null,
]);

$currency = $d['matauang'] ?? 'IDR';
$ro       = $is_view ? 'readonly' : '';
?>

<div class="row g-3">

    <!-- ═══ INFORMASI PO (Readonly/Otomatis) ═══ -->
    <div class="col-12">
        <p class="fw-bold mb-2 text-primary">Informasi PO</p>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nomor PO</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $d['no_surat'] ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nama Supplier</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $d['nm_supplier'] ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Currency</label>
        <input type="text" name="currency" class="form-control form-control-sm"
            value="<?= $currency ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Jumlah PO</label>
        <input type="text" id="jumlah_po"
            class="form-control form-control-sm text-end"
            value="<?= number_format($is_view ? $d['hargatotal'] : 0, 4) ?>"
            readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Persentase DP (%)</label>
        <input type="text" class="form-control form-control-sm text-end"
            value="<?= number_format($is_view ? $d['persen_dp'] : $data_po['progress'], 2) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Value DP</label>
        <input type="text" name="value_dp" class="form-control form-control-sm text-end"
            value="<?= number_format($is_view ? $d['value_dp'] : $data_po['nilai'], 4) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">DPP</label>
        <input type="text" name="dpp" class="form-control form-control-sm text-end"
            value="<?= number_format($is_view ? $d['dpp'] : $dpp, 4) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nilai PPN</label>
        <input type="text" name="nilai_ppn" class="form-control form-control-sm text-end"
            value="<?= number_format($is_view ? $d['nilai_ppn'] : $nilai_ppn, 2) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">
            Kurs Receiving Invoice <?php if (!$is_view && $currency !== 'IDR'): ?><span class="text-danger">*</span><?php endif; ?>
        </label>
        <input type="text" name="kurs" id="input_kurs"
            class="form-control form-control-sm text-end <?= !$is_view ? 'auto_num' : '' ?>"
            value="<?= $is_view ? number_format($d['kurs'], 2) : ($currency === 'IDR' ? '1' : '') ?>"
            placeholder="<?= (!$is_view && $currency !== 'IDR') ? 'Wajib diisi' : '' ?>"
            <?= $ro ?>>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Jumlah Invoice (IDR)</label>
        <input type="text" name="jumlah_rupiah" id="jumlah_rupiah"
            class="form-control form-control-sm text-end"
            value="<?= number_format($is_view ? $d['jumlah_rupiah'] : 0, 2) ?>"
            readonly>
    </div>

    <!-- ═══ FORM INPUT ═══ -->
    <div class="col-12">
        <hr class="my-2">
        <p class="fw-bold mb-2 text-primary">Data Invoice</p>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">
            Receive Invoice Date <?php if (!$is_view): ?><span class="text-danger">*</span><?php endif; ?>
        </label>
        <input type="text" name="invoice_date"
            class="form-control form-control-sm <?= !$is_view ? 'fp-date' : '' ?>"
            value="<?= !empty($d['invoice_date']) ? date('d M Y', strtotime($d['invoice_date'])) : '' ?>"
            placeholder="<?= !$is_view ? 'Pilih tanggal...' : '' ?>"
            <?= $ro ?> <?= !$is_view ? 'required' : '' ?>>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Invoice Date</label>
        <input type="text" name="invoice_date_real"
            class="form-control form-control-sm <?= !$is_view ? 'fp-date' : '' ?>"
            value="<?= !empty($d['invoice_date_real']) ? date('d M Y', strtotime($d['invoice_date_real'])) : '' ?>"
            placeholder="<?= !$is_view ? 'Pilih tanggal...' : '' ?>"
            <?= $ro ?>>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">
            Nomor Invoice <?php if (!$is_view): ?><span class="text-danger">*</span><?php endif; ?>
        </label>
        <input type="text" name="nomor_invoice" class="form-control form-control-sm"
            value="<?= $d['nomor_invoice'] ?>"
            <?= $ro ?> <?= !$is_view ? 'required' : '' ?>>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nomor Faktur Pajak</label>
        <input type="text" name="nomor_faktur_pajak" class="form-control form-control-sm"
            value="<?= $d['nomor_faktur_pajak'] ?? '' ?>"
            placeholder="<?= !$is_view ? 'Masukkan nomor faktur pajak' : '' ?>"
            <?= $ro ?>>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Tanggal Faktur Pajak</label>
        <input type="text" name="tanggal_faktur_pajak"
            class="form-control form-control-sm <?= !$is_view ? 'fp-date' : '' ?>"
            value="<?= !empty($d['tanggal_faktur_pajak']) ? date('d M Y', strtotime($d['tanggal_faktur_pajak'])) : '' ?>"
            placeholder="<?= !$is_view ? 'Pilih tanggal...' : '' ?>"
            <?= $ro ?>>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Upload Invoice</label>
        <?php if ($is_view && !empty($d['file_invoice'])): ?>
            <br>
            <a href="<?= base_url('uploads/invoice_dp/' . $d['file_invoice']) ?>"
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
            $status = strtolower($d['status_payment'] ?? '');
            if ($status === 'payment') {
                echo '<span class="badge bg-success">Lunas</span>';
            } elseif ($status === 'approve management') {
                echo '<span class="badge bg-info">Approve Management</span>';
            } elseif ($status === 'approve checker') {
                echo '<span class="badge bg-primary">Approve Checker</span>';
            } elseif ($status === 'request payment') {
                echo '<span class="badge bg-secondary">Request Payment</span>';
            } elseif ($status === 'draft') {
                echo '<span class="badge bg-warning text-dark">Draft</span>';
            } else {
                echo '<span class="badge bg-warning text-dark">Menunggu Proses</span>';
            }
            ?>
        </div>
        <?php if (!empty($d['no_payment'])): ?>
            <div class="col-md-6">
                <label class="form-label fw-semibold">No. Payment</label>
                <input type="text" class="form-control form-control-sm"
                    value="<?= $d['no_payment'] ?>" readonly>
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
            value="<?= $d['bank'] ?>"
            placeholder="<?= !$is_view ? 'Nama Bank' : '' ?>"
            <?= $ro ?> <?= !$is_view ? 'required' : '' ?>>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">
            No. Rekening <?php if (!$is_view): ?><span class="text-danger">*</span><?php endif; ?>
        </label>
        <input type="text" name="no_bank" class="form-control form-control-sm"
            value="<?= $d['no_bank'] ?>"
            placeholder="<?= !$is_view ? 'Nomor Rekening' : '' ?>"
            <?= $ro ?> <?= !$is_view ? 'required' : '' ?>>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">
            Nama Pemilik <?php if (!$is_view): ?><span class="text-danger">*</span><?php endif; ?>
        </label>
        <input type="text" name="nm_acc_bank" class="form-control form-control-sm"
            value="<?= $d['nm_acc_bank'] ?>"
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

            // ── Konstanta dari PHP ──────────────────────────────────────────
            var jumlahPoForeign = <?= (float)$jumlah_po ?>; // grand total PO dalam currency asli
            var currency = '<?= strtoupper($currency) ?>';

            // ── Format angka ribuan ─────────────────────────────────────────
            function formatNumber(num, decimal = 2) {
                return num.toLocaleString('id-ID', {
                    minimumFractionDigits: decimal,
                    maximumFractionDigits: decimal
                });
            }

            // ── Hitung Jumlah Invoice IDR ───────────────────────────────────
            function hitungSemua() {
                var valueDp = <?= (float)($is_view ? $d['value_dp'] : $data_po['nilai']) ?>;

                var kurs = 1;
                if (currency !== 'IDR') {
                    var kursRaw = $('#input_kurs').autoNumeric('get');
                    kurs = parseFloat(kursRaw) || 0;
                }

                var jumlahRupiah = valueDp * kurs;

                $('#jumlah_rupiah').val(formatNumber(jumlahRupiah));
                $('#jumlah_po').val(formatNumber(jumlahPoForeign, 4));
            }

            // ── Event listener ──────────────────────────────────────────────
            $('#input_kurs').on('change keyup', function() {
                hitungSemua();
            });

            // Jalankan sekali saat load
            hitungSemua();

        });
    </script>
<?php endif; ?>