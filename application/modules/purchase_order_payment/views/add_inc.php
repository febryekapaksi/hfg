<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<input type="hidden" name="tipe_req" value="inc">
<input type="hidden" name="no_po" value="<?= implode(',', $results['no_po']) ?>">
<input type="hidden" name="no_incoming" value="<?= implode(',', $results['no_incoming']) ?>">

<div class="row g-3">

    <!-- Info Supplier & Incoming -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nama Supplier</label>
        <input type="hidden" name="kode_supplier" value="<?= $results['kode_supplier'] ?>">
        <input type="text" name="nama_supplier" class="form-control form-control-sm"
            value="<?= $results['nm_supplier'] ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nomor Incoming</label>
        <input type="text" name="nomor_po" class="form-control form-control-sm"
            value="<?= $results['incoming_no'] ?>" readonly>
    </div>

    <!-- Nomor Invoice & Faktur -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nomor Invoice <span class="text-danger">*</span></label>
        <input type="text" name="nomor_invoice" class="form-control form-control-sm" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nomor Faktur Pajak</label>
        <input type="text" name="nomor_faktur_pajak" class="form-control form-control-sm">
    </div>

    <!-- Tanggal -->
    <div class="col-md-4">
        <label class="form-label fw-semibold">Receive Invoice Date <span class="text-danger">*</span></label>
        <input type="text" name="invoice_date" class="form-control form-control-sm flatpickr-input"
            placeholder="Pilih tanggal..." required>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Invoice Date</label>
        <input type="text" name="invoice_date_real" class="form-control form-control-sm flatpickr-input"
            placeholder="Pilih tanggal...">
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Tanggal Faktur Pajak</label>
        <input type="text" name="tanggal_faktur_pajak" class="form-control form-control-sm flatpickr-input"
            placeholder="Pilih tanggal...">
    </div>

    <!-- Divider Pembayaran -->
    <div class="col-12">
        <hr class="my-1">
        <p class="fw-bold mb-2">Informasi Pembayaran</p>
    </div>

    <!-- Currency & Kurs -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">Currency</label>
        <input type="text" name="currency" class="form-control form-control-sm"
            value="<?= $results['currency'] ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Kurs</label>
        <input type="text" name="kurs" value="1" class="form-control form-control-sm text-end auto_num">
    </div>

    <!-- Nilai -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nilai Disc</label>
        <input type="text" name="nilai_disc" class="form-control form-control-sm text-end auto_num nilai_disc"
            value="<?= number_format($results['nilai_disc'], 2) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nilai PPN</label>
        <input type="text" name="nilai_ppn" class="form-control form-control-sm text-end auto_num nilai_ppn"
            value="<?= number_format($results['nilai_ppn'], 2) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Value DP</label>
        <input type="text" name="value_dp" class="form-control form-control-sm text-end value_dp"
            value="<?= number_format($results['value_dp'], 2) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Total Incoming</label>
        <input type="text" name="total_pembelian" class="form-control form-control-sm text-end total_pembelian"
            value="<?= number_format($results['total_invoice'], 2) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Total Invoice <span class="text-danger">*</span></label>
        <input type="text" name="total_invoice" class="form-control form-control-sm text-end auto_num total_invoice"
            value="<?= number_format($results['total_invoice'], 2) ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Request Payment PO <span class="text-danger">*</span></label>
        <input type="text" name="req_payment_po" class="form-control form-control-sm text-end auto_num req_payment_po" required>
    </div>

    <!-- Upload & Notes -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">Upload Invoice</label>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <input type="file" name="upload_invoice" id="photo" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
            <button type="button" class="btn btn-sm btn-outline-warning" id="btnPickInv">
                <i class="ti ti-upload me-1"></i> Choose File
            </button>
            <span class="text-muted small" id="InvFileName">No file chosen</span>
            <button type="button" class="btn btn-sm btn-outline-danger" id="btnClearInv" style="display:none;">
                <i class="ti ti-trash"></i>
            </button>
        </div>
        <small class="text-muted">PDF/JPG/PNG. Max 2MB.</small>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Notes</label>
        <textarea name="notes" class="form-control form-control-sm" rows="3"></textarea>
    </div>

    <!-- Divider Info Bank -->
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

