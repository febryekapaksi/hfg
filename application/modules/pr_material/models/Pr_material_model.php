<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pr_material_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('PR_Material.Add');
        $this->ENABLE_MANAGE  = has_permission('PR_Material.Manage');
        $this->ENABLE_VIEW    = has_permission('PR_Material.View');
        $this->ENABLE_DELETE  = has_permission('PR_Material.Delete');
    }

    public function get_data_json_reorder_point()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_reorder_point(
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );

        $totalData     = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query         = $fetch['query'];

        $GET_SATUAN = get_list_satuan();
        $GET_OUTSTANDING_PR = get_pr_on_progress();

        $data = [];
        $urut = 1;

        foreach ($query->result_array() as $row) {
            $nomor = $urut + $requestData['start'];

            $satuan = $GET_SATUAN[$row['id_unit']]['code'] ?? '';
            $outstanding_pr = $GET_OUTSTANDING_PR[$row['code_lv4']] ?? 0;

            $konversi = $row['konversi'] ?? 0;
            $qty_pack = ($konversi > 0 && $row['qty_stock'] > 0) ? $row['qty_stock'] / $konversi : 0;

            $qty_pr = '';
            if ($row['qty_stock'] < $row['min_stok']) {
                $qty_pr = max(0, $row['max_stok'] - ($row['qty_stock'] + $outstanding_pr));
            }

            $purchase = $row['request'] ?: $qty_pr;
            $forecast = $row['forecast'];
            $purchase_pack = ($konversi > 0 && $purchase > 0) ? $purchase / $konversi : 0;
            $keterangan = $row['keterangan'] ?? '';
            $stock_awal = $row['max_stok'] - $row['qty_stock'];

            $nestedData = [
                "<div align='center'>{$nomor}</div>",
                "<div align='left'>{$row['code_lv4']}</div>",
                "<div align='left'>{$row['local_code']}</div>",
                "<div align='left'>" . strtoupper($row['nm_material']) . "</div>",
                "<div align='left'>" . strtoupper($row['nm_lain']) . "</div>",
                "<div align='left'>{$row['category']}</div>",
                "<div align='right'>" . number_format($row['qty_stock']) . "</div>",
                "<div align='right'>" . number_format($row['min_stok']) . "</div>",
                "<div align='right'>" . number_format($row['max_stok']) . "</div>",
                "<div align='right'>" . number_format($outstanding_pr) . "<input type='hidden' name='stock_awal' id='stock_awal_{$nomor}' data-id_material='{$row['code_lv4']}' data-no='{$nomor}' value='{$stock_awal}'></div>",
                "<input type='text' name='forecast' id='forecast_{$nomor}' data-id_material='{$row['code_lv4']}' data-no='{$nomor}' class='form-control moneyFormat input-sm text-end changeSave' style='width:100px;' value='{$forecast}'>",
                "<input type='text' name='purchase' id='purchase_{$nomor}' data-id_material='{$row['code_lv4']}' data-no='{$nomor}' class='form-control moneyFormat input-sm text-end changeSave' style='width:100px;' value='{$purchase}'>",
                "<input type='text' name='kuota_internal' id='kuota_internal_{$nomor}' data-id_material='{$row['code_lv4']}' data-no='{$nomor}' class='form-control moneyFormat input-sm text-end changeSave' style='width:100px;' value='{$row['kuota']}'>",
                "<input type='text' name='keterangan' id='keterangan_{$nomor}' data-id_material='{$row['code_lv4']}' data-no='{$nomor}' class='form-control input-sm changeSave' style='width:150px;' value='{$keterangan}'>",
            ];

            $data[] = $nestedData;
            $urut++;
        }

        echo json_encode([
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ]);
    }

    public function get_query_json_reorder_point($like = null, $column_order = null, $column_dir = null, $limit_start = 0, $limit_length = 10)
    {
        $columns_order_by = [
            0 => 'a.code',
            1 => 'a.nm_material',
            2 => 'z.nama',
            3 => 'a.code_lv4',
        ];

        $this->db->select("
        a.code,
        a.id_unit,
        a.hscode,
        a.code_lv4               AS code_lv4,
        a.nama                   AS nm_material,
        a.trade_name                   AS nm_lain,
        z.nama                   AS category,
        h.local_code             AS local_code,
        h.kuota_internal         AS kuota,
        COALESCE(b.qty_stock,0)  AS qty_stock,
        COALESCE(a.min_stok,0)   AS min_stok,
        COALESCE(a.max_stok,0)   AS max_stok,
        COALESCE(a.konversi,0)   AS konversi,
        COALESCE(a.weight,0)    AS weight,
        COALESCE(a.request,0)    AS request,
        COALESCE(a.forecast,0)    AS forecast,
        COALESCE(a.keterangan,'') AS keterangan
        ");
        $this->db->from('new_inventory_4 a');
        $this->db->join('new_inventory_1 z', 'a.code_lv1 = z.code_lv1', 'left');
        $this->db->join('hscode h', 'h.id = a.hscode', 'left');
        $this->db->join('warehouse_stock b', 'a.code_lv4 = b.code_lv4 AND b.id_gudang = 1', 'left');
        $this->db->where('a.category', 'material');
        $this->db->where('a.deleted_date IS NULL');

        if ($like) {
            $this->db->group_start();
            $this->db->like('a.nama', $like);
            $this->db->or_like('a.trade_name', $like);
            $this->db->group_end();
        }

        $totalData = $this->db->count_all_results('', false);
        $totalFiltered = $totalData;

        if ($column_order !== null && isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('a.id', 'asc');
        }

        if ($limit_length != -1) {
            $this->db->limit($limit_length, $limit_start);
        }

        $query = $this->db->get();
        return compact('totalData', 'totalFiltered', 'query');
    }


    public function get_data_json_material_planning()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_material_planning(
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );

        $totalData     = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query         = $fetch['query'];

        $data = [];
        $urut = 1;

        foreach ($query->result_array() as $row) {
            $nomor = $urut + $requestData['start'];

            $this->db->select('a.propose_purchase, a.qty_order, b.nama as nm_barang, b.trade_name as nama_lain, c.code as satuan');
            $this->db->from('material_planning_base_on_produksi_detail a');
            $this->db->join('new_inventory_4 b', 'b.code_lv4 = a.id_material', 'left');
            $this->db->join('ms_satuan c', 'c.id = b.id_unit', 'left');
            $this->db->where('a.so_number', $row['so_number']);
            $this->db->where('b.nama IS NOT NULL');

            $get_barang = $this->db->get()->result();

            $list_barang = [];
            $list_lainnya = [];
            $list_qty_barang = [];

            foreach ($get_barang as $item) {
                $list_barang[] = $item->nm_barang;
                $list_lainnya[] = $item->nama_lain;
                $jumlah = ($item->propose_purchase == null || $item->propose_purchase <= 0)
                    ? $item->qty_order
                    : $item->propose_purchase;

                $list_qty_barang[] = number_format($jumlah, 2) . ' ' . strtoupper($item->satuan);
            }

            $status_info = $this->get_status_info($row);

            $nestedData = [
                "<div align='center'>{$nomor}</div>",
                "<div align='center'>{$row['no_pr']}</div>",
                "<div align='left'>" . implode('<br><br>', $list_barang) . "</div>",
                "<div align='left'>" . implode('<br><br>', $list_lainnya) . "</div>",
                "<div align='left'>" . implode('<br><br>', $list_qty_barang) . "</div>",
                "<div align='center'>" . date('d F Y', strtotime($row['tgl_dibutuhkan'])) . "</div>",
                "<div align='center'><span class='badge' style='background-color: {$status_info['warna']};'>{$status_info['sts']}</span></div>",
                "<div align='center'>{$row['request_by']}</div>",
                "<div align='center'>{$row['request_date']}</div>",
                "<div align='center'>{$status_info['aksi']}</div>",
            ];

            $data[] = $nestedData;
            $urut++;
        }

        echo json_encode([
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ]);
    }

    public function get_query_json_material_planning($like = null, $column_order = null, $column_dir = null, $limit_start = 0, $limit_length = 10)
    {
        $columns_order_by = [
            0 => 'a.no_pr',
            1 => 'b.nm_customer',
            2 => 'a.tgl_dibutuhkan',
            7 => 'a.created_date'
        ];

        $this->db->select("a.*, b.nm_customer, c.nm_lengkap as request_by, DATE_FORMAT(a.created_date, '%d %M %Y') as request_date");
        $this->db->from('material_planning_base_on_produksi a');
        $this->db->join('customer b', 'a.id_customer = b.id_customer', 'left');
        $this->db->join('users c', 'c.id_user = a.created_by', 'left');
        // $this->db->join('new_inventory_4 i', 'i.code_lv4 = a.', 'left');
        $this->db->where("a.category IN ('pr material','base on production')");
        $this->db->where("a.booking_date IS NOT NULL");
        $this->db->where("a.close_pr IS NULL");

        if ($like) {
            $this->db->group_start();
            $this->db->like('a.no_pr', $like);
            $this->db->or_like('b.nm_customer', $like);
            $this->db->or_like('c.nm_lengkap', $like);
            $this->db->group_end();
        }

        $totalData = $this->db->count_all_results('', false);
        $totalFiltered = $totalData;

        if ($column_order !== null && isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('a.created_date', 'ASC');
        }

        if ($limit_length != -1) {
            $this->db->limit($limit_length, $limit_start);
        }

        $query = $this->db->get();

        return [
            'totalData'     => $totalData,
            'totalFiltered' => $totalFiltered,
            'query'         => $query
        ];
    }

    private function get_status_info($row)
    {
        $getCheck = $this->db->get_where('material_planning_base_on_produksi_detail', [
            'so_number' => $row['so_number'],
            'status_app' => 'N'
        ])->result();

        $getCheckN = $this->db->get_where('material_planning_base_on_produksi_detail', [
            'so_number' => $row['so_number'],
            'status_app' => 'N'
        ])->num_rows();

        $getCheckY = $this->db->get_where('material_planning_base_on_produksi_detail', [
            'so_number' => $row['so_number'],
            'status_app' => 'Y'
        ])->num_rows();

        if (($row['sts_reject1'] || $row['sts_reject2'] || $row['sts_reject3']) && $row['rejected'] == 1) {
            if ($row['sts_reject1'] == "1") {
                $warna = "red";
                $sts = "Rejected By Head";
            } elseif ($row['sts_reject2'] == "1") {
                $warna = "red";
                $sts = "Rejected By Cost Control";
            } elseif ($row['sts_reject3'] == "1") {
                $warna = "red";
                $sts = "Rejected By Management";
            }
        } else {
            if (!$row['app_1'] && !$row['app_2'] && !$row['app_3']) {
                $warna = "blue";
                $sts = "Waiting Approval Head";
            } elseif ($row['app_1'] && !$row['app_2'] && !$row['app_3']) {
                $warna = "blue";
                $sts = "Waiting Approval Cost Control";
            } elseif ($row['app_1'] && $row['app_2'] && !$row['app_3']) {
                $warna = "blue";
                $sts = "Waiting Approval Management";
            } elseif ($row['sts_app'] == "Y") {
                $warna = "green";
                $sts = "Approved";
            } else {
                $warna = "blue";
                $sts = "Waiting Approval Head";
            }
        }

        if ($getCheckY > 0 && $getCheckN > 0) {
            $warna = "orange";
            $sts = "Approved Partial";
        } elseif ($getCheckN <= 0) {
            $warna = "green";
            $sts = "Approved";
        }

        $base_url = base_url($this->uri->segment(1));
        $view = "<a href='{$base_url}/detail_planning/{$row['so_number']}' class='btn btn-sm btn-warning'><i class='fa fa-eye'></i></a>";
        $print = "<a href='{$base_url}/print_new/{$row['so_number']}' class='btn btn-sm btn-primary' target='_blank'><i class='fa fa-print'></i></a>";
        $edit = '';
        if ($this->ENABLE_MANAGE && (count($getCheck) > 0 || $row['reject_status'] == '1')) {
            $edit = "<a href='{$base_url}/edit_planning/{$row['so_number']}' class='btn btn-sm btn-info'><i class='fa fa-edit'></i></a>";
        }

        $close = '';
        if ($this->ENABLE_DELETE) {
            $close = "<button type='button' class='btn btn-sm btn-danger close_pr_modal' data-so_number='{$row['so_number']}'><i class='fas fa-ban'></i></button>";
        }

        $aksi = ($row['reject_status'] == '1')
            ? "{$view} {$edit} {$close}"
            : "{$view} {$edit} {$print} {$close}";

        return ['warna' => $warna, 'sts' => $sts, 'aksi' => $aksi];
    }
}
