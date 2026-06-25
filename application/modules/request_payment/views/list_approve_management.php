<?php
$ENABLE_ADD     = has_permission('Approval_Request_Payment_Management.Add');
$ENABLE_MANAGE  = has_permission('Approval_Request_Payment_Management.Manage');
$ENABLE_VIEW    = has_permission('Approval_Request_Payment_Management.View');
$ENABLE_DELETE  = has_permission('Approval_Request_Payment_Management.Delete');

$count_transport = 0;
$count_kasbon = 0;
$count_expense = 0;
$count_periodik = 0;
$count_pembayaran_po = 0;
$count_direct_payment = 0;

foreach ($data as $item) :
    if ($item->tipe == 'transportasi') {
        $count_transport += 1;
    }
    if ($item->tipe == 'kasbon') {
        $count_kasbon += 1;
    }
    if ($item->tipe == 'expense') {
        if (strpos($item->no_doc, 'ER-') !== false || strpos($item->no_doc, 'ROS-') !== false) {
            $count_expense += 1;
        } else {
            $count_pembayaran_po += 1;
        }
    }
    if ($item->tipe == 'periodik') {
        $count_periodik += 1;
    }
    if ($item->tipe == 'direct_payment') {
        $count_direct_payment += 1;
    }
    if (in_array($item->tipe, ['invoice_dp', 'invoice_import', 'invoice_local'])) {
        $count_pembayaran_po += 1;
    }
endforeach;
?>

<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<style>
    .card-counter {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card-counter:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }

    .table thead th {
        background-color: #f8f9fa !important;
        color: #333 !important;
        font-weight: 600;
        vertical-align: middle;
    }