<?php
foreach ($results['no_incoming'] as $id_incoming) {
?>
    <div class="col-md-12">
        <hr>
        <div class="mb-2"><b>Detail Incoming : <?= $id_incoming ?></b></div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Nomor PO</th>
                    <th class="text-center">Produk</th>
                    <th class="text-center">Qty PO</th>
                    <th class="text-center">Qty Incoming</th>
                    <th class="text-center">Harga Satuan</th>
                    <th class="text-center">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                <?php

                $get_detail_inc = $this->db->query("
                        SELECT
                            e.qty_oke as qty_order,   
                            b.qty as qty_po,
                            b.hargasatuan as hargasatuan,
                            c.no_surat as no_surat,
                            d.nama as nm_material
                        FROM
                            tr_incoming_check_detail a
                            LEFT JOIN dt_trans_po b ON b.id = a.id_po_detail
                            LEFT JOIN tr_purchase_order c ON c.no_po  = b.no_po
                            LEFT JOIN new_inventory_4 d ON d.code_lv4 = a.id_material 
                            JOIN tr_checked_incoming_detail e ON e.kode_trans = a.kode_trans AND e.id_detail = a.id
                        WHERE
                            a.kode_trans = '" . $id_incoming . "'
                        GROUP BY a.id
                        
                        UNION ALL

                        SELECT 
                            a.qty_oke as qty_order,
                            b.qty as qty_po,
                            b.hargasatuan as hargasatuan,
                            c.no_surat as no_surat,
                            d.stock_name as nm_material
                        FROM
                            warehouse_adjustment_detail a
                            LEFT JOIN dt_trans_po b ON b.id = a.no_ipp
                            LEFT JOIN tr_purchase_order c ON c.no_po  = b.no_po
                            LEFT JOIN accessories d ON d.id = a.id_material 
                            LEFT JOIN warehouse_adjustment e ON e.kode_trans = a.kode_trans
                        WHERE
                            a.kode_trans = '" . $id_incoming . "' AND e.category = 'incoming stok'
                        GROUP BY a.id

                        UNION ALL

                        SELECT
                            a.qty_oke as qty_order,
                            a.qty_oke as qty_po,
                            c.harga as hargasatuan,
                            d.no_pr as no_surat,
                            d.nm_barang as nm_material
                        FROM
                            warehouse_adjustment_detail a
                            LEFT JOIN warehouse_adjustment b ON b.kode_trans = a.kode_trans
                            LEFT JOIN tr_pr_detail_kasbon c ON c.id_kasbon = b.no_ipp AND c.id_detail = a.id_material
                            LEFT JOIN rutin_non_planning_detail d ON d.id = a.id_material
                        WHERE
                            a.kode_trans = '" . $id_incoming . "' AND b.category = 'incoming non rutin'
                        GROUP BY a.id

                        UNION ALL

                        SELECT 
                            a.qty_oke as qty_order,
                            b.qty as qty_po,
                            b.hargasatuan as hargasatuan,
                            c.no_surat as no_surat,
                            a.nm_material as nm_material
                        FROM
                            warehouse_adjustment_detail a
                            LEFT JOIN tr_purchase_order c ON c.no_surat = a.no_ipp
                            LEFT JOIN dt_trans_po b ON b.no_po = c.no_po AND b.namamaterial = a.nm_material
                            LEFT JOIN warehouse_adjustment e ON e.kode_trans = a.kode_trans
                        WHERE
                            a.kode_trans = '" . $id_incoming . "' AND e.category = 'incoming asset'
                        GROUP BY a.id
                     ")->result();
                if (!$get_detail_inc) {
                    print_r($this->db->error($get_detail_inc));
                    exit;
                }

                $grand_total = 0;

                $no = 1;
                foreach ($get_detail_inc as $item) {
                    echo '<tr>';
                    echo '<td class="text-center">' . $no . '</td>';
                    echo '<td class="text-center">' . $item->no_surat . '</td>';
                    echo '<td class="text-center">' . $item->nm_material . '</td>';
                    echo '<td class="text-center">' . number_format($item->qty_po) . '</td>';
                    echo '<td class="text-center">' . number_format($item->qty_order) . '</td>';
                    echo '<td class="text-right">' . number_format($item->hargasatuan, 2) . '</td>';
                    echo '<td class="text-right">' . number_format($item->qty_order * $item->hargasatuan, 2) . '</td>';
                    echo '</tr>';
                    $no++;

                    $grand_total += ($item->qty_order * $item->hargasatuan);
                }
                ?>
            </tbody>
            <tbody>
                <tr>
                    <td colspan="6" align="right">
                        <b>Grand Total</b>
                    </td>
                    <td align="right">
                        <b>
                            <?= number_format($grand_total, 2) ?>
                        </b>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
<?php
}
?>

</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script>
    $(document).ready(function() {
        $('.select2_modal').select2({
            width: '100%',
            dropdownParent: $('#dialog-popup')
        });
    });
    $('.auto_num').autoNumeric('init');
    $(document).on('change', '.persen_dp', function() {
        var total_pembelian = $('.total_pembelian').val();
        if (total_pembelian == '' || total_pembelian == null) {
            total_pembelian = 0;
        } else {
            total_pembelian = total_pembelian.split(',').join('');
            total_pembelian = parseFloat(total_pembelian);
        }

        var persen_dp = parseFloat($(this).val());

        var value_dp = (total_pembelian * persen_dp / 100);

        $('.value_dp').val(value_dp.toLocaleString());
    });

    (function() {
        const input = document.getElementById('photo');
        const btnPick = document.getElementById('btnPickInv');
        const btnClear = document.getElementById('btnClearInv');
        const fileName = document.getElementById('InvFileName');

        if (!input || !btnPick || !fileName) return;

        btnPick.addEventListener('click', function() {
            input.click();
        });

        input.addEventListener('change', function() {
            const name = (input.files && input.files.length) ? input.files[0].name : 'No file chosen';
            fileName.textContent = name;

            if (input.files && input.files.length) {
                btnClear.style.display = 'inline-flex';
            } else {
                btnClear.style.display = 'none';
            }
        });

        if (btnClear) {
            btnClear.addEventListener('click', function() {
                input.value = '';
                fileName.textContent = 'No file chosen';
                btnClear.style.display = 'none';
            });
        }
    })();
</script>