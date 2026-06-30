<?php
$h = $header;
$FORWARDING_RATE = 200;

// Hitung total others
$total_others_val = 0;
foreach ($others as $ot) {
    $total_others_val += (float) $ot['nilai'];
}

// Hitung total FC
$total_fc = $h['cost_bm'] + $h['cost_bm_kite'] + $h['cost_bmt'] + $h['cost_cukai'] + $h['cost_ppn'] + $h['cost_ppnbm'] + $h['cost_pph_import'];
?>
<style>
    .section-title {
        background: #f8f9fa;
        padding: 10px 15px;
        border-left: 4px solid #0d6efd;
        margin: 20px 0 15px;
        font-weight: bold;
    }

    .section-title.pib {
        border-left-color: #0d6efd;
    }

    .section-title.ls {
        border-left-color: #198754;
    }

    .section-title.insurance {
        border-left-color: #ffc107;
    }

    .section-title.others {
        border-left-color: #dc3545;
    }

    .section-title.data-po {
        border-left-color: #6f42c1;
    }

    .table-view th {
        font-size: 12px;
    }

    .table-view td {
        font-size: 12px;
    }

    .summary-row td {
        font-weight: bold;
        background-color: #f0f8ff;
    }
</style>

<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <a href="<?= base_url('new_ros') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
            <span class="badge rounded-pill <?= $h['status'] == 1 ? 'bg-success' : 'bg-warning' ?>"><?= $h['status'] == 1 ? 'Final' : 'Draft' ?></span>
        </div>
        <hr>

        <!-- Header Info -->
        <div class="row mb-3">
            <div class="col-md-4"><strong>No. ROS:</strong> <?= $h['id'] ?></div>
            <div class="col-md-4"><strong>Supplier:</strong> <?= $h['nm_supplier'] ?></div>
            <div class="col-md-4"><strong>No. PO:</strong> <?= $h['no_surat'] ?></div>
        </div>

        <!-- TAHAP 1 -->
        <div class="section-title pib"><i class="fas fa-file-invoice"></i> PIB Data</div>

        <div class="row mb-3">
            <div class="col-md-3">
                <strong>No. Submission:</strong><br>
                <?= $h['no_pengajuan'] ?: '-' ?>
            </div>
            <div class="col-md-3">
                <strong>No. Billing:</strong><br>
                <?= $h['no_billing'] ?: '-' ?>
            </div>
            <div class="col-md-3">
                <strong>Tgl. Billing:</strong><br>
                <?= $h['tgl_billing'] ? date('d-m-Y', strtotime($h['tgl_billing'])) : '-' ?>
            </div>
            <div class="col-md-3">
                <strong>Doc PIB:</strong><br>
                <?php if (!empty($h['file_original_name_pib'])) : ?>
                    <a href="<?= base_url('uploads/pib_ros/' . $h['file_hash_name_pib']) ?>" target="_blank">
                        <i class="fas fa-paperclip"></i> <?= $h['file_original_name_pib'] ?>
                    </a>
                <?php else : ?>
                    -
                <?php endif; ?>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4"><strong>PO Value (U$):</strong> <?= number_format($h['nilai_po_usd'], 4) ?></div>
            <div class="col-md-4"><strong>PIB Exchange Rate:</strong> <?= number_format($h['kurs_pib'], 2) ?></div>
            <div class="col-md-4"><strong>PO PIB Value (Rp):</strong> <?= number_format($h['nilai_po_pib_rp'], 2) ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4"><strong>Total Gross KG PIB:</strong> <?= number_format($h['total_kg_kotor_pib'], 4) ?></div>
            <div class="col-md-4"><strong>Total Net KG PIB:</strong> <?= number_format($h['total_kg_bersih_pib'], 4) ?></div>
        </div>

        <h6 class="fw-bold">F&C Estimation</h6>
        <div class="row mb-3">
            <div class="col-md-6">
                <table class="table table-bordered table-sm">
                    <tr>
                        <td>BM</td>
                        <td class="text-end"><?= number_format($h['cost_bm'], 0) ?></td>
                    </tr>
                    <tr>
                        <td>BM Kite</td>
                        <td class="text-end"><?= number_format($h['cost_bm_kite'], 0) ?></td>
                    </tr>
                    <tr>
                        <td>BMT</td>
                        <td class="text-end"><?= number_format($h['cost_bmt'], 0) ?></td>
                    </tr>
                    <tr>
                        <td>Excise Duty</td>
                        <td class="text-end"><?= number_format($h['cost_cukai'], 0) ?></td>
                    </tr>
                    <tr>
                        <td>PPN</td>
                        <td class="text-end"><?= number_format($h['cost_ppn'], 0) ?></td>
                    </tr>
                    <tr>
                        <td>PPnBM</td>
                        <td class="text-end"><?= number_format($h['cost_ppnbm'], 0) ?></td>
                    </tr>
                    <tr>
                        <td>PPH Import</td>
                        <td class="text-end"><?= number_format($h['cost_pph_import'], 0) ?></td>
                    </tr>
                    <tr class="table-secondary">
                        <td class="fw-bold">TOTAL</td>
                        <td class="text-end fw-bold"><?= number_format($total_fc, 0) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- TAHAP 2 -->
        <div class="section-title ls"><i class="fas fa-search-dollar"></i> LS Cost (Surveyor)</div>
        <div class="row mb-3">
            <div class="col-md-3">
                <strong>No. Invoice LS:</strong><br>
                <?= $h['no_invoice_ls'] ?: '-' ?>
            </div>
            <div class="col-md-3">
                <strong>LS Cost:</strong><br>
                <?= number_format($h['biaya_ls'], 0) ?>
            </div>
            <div class="col-md-3">
                <strong>PPN LS:</strong><br>
                <?= number_format($h['ppn_ls'], 0) ?>
            </div>
            <div class="col-md-3">
                <strong>PPH LS:</strong><br>
                <?= number_format($h['pph_ls'], 0) ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3">
                <strong>DPP <small class="text-muted">(LS Cost × 11/12):</small></strong><br>
                <?= number_format($h['biaya_ls'] * (11 / 12), 0) ?>
            </div>
            <div class="col-md-3">
                <strong>Total Biaya LS <small class="text-muted">(LS Cost + PPN - PPH):</small></strong><br>
                <?= number_format($h['biaya_ls'] + $h['ppn_ls'] - $h['pph_ls'], 0) ?>
            </div>
        </div>

        <!-- TAHAP 3 -->
        <div class="section-title insurance"><i class="fas fa-shield-alt"></i> Insurance</div>
        <div class="row mb-3">
            <div class="col-md-3">
                <strong>No. Insurance:</strong><br>
                <?= $h['no_insurance'] ?: '-' ?>
            </div>
            <div class="col-md-3">
                <strong>Insurance Value:</strong><br>
                <?= number_format($h['insurance'], 0) ?>
            </div>
        </div>

        <!-- TAHAP 4 -->
        <div class="section-title others"><i class="fas fa-coins"></i> Other Costs</div>
        <?php if (!empty($others)) : ?>
            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>No. Ref</th>
                                <th>Description</th>
                                <th class="text-end">Amount (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($others as $idx => $ot) : ?>
                                <tr>
                                    <td class="text-center"><?= $idx + 1 ?></td>
                                    <td><?= $ot['no_others'] ?: '-' ?></td>
                                    <td><?= $ot['keterangan'] ?></td>
                                    <td class="text-end"><?= number_format($ot['nilai'], 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <td colspan="3" class="text-end fw-bold">Total</td>
                                <td class="text-end fw-bold"><?= number_format($total_others_val, 0) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        <?php else : ?>
            <p class="text-muted">No other costs.</p>
        <?php endif; ?>

        <!-- DATA PO -->
        <div class="section-title data-po"><i class="fas fa-calculator"></i> PO Data & Inventory Value Calculation</div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-view">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">PO Name</th>
                        <th class="text-center">PO Name (Alias)</th>
                        <th class="text-center">Kg Unit</th>
                        <th class="text-center">Unit Price (U$)</th>
                        <th class="text-center">Total Value (U$)</th>
                        <th class="text-center">Total Value (Rp)</th>
                        <th class="text-center">BM %</th>
                        <th class="text-center">BM (Rp)</th>
                        <th class="text-center">Prorate LS</th>
                        <th class="text-center">Forwarding Cost</th>
                        <th class="text-center">Pro Rate Insurance</th>
                        <th class="text-center">Pro Rate Other Costs</th>
                        <th class="text-center">Total Inventory Value</th>
                        <th class="text-center">Cost Book</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sum_usd = 0;
                    $sum_rp = 0;
                    $sum_bm = 0;
                    $sum_ls = 0;
                    $sum_fwd = 0;
                    $sum_ins = 0;
                    $sum_oth = 0;
                    $sum_inv = 0;
                    foreach ($materials as $idx => $m) :
                        $sum_usd += $m['total_value_usd'];
                        $sum_rp  += $m['total_value_rp'];
                        $sum_bm  += $m['bm_rp'];
                        $sum_ls  += $m['prorate_ls'];
                        $sum_fwd += $m['forwarding_cost'];
                        $sum_ins += $m['prorate_insurance'];
                        $sum_oth += $m['prorate_others'];
                        $sum_inv += $m['total_nilai_inventory'];
                    ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td><?= $m['nm_barang'] ?></td>
                            <td><?= $m['nm_alias'] ?></td>
                            <td class="text-end"><?= number_format($m['kg_unit'], 4) ?></td>
                            <td class="text-end"><?= number_format($m['unit_price_usd'], 6) ?></td>
                            <td class="text-end"><?= number_format($m['total_value_usd'], 4) ?></td>
                            <td class="text-end"><?= number_format($m['total_value_rp'], 0) ?></td>
                            <td class="text-center"><?= number_format($m['bm_persen'], 0) ?>%</td>
                            <td class="text-end"><?= number_format($m['bm_rp'], 0) ?></td>
                            <td class="text-end"><?= number_format($m['prorate_ls'], 0) ?></td>
                            <td class="text-end"><?= number_format($m['forwarding_cost'], 0) ?></td>
                            <td class="text-end"><?= number_format($m['prorate_insurance'], 0) ?></td>
                            <td class="text-end"><?= number_format($m['prorate_others'], 0) ?></td>
                            <td class="text-end fw-bold"><?= number_format($m['total_nilai_inventory'], 0) ?></td>
                            <td class="text-end fw-bold"><?= number_format($m['cost_book'], 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="summary-row">
                        <td colspan="5" class="text-end">Total PO</td>
                        <td class="text-end"><?= number_format($sum_usd, 4) ?></td>
                        <td class="text-end"><?= number_format($sum_rp, 0) ?></td>
                        <td></td>
                        <td class="text-end"><?= number_format($sum_bm, 0) ?></td>
                        <td class="text-end"><?= number_format($sum_ls, 0) ?></td>
                        <td class="text-end"><?= number_format($sum_fwd, 0) ?></td>
                        <td class="text-end"><?= number_format($sum_ins, 0) ?></td>
                        <td class="text-end"><?= number_format($sum_oth, 0) ?></td>
                        <td class="text-end"><?= number_format($sum_inv, 0) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <!-- DATA COIL -->
        <?php
        $total_coil_count = 0;
        $has_coil = false;
        $total_nw = 0;  // tambah ini
        $total_gw = 0;  // tambah ini
        foreach ($materials as $mat) {
            if (!empty($mat['coils'])) {
                $has_coil = true;
                $total_coil_count += count($mat['coils']);
                foreach ($mat['coils'] as $coil) {  // tambah loop ini
                    $total_nw += (float) $coil['berat_bersih'];
                    $total_gw += (float) $coil['berat_kotor'];
                }
            }
        }
        ?>
        <?php if ($has_coil) : ?>
            <div class="section-title" style="border-left-color:#17a2b8;">
                <i class="fas fa-file-excel"></i> Coil Data (Packing List)
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-view">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="4%">No</th>
                            <th class="text-center">Original Name</th>
                            <th class="text-center">Alias Name</th>
                            <th class="text-center">Coil No.</th>
                            <th class="text-center">Internal Code</th>
                            <th class="text-center">N.W. (Kg)</th>
                            <th class="text-center">G.W. (Kg)</th>
                            <th class="text-center">Length (M)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($materials as $mat) :
                            if (empty($mat['coils'])) continue;

                            $rowspan  = count($mat['coils']);
                            $nm_asli  = $mat['nm_barang'] ?: $mat['nm_erp'];
                            $nm_alias = $mat['nm_alias']  ?: $mat['nm_barang'];
                        ?>
                            <?php foreach ($mat['coils'] as $j => $coil) : ?>
                                <tr>
                                    <?php if ($j === 0) : ?>
                                        <td class="text-center align-middle" rowspan="<?= $rowspan ?>"><?= $no ?></td>
                                        <td class="align-middle" rowspan="<?= $rowspan ?>"><?= $nm_asli ?></td>
                                        <td class="align-middle" rowspan="<?= $rowspan ?>"><?= $nm_alias ?></td>
                                    <?php endif; ?>
                                    <td class="text-center"><?= $coil['no_coil'] ?></td>
                                    <td class="text-center"><?= $coil['kode_internal'] ?></td>
                                    <td class="text-end"><?= number_format($coil['berat_bersih'], 2) ?></td>
                                    <td class="text-end"><?= number_format($coil['berat_kotor'],  2) ?></td>
                                    <td class="text-end"><?= number_format($coil['panjang'],      2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php $no++;
                        endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-secondary">
                            <td colspan="3" class="text-end fw-bold">Total Coil: <?= $total_coil_count ?></td>
                            <td colspan="2"></td>
                            <td class="text-end fw-bold"><?= number_format($total_nw, 2) ?></td>
                            <td class="text-end fw-bold"><?= number_format($total_gw, 2) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="card-footer text-end">
        <a href="<?= base_url('new_ros') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        <?php if ($h['status'] == 0) : ?>
            <a href="<?= base_url('new_ros/edit/' . $h['id']) ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
        <?php endif; ?>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#btn_view_qr').on('click', function() {
            $.ajax({
                url: siteurl + 'new_ros/get_coil_list',
                type: 'POST',
                data: {
                    id_ros: '<?= $h['id'] ?>'
                },
                success: function(html) {
                    $('#modal_body_qr').html(html);
                    $('#modalPrintQR').modal('show');
                }
            });
        });

        $(document).on('click', '#check_all_modal', function() {
            $('.check_item_modal').prop('checked', this.checked);
        });
    });
</script>