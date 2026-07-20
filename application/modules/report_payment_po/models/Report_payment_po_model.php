<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_payment_po_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_report_data($no_po = null)
    {
        // 1. Get Base PO Data from tr_receive_invoice
        $this->db->select('
            a.no_surat as no_po,
            a.tipe as tipe_po, 
            a.nilai_invoice as value_po, 
            a.tipe as tipe_top, 
            a.id_top,
            top.progress as value_pct,
            ros.gl_persediaan_intransit as total_material, 
            a.gl_unbill as unbill, 
            ros.gl_selisih_kurs as selisih_kurs_receive_1,
            a.nilai_invoice as receive_invoice_value, 
            a.kurs as receive_kurs, 
            a.jumlah_rupiah as value_receive_idr,
            a.gl_selisih as selisih_kurs_receive_2,
            a.id as id_receive_invoice,
            a.tipe
        ');
        $this->db->from('tr_receive_invoice a');
        $this->db->join('tr_top_po top', 'top.id = a.id_top', 'left');
        $this->db->join('tr_ros_header ros', 'ros.id = a.id_ros', 'left');
        if (!empty($no_po)) {
            $this->db->where('a.no_surat', $no_po);
        }
        $this->db->where_in('a.status', ['request payment', 'approve checker', 'approve management', 'payment']);

        $receives = $this->db->get()->result();

        // Output array
        $result = [];

        foreach ($receives as $rec) {
            $row = [
                'no_po' => $rec->no_po,
                'tipe_po' => $rec->tipe_po,
                'value_po' => $rec->value_po,
                'tipe_top' => $rec->tipe_top,
                'value_pct' => $rec->value_pct,
                'total_material' => $rec->total_material,
                'unbill' => $rec->unbill,
                'selisih_kurs_receive_1' => $rec->selisih_kurs_receive_1,
                'receive_invoice_value' => $rec->receive_invoice_value,
                'receive_kurs' => $rec->receive_kurs,
                'value_receive_idr' => $rec->value_receive_idr,
                'selisih_kurs_receive_2' => $rec->selisih_kurs_receive_2,

                // Fields to be filled by payment_approve
                'selisih_kurs_pay' => 0,
                'invoice_pay' => 0,
                'kurs_pay' => 0,
                'currency_pay' => '',
                'payment_idr' => 0,
                'admin_bank' => 0,
                'selisih_kurs_admin' => 0,

                // Type category
                'category' => '' // DP or ROS
            ];

            // DP logic
            if (strtolower($rec->tipe) == 'dp' || strpos(strtolower($rec->tipe_top), 'dp') !== false || strpos(strtolower($rec->tipe_top), 'uang muka') !== false) {
                $row['category'] = 'dp';
            } elseif (strtolower($rec->tipe) == 'import') {
                $row['category'] = 'import';
            } elseif (strtolower($rec->tipe) == 'local') {
                $row['category'] = 'local';
            } else {
                $row['category'] = strtolower($rec->tipe);
            }

            // Get payment details
            $this->db->select('p.nominal_asli as invoice_pay, p.kurs_payment as kurs_pay, p.mata_uang as currency_pay, p.total_payment as payment_idr, p.bank_charge as admin_bank, p.gl_selisih_kurs as selisih_kurs_admin');
            $this->db->from('payment_approve_details pd');
            $this->db->join('payment_approve p', 'p.no_doc = pd.payment_id', 'left'); // <-- ini yang benar
            $this->db->where('pd.id_receive_invoice', $rec->id_receive_invoice);
            $pay_detail = $this->db->get()->row();

            if (!empty($pay_detail)) {
                $row['invoice_pay'] = $pay_detail->invoice_pay;
                $row['kurs_pay'] = $pay_detail->kurs_pay;
                $row['currency_pay'] = $pay_detail->currency_pay;
                $row['payment_idr'] = $pay_detail->payment_idr;
                $row['admin_bank'] = $pay_detail->admin_bank;
                $row['selisih_kurs_admin'] = $pay_detail->selisih_kurs_admin;
            }

            $result[] = $row;
        }

        return $result;
    }
}
