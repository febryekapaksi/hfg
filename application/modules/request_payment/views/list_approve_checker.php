<?php
$ENABLE_ADD     = has_permission('Approval_Request_Payment_Checker.Add');
$ENABLE_MANAGE  = has_permission('Approval_Request_Payment_Checker.Manage');
$ENABLE_VIEW    = has_permission('Approval_Request_Payment_Checker.View');
$ENABLE_DELETE  = has_permission('Approval_Request_Payment_Checker.Delete');

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
    if (in_array($item->tipe, ['invoice_dp', 'invoice_import', 'invoice_local'])) {
        $count_pembayaran_po += 1;
    }
    if ($item->tipe == 'direct_payment') {
        $count_direct_payment += 1;
    }
endforeach;
?>

<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
    .table thead th {
        background-color: #198754 !important;
        color: white !important;
        vertical-align: middle;
    }

    .table-responsive {
        border-radius: 0.375rem;
        overflow: hidden;
    }

    .swal2-container {
        z-index: 99999 !important;
    }
</style>

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form')); ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-4">
                <div class="card h-100 border-0 shadow-sm card-counter border-start border-4 border-success">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold te `xt-uppercase">Transportasi</small>
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

        <div class="col-12 list_transportasi table-section mb-4" style="display: none;">
            <h4 class="text-dark fw-bold mb-3"><i class="fa fa-car me-2"></i>Transportasi</h4>
            <div class="table-responsive shadow-sm">
                <table class="table table-striped table-hover table-bordered align-middle mb-0" id="table_transportasi" width="100%">
                    <thead class="text-center">
                        <tr>
                            <th>No Dokument</th>
                            <th>Request By</th>
                            <th>Tanggal</th>
                            <th>Keperluan</th>
                            <th>Tipe</th>
                            <th>Nilai Pengajuan</th>
                            <th>Tanggal Pembayaran</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($data as $item_transportasi) :
                            if ($item_transportasi->tipe == 'transportasi') {
                                echo '<tr>';
                                echo '<td class="text-center fw-semibold">' . $item_transportasi->no_doc . '</td>';
                                echo '<td>' . $item_transportasi->nama . '</td>';
                                echo '<td class="text-center">' . $item_transportasi->tgl_doc . '</td>';
                                echo '<td>' . $item_transportasi->keperluan . '</td>';
                                echo '<td class="text-center text-capitalize">' . $item_transportasi->tipe . '</td>';
                                echo '<td class="text-end fw-semibold">' . number_format($item_transportasi->jumlah) . '</td>';
                                echo '<td class="text-center">' . $item_transportasi->tanggal . '</td>';
                                echo '<td class="text-center">';
                                $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_transportasi->no_doc, 'ids' => $item_transportasi->ids])->row_array();

                                if ($item_transportasi->status == '0' || empty($get_sts_payment)) {
                                    echo ($item_transportasi->status == '9') ? '<span class="badge bg-danger">Rejected</span>' : '<span class="badge bg-info text-dark">Open</span>';
                                } elseif ($get_sts_payment['status'] == 1) {
                                    echo '<span class="badge bg-warning text-dark">Process</span>';
                                } elseif ($get_sts_payment['status'] == 2) {
                                    echo '<span class="badge bg-secondary">Close</span>';
                                } else {
                                    echo '<span class="badge bg-light text-dark">Undefined</span>';
                                }
                                echo '</td>';
                                echo '<td class="text-center">';
                                if ($ENABLE_MANAGE) : ?>
                                    <a href="<?= base_url($this->uri->segment(1) . '/approval_payment_checker/?type=' . $item_transportasi->tipe . '&id=' . $item_transportasi->id . '&nilai=' . $item_transportasi->jumlah); ?>" class="btn btn-success btn-sm"><i class="fa fa-check-square-o me-1"></i>Approve</a>
                        <?php endif;
                                echo '</td>';
                                echo '</tr>';
                            }
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-12 list_kasbon table-section mb-4" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-dark fw-bold m-0"><i class="fa fa-money me-2"></i>Kasbon</h4>
                <a href="<?= base_url('approval_request_payment/export_excel_kasbon_checker/?tingkat=1') ?>" class="btn btn-sm btn-success"><i class="fa fa-file-excel-o me-1"></i> Export Excel</a>
            </div>
            <div class="table-responsive shadow-sm">
                <table class="table table-striped table-hover table-bordered align-middle mb-0" id="table_kasbon" width="100%">
                    <thead class="text-center">
                        <tr>
                            <th>No Dokument</th>
                            <th>Request By</th>
                            <th>Tanggal</th>
                            <th>Keperluan</th>
                            <th>Tipe</th>
                            <th>Nilai Pengajuan</th>
                            <th>Tanggal Pembayaran</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($data_kasbon as $item_kasbon) :
                            if ($item_kasbon->tipe == 'kasbon') {
                                $get_kasbon = $this->db->get_where('tr_kasbon', array('no_doc' => $item_kasbon->no_doc))->row();
                                $get_req_payment = $this->db->get_where('request_payment', ['no_doc' => $item_kasbon->no_doc])->result();
                                $no_kasbon = (!empty($get_kasbon->no_kasbon_consultant)) ? $get_kasbon->no_kasbon_consultant : $item_kasbon->no_doc;
                                echo '<tr>';
                                echo '<td class="text-center fw-semibold">' . $no_kasbon . '</td>';
                                echo '<td>' . $item_kasbon->nama . '</td>';
                                echo '<td class="text-center">' . $item_kasbon->tgl_doc . '</td>';
                                echo '<td>' . $item_kasbon->keperluan . '</td>';
                                echo '<td class="text-center text-capitalize">' . $item_kasbon->tipe . '</td>';
                                echo '<td class="text-end fw-semibold">' . number_format($item_kasbon->jumlah) . '</td>';
                                echo '<td class="text-center">' . $item_kasbon->tanggal . '</td>';
                                echo '<td class="text-center">';
                                $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_kasbon->no_doc, 'ids' => $item_kasbon->ids])->row_array();

                                if ($item_kasbon->status == '0' || empty($get_sts_payment)) {
                                    echo ($item_kasbon->status == '9') ? '<span class="badge bg-danger">Rejected</span>' : '<span class="badge bg-info text-dark">Open</span>';
                                } elseif ($get_sts_payment['status'] == 1) {
                                    echo '<span class="badge bg-warning text-dark">Process</span>';
                                } elseif ($get_sts_payment['status'] == 2) {
                                    echo '<span class="badge bg-secondary">Close</span>';
                                } else {
                                    echo '<span class="badge bg-light text-dark">Undefined</span>';
                                }
                                echo '</td>';
                                echo '<td class="text-center"><div class="d-flex justify-content-center gap-1">';
                                if ($ENABLE_MANAGE && $get_kasbon->project_consultant == '0') :
                                    if ($item_kasbon->status !== '2' && count($get_req_payment) > 0 && $get_req_payment[0]->app_checker === null) : ?>
                                        <a href="<?= base_url($this->uri->segment(1) . '/approval_payment_checker/?type=' . $item_kasbon->tipe . '&id=' . $item_kasbon->id . '&nilai=' . $item_kasbon->jumlah); ?>" class="btn btn-success btn-sm"><i class="fa fa-check-square-o"></i> Approve</a>
                        <?php endif;
                                endif;
                                if ($ENABLE_MANAGE && $get_kasbon->project_consultant == '1') :
                                    $get_kasbon_sendigs = $this->db->get_where('tr_kasbon', ['no_doc' => $item_kasbon->no_doc])->row();
                                    $no_kasbon_consultant = (!empty($get_kasbon_sendigs)) ? $get_kasbon_sendigs->no_kasbon_consultant : '';
                                    $get_kasbon_header = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_header', array('id' => $no_kasbon_consultant))->row();

                                    $link_view = '';
                                    if (!empty($get_kasbon_header)) {
                                        if ($get_kasbon_header->tipe == '1') $link_view = base_url('kasbon_project/view_kasbon_subcont/' . urlencode(str_replace('/', '|', $get_kasbon_header->id)));
                                        if ($get_kasbon_header->tipe == '2') $link_view = base_url('kasbon_project/view_kasbon_akomodasi/' . urlencode(str_replace('/', '|', $get_kasbon_header->id)));
                                        if ($get_kasbon_header->tipe == '3') $link_view = base_url('kasbon_project/view_kasbon_others/' . urlencode(str_replace('/', '|', $get_kasbon_header->id)));
                                    }

                                    if (($item_kasbon->status !== '2' && is_null($item_kasbon->app_checker))) :
                                        echo '<a href="' . base_url('approval_request_payment/approval_payment_checker/?id_exp_consultant=' . str_replace('/', '|', $get_kasbon->no_kasbon_consultant)) . '&id_kasbon=' . $item_kasbon->no_doc . '" class="btn btn-success btn-sm"><i class="fa fa-check-square-o"></i> Approve</a>';
                                    endif;

                                    echo '<a href="' . base_url('approval_request_payment/print_kasbon/' . urlencode(str_replace('/', '|', $get_kasbon->no_kasbon_consultant))) . '" class="btn btn-sm btn-secondary" title="Print PDF" target="_blank"><i class="fa fa-print"></i></a>';
                                    echo '<a href="' . $link_view . '" class="btn btn-sm btn-info text-white" title="View Kasbon" target="_blank"><i class="fa fa-eye"></i></a>';
                                endif;
                                echo '</div></td>';
                                echo '</tr>';
                            }
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-12 list_expense table-section mb-4" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-dark fw-bold m-0"><i class="fa fa-file-text me-2"></i>Expense</h4>
                <a href="<?= base_url('approval_request_payment/export_excel_expense_checker/?tingkat=1') ?>" class="btn btn-sm btn-success"><i class="fa fa-file-excel-o me-1"></i> Export Excel</a>
            </div>
            <div class="table-responsive shadow-sm">
                <table class="table table-striped table-hover table-bordered align-middle mb-0" id="table_expense" width="100%">
                    <thead class="text-center">
                        <tr>
                            <th>No Dokument</th>
                            <th>Request By</th>
                            <th>Tanggal</th>
                            <th>Keperluan</th>
                            <th>Tipe</th>
                            <th>Nilai Pengajuan</th>
                            <th>Tanggal Pembayaran</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($data as $item_expense) :
                            if ($item_expense->tipe == 'expense') {
                                $tipe = ucfirst($item_expense->tipe);
                                $get_expense = $this->db->get_where('tr_expense', ['no_doc' => $item_expense->no_doc])->row_array();
                                $get_req_payment = $this->db->get_where('request_payment', ['no_doc' => $item_expense->no_doc])->result();
                                if ($get_expense['exp_inv_po'] == '1') $tipe = 'Pembayaran PO';
                                if (strpos($item_expense->no_doc, 'ROS') === true) $tipe = 'Pembayaran PIB';

                                if (strpos($item_expense->no_doc, 'ER-') !== false || strpos($item_expense->no_doc, 'ROS-') !== false) {
                                    echo '<tr>';
                                    echo '<td class="text-center fw-semibold">' . $item_expense->no_doc . '</td>';
                                    echo '<td>' . $item_expense->nama . '</td>';
                                    echo '<td class="text-center">' . $item_expense->tgl_doc . '</td>';
                                    echo '<td>' . $item_expense->keperluan . '</td>';
                                    echo '<td class="text-center">' . $tipe . '</td>';
                                    echo '<td class="text-end fw-semibold">' . number_format($item_expense->jumlah) . '</td>';
                                    echo '<td class="text-center">' . $item_expense->tanggal . '</td>';
                                    echo '<td class="text-center">';
                                    $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_expense->no_doc, 'ids' => $item_expense->ids])->row_array();

                                    if ($item_expense->status == '0' || empty($get_sts_payment)) {
                                        echo ($item_expense->status == '9') ? '<span class="badge bg-danger">Rejected</span>' : '<span class="badge bg-info text-dark">Open</span>';
                                    } elseif ($get_sts_payment['status'] == 1) {
                                        echo '<span class="badge bg-warning text-dark">Process</span>';
                                    } elseif ($get_sts_payment['status'] == 2) {
                                        echo '<span class="badge bg-secondary">Close</span>';
                                    } else {
                                        echo '<span class="badge bg-light text-dark">Undefined</span>';
                                    }
                                    echo '</td>';
                                    echo '<td class="text-center">';
                                    if ($ENABLE_MANAGE || (!empty($get_sts_payment) && $get_sts_payment['status'] < 1) && count($get_req_payment) > 0 && $get_req_payment[0]->app_checker === null) {
                                        if ($get_expense['project_consultant'] == '1') {
                                            echo '<a href="' . base_url('approval_request_payment/approval_payment_checker/?id_exp_consultant=' . urlencode(str_replace('/', '|', $get_expense['no_expense_consultant']))) . '&id_expense=' . $item_expense->no_doc . '" class="btn btn-sm btn-success"><i class="fa fa-check-square-o"></i> Approve</a>';
                                        } else {
                                            echo '<a href="' . base_url($this->uri->segment(1) . '/approval_payment_checker/?type=' . $item_expense->tipe . '&id=' . $item_expense->id . '&nilai=' . $item_expense->jumlah) . '" class="btn btn-sm btn-success"><i class="fa fa-check-square-o"></i> Approve</a>';
                                        }
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            }
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-12 list_periodik table-section mb-4" style="display: none;">
            <h4 class="text-dark fw-bold mb-3"><i class="fa fa-clock-o me-2"></i>Periodik</h4>
            <div class="table-responsive shadow-sm">
                <table class="table table-striped table-hover table-bordered align-middle mb-0" id="table_periodik" width="100%">
                    <thead class="text-center">
                        <tr>
                            <th>No Dokument</th>
                            <th>Request By</th>
                            <th>Tanggal</th>
                            <th>Keperluan</th>
                            <th>Tipe</th>
                            <th>Nilai Pengajuan</th>
                            <th>Tanggal Pembayaran</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($data as $item_periodik) :
                            if ($item_periodik->tipe == 'periodik') {
                                echo '<tr>';
                                echo '<td class="text-center fw-semibold">' . $item_periodik->no_doc . '</td>';
                                echo '<td>' . $item_periodik->nama . '</td>';
                                echo '<td class="text-center">' . $item_periodik->tgl_doc . '</td>';
                                echo '<td>' . $item_periodik->keperluan . '</td>';
                                echo '<td class="text-center text-capitalize">' . $item_periodik->tipe . '</td>';
                                echo '<td class="text-end fw-semibold">' . number_format($item_periodik->jumlah) . '</td>';
                                echo '<td class="text-center">' . $item_periodik->tanggal . '</td>';
                                echo '<td class="text-center">';
                                $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_periodik->no_doc, 'ids' => $item_periodik->ids])->row_array();

                                if ($item_periodik->status == '0' || empty($get_sts_payment)) {
                                    echo ($item_periodik->status == '9') ? '<span class="badge bg-danger">Rejected</span>' : '<span class="badge bg-info text-dark">Open</span>';
                                } elseif ($get_sts_payment['status'] == 1) {
                                    echo '<span class="badge bg-warning text-dark">Process</span>';
                                } elseif ($get_sts_payment['status'] == 2) {
                                    echo '<span class="badge bg-secondary">Close</span>';
                                } else {
                                    echo '<span class="badge bg-light text-dark">Undefined</span>';
                                }
                                echo '</td>';
                                echo '<td class="text-center">';
                                if ($ENABLE_MANAGE) : ?>
                                    <a href="<?= base_url($this->uri->segment(1) . '/approval_payment_checker/?type=' . $item_periodik->tipe . '&id=' . $item_periodik->id . '&nilai=' . $item_periodik->jumlah); ?>" class="btn btn-success btn-sm"><i class="fa fa-check-square-o"></i> Approve</a>
                        <?php endif;
                                echo '</td>';
                                echo '</tr>';
                            }
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-12 list_pembayaran_po table-section mb-4" style="display: none;">
            <h4 class="text-dark fw-bold mb-3"><i class="fa fa-shopping-cart me-2"></i>Pembayaran PO</h4>
            <div class="table-responsive shadow-sm">
                <table class="table table-striped table-hover table-bordered align-middle mb-0" id="table_pembayaran_po" width="100%">
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($data as $item_expense) :
                            $no_invoice = (isset($list_no_invoice[$item_expense->no_doc])) ? $list_no_invoice[$item_expense->no_doc] : '';

                            if ($item_expense->tipe == 'expense') {
                                $tipe = ucfirst($item_expense->tipe);
                                $get_expense = $this->db->get_where('tr_expense', ['no_doc' => $item_expense->no_doc])->row_array();
                                if ($get_expense['exp_inv_po'] == '1') $tipe = 'Pembayaran PO';
                                if (strpos($item_expense->no_doc, 'ROS-') !== false) $tipe = 'Pembayaran PIB';

                                $exp_id_po = explode(',', $get_expense['id_po']);
                                $po_note = [];
                                $get_po_note = $this->db->select('note')->from('tr_purchase_order')->where_in('no_surat', $exp_id_po)->get()->result();

                                foreach ($get_po_note as $item_po_note) {
                                    $po_note[] = $item_po_note->note;
                                }
                                $po_note = implode(', ', $po_note);

                                if ($get_expense['exp_inv_po'] == '1') {
                                    echo '<tr>';
                                    echo '<td class="text-center fw-semibold">' . $item_expense->no_doc . '</td>';
                                    echo '<td class="text-center">' . $no_invoice . '</td>';
                                    echo '<td>' . $item_expense->nama . '</td>';
                                    echo '<td class="text-center">' . $item_expense->tgl_doc . '</td>';
                                    echo '<td>' . $item_expense->keperluan . '</td>';
                                    echo '<td class="text-center">' . $tipe . '</td>';
                                    echo '<td class="text-end fw-semibold">' . number_format($item_expense->jumlah) . '</td>';
                                    echo '<td class="text-center">' . $item_expense->tanggal . '</td>';
                                    echo '<td>' . $po_note . '</td>';
                                    echo '<td class="text-center">';
                                    $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_expense->no_doc, 'ids' => $item_expense->ids])->row_array();

                                    if ($item_expense->status == '0' || empty($get_sts_payment)) {
                                        echo ($item_expense->status == '9') ? '<span class="badge bg-danger">Rejected</span>' : '<span class="badge bg-info text-dark">Open</span>';
                                    } elseif ($get_sts_payment['status'] == 1) {
                                        echo '<span class="badge bg-warning text-dark">Process</span>';
                                    } elseif ($get_sts_payment['status'] == 2) {
                                        echo '<span class="badge bg-secondary">Close</span>';
                                    } else {
                                        echo '<span class="badge bg-light text-dark">Undefined</span>';
                                    }
                                    echo '</td>';
                                    echo '<td class="text-center"><div class="d-flex justify-content-center gap-1">';
                                    if ($ENABLE_MANAGE) : ?>
                                        <a href="<?= base_url($this->uri->segment(1) . '/approval_payment_checker/?type=' . $item_expense->tipe . '&id=' . $item_expense->id . '&nilai=' . $item_expense->jumlah); ?>" class="btn btn-success btn-sm" title="Approve"><i class="fa fa-check-square"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-sm btn-info text-white view_receive_invoice" data-id_invoice="<?= $item_expense->no_doc ?>" title="View"><i class="fa fa-eye"></i></a>
                        <?php endif;
                                    echo '</div></td>';
                                    echo '</tr>';
                                }
                            }
                        endforeach;
                        ?>

                        <?php
                        // === Tambahan: Invoice PO (DP/Import/Local) dari tipe baru ===
                        foreach ($data as $item_inv) :
                            if (in_array($item_inv->tipe, ['invoice_dp', 'invoice_import', 'invoice_local'])) {
                                $tipe_label = str_replace(['invoice_dp', 'invoice_import', 'invoice_local'], ['Invoice DP', 'Invoice Import', 'Invoice Local'], $item_inv->tipe);

                                echo '<tr>';
                                echo '<td class="text-center fw-semibold">' . $item_inv->no_surat . '</td>';
                                echo '<td class="text-center">' . $item_inv->keperluan . '</td>';
                                echo '<td>' . $item_inv->nama . '</td>';
                                echo '<td class="text-center">' . $item_inv->tgl_doc . '</td>';
                                echo '<td>' . $item_inv->keperluan . '</td>';
                                echo '<td class="text-center">' . $tipe_label . '</td>';
                                echo '<td class="text-end fw-semibold">' . number_format($item_inv->jumlah, 2) . '</td>';
                                echo '<td class="text-center">' . $item_inv->tanggal . '</td>';
                                echo '<td>' . ($item_inv->currency ?? 'IDR') . '</td>';
                                echo '<td class="text-center">';
                                if ($item_inv->status == '0') {
                                    echo '<span class="badge bg-info text-dark">Open</span>';
                                } elseif ($item_inv->status == '9') {
                                    echo '<span class="badge bg-danger">Rejected</span>';
                                } else {
                                    echo '<span class="badge bg-warning text-dark">Process</span>';
                                }
                                echo '</td>';
                                echo '<td class="text-center"><div class="d-flex justify-content-center gap-1">';
                                if ($ENABLE_MANAGE && in_array($item_inv->status, ['0', '1']) && $item_inv->app_checker === null) {
                                    echo '<a href="' . base_url($this->uri->segment(1) . '/approval_payment_checker/?type=' . $item_inv->tipe . '&id=' . $item_inv->id . '&nilai=' . $item_inv->jumlah) . '" class="btn btn-success btn-sm" title="Approve"><i class="fa fa-check-square"></i></a>';
                                }
                                echo '</div></td>';
                                echo '</tr>';
                            }
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-12 list_direct_payment table-section mb-4" style="display: none;">
            <h4 class="text-dark fw-bold mb-3"><i class="fa fa-exchange me-2"></i>Direct Payment</h4>
            <div class="table-responsive shadow-sm">
                <table class="table table-striped table-hover table-bordered align-middle mb-0" id="table_direct_payment" width="100%">
                    <thead class="text-center">
                        <tr>
                            <th>No Dokument</th>
                            <th>Request By</th>
                            <th>Tanggal</th>
                            <th>Keperluan</th>
                            <th>Tipe</th>
                            <th>Nilai Pengajuan</th>
                            <th>Tanggal Pembayaran</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($data as $item_dp) :
                            if ($item_dp->tipe == 'direct_payment') {
                                echo '<tr>';
                                echo '<td class="text-center fw-semibold">' . $item_dp->no_doc . '</td>';
                                echo '<td>' . $item_dp->nama . '</td>';
                                echo '<td class="text-center">' . $item_dp->tgl_doc . '</td>';
                                echo '<td>' . $item_dp->keperluan . '</td>';
                                echo '<td class="text-center text-capitalize">' . $item_dp->tipe . '</td>';
                                echo '<td class="text-end fw-semibold">' . number_format($item_dp->jumlah) . '</td>';
                                echo '<td class="text-center">' . $item_dp->tanggal . '</td>';
                                echo '<td class="text-center">';
                                $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_dp->no_doc, 'ids' => $item_dp->ids])->row_array();

                                if ($item_dp->status == '0' || empty($get_sts_payment)) {
                                    echo ($item_dp->status == '9') ? '<span class="badge bg-danger">Rejected</span>' : '<span class="badge bg-info text-dark">Open</span>';
                                } elseif ($get_sts_payment['status'] == 1) {
                                    echo '<span class="badge bg-warning text-dark">Process</span>';
                                } elseif ($get_sts_payment['status'] == 2) {
                                    echo '<span class="badge bg-secondary">Close</span>';
                                } else {
                                    echo '<span class="badge bg-light text-dark">Undefined</span>';
                                }
                                echo '</td>';
                                echo '<td class="text-center">';
                                if ($ENABLE_MANAGE) : ?>
                                    <a href="<?= base_url($this->uri->segment(1) . '/approval_payment_checker/?type=' . $item_dp->tipe . '&id=' . $item_dp->id . '&nilai=' . $item_dp->jumlah); ?>" class="btn btn-success btn-sm"><i class="fa fa-check-square-o me-1"></i>Approve</a>
                        <?php endif;
                                echo '</td>';
                                echo '</tr>';
                            }
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
<?= form_close() ?>

<div class="modal fade" id="modal_view_receive_invoice" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="myModalLabel">View Receive Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="ModalViewSPPLM" style="max-height: 80vh; overflow-y: auto;">
                <div class="text-center py-3 text-muted">Memuat data...</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    var url_save = siteurl + 'request_payment/save_approval/';

    $(document).ready(function() {
        // Inisialisasi Seluruh DataTables v2 secara lokal aman
        $('#table_transportasi').dataTable({
            "pageLength": 10
        });
        $('#table_kasbon').dataTable({
            "pageLength": 10
        });
        $('#table_expense').dataTable({
            "pageLength": 10
        });
        $('#table_periodik').dataTable({
            "pageLength": 10
        });
        $('#table_pembayaran_po').dataTable({
            "pageLength": 10
        });
        $('#table_direct_payment').dataTable({
            "pageLength": 10
        });

        // Hubungkan Flatpickr jika ada form input tanggal dinamis di dalam baris table
        initFlatpickr();
    });

    function initFlatpickr() {
        if ($(".flatpickr-date").length > 0) {
            $(".flatpickr-date").flatpickr({
                altInput: true,
                altFormat: "d M Y",
                dateFormat: "Y-m-d",
                allowInput: true
            });
        }
    }

    // Toggle Section Management
    $(document).on("click", ".btn_view_req", function() {
        var val = $(this).data('val');

        // Sembunyikan semua section tabel terlebih dahulu
        $(".table-section").hide();

        // Tampilkan hanya section tabel yang dipilih
        $(".list_" + val).fadeIn(300);
    });

    // AJAX Modal Handler
    $(document).on('click', '.view_receive_invoice', function() {
        var id_invoice = $(this).data('id_invoice');
        $('#ModalViewSPPLM').html('<div class="text-center py-3 text-muted"><i class="fa fa-spinner fa-spin me-2"></i>Memuat data...</div>');
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
                $('#modal_view_receive_invoice').modal('hide');
                Swal.fire({
                    title: 'Error!',
                    text: 'Please try again later!',
                    icon: 'error'
                });
            }
        });
    });

    // Modernisasi SweetAlert2 Submit Form Handler
    $('#frm_data').on('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: "Anda Yakin?",
            text: "Data Akan Di Setujui!",
            icon: "info",
            showCancelButton: true,
            confirmButtonText: "Ya, Setujui!",
            cancelButtonText: "Tidak!",
            customClass: {
                confirmButton: 'btn btn-primary me-2',
                cancelButton: 'btn btn-danger'
            },
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
                            Swal.fire({
                                title: "Sukses!",
                                text: "Data Berhasil Di Setujui",
                                icon: "success",
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: "Gagal!",
                                text: "Data Gagal Di Setujui",
                                icon: "error"
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: "Gagal!",
                            text: "Ajax Data Gagal Di Proses",
                            icon: "error"
                        });
                    }
                });
            }
        });
    });
</script>