</style>

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form')); ?>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-md-4">
        <div class="card h-100 border-0 shadow-sm card-counter border-start border-4 border-success">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted fw-bold text-uppercase">Transportasi</small>
                    <h2 class="fw-bold m-0 mt-1 text-success"><?= $count_transport ?></h2>
                </div>
                <button type="button" class="btn btn-sm btn-outline-success btn_view_req" data-val="transportasi"><i class="fa fa-eye"></i> View</button>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4">
        <div class="card h-100 border-0 shadow-sm card-counter border-start border-4 border-warning">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted fw-bold text-uppercase">Kasbon</small>
                    <h2 class="fw-bold m-0 mt-1 text-warning"><?= $count_kasbon ?></h2>
                </div>
                <button type="button" class="btn btn-sm btn-outline-warning text-dark btn_view_req" data-val="kasbon"><i class="fa fa-eye"></i> View</button>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4">
        <div class="card h-100 border-0 shadow-sm card-counter border-start border-4 border-primary">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted fw-bold text-uppercase">Expense</small>
                    <h2 class="fw-bold m-0 mt-1 text-primary"><?= $count_expense ?></h2>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary btn_view_req" data-val="expense"><i class="fa fa-eye"></i> View</button>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4">
        <div class="card h-100 border-0 shadow-sm card-counter border-start border-4 border-danger">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted fw-bold text-uppercase">Periodik</small>
                    <h2 class="fw-bold m-0 mt-1 text-danger"><?= $count_periodik ?></h2>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger btn_view_req" data-val="periodik"><i class="fa fa-eye"></i> View</button>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4">
        <div class="card h-100 border-0 shadow-sm card-counter border-start border-4 border-info">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted fw-bold text-uppercase">Pembayaran PO</small>
                    <h2 class="fw-bold m-0 mt-1 text-info"><?= $count_pembayaran_po ?></h2>
                </div>
                <button type="button" class="btn btn-sm btn-outline-info text-dark btn_view_req" data-val="pembayaran_po"><i class="fa fa-eye"></i> View</button>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4">
        <div class="card h-100 border-0 shadow-sm card-counter border-start border-4 border-secondary">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted fw-bold text-uppercase">Direct Payment</small>
                    <h2 class="fw-bold m-0 mt-1 text-secondary"><?= $count_direct_payment ?></h2>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary btn_view_req" data-val="direct_payment"><i class="fa fa-eye"></i> View</button>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">

    <div class="col-12 list_transportasi" style="display: none;">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="fw-bold text-success m-0">Data Request: Transportasi</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered align-middle w-100 data-table-init">
                        <thead class="text-center">
                            <tr>
                                <th>No Dokumen</th>
                                <th>Request By</th>
                                <th>Tanggal</th>
                                <th>Keperluan</th>
                                <th>Tipe</th>
                                <th>Nilai Pengajuan</th>
                                <th>Tanggal Pembayaran</th>
                                <th>Status</th>
                                <th style="width: 10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $item_transportasi) :
                                if ($item_transportasi->tipe == 'transportasi') { ?>
                                    <tr>
                                        <td class="text-center fw-semibold"><?= $item_transportasi->no_doc ?></td>
                                        <td><?= $item_transportasi->nama ?></td>
                                        <td class="text-center"><?= $item_transportasi->tgl_doc ?></td>
                                        <td><?= $item_transportasi->keperluan ?></td>
                                        <td class="text-center small"><?= $item_transportasi->tipe ?></td>
                                        <td class="text-end fw-semibold"><?= number_format($item_transportasi->jumlah) ?></td>
                                        <td class="text-center"><?= $item_transportasi->tanggal ?></td>
                                        <td class="text-center">
                                            <?php
                                            $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_transportasi->no_doc, 'ids' => $item_transportasi->ids])->row_array();
                                            if ($item_transportasi->status == '0' || empty($get_sts_payment)) {
                                                echo ($item_transportasi->status == '9') ? '<span class="badge bg-danger">Rejected</span>' : '<span class="badge bg-info">Open</span>';
                                            } else if ($get_sts_payment['status'] == 1) {
                                                echo '<span class="badge bg-warning text-dark">Process</span>';
                                            } else if ($get_sts_payment['status'] == 2) {
                                                echo '<span class="badge bg-success">Close</span>';
                                            } else {
                                                echo '<span class="badge bg-light text-muted">Undefined</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($ENABLE_MANAGE) : ?>
                                                <a href="<?= base_url($this->uri->segment(1) . '/approval_payment/?type=' . $item_transportasi->tipe . '&id=' . $item_transportasi->id . '&nilai=' . $item_transportasi->jumlah); ?>" class="btn btn-success btn-sm w-100"><i class="fa fa-check-square-o"></i> Approve</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                            <?php }
                            endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 list_kasbon" style="display: none;">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="fw-bold text-warning m-0">Data Request: Kasbon</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered align-middle w-100 data-table-init">
                        <thead class="text-center">
                            <tr>
                                <th>No Dokumen</th>
                                <th>Request By</th>
                                <th>Tanggal</th>
                                <th>Keperluan</th>
                                <th>Tipe</th>
                                <th>Nilai Pengajuan</th>
                                <th>Tanggal Pembayaran</th>
                                <th>Status</th>
                                <th style="width: 15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $item_kasbon) :
                                if ($item_kasbon->tipe == 'kasbon') {
                                    $get_kasbon = $this->db->get_where('tr_kasbon', array('no_doc' => $item_kasbon->no_doc))->row();
                                    $get_kasbon_sendigs = $this->db->get_where('tr_kasbon', ['no_doc' => $item_kasbon->no_doc])->row();
                                    $no_kasbon_consultant = (!empty($get_kasbon_sendigs)) ? $get_kasbon_sendigs->no_kasbon_consultant : '';
                                    $get_kasbon_header = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_header', array('id' => $no_kasbon_consultant))->row();
                            ?>
                                    <tr>
                                        <td class="text-center fw-semibold"><?= $item_kasbon->no_doc ?></td>
                                        <td><?= $item_kasbon->nama ?></td>
                                        <td class="text-center"><?= $item_kasbon->tgl_doc ?></td>
                                        <td><?= $item_kasbon->keperluan ?></td>
                                        <td class="text-center small"><?= $item_kasbon->tipe ?></td>
                                        <td class="text-end fw-semibold"><?= number_format($item_kasbon->jumlah) ?></td>
                                        <td class="text-center"><?= $item_kasbon->tanggal ?></td>
                                        <td class="text-center">
                                            <?php
                                            $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_kasbon->no_doc, 'ids' => $item_kasbon->ids])->row_array();
                                            if ($item_kasbon->status == '0' || empty($get_sts_payment)) {
                                                echo ($item_kasbon->status == '9') ? '<span class="badge bg-danger">Rejected</span>' : '<span class="badge bg-info">Open</span>';
                                            } else if ($get_sts_payment['status'] == 1) {
                                                echo '<span class="badge bg-warning text-dark">Process</span>';
                                            } else if ($get_sts_payment['status'] == 2) {
                                                echo '<span class="badge bg-success">Close</span>';
                                            } else {
                                                echo '<span class="badge bg-light text-muted">Undefined</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <?php if ($ENABLE_MANAGE && $get_kasbon->project_consultant == '0') : ?>
                                                    <a href="<?= base_url($this->uri->segment(1) . '/approval_payment/?type=' . $item_kasbon->tipe . '&id=' . $item_kasbon->id . '&nilai=' . $item_kasbon->jumlah); ?>" class="btn btn-success btn-sm w-100"><i class="fa fa-check-square-o"></i> Approve</a>
                                                <?php endif; ?>
                                                <?php if ($ENABLE_MANAGE && $get_kasbon->project_consultant == '1') :
                                                    echo '<a href="' . base_url('approval_request_payment/approval_payment/?id_cons=' . str_replace('/', '|', $get_kasbon->no_kasbon_consultant)) . '&id_sendigs=' . $item_kasbon->no_doc . '" class="btn btn-success btn-sm"><i class="fa fa-check-square-o"></i> Approve</a>';
                                                    $link_view = '#';
                                                    if ($get_kasbon_header->tipe == '1') {
                                                        $link_view = base_url('kasbon_project/view_kasbon_subcont/' . urlencode(str_replace('/', '|', $get_kasbon->no_kasbon_consultant)));
                                                    }
                                                    if ($get_kasbon_header->tipe == '2') {
                                                        $link_view = base_url('kasbon_project/view_kasbon_akomodasi/' . urlencode(str_replace('/', '|', $get_kasbon->no_kasbon_consultant)));
                                                    }
                                                    if ($get_kasbon_header->tipe == '3') {
                                                        $link_view = base_url('kasbon_project/view_kasbon_others/' . urlencode(str_replace('/', '|', $get_kasbon->no_kasbon_consultant)));
                                                    }
                                                    echo '<a href="' . $link_view . '" class="btn btn-sm btn-info text-white" title="View Kasbon" target="_blank"><i class="fa fa-eye"></i></a>';
                                                endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                            <?php }
                            endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 list_expense" style="display: none;">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="fw-bold text-primary m-0">Data Request: Expense</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered align-middle w-100 data-table-init">
                        <thead class="text-center">
                            <tr>
                                <th>No Dokumen</th>
                                <th>Request By</th>
                                <th>Tanggal</th>
                                <th>Keperluan</th>
                                <th>Tipe</th>
                                <th>Nilai Pengajuan</th>
                                <th>Tanggal Pembayaran</th>
                                <th>Status</th>
                                <th style="width: 10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $item_expense) :
                                if ($item_expense->tipe == 'expense') {
                                    $tipe = ucfirst($item_expense->tipe);
                                    $get_expense = $this->db->get_where('tr_expense', ['no_doc' => $item_expense->no_doc])->row_array();
                                    if ($get_expense['exp_inv_po'] == '1') {
                                        $tipe = 'Pembayaran PO';
                                    }
                                    if (strpos($item_expense->no_doc, 'ROS-') !== false) {
                                        $tipe = 'Pembayaran PIB';
                                    }

                                    if (strpos($item_expense->no_doc, 'ER-') !== false || strpos($item_expense->no_doc, 'ROS-') !== false) {
                            ?>
                                        <tr>
                                            <td class="text-center fw-semibold"><?= $item_expense->no_doc ?></td>
                                            <td><?= $item_expense->nama ?></td>
                                            <td class="text-center"><?= $item_expense->tgl_doc ?></td>
                                            <td><?= $item_expense->keperluan ?></td>
                                            <td class="text-center small"><?= $tipe ?></td>
                                            <td class="text-end fw-semibold"><?= number_format($item_expense->jumlah) ?></td>
                                            <td class="text-center"><?= $item_expense->tanggal ?></td>
                                            <td class="text-center">
                                                <?php
                                                $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_expense->no_doc, 'ids' => $item_expense->ids])->row_array();
                                                if ($item_expense->status == '0' || empty($get_sts_payment)) {
                                                    echo ($item_expense->status == '9') ? '<span class="badge bg-danger">Rejected</span>' : '<span class="badge bg-info">Open</span>';
                                                } else if ($get_sts_payment['status'] == 1) {
                                                    echo '<span class="badge bg-warning text-dark">Process</span>';
                                                } else if ($get_sts_payment['status'] == 2) {
                                                    echo '<span class="badge bg-success">Close</span>';
                                                } else {
                                                    echo '<span class="badge bg-light text-muted">Undefined</span>';
                                                }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($ENABLE_MANAGE || $get_sts_payment['status'] < 1) {
                                                    if ($get_expense['project_consultant'] == '1') {
                                                        echo '<a href="' . base_url('approval_request_payment/approval_payment/?id_exp_consultant=' . urlencode(str_replace('/', '|', $get_expense['no_expense_consultant']))) . '&id_expense=' . $item_expense->no_doc . '" class="btn btn-sm btn-success w-100"><i class="fa fa-check-square-o"></i> Approve</a>';
                                                    } else {
                                                        echo '<a href="' . base_url($this->uri->segment(1) . '/approval_payment/?type=' . $item_expense->tipe . '&id=' . $item_expense->id . '&nilai=' . $item_expense->jumlah) . '" class="btn btn-sm btn-success w-100"><i class="fa fa-check-square-o"></i> Approve</a>';
                                                    }
                                                } ?>
                                            </td>
                                        </tr>
                            <?php }
                                }
                            endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 list_periodik" style="display: none;">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="fw-bold text-danger m-0">Data Request: Periodik</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered align-middle w-100 data-table-init">
                        <thead class="text-center">
                            <tr>
                                <th>No Dokumen</th>
                                <th>Request By</th>
                                <th>Tanggal</th>
                                <th>Keperluan</th>
                                <th>Tipe</th>
                                <th>Nilai Pengajuan</th>
                                <th>Tanggal Pembayaran</th>
                                <th>Status</th>
                                <th style="width: 10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $item_periodik) :
                                if ($item_periodik->tipe == 'periodik') { ?>
                                    <tr>
                                        <td class="text-center fw-semibold"><?= $item_periodik->no_doc ?></td>
                                        <td><?= $item_periodik->nama ?></td>
                                        <td class="text-center"><?= $item_periodik->tgl_doc ?></td>
                                        <td><?= $item_periodik->keperluan ?></td>
                                        <td class="text-center small"><?= $item_periodik->tipe ?></td>
                                        <td class="text-end fw-semibold"><?= number_format($item_periodik->jumlah) ?></td>
                                        <td class="text-center"><?= $item_periodik->tanggal ?></td>
                                        <td class="text-center">
                                            <?php
                                            $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_periodik->no_doc, 'ids' => $item_periodik->ids])->row_array();
                                            if ($item_periodik->status == '0' || empty($get_sts_payment)) {
                                                echo ($item_periodik->status == '9') ? '<span class="badge bg-danger">Rejected</span>' : '<span class="badge bg-info">Open</span>';
                                            } else if ($get_sts_payment['status'] == 1) {
                                                echo '<span class="badge bg-warning text-dark">Process</span>';
                                            } else if ($get_sts_payment['status'] == 2) {
                                                echo '<span class="badge bg-success">Close</span>';
                                            } else {
                                                echo '<span class="badge bg-light text-muted">Undefined</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($ENABLE_MANAGE) : ?>
                                                <a href="<?= base_url($this->uri->segment(1) . '/approval_payment/?type=' . $item_periodik->tipe . '&id=' . $item_periodik->id . '&nilai=' . $item_periodik->jumlah); ?>" class="btn btn-success btn-sm w-100"><i class="fa fa-check-square-o"></i> Approve</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                            <?php }
                            endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 list_pembayaran_po" style="display: none;">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="fw-bold text-info m-0">Data Request: Pembayaran PO</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered align-middle w-100 data-table-init">
                        <thead class="text-center">
                            <tr>
                                <th>No Dokumen</th>
                                <th>No Invoice</th>
                                <th>Request By</th>
                                <th>Tanggal</th>
                                <th>Keperluan</th>
                                <th>Tipe</th>
                                <th>Nilai Pengajuan</th>
                                <th>Tanggal Pembayaran</th>
                                <th>Keterangan PO</th>
                                <th>Status</th>
                                <th style="width: 12%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $item_expense) :
                                $no_invoice = (isset($list_no_invoice[$item_expense->no_doc])) ? $list_no_invoice[$item_expense->no_doc] : '';
                                if ($item_expense->tipe == 'expense') {
                                    $tipe = ucfirst($item_expense->tipe);
                                    $get_expense = $this->db->get_where('tr_expense', ['no_doc' => $item_expense->no_doc])->row_array();
                                    if ($get_expense['exp_inv_po'] == '1') {
                                        $tipe = 'Pembayaran PO';
                                    }
                                    if (strpos($item_expense->no_doc, 'ROS-') !== false) {
                                        $tipe = 'Pembayaran PIB';
                                    }

                                    if ($get_expense['exp_inv_po'] == '1') {
                                        $exp_id_po = explode(',', $get_expense['id_po']);
                                        $po_note = [];
                                        $this->db->select('note')->from('tr_purchase_order')->where_in('no_surat', $exp_id_po);
                                        $get_po_note = $this->db->get()->result();
                                        foreach ($get_po_note as $item_po_note) {
                                            $po_note[] = $item_po_note->note;
                                        }
                                        $po_note = implode(', ', $po_note);
                            ?>
                                        <tr>
                                            <td class="text-center fw-semibold"><?= $item_expense->no_doc ?></td>
                                            <td class="text-center"><?= $no_invoice ?></td>
                                            <td><?= $item_expense->nama ?></td>
                                            <td class="text-center"><?= $item_expense->tgl_doc ?></td>
                                            <td><?= $item_expense->keperluan ?></td>
                                            <td class="text-center small"><?= $tipe ?></td>
                                            <td class="text-end fw-semibold"><?= number_format($item_expense->jumlah) ?></td>
                                            <td class="text-center"><?= $item_expense->tanggal ?></td>
                                            <td><small class="text-muted"><?= $po_note ?></small></td>
                                            <td class="text-center">
                                                <?php
                                                $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_expense->no_doc, 'ids' => $item_expense->ids])->row_array();
                                                if ($item_expense->status == '0' || empty($get_sts_payment)) {
                                                    echo ($item_expense->status == '9') ? '<span class="badge bg-danger">Rejected</span>' : '<span class="badge bg-info">Open</span>';
                                                } else if ($get_sts_payment['status'] == 1) {
                                                    echo '<span class="badge bg-warning text-dark">Process</span>';
                                                } else if ($get_sts_payment['status'] == 2) {
                                                    echo '<span class="badge bg-success">Close</span>';
                                                } else {
                                                    echo '<span class="badge bg-light text-muted">Undefined</span>';
                                                }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($ENABLE_MANAGE) : ?>
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        <a href="<?= base_url($this->uri->segment(1) . '/approval_payment/?type=' . $item_expense->tipe . '&id=' . $item_expense->id . '&nilai=' . $item_expense->jumlah); ?>" class="btn btn-success btn-sm" title="Approve"><i class="fa fa-check-square"></i></a>
                                                        <button type="button" class="btn btn-sm btn-info text-white view_receive_invoice" data-id_invoice="<?= $item_expense->no_doc ?>" title="View Invoice"><i class="fa fa-eye"></i></button>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                            <?php }
                                }
                            endforeach; ?>

                            <?php
                            // === Tambahan: Invoice PO (DP/Import/Local) ===
                            foreach ($data as $item_inv) :
                                if (in_array($item_inv->tipe, ['invoice_dp', 'invoice_import', 'invoice_local'])) {
                                    $tipe_label = str_replace(['invoice_dp', 'invoice_import', 'invoice_local'], ['Invoice DP', 'Invoice Import', 'Invoice Local'], $item_inv->tipe);
                            ?>
                                    <tr>
                                        <td class="text-center fw-semibold"><?= $item_inv->no_surat ?></td>
                                        <td class="text-center"><?= $item_inv->keperluan ?></td>
                                        <td><?= $item_inv->nama ?></td>
                                        <td class="text-center"><?= $item_inv->tgl_doc ?></td>
                                        <td><?= $item_inv->keperluan ?></td>
                                        <td class="text-center small"><?= $tipe_label ?></td>
                                        <td class="text-end fw-semibold"><?= number_format($item_inv->jumlah, 2) ?></td>
                                        <td class="text-center"><?= $item_inv->tanggal ?></td>
                                        <td><small class="text-muted"><?= $item_inv->currency ?? 'IDR' ?></small></td>
                                        <td class="text-center">
                                            <?php if ($item_inv->status == '0') {
                                                echo '<span class="badge bg-info">Open</span>';
                                            } elseif ($item_inv->status == '9') {
                                                echo '<span class="badge bg-danger">Rejected</span>';
                                            } else {
                                                echo '<span class="badge bg-warning text-dark">Process</span>';
                                            } ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($ENABLE_MANAGE) : ?>
                                                <a href="<?= base_url($this->uri->segment(1) . '/approval_payment/?type=' . $item_inv->tipe . '&id=' . $item_inv->id . '&nilai=' . $item_inv->jumlah); ?>" class="btn btn-success btn-sm" title="Approve"><i class="fa fa-check-square"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                            <?php }
                            endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 list_direct_payment" style="display: none;">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="fw-bold text-secondary m-0">Data Request: Direct Payment</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered align-middle w-100 data-table-init">
                        <thead class="text-center">
                            <tr>
                                <th>No Dokumen</th>
                                <th>Request By</th>
                                <th>Tanggal</th>
                                <th>Keperluan</th>
                                <th>Tipe</th>
                                <th>Nilai Pengajuan</th>
                                <th>Tanggal Pembayaran</th>
                                <th>Status</th>
                                <th style="width: 10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $item_dp) :
                                if ($item_dp->tipe == 'direct_payment') { ?>
                                    <tr>
                                        <td class="text-center fw-semibold"><?= $item_dp->no_doc ?></td>
                                        <td><?= $item_dp->nama ?></td>
                                        <td class="text-center"><?= $item_dp->tgl_doc ?></td>
                                        <td><?= $item_dp->keperluan ?></td>
                                        <td class="text-center small"><?= $item_dp->tipe ?></td>
                                        <td class="text-end fw-semibold"><?= number_format($item_dp->jumlah) ?></td>
                                        <td class="text-center"><?= $item_dp->tgl_doc ?></td>
                                        <td class="text-center">
                                            <?php
                                            $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_dp->no_doc, 'ids' => $item_dp->ids])->row_array();
                                            if ($item_dp->status == '0' || empty($get_sts_payment)) {
                                                echo ($item_dp->status == '9') ? '<span class="badge bg-danger">Rejected</span>' : '<span class="badge bg-info">Open</span>';
                                            } else if ($get_sts_payment['status'] == 1) {
                                                echo '<span class="badge bg-warning text-dark">Process</span>';
                                            } else if ($get_sts_payment['status'] == 2) {
                                                echo '<span class="badge bg-success">Close</span>';
                                            } else {
                                                echo '<span class="badge bg-light text-muted">Undefined</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($ENABLE_MANAGE) : ?>
                                                <a href="<?= base_url($this->uri->segment(1) . '/approval_payment/?type=' . $item_dp->tipe . '&id=' . $item_dp->id . '&nilai=' . $item_dp->jumlah); ?>" class="btn btn-success btn-sm w-100"><i class="fa fa-check-square"></i> Approve</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                            <?php }
                            endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= form_close() ?>

