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
    $keterangan = $tipe_label . ' - ' . $header->no_po . ' - ' . $header->nomor_invoice;
    $no_doc = $header->no_po;
    $tgl_doc = $header->invoice_date;
    $bank_id = $header->bank;
    $accnumber = $header->no_bank;
    $accname = $header->nm_acc_bank;
}
?>

<style>
    /* Styling Header Tabel Bootstrap 5 */
    .table thead th {
        background-color: #198754 !important; /* Tema Hijau Success */
        color: white !important;
        vertical-align: middle;
    }
    .swal2-container {
        z-index: 99999 !important;
    }
    /* Menghilangkan border tebal nested table info pengajuan */
    .table-nested td {
        border: none !important;
        padding: 2px 4px !important;
    }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form')); ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        
        <input type="hidden" name="id" value="<?= $header->id; ?>">
        <input type="hidden" name="tipe" value="<?= $type; ?>">
        <input type="hidden" name="tingkat_approval" value="1">

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label text-md-end fw-semibold small">Nomor Dokumen</label>
                    <div class="col-sm-8">
                        <input type="text" name="no_doc" class="form-control bg-light" readonly value="<?= $no_doc; ?>">
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label text-md-end fw-semibold small">Keterangan</label>
                    <div class="col-sm-8">
                        <input type="text" name="informasi" class="form-control bg-light" readonly value="<?= ($keterangan) ?: ''; ?>">
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label text-md-end fw-semibold small">Biaya Admin</label>
                    <div class="col-sm-8">
                        <input type="text" name="admin_bank" class="form-control bg-light text-end fw-bold" readonly value="<?= number_format(($data_req_payment['admin_bank']), 2) ?>">
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label text-md-end fw-semibold small">Dokumen Req Payment</label>
                    <div class="col-sm-8">
                        <?php
                        if (!empty($data_req_payment['link_doc'])) {
                            if (file_exists('./assets/expense/' . $data_req_payment['link_doc'])) {
                                echo '<a href="' . base_url('assets/expense/' . $data_req_payment['link_doc']) . '" class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="fa fa-download me-1"></i> Download File
                                      </a>';
                            }
                        } else {
                            echo '<span class="text-muted small italic">Tidak ada file</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label text-md-end fw-semibold small">Bank Destination</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control bg-light" readonly value="<?= $data_req_payment['bank_id']; ?>">
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label text-md-end fw-semibold small">Tanggal Doc</label>
                    <div class="col-sm-8">
                        <input type="text" name="date" class="form-control bg-light" readonly value="<?= $tgl_doc; ?>">
                    </div>
                </div>
                <div class="row mb-3 align-items-start">
                    <label class="col-sm-4 col-form-label text-md-end fw-semibold small text-danger">Reject Reason</label>
                    <div class="col-sm-8">
                        <textarea name="reject_reason" class="form-control reject_reason" rows="2" placeholder="Wajib diisi jika menolak pengajuan..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive mb-4 shadow-sm rounded">
            <table id="mytabledata" class="table table-striped table-hover table-bordered align-middle mb-0">
                <thead class="text-center">
                    <tr>
                        <th style="width: 4%">#</th>
                        <th>Barang/Jasa</th>
                        <th>Tanggal Transaksi</th>
                        <th style="width: 6%">Qty</th>
                        <th style="width: 8%">Currency</th>
                        <th style="width: 35%">Rincian Nilai</th>
                        <th style="width: 6%">Bukti</th>
                        <th style="width: 10%">
                            <div class="form-check d-flex justify-content-center align-items-center m-0">
                                <input class="form-check-input master_check me-1" type="checkbox" id="checkAll" checked>
                                <label class="form-check-label fw-bold small" for="checkAll">Semua</label>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $n = 0;
                    $gTotal = 0;
                    if (!empty($details)) {
                        foreach ($details as $dtl) : $n++;
                            $coa = (isset($dtl->coa)) ? $dtl->coa : '';
                            $nm_coa = (isset($list_coa[$coa]) && $coa !== '') ? $list_coa[$coa] : '';
                            
                            // ------------------------- TYPE EXPENSE -------------------------
                            if ($type == 'expense') :
                                $harga  = $dtl->harga;
                                if (isset($dtl->id_kasbon) && $dtl->id_kasbon !== '') {
                                    $harga = $dtl->kasbon * -1;
                                }
                                $gTotal += ($data_req_payment['jumlah'] + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']); 
                    ?>
                                <tr>
                                    <td class="text-center"><?= $n; ?></td>
                                    
                                    <td><?= $dtl->deskripsi; ?> <?= (isset($dtl->id_kasbon) && $dtl->id_kasbon !== '') ? '<span class="badge bg-warning text-dark ms-1">Kasbon</span>' : '' ?></td>
                                    <td class="text-center"><?= $dtl->tanggal; ?></td>
                                    <td class="text-center"><?= $dtl->qty; ?></td>
                                    <td class="text-center fw-bold text-primary"><?= $data_req_payment['currency']; ?></td>
                                    <td>
                                        <table class="table table-nested table-sm mb-0 w-100 small">
                                            <tr><td>Nilai Pengajuan</td><td class="text-center" style="width:10px">:</td><td class="text-end fw-semibold"><?= number_format($data_req_payment['jumlah'], 2) ?></td></tr>
                                            <tr><td>Nilai PPh</td><td class="text-center">:</td><td class="text-end text-danger"><?= number_format($data_req_payment['total_pph'], 2) ?></td></tr>
                                            <tr><td>Bank Charge</td><td class="text-center">:</td><td class="text-end"><?= number_format($data_req_payment['admin_bank'], 2) ?></td></tr>
                                            <tr class="table-light fw-bold"><td>Net Payment</td><td class="text-center">:</td><td class="text-end text-success"><?= number_format(($data_req_payment['jumlah'] + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']), 2) ?></td></tr>
                                        </table>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $get_ros = $this->db->get_where('tr_ros', ['id' => $dtl->no_doc])->row_array();
                                        $get_invoice = $this->db->get_where('tr_invoice_po', ['id' => $dtl->no_doc])->row_array();
                                        if (!empty($get_ros) && file_exists($get_ros['link_doc'])) {
                                            echo '<a href="' . base_url($get_ros['link_doc']) . '" target="_blank" class="text-primary"><i class="fa fa-download fa-lg"></i></a>';
                                        } else if (!empty($get_invoice) && file_exists($get_invoice['link_doc'])) {
                                            echo '<a href="' . base_url($get_invoice['link_doc']) . '" target="_blank" class="text-primary"><i class="fa fa-download fa-lg"></i></a>';
                                        } else if (file_exists('./assets/expense/' . $dtl->doc_file) && $dtl->doc_file !== '') {
                                            echo '<a href="' . base_url('assets/expense/' . $dtl->doc_file) . '" target="_blank" class="text-primary"><i class="fa fa-download fa-lg"></i></a>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="form-check-input check_item">
                                    </td>
                                </tr>

                    <?php 
                            // ------------------------- TYPE KASBON -------------------------
                            elseif ($type == 'kasbon') :
                                if ($kasbon_pr == '1') {
                    ?>
                                    <tr>
                                        <td class="text-center"><?= $n; ?></td>
                                        
                                        <td><?= $dtl->keperluan; ?></td>
                                        <td class="text-center"><?= $dtl->tgl_doc; ?></td>
                                        <td class="text-center">-</td>
                                        <td class="text-center fw-bold text-primary"><?= $data_req_payment['currency']; ?></td>
                                        <td class="text-center text-muted small">-</td>
                                        <td class="text-center">
                                            <a href="<?= base_url('assets/expense/' . $dtl->doc_file); ?>" target="_blank" class="text-primary"><i class="fa fa-download fa-lg"></i></a>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($dtl->status == '2') : ?>
                                                <input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="form-check-input check_item">
                                            <?php elseif ($dtl->status == '3') : ?>
                                                <span class="badge bg-warning text-dark">Process</span>
                                            <?php elseif ($dtl->status == '4') : ?>
                                                <span class="badge bg-success">PAID</span>
                                            <?php else : ?>
                                                <span class="badge bg-light text-muted">Undefined</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                    foreach ($data_detail_pr_kasbon as $detail_kasbon_pr) :
                                        echo '<tr>';
                                        echo '<td></td><td></td>';
                                        echo '<td class="text-muted small">' . $detail_kasbon_pr->nm_material . '</td>';
                                        echo '<td></td>';
                                        echo '<td class="text-center">' . number_format($detail_kasbon_pr->qty) . '</td>';
                                        echo '<td class="text-center fw-bold text-primary">' . $data_req_payment['currency'] . '</td>';
                                        echo '<td>
                                                <table class="table table-nested table-sm mb-0 w-100 small">
                                                    <tr><td>Harga Material</td><td class="text-center" style="width:10px">:</td><td class="text-end">' . number_format($detail_kasbon_pr->total_harga, 2) . '</td></tr>
                                                    <tr><td>Bank Charge</td><td class="text-center">:</td><td class="text-end">' . number_format($data_req_payment['admin_bank'], 2) . '</td></tr>
                                                </table>
                                              </td>';
                                        echo '<td></td><td></td>';
                                        echo '</tr>';
                                        $gTotal += $detail_kasbon_pr->total_harga;
                                    endforeach;
                                } else {
                                    $gTotal += ($dtl->jumlah_kasbon + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']);
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $n; ?></td>
                                        
                                        <td><?= $dtl->keperluan; ?></td>
                                        <td class="text-center"><?= $dtl->tgl_doc; ?></td>
                                        <td class="text-center">1</td>
                                        <td class="text-center fw-bold text-primary"><?= $data_req_payment['currency']; ?></td>
                                        <td>
                                            <table class="table table-nested table-sm mb-0 w-100 small">
                                                <tr><td>Nilai Pengajuan</td><td class="text-center" style="width:10px">:</td><td class="text-end fw-semibold"><?= number_format($data_req_payment['jumlah'], 2) ?></td></tr>
                                                <tr><td>Nilai PPh</td><td class="text-center">:</td><td class="text-end text-danger"><?= number_format($data_req_payment['total_pph'], 2) ?></td></tr>
                                                <tr><td>Bank Charge</td><td class="text-center">:</td><td class="text-end"><?= number_format($data_req_payment['admin_bank'], 2) ?></td></tr>
                                                <tr class="table-light fw-bold"><td>Net Payment</td><td class="text-center">:</td><td class="text-end text-success"><?= number_format($data_req_payment['jumlah'] + $data_req_payment['admin_bank'] - $data_req_payment['total_pph'], 2) ?></td></tr>
                                            </table>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('assets/expense/' . $dtl->doc_file); ?>" target="_blank" class="text-primary"><i class="fa fa-download fa-lg"></i></a>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($dtl->status == '2') : ?>
                                                <input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="form-check-input check_item">
                                            <?php elseif ($dtl->status == '3') : ?>
                                                <span class="badge bg-warning text-dark">Process</span>
                                            <?php elseif ($dtl->status == '4') : ?>
                                                <span class="badge bg-success">PAID</span>
                                            <?php else : ?>
                                                <span class="badge bg-light text-muted">Undefined</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                    <?php
                                }
                     
                            // ------------------------- TYPE TRANSPORTASI -------------------------
                            elseif ($type == 'transportasi') :
                                $gTotal += ($dtl->jumlah_kasbon + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']); 
                    ?>
                                <tr>
                                    <td class="text-center"><?= $n; ?></td>
                                    
                                    <td><?= $dtl->keperluan; ?></td>
                                    <td class="text-center"><?= $dtl->tgl_doc; ?></td>
                                    <td class="text-center">1</td>
                                    <td class="text-center fw-bold text-primary"><?= $data_req_payment['currency']; ?></td>
                                    <td>
                                        <table class="table table-nested table-sm mb-0 w-100 small">
                                            <tr><td>Nilai Pengajuan</td><td class="text-center" style="width:10px">:</td><td class="text-end fw-semibold"><?= number_format($dtl->jumlah_kasbon, 2) ?></td></tr>
                                            <tr><td>Nilai PPh</td><td class="text-center">:</td><td class="text-end text-danger"><?= number_format($data_req_payment['total_pph'], 2) ?></td></tr>
                                            <tr><td>Bank Charge</td><td class="text-center">:</td><td class="text-end"><?= number_format($data_req_payment['admin_bank'], 2) ?></td></tr>
                                            <tr class="table-light fw-bold"><td>Net Payment</td><td class="text-center">:</td><td class="text-end text-success"><?= number_format(($dtl->jumlah_kasbon + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']), 2) ?></td></tr>
                                        </table>
                                    </td>
                                    <td class="text-center">
                                        <?php if (file_exists('./assets/expense/' . $dtl->doc_file) && $dtl->doc_file !== '') : ?>
                                            <a href="<?= base_url('assets/expense/' . $dtl->doc_file); ?>" target="_blank" class="text-primary"><i class="fa fa-download fa-lg"></i></a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($dtl->status == '1') : ?>
                                            <input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="form-check-input check_item">
                                        <?php elseif ($dtl->status == '2') : ?>
                                            <span class="badge bg-warning text-dark">Process</span>
                                        <?php elseif ($dtl->status == '3') : ?>
                                            <span class="badge bg-success">PAID</span>
                                        <?php else : ?>
                                            <span class="badge bg-light text-muted">Undefined</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                    <?php 
                            // ------------------------- TYPE NON PO -------------------------
                            elseif ($type == 'nonpo') :
                                $gTotal += ($dtl->total_request + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']); 
                    ?>
                                <tr>
                                    <td class="text-center"><?= $n; ?></td>
                                    
                                    <td><?= $dtl->deskripsi; ?></td>
                                    <td class="text-center"><?= $dtl->tgl_pr; ?></td>
                                    <td class="text-center">1</td>
                                    <td class="text-center fw-bold text-primary"><?= $data_req_payment['currency']; ?></td>
                                    <td>
                                        <table class="table table-nested table-sm mb-0 w-100 small">
                                            <tr><td>Nilai Pengajuan</td><td class="text-center" style="width:10px">:</td><td class="text-end fw-semibold"><?= number_format($dtl->total_request, 2) ?></td></tr>
                                            <tr><td>Nilai PPh</td><td class="text-center">:</td><td class="text-end text-danger"><?= number_format($data_req_payment['total_pph'], 2) ?></td></tr>
                                            <tr><td>Bank Charge</td><td class="text-center">:</td><td class="text-end"><?= number_format($data_req_payment['admin_bank'], 2) ?></td></tr>
                                            <tr class="table-light fw-bold"><td>Net Payment</td><td class="text-center">:</td><td class="text-end text-success"><?= number_format(($dtl->total_request + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']), 2) ?></td></tr>
                                        </table>
                                    </td>
                                    <td class="text-center">
                                        <?php if (file_exists('./assets/expense/' . $dtl->doc_file) && $dtl->doc_file !== '') : ?>
                                            <a href="<?= base_url('assets/expense/' . $dtl->doc_file); ?>" target="_blank" class="text-primary"><i class="fa fa-download fa-lg"></i></a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="form-check-input check_item">
                                    </td>
                                </tr>

                    <?php 
                            // ------------------------- TYPE PERIODIK -------------------------
                            elseif ($type == 'periodik') :
                                $gTotal += ($data_req_payment['jumlah'] + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']); 
                    ?>
                                <tr>
                                    <td class="text-center"><?= $n; ?></td>
                                    
                                    <td><?= $dtl->keterangan; ?></td>
                                    <td class="text-center"><?= $dtl->tanggal; ?></td>
                                    <td class="text-center">1</td>
                                    <td class="text-center fw-bold text-primary"><?= $data_req_payment['currency']; ?></td>
                                    <td>
                                        <table class="table table-nested table-sm mb-0 w-100 small">
                                            <tr><td>Nilai Pengajuan</td><td class="text-center" style="width:10px">:</td><td class="text-end fw-semibold"><?= number_format($data_req_payment['jumlah'], 2) ?></td></tr>
                                            <tr><td>Nilai PPh</td><td class="text-center">:</td><td class="text-end text-danger"><?= number_format($data_req_payment['total_pph'], 2) ?></td></tr>
                                            <tr><td>Bank Charge</td><td class="text-center">:</td><td class="text-end"><?= number_format($data_req_payment['admin_bank'], 2) ?></td></tr>
                                            <tr class="table-light fw-bold"><td>Net Payment</td><td class="text-center">:</td><td class="text-end text-success"><?= number_format(($data_req_payment['jumlah'] + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']), 2) ?></td></tr>
                                        </table>
                                    </td>
                                    <td class="text-center">
                                        <?php if (file_exists('./assets/bayar_rutin/' . $dtl->doc_file) && $dtl->doc_file !== '') : ?>
                                            <a href="<?= base_url('assets/bayar_rutin/' . $dtl->doc_file); ?>" target="_blank" class="text-primary"><i class="fa fa-download fa-lg"></i></a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="form-check-input check_item">
                                    </td>
                                </tr>

                    <?php 
                            // ------------------------- TYPE DIRECT PAYMENT -------------------------
                            elseif ($type == 'direct_payment') :
                                $gTotal += $dtl->grand_total;
                    ?>
                                <tr>
                                    <td class="text-center"><?= $n; ?></td>
                                    
                                    <td><?= $dtl->deskripsi; ?></td>
                                    <td class="text-center"><?= $dtl->tgl_doc; ?></td>
                                    <td class="text-end"><?= number_format($dtl->grand_total, 2) ?></td>
                                    <td class="text-center fw-bold text-primary"><?= $data_req_payment['currency']; ?></td>
                                    <td class="text-end fw-bold text-success"><?= number_format($dtl->grand_total, 2) ?></td>
                                    <td class="text-center">
                                        <a href="<?= base_url('assets/expense/' . $data_req_payment['link_doc']); ?>" target="_blank" class="text-primary"><i class="fa fa-download fa-lg"></i></a>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($dtl->sts == '2') : ?>
                                            <input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="form-check-input check_item">
                                        <?php elseif ($dtl->sts == '3') : ?>
                                            <span class="badge bg-warning text-dark">Process</span>
                                        <?php elseif ($dtl->sts == '4') : ?>
                                            <span class="badge bg-success">PAID</span>
                                        <?php else : ?>
                                            <span class="badge bg-light text-muted">Undefined</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                    <?php 
                            // ------------------------- TYPE INVOICE PO (DP/IMPORT/LOCAL) -------------------------
                            elseif (in_array($type, ['invoice_dp', 'invoice_import', 'invoice_local'])) :
                                $kurs_val = (float)($dtl->kurs ?? 1);
                                if ($kurs_val <= 0) $kurs_val = 1;
                                $nilai_invoice = ($type == 'invoice_dp') 
                                    ? ((float)($dtl->value_dp ?? 0) + (float)($dtl->nilai_ppn ?? 0)) * $kurs_val
                                    : ((float)($dtl->sisa_nilai ?? 0) + (float)($dtl->nilai_ppn ?? 0)) * $kurs_val;
                                $gTotal += $nilai_invoice;
                    ?>
                                <tr>
                                    <td class="text-center"><?= $n; ?></td>
                                    
                                    <td><?= $data_req_payment['keperluan'] ?? ($dtl->nomor_invoice ?? '-'); ?></td>
                                    <td class="text-center"><?= $dtl->invoice_date; ?></td>
                                    <td class="text-center">1</td>
                                    <td class="text-center fw-bold text-primary"><?= $dtl->currency ?? 'IDR'; ?></td>
                                    <td>
                                        <table class="table table-nested table-sm mb-0 w-100 small">
                                            <?php if ($type == 'invoice_dp') : ?>
                                                <tr><td>Value DP</td><td class="text-center" style="width:10px">:</td><td class="text-end fw-semibold"><?= number_format($dtl->value_dp ?? 0, 2) ?></td></tr>
                                            <?php else : ?>
                                                <tr><td>Sisa Nilai</td><td class="text-center" style="width:10px">:</td><td class="text-end fw-semibold"><?= number_format($dtl->sisa_nilai ?? 0, 2) ?></td></tr>
                                            <?php endif; ?>
                                            <tr><td>PPN</td><td class="text-center">:</td><td class="text-end"><?= number_format($dtl->nilai_ppn ?? 0, 2) ?></td></tr>
                                            <tr><td>Kurs</td><td class="text-center">:</td><td class="text-end"><?= number_format($kurs_val, 2) ?></td></tr>
                                            <tr class="table-light fw-bold"><td>Total (IDR)</td><td class="text-center">:</td><td class="text-end text-success"><?= number_format($nilai_invoice, 2) ?></td></tr>
                                        </table>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($dtl->file_invoice) && file_exists(FCPATH . 'uploads/invoice_dp/' . $dtl->file_invoice)) : ?>
                                            <a href="<?= base_url('uploads/invoice_dp/' . $dtl->file_invoice); ?>" target="_blank" class="text-primary"><i class="fa fa-download fa-lg"></i></a>
                                        <?php elseif (!empty($dtl->file_invoice) && file_exists(FCPATH . 'uploads/invoice_il/' . $dtl->file_invoice)) : ?>
                                            <a href="<?= base_url('uploads/invoice_il/' . $dtl->file_invoice); ?>" target="_blank" class="text-primary"><i class="fa fa-download fa-lg"></i></a>
                                        <?php else : ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="form-check-input check_item">
                                    </td>
                                </tr>
                    <?php 
                            endif;
                        endforeach;
                    }  ?>
                </tbody>
                <tfoot>
                    <tr class="table-success align-middle">
                        <th colspan="5" class="text-end fw-bold">Grand Total</th>
                        <th class="text-end fw-bold text-success fs-5"><?= number_format($gTotal, 2); ?></th>
                        <th colspan="2" class="text-center"></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <div class="card border border-light shadow-sm">
                    <div class="card-header bg-light py-2 fw-bold text-dark small text-center">Info Transfer Vendor</div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-borderless align-middle m-0 small">
                            <tbody>
                                <tr class="border-bottom"><td class="ps-3 py-2 text-muted">Bank</td><td class="text-center" style="width:20px">:</td><td class="pe-3 text-end fw-semibold"><?= $bank_id ?></td></tr>
                                <tr class="border-bottom"><td class="ps-3 py-2 text-muted">Account Number</td><td class="text-center">:</td><td class="pe-3 text-end fw-semibold text-primary"><?= $accnumber ?></td></tr>
                                <tr><td class="ps-3 py-2 text-muted">Account Name</td><td class="text-center">:</td><td class="pe-3 text-end fw-semibold"><?= $accname ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-8 text-end d-flex justify-content-md-end justify-content-start gap-2">
                <a href="<?= base_url($this->uri->segment(1) . '/list_approve_checker'); ?>" class="btn btn-secondary">
                    <i class="fa fa-reply me-1"></i> Kembali
                </a>
                <button type="button" class="btn btn-danger" id="reject">
                    <i class="fa fa-close me-1"></i> Reject
                </button>
                <button type="button" class="btn btn-success" id="process">
                    <i class="fa fa-check me-1"></i> Approve & Process
                </button>
            </div>
        </div>

    </div>
</div>
<?= form_close() ?>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
    var url_save = siteurl + 'request_payment/save_approval_checker';
    var url_reject = siteurl + 'request_payment/reject_approval';

    // Master Checkbox Logic (Check / Uncheck All)
    $(document).on('click', '.master_check', function() {
        const checked = $(this).is(':checked');
        $('.check_item').prop('checked', checked);
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

    // ------------------------- BUTTON APPROVE / PROCESS HANDLER -------------------------
    $(document).on('click', '#process', function(e) {
        e.preventDefault();
        
        if ($("#bank_coa").val() == "0") {
            Swal.fire({ icon: 'warning', title: 'Warning!', text: 'Bank tidak boleh kosong' });
            return false;
        }

        const isAnyChecked = $('.check_item').is(':checked');
        if (!isAnyChecked) {
            Swal.fire({ icon: 'warning', title: 'Warning!', text: 'Pilih minimal satu item yang akan di Approve!' });
            return false;
        }

        Swal.fire({
            title: "Anda Yakin?",
            text: "Item terpilih akan disetujui (Approve)!",
            icon: "info",
            showCancelButton: true,
            confirmButtonText: "Ya, Approve!",
            cancelButtonText: "Batal",
            customClass: { confirmButton: 'btn btn-success me-2', cancelButton: 'btn btn-secondary' },
            buttonsStyling: false
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
                            Swal.fire({ title: "Sukses!", text: "Data Berhasil Di Approve", icon: "success", timer: 1500, showConfirmButton: false })
                            .then(() => {
                                location.href = siteurl + active_controller + 'list_approve_checker';
                            });
                        } else {
                            Swal.fire({ title: "Gagal!", text: "Data Gagal Di Approve", icon: "error" });
                        }
                    },
                    error: function() {
                        Swal.fire({ title: "Gagal!", text: "Gagal memproses data via AJAX", icon: "error" });
                    }
                });
            }
        });
    });

    // ------------------------- BUTTON REJECT HANDLER -------------------------
    $(document).on('click', '#reject', function(e) {
        e.preventDefault();
        
        const isAnyChecked = $('.check_item').is(':checked');
        var reject_reason = $('.reject_reason').val().trim();

        if (!isAnyChecked || reject_reason === '') {
            Swal.fire({ 
                icon: 'warning', 
                title: 'Peringatan!', 
                text: 'Pastikan Anda mencentang item dan mengisi kolom Alasan Penolakan (Reject Reason)!' 
            });
            return false;
        }

        Swal.fire({
            title: "Anda Yakin?",
            text: "Item terpilih akan ditolak (Reject)!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, Reject!",
            cancelButtonText: "Batal",
            customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-secondary' },
            buttonsStyling: false
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
                            Swal.fire({ title: "Sukses!", text: "Data Berhasil Di Reject", icon: "success", timer: 1500, showConfirmButton: false })
                            .then(() => {
                                location.href = siteurl + active_controller + 'list_approve_checker';
                            });
                        } else {
                            Swal.fire({ title: "Gagal!", text: "Data Gagal Di Reject", icon: "error" });
                        }
                    },
                    error: function() {
                        Swal.fire({ title: "Gagal!", text: "Gagal memproses data penolakan via AJAX", icon: "error" });
                    }
                });
            }
        });
    });
</script>
