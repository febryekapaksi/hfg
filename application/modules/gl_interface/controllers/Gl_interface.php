<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Gl_interface extends Admin_Controller
{
    protected $viewPermission   = 'Gl_interface.View';
    protected $managePermission = 'Gl_interface.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('gl_interface/Gl_interface_model');
        $this->load->model('pembayaran_material/Jurnal_model');
        $this->template->title('GL Interface');
        $this->template->page_icon('fa fa-exchange');
        date_default_timezone_set('Asia/Bangkok');
    }

    // ─── INDEX ────────────────────────────────────────────────────────
    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->title('GL Interface');
        $this->template->render('index');
    }

    // ─── SERVER-SIDE DATATABLE ────────────────────────────────────────
    public function data()
    {
        $this->auth->restrict($this->viewPermission);

        $search   = $this->input->post('search')['value'] ?? '';
        $jenis_tr = $this->input->post('jenis_transaksi') ?? '';
        $status   = $this->input->post('filter_status') ?? '';
        $start    = (int) ($this->input->post('start') ?? 0);
        $length   = (int) ($this->input->post('length') ?? 25);
        $draw     = (int) ($this->input->post('draw') ?? 1);

        $result = $this->Gl_interface_model->get_datatable($search, $jenis_tr, $status, $start, $length);

        echo json_encode([
            'draw'            => $draw,
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data'            => $result['data'],
        ]);
    }

    // ─── DETAIL (AJAX — for quick peek) ─────────────────────────────
    public function detail($id)
    {
        $this->auth->restrict($this->viewPermission);

        $header  = $this->Gl_interface_model->get_header($id);
        $details = $this->Gl_interface_model->get_details($id);

        echo json_encode([
            'status'  => $header ? 1 : 0,
            'header'  => $header,
            'details' => $details,
        ]);
    }

    // ─── VIEW PAGE (preview sebelum posting) ─────────────────────────
    public function view($id)
    {
        $this->auth->restrict($this->viewPermission);

        $header  = $this->Gl_interface_model->get_header($id);
        if (empty($header)) {
            show_404();
            return;
        }

        $details = $this->Gl_interface_model->get_details($id);

        $this->template->set('header', $header);
        $this->template->set('details', $details);
        $this->template->title('View GL Interface — ' . $header['nomor']);
        $this->template->render('view');
    }

    // ─── POST TO ACCOUNTING (manual) ─────────────────────────────────
    public function post()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        $this->auth->restrict($this->managePermission);

        $id = $this->input->post('id');
        if (empty($id)) {
            echo json_encode(['status' => 0, 'pesan' => 'ID is required']);
            return;
        }

        $header = $this->Gl_interface_model->get_header($id);
        if (empty($header)) {
            echo json_encode(['status' => 0, 'pesan' => 'Data not found']);
            return;
        }
        if ($header['status'] === 'posted') {
            echo json_encode(['status' => 0, 'pesan' => 'Data has already been posted']);
            return;
        }

        // Nomor belum ada (akan di-generate saat posting)
        // Cek duplikat hanya jika sudah pernah punya nomor
        if (!empty($header['nomor'])) {
            $nomor = $header['nomor'];
            $jenis = strtoupper($header['jenis']);

            if ($jenis === 'JV') {
                $already = $this->db->get_where(DBACC . '.javh', ['nomor' => $nomor])->num_rows();
            } else {
                $already = $this->db->get_where(DBACC . '.japh', ['nomor' => $nomor])->num_rows();
            }
            if ($already > 0) {
                echo json_encode(['status' => 0, 'pesan' => 'Number ' . $nomor . ' already exists in accounting']);
                return;
            }
        }

        $this->db->trans_start();

        try {
            $this->_do_post($header);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->db->update('gl_interface', [
                'status'    => 'error',
                'error_msg' => $e->getMessage(),
            ], ['id' => $id]);
            echo json_encode(['status' => 0, 'pesan' => 'Posting failed: ' . $e->getMessage()]);
            return;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            echo json_encode(['status' => 0, 'pesan' => 'Posting failed (trans_status false)']);
            return;
        }

        // Ambil nomor yang baru di-generate
        $updated = $this->Gl_interface_model->get_header($id);
        echo json_encode(['status' => 1, 'pesan' => 'Posting successful — Number: ' . ($updated['nomor'] ?? '-')]);
    }

    // ─── BATCH POST ──────────────────────────────────────────────────
    public function post_batch()
    {
        $this->auth->restrict($this->managePermission);

        $ids = $this->input->post('ids');
        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['status' => 0, 'pesan' => 'No data selected']);
            return;
        }

        $success = 0;
        $failed  = 0;
        $errors  = [];

        foreach ($ids as $id) {
            $header = $this->Gl_interface_model->get_header($id);
            if (empty($header) || $header['status'] === 'posted') {
                $failed++;
                continue;
            }

            $nomor = $header['nomor'];
            $jenis = strtoupper($header['jenis']);

            if (!empty($nomor)) {
                if ($jenis === 'JV') {
                    $already = $this->db->get_where(DBACC . '.javh', ['nomor' => $nomor])->num_rows();
                } else {
                    $already = $this->db->get_where(DBACC . '.japh', ['nomor' => $nomor])->num_rows();
                }
                if ($already > 0) {
                    $failed++;
                    $errors[] = $nomor . ' already exists in accounting';
                    continue;
                }
            }

            $this->db->trans_start();
            try {
                $this->_do_post($header);
                $success++;
            } catch (Exception $e) {
                $this->db->trans_rollback();
                $this->db->update('gl_interface', [
                    'status'    => 'error',
                    'error_msg' => $e->getMessage(),
                ], ['id' => $id]);
                $failed++;
                $errors[] = $nomor . ': ' . $e->getMessage();
                continue;
            }
            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                $failed++;
            }
        }

        echo json_encode([
            'status' => ($success > 0) ? 1 : 0,
            'pesan'  => "Success: {$success}, Failed: {$failed}",
            'errors' => $errors,
        ]);
    }

    // ─── JENIS TRANSAKSI LIST (for filter dropdown) ─────────────────
    public function get_jenis_list()
    {
        $rows = $this->Gl_interface_model->get_jenis_transaksi_list();
        $list = array_column($rows, 'jenis_transaksi');
        echo json_encode($list);
    }

    // ─── CORE POSTING LOGIC ──────────────────────────────────────────
    private function _do_post($header)
    {
        $id     = $header['id'];
        $jenis  = strtoupper($header['jenis']); // JV atau BUK

        $memo          = !empty($header['memo']) ? json_decode($header['memo'], true) : [];
        $id_supplier   = $memo['id_supplier']   ?? null;
        $nama_supplier = $memo['nama_supplier'] ?? null;

        $details = $this->db->get_where('gl_interface_detail', ['id_gl_interface' => $id])->result_array();
        if (empty($details)) {
            throw new Exception('Journal detail is empty');
        }

        // ── Generate nomor jurnal baru saat posting ──
        $tgl_posting = $header['tgl'] ?? date('Y-m-d');
        if ($jenis === 'JV') {
            $nomor = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_posting);
        } else {
            $nomor = $this->Jurnal_model->get_no_buk('HSJ');
        }

        if (empty($nomor)) {
            throw new Exception('Failed to generate journal number');
        }

        // ── Update nomor di gl_interface header ──
        $this->db->update('gl_interface', ['nomor' => $nomor], ['id' => $id]);

        // ── Update no_batch di semua gl_interface_detail ──
        $this->db->update('gl_interface_detail', ['no_batch' => $nomor], ['id_gl_interface' => $id]);

        // Refresh details setelah update
        $details = $this->db->get_where('gl_interface_detail', ['id_gl_interface' => $id])->result_array();

        // Balance check & auto-adjust untuk JV (unbill)
        if ($jenis === 'JV') {
            $total_debet  = array_sum(array_column($details, 'debet'));
            $total_kredit = array_sum(array_column($details, 'kredit'));
            $selisih      = round($total_debet) - round($total_kredit);

            if ($selisih != 0) {
                foreach ($details as &$line) {
                    if ($line['no_perkiraan'] === '2101-01-06') {
                        $line['kredit'] = round($line['kredit'] + $selisih);
                        $this->db->update(
                            'gl_interface_detail',
                            ['kredit' => $line['kredit']],
                            ['id'     => $line['id']]
                        );
                        break;
                    }
                }
                unset($line);
            }
        }

        // ── Insert header ke accounting ──
        if ($jenis === 'JV') {
            $this->db->insert(DBACC . '.javh', [
                'nomor'      => $nomor,
                'tgl'        => $header['tgl'],
                'jml'        => $header['jml'] ?? array_sum(array_column($details, 'debet')),
                'kdcab'      => $header['kdcab'],
                'jenis'      => 'JV',
                'keterangan' => $header['keterangan'],
                'bulan'      => $header['bulan'],
                'tahun'      => $header['tahun'],
                'user_id'    => $header['user_id'],
            ]);
        } else {
            // BUK → japh
            $total_debet = array_sum(array_column($details, 'debet'));
            $this->db->insert(DBACC . '.japh', [
                'nomor'        => $nomor,
                'tgl'          => $header['tgl'],
                'jml'          => $total_debet,
                'jenis_ap'     => 'V',
                'bayar_kepada' => $nama_supplier ?? '',
                'kdcab'        => $header['kdcab'],
                'jenis_reff'   => 'BUK',
                'no_reff'      => $memo['no_reff'] ?? '',
                'note'         => $header['keterangan'],
                'user_id'      => $header['user_id'],
                'ho_valid'     => '',
            ]);
        }

        // ── Insert detail jurnal ──
        foreach ($details as $line) {
            $jurnal_row = [
                'tipe'         => $line['tipe'],
                'nomor'        => $nomor,
                'tanggal'      => $line['tanggal'],
                'no_perkiraan' => $line['no_perkiraan'],
                'keterangan'   => $line['keterangan'],
                'no_reff'      => $line['no_reff'],
                'debet'        => $line['debet'],
                'kredit'       => $line['kredit'],
                'created_by'   => $header['user_id'],
                'created_on'   => date('Y-m-d H:i:s'),
            ];

            // Tambahan kolom untuk JV (incoming)
            if ($jenis === 'JV') {
                $jurnal_row['id_material'] = $line['id_material'] ?? null;
                $jurnal_row['nm_material'] = $line['nm_material'] ?? null;
                $jurnal_row['id_gudang']   = $line['id_gudang'] ?? null;
                $jurnal_row['no_coil']     = $line['no_coil'] ?? null;
            }

            $this->db->insert(DBACC . '.jurnal', $jurnal_row);

            // Kartu hutang untuk baris kredit (JV incoming)
            if ($jenis === 'JV' && $header['jenis_transaksi'] === 'incoming' && $line['kredit'] > 0) {
                $this->db->insert('tr_kartu_hutang', [
                    'tipe'          => $line['tipe'],
                    'nomor'         => $nomor,
                    'tanggal'       => $line['tanggal'],
                    'no_perkiraan'  => $line['no_perkiraan'],
                    'keterangan'    => $line['keterangan'],
                    'no_reff'       => $line['no_reff'],
                    'debet'         => 0,
                    'kredit'        => $line['kredit'],
                    'id_supplier'   => $id_supplier,
                    'nama_supplier' => $nama_supplier,
                    'no_request'    => $line['no_request'] ?? '',
                ]);
            }
        }

        // ── Kartu hutang untuk receive invoice ──
        if ($jenis === 'JV' && $header['jenis_transaksi'] === 'receive invoice') {
            $coaunbill  = $memo['coaunbill']  ?? null;
            $totalunbill = $memo['totalunbill'] ?? 0;
            $coaap      = $memo['coaap']      ?? null;
            $totalap    = $memo['totalap']     ?? 0;
            $no_reff_po = $memo['no_reff']     ?? '';
            $no_request = $memo['no_request']  ?? '';

            if ($coaunbill && $totalunbill > 0) {
                $this->db->insert('tr_kartu_hutang', [
                    'tipe'          => 'JV',
                    'nomor'         => $nomor,
                    'tanggal'       => $header['tgl'],
                    'no_perkiraan'  => $coaunbill,
                    'keterangan'    => $header['keterangan'],
                    'no_reff'       => $no_reff_po,
                    'kredit'        => 0,
                    'debet'         => $totalunbill,
                    'id_supplier'   => $id_supplier,
                    'nama_supplier' => $nama_supplier,
                    'no_request'    => $no_request,
                ]);
            }

            if ($coaap && $totalap > 0) {
                $this->db->insert('tr_kartu_hutang', [
                    'tipe'          => 'JV',
                    'nomor'         => $nomor,
                    'tanggal'       => $header['tgl'],
                    'no_perkiraan'  => $coaap,
                    'keterangan'    => $header['keterangan'],
                    'no_reff'       => $no_reff_po,
                    'kredit'        => $totalap,
                    'debet'         => 0,
                    'id_supplier'   => $id_supplier,
                    'nama_supplier' => $nama_supplier,
                    'no_request'    => $no_request,
                ]);
            }
        }

        // ── Kartu hutang untuk payment (BUK) — hanya debet ──
        if ($jenis === 'BUK') {
            $no_reff_payment = $memo['no_reff'] ?? '';

            foreach ($details as $line) {
                if ($line['debet'] > 0) {
                    $this->db->insert('tr_kartu_hutang', [
                        'tipe'          => 'BUK',
                        'nomor'         => $nomor,
                        'tanggal'       => $line['tanggal'],
                        'no_perkiraan'  => $line['no_perkiraan'],
                        'keterangan'    => $line['keterangan'],
                        'no_reff'       => $line['no_reff'] ?? $no_reff_payment,
                        'debet'         => $line['debet'],
                        'kredit'        => 0,
                        'id_supplier'   => $id_supplier,
                        'nama_supplier' => $nama_supplier,
                        'no_request'    => $line['no_request'] ?? $no_reff_payment,
                    ]);
                }
            }
        }

        // ── Tandai posted + simpan nomor ──
        // Note: get_Nomor_Jurnal_Sales sudah update counter nomorJC untuk JV
        // Tapi get_no_buk TIDAK update counter, jadi perlu manual untuk BUK
        if ($jenis !== 'JV') {
            $this->db->query("UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobuk = nobuk + 1 WHERE cabang = 'HSJ'");
        }

        $this->db->update('gl_interface', [
            'status'    => 'posted',
            'posted_at' => date('Y-m-d H:i:s'),
            'error_msg' => null,
        ], ['id' => $id]);
    }
}