<div class="modal fade" id="modal_view_receive_invoice" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalLabel">View Receive Invoice</h5>
                <button type="button" class="btn-close" data-shadow="none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="ModalViewSPPLM" style="max-height: 80vh; overflow-y: auto;">
                <div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // Inisialisasi DataTables secara dinamis untuk seluruh tabel ber-class '.data-table-init'
        $('.data-table-init').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "language": {
                "search": "Cari data:",
                "lengthMenu": "Tampilkan _MENU_ entri"
            }
        });
    });

    // Toggle Visibility Section List berdasarkan Card Counter yang di-click
    $(document).on("click", ".btn_view_req", function() {
        var val = $(this).data('val');

        // Sembunyikan semua tabel list terlebih dahulu, lalu toggle target list
        $("[class*='list_']").not(".list_" + val).hide();
        $(".list_" + val).slideToggle(300);
    });

    // Request AJAX View Invoice Modal Pop-up 
    $(document).on('click', '.view_receive_invoice', function() {
        var id_invoice = $(this).data('id_invoice');
        $('#ModalViewSPPLM').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>');
        $('#modal_view_receive_invoice').modal('show');

        $.ajax({
            type: "POST",
            url: siteurl + active_controller + "view_receive_invoice",
            data: {
                "id_invoice": id_invoice
            },
            cache: false,
            success: function(result) {
                $('#ModalViewSPPLM').html(result);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Gagal memuat rincian invoice, silakan coba lagi nanti!'
                });
            }
        });
    });
</script>