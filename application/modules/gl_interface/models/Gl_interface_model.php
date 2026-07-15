<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Gl_interface_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * DataTable server-side
     */
    public function get_datatable($search = '', $jenis_transaksi = '', $status = '', $start = 0, $length = 25)
    {
        // Total tanpa filter
        $total = $this->db->count_all('gl_interface');

        // Base query
        $this->db->from('gl_interface');

        if (!empty($jenis_transaksi)) {
            $this->db->where('jenis_transaksi', $jenis_transaksi);
        }
        if (!empty($status)) {
            $this->db->where('status', $status);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('nomor', $search);
            $this->db->or_like('keterangan', $search);
            $this->db->or_like('jenis_transaksi', $search);
            $this->db->or_like('memo', $search);
            $this->db->group_end();
        }

        $filtered = $this->db->count_all_results('', false);

        $this->db->order_by('id', 'DESC');
        $this->db->limit($length, $start);
        $rows = $this->db->get()->result_array();

        // Hitung total debet/kredit per row
        foreach ($rows as &$row) {
            $totals = $this->db->select('SUM(debet) as total_debet, SUM(kredit) as total_kredit')
                ->where('id_gl_interface', $row['id'])
                ->get('gl_interface_detail')
                ->row();
            $row['total_debet']  = $totals ? (float) $totals->total_debet : 0;
            $row['total_kredit'] = $totals ? (float) $totals->total_kredit : 0;
            $row['memo_decoded'] = !empty($row['memo']) ? json_decode($row['memo'], true) : [];
        }
        unset($row);

        return [
            'total'    => $total,
            'filtered' => $filtered,
            'data'     => $rows,
        ];
    }

    public function get_header($id)
    {
        return $this->db
            ->select('gl_interface.*, users.nm_lengkap')
            ->from('gl_interface')
            ->join('users', 'users.id_user = gl_interface.user_id', 'left')
            ->where('gl_interface.id', $id)
            ->get()
            ->row_array();
    }

    public function get_details($id)
    {
        return $this->db->select('a.*, b.nama as nama_coa')
            ->from('gl_interface_detail a')
            ->join(DBACC . '.coa_master b', 'a.no_perkiraan = b.no_perkiraan', 'left')
            ->where('a.id_gl_interface', $id)
            ->get()
            ->result_array();
    }

    /**
     * Ambil daftar jenis_transaksi unik untuk dropdown filter
     */
    public function get_jenis_transaksi_list()
    {
        return $this->db->distinct()
            ->select('jenis_transaksi')
            ->order_by('jenis_transaksi', 'ASC')
            ->get('gl_interface')
            ->result_array();
    }

    public function generate_jurnal_dari_template($kode_master_jurnal, $data_source)
    {
        $db_acc = $this->load->database('accounting', TRUE);

        $header = $db_acc->get_where('master_oto_jurnal_header', [
            'kode_master_jurnal' => $kode_master_jurnal
        ])->row();

        $data_detail = $db_acc
            ->order_by('urutan', 'ASC')
            ->get_where('master_oto_jurnal_detail', [
                'kode_master_jurnal' => $kode_master_jurnal
            ])->result();

        if (!$header || empty($data_detail)) {
            log_message('error', "Template jurnal '$kode_master_jurnal' tidak ditemukan atau tidak punya detail");
            return false;
        }

        $tgl_now    = $data_source['tanggal'] ?? date('Y-m-d');
        $created_on = date('Y-m-d H:i:s');
        $user_id    = (isset($this->auth)) ? $this->auth->user_id() : 1;
        $nomor_jv   = $this->_generate_nomor_jv_generic($header->tipe);
        $memo_data = [
            'id_supplier'   => $data_source['id_supplier']  ?? ($data_source['id_suplier']   ?? null),
            'nama_supplier' => $data_source['nm_supplier']   ?? ($data_source['nama_supplier'] ?? null),
            'no_reff'       => $data_source['no_doc']        ?? ($data_source['no_po']         ?? null),
            'no_request'    => $data_source['no_request']    ?? null,
            'coaunbill'     => $data_source['coaunbill']      ?? null,
            'totalunbill'   => $data_source['gl_unbill']      ?? null,
            'coaap'         => $data_source['coaap']          ?? null,
            'totalap'       => $data_source['gl_hutang_dagang'] ?? null,
        ];

        // ── Insert Header GL Interface ──
        $this->db->insert('gl_interface', [
            'nomor'           => $nomor_jv,
            'tgl'             => $tgl_now,
            'bulan'           => date('m', strtotime($tgl_now)),
            'tahun'           => date('Y', strtotime($tgl_now)),
            'kdcab'           => '101',
            'jenis'           => $header->tipe,
            'keterangan'      => $header->keterangan_header,
            'jenis_transaksi' => $header->jenis_transaksi,
            'status'          => 'pending',
            'user_id'         => $user_id,
            'memo'            => json_encode($memo_data),
        ]);
        $id_gl = $this->db->insert_id();

        // ── Insert Detail GL Interface, per baris template ──
        foreach ($data_detail as $row) {
            $nominal = isset($data_source[$row->field]) ? (float) $data_source[$row->field] : 0;

            $nominal_kurs = 0;
            if (!empty($row->field_nominal_kurs) && isset($data_source[$row->field_nominal_kurs])) {
                $nominal_kurs = (float) $data_source[$row->field_nominal_kurs];
            }

            $no_reff = $data_source[$row->field_no_reff] ?? null;

            if ($row->sumber_coa === 'tetap') {
                $no_perkiraan = $row->no_perkiraan;
            } else {
                $no_perkiraan = $data_source[$row->field_coa_dinamis] ?? null;
            }

            $coa_row  = $db_acc->get_where('coa_master', ['no_perkiraan' => $no_perkiraan])->row();
            $nama_coa = $coa_row->nama ?? $no_perkiraan;

            // ── FIX: tentukan posisi final (D/K) ──
            // Template mendukung 3 opsi posisi: 'D', 'K', atau 'otomatis'.
            // Untuk 'D'/'K' → posisi tetap sesuai setting template.
            // Untuk 'otomatis' → posisi ditentukan dari TANDA nilai $nominal:
            //     nominal >= 0  → Debet
            //     nominal <  0  → Kredit (nilai disimpan dalam bentuk absolut)
            $posisi_final = $row->posisi;

            if (!in_array($row->posisi, ['D', 'K'], true)) {
                $posisi_final = ($nominal >= 0) ? 'D' : 'K';
                $nominal      = abs($nominal);
                $nominal_kurs = abs($nominal_kurs);
            }

            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'no_batch'        => $nomor_jv,
                'tipe'            => $header->tipe,
                'tanggal'         => $tgl_now,
                'no_perkiraan'    => $no_perkiraan,
                'keterangan'      => $nama_coa . ' | ' . $row->keterangan,
                'no_reff'         => $no_reff,
                'debet'           => ($posisi_final === 'D') ? $nominal : 0,
                'kredit'          => ($posisi_final === 'K') ? $nominal : 0,
                'debet_kurs'      => ($posisi_final === 'D') ? $nominal_kurs : 0,
                'kredit_kurs'     => ($posisi_final === 'K') ? $nominal_kurs : 0,
                'created_at'      => $created_on,
            ]);
        }

        return $id_gl;
    }


    private function _generate_nomor_jv_generic($tipe)
    {
        $db_acc = $this->load->database('accounting', TRUE);

        // Mapping tipe -> prefix nomor & nama kolom counter di pastibisa_tb_cabang
        $map = [
            'JV'  => ['prefix' => 'AJV', 'kolom_counter' => 'nomorJC'],
            'BUK' => ['prefix' => 'ABK', 'kolom_counter' => 'nobuk'],
            'BUM' => ['prefix' => 'ABM', 'kolom_counter' => 'nobum'],
        ];

        if (!isset($map[$tipe])) {
            log_message('error', "_generate_nomor_jv_generic: tipe '$tipe' tidak dikenali");
            return '101-A' . strtoupper($tipe) . date('ym') . '0001';
        }

        $prefix        = $map[$tipe]['prefix'];
        $kolom_counter = $map[$tipe]['kolom_counter'];

        $cabang = $db_acc->query(
            "SELECT `$kolom_counter` AS counter_val FROM pastibisa_tb_cabang WHERE nocab = '101' LIMIT 1 FOR UPDATE"
        )->row();

        if (empty($cabang)) {
            // Fallback jika data cabang belum ada
            return '101-' . $prefix . date('ym') . '0001';
        }

        $nomor_urut = (int) $cabang->counter_val + 1;
        $nomor_jv   = '101-' . $prefix . date('ym') . $nomor_urut;

        $db_acc->query(
            "UPDATE pastibisa_tb_cabang SET `$kolom_counter` = `$kolom_counter` + 1 WHERE nocab = '101'"
        );

        return $nomor_jv;
    }
}
