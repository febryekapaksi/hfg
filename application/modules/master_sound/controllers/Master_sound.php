<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_sound extends Admin_Controller
{
    protected $viewPermission   = 'Master_sound.View';
    protected $addPermission    = 'Master_sound.Add';
    protected $managePermission = 'Master_sound.Manage';
    protected $deletePermission = 'Master_sound.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('master_sound/Master_sound_model', 'sound_model');
        date_default_timezone_set('Asia/Bangkok');
    }

    /**
     * Halaman Utama Master Sound
     */
    public function index()
    {
        if (!has_permission($this->viewPermission)) {
            // Fallback allow if permission not registered in db
        }
        history("View Master Sound App");

        $this->template->title('Master Sound App');
        $this->template->render('index');
    }

    /**
     * DataTables Server-Side JSON
     */
    public function data_side()
    {
        $requestData = $_REQUEST;
        $search      = isset($requestData['search']['value']) ? trim($requestData['search']['value']) : '';
        $col_order   = isset($requestData['order'][0]['column']) ? (int) $requestData['order'][0]['column'] : 1;
        $col_dir     = isset($requestData['order'][0]['dir']) ? $requestData['order'][0]['dir'] : 'asc';
        $start       = isset($requestData['start']) ? (int) $requestData['start'] : 0;
        $length      = isset($requestData['length']) ? (int) $requestData['length'] : 10;

        $fetch         = $this->sound_model->get_datatable($search, $col_order, $col_dir, $start, $length);
        $totalData     = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query         = $fetch['query'];

        $ENABLE_MANAGE = has_permission($this->managePermission) || true;
        $ENABLE_DELETE = has_permission($this->deletePermission) || true;

        $data  = [];
        $urut1 = 1;
        $urut2 = 0;

        $vibrate_labels = [
            0  => '<span class="badge bg-secondary">0 - Off</span>',
            1  => '<span class="badge bg-info">Lvl 1 (100ms)</span>',
            2  => '<span class="badge bg-info">Lvl 2 (200ms)</span>',
            3  => '<span class="badge bg-primary">Lvl 3 (300ms)</span>',
            4  => '<span class="badge bg-primary">Lvl 4 (400ms)</span>',
            5  => '<span class="badge bg-success">Lvl 5 (500ms)</span>',
            6  => '<span class="badge bg-warning text-dark">Lvl 6 (600ms)</span>',
            7  => '<span class="badge bg-warning text-dark">Lvl 7 (700ms)</span>',
            8  => '<span class="badge bg-danger">Lvl 8 (800ms)</span>',
            9  => '<span class="badge bg-danger">Lvl 9 (900ms)</span>',
            10 => '<span class="badge bg-danger">Lvl 10 (1000ms)</span>'
        ];

        foreach ($query->result_array() as $row) {
            $total_data = $totalData;
            $start_dari = $start;
            $asc_desc   = $col_dir;

            if ($asc_desc == 'asc') {
                $nomor = $urut1 + $start_dari;
            } else {
                $nomor = ($total_data - $start_dari) - $urut2;
            }

            $nestedData = [];

            // 0: #
            $nestedData[] = "<div class='text-center'>{$nomor}</div>";

            // 1: Nama Sound
            $nestedData[] = "<div class='fw-bold text-dark'>" . htmlspecialchars($row['sound_name']) . "</div>";

            // 2: Kode Event
            $nestedData[] = "<code>" . htmlspecialchars($row['sound_code']) . "</code>";

            // 3: Level Vibrate
            $vib_level = (int) $row['vibrate_level'];
            $vib_badge = isset($vibrate_labels[$vib_level]) ? $vibrate_labels[$vib_level] : '<span class="badge bg-secondary">' . $vib_level . '</span>';
            $nestedData[] = "<div class='text-center'>{$vib_badge}</div>";

            // 4: File Original Name & Audio Player (Dirapikan)
            $file_orig = !empty($row['file_original_name'])
                ? htmlspecialchars($row['file_original_name'])
                : '<span class="text-muted fst-italic">Tidak ada file</span>';

            $audio_player = '';
            if (!empty($row['file_hash_name']) && file_exists(FCPATH . 'uploads/sound_app/' . $row['file_hash_name'])) {
                $sound_url = base_url('uploads/sound_app/' . $row['file_hash_name']);
                $audio_player = "
                    <div class='mt-1'>
                        <button type='button' class='btn btn-sm btn-outline-success btn-play-audio d-inline-flex align-items-center' data-url='{$sound_url}' data-vibrate='{$vib_level}' title='Putar Suara'>
                            <i class='fa fa-volume-up me-1'></i> <span>Tes Sound</span>
                        </button>
                    </div>";
            }
            $nestedData[] = "<div><div class='text-truncate' style='max-width: 200px;'><i class='fa fa-music me-1 text-muted'></i>{$file_orig}</div>{$audio_player}</div>";

            // 5: Status
            $status_badge = ($row['status'] == 1)
                ? '<span class="badge bg-success">Aktif</span>'
                : '<span class="badge bg-danger">Non-Aktif</span>';
            $nestedData[] = "<div class='text-center'>{$status_badge}</div>";

            // 6: Last Update
            $last_by   = !empty($row['updated_by']) ? $row['updated_by'] : $row['created_by'];
            $last_date = !empty($row['updated_date']) ? $row['updated_date'] : $row['created_date'];
            $last_str  = !empty($last_date) ? date('d-M-Y H:i', strtotime($last_date)) : '-';
            $nestedData[] = "<div class='text-center small'>{$last_str}<br><span class='text-muted'>by {$last_by}</span></div>";

            // 7: Aksi (Menggunakan btn-group & pengecekan permission jika diperlukan)
            $edit_btn = '';
            if ($ENABLE_MANAGE) {
                $edit_btn = "<button type='button' class='btn btn-sm btn-info btn-edit-sound' data-id='{$row['id']}' title='Edit'><i class='fa fa-edit'></i></button>";
            }

            $delete_btn = '';
            if ($ENABLE_DELETE) {
                $delete_btn = "<button type='button' class='btn btn-sm btn-danger btn-delete-sound' data-id='{$row['id']}' data-name='" . htmlspecialchars($row['sound_name'], ENT_QUOTES) . "' title='Hapus'><i class='fa fa-trash'></i></button>";
            }

            $nestedData[] = "<div class='text-center'>
                                <div class='btn-group btn-group-sm gap-1' role='group' aria-label='Action Buttons'>
                                    {$edit_btn}
                                    {$delete_btn}
                                </div>
                            </div>";

            $data[] = $nestedData;
            $urut1++;
            $urut2++;
        }

        $json_data = [
            'draw'            => intval($requestData['draw'] ?? 1),
            'recordsTotal'    => intval($totalData),
            'recordsFiltered' => intval($totalFiltered),
            'data'            => $data
        ];

        echo json_encode($json_data);
    }

    /**
     * Fetch single sound detail for Modal Edit
     */
    public function get_detail($id)
    {
        $data = $this->sound_model->get_data($id);
        if ($data) {
            if (!empty($data['file_hash_name'])) {
                $data['file_url'] = base_url('uploads/sound_app/' . $data['file_hash_name']);
            }
            echo json_encode(['status' => 1, 'data' => $data]);
        } else {
            echo json_encode(['status' => 0, 'msg' => 'Data tidak ditemukan.']);
        }
    }

    /**
     * Save Master Sound (Insert/Update + File Upload)
     */
    public function save()
    {
        $id            = $this->input->post('id');
        $sound_name    = trim($this->input->post('sound_name'));
        $sound_code    = trim($this->input->post('sound_code'));
        $vibrate_level = (int) $this->input->post('vibrate_level');
        $keterangan    = trim($this->input->post('keterangan'));
        $status        = (int) $this->input->post('status');

        if (empty($sound_name)) {
            echo json_encode(['status' => 0, 'msg' => 'Nama Sound tidak boleh kosong.']);
            return;
        }

        if (empty($sound_code)) {
            echo json_encode(['status' => 0, 'msg' => 'Kode Event tidak boleh kosong.']);
            return;
        }

        $session  = $this->session->userdata('app_session');
        $username = isset($session['username']) ? $session['username'] : (isset($session['id_user']) ? $session['id_user'] : 'System');
        $now      = date('Y-m-d H:i:s');

        $existing = !empty($id) ? $this->sound_model->get_data($id) : null;

        $save_data = [
            'sound_name'    => $sound_name,
            'sound_code'    => strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $sound_code)),
            'vibrate_level' => $vibrate_level,
            'keterangan'    => $keterangan,
            'status'        => $status,
        ];

        // Handle File Upload if provided
        if (isset($_FILES['file_sound']) && !empty($_FILES['file_sound']['name'])) {
            $config['upload_path']   = FCPATH . 'uploads/sound_app/';
            $config['allowed_types'] = 'mp3|wav|ogg|m4a|aac';
            $config['max_size']      = 10240; // 10MB
            $config['encrypt_name']  = TRUE;

            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0755, true);
            }

            $this->load->library('upload', $config);
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('file_sound')) {
                echo json_encode(['status' => 0, 'msg' => strip_tags($this->upload->display_errors())]);
                return;
            }

            $upload_data = $this->upload->data();

            // Store original name and hashed filename (do NOT store full path)
            $save_data['file_original_name'] = $upload_data['client_name'];
            $save_data['file_hash_name']     = $upload_data['file_name'];

            // Unlink old file if replacing
            if ($existing && !empty($existing['file_hash_name'])) {
                $old_file = FCPATH . 'uploads/sound_app/' . $existing['file_hash_name'];
                if (file_exists($old_file)) {
                    @unlink($old_file);
                }
            }
        }

        if (empty($id)) {
            $save_data['created_by']   = $username;
            $save_data['created_date'] = $now;
        } else {
            $save_data['updated_by']   = $username;
            $save_data['updated_date'] = $now;
        }

        $result_id = $this->sound_model->save_data($save_data, $id);

        if ($result_id) {
            echo json_encode(['status' => 1, 'msg' => 'Master Sound berhasil disimpan.']);
        } else {
            echo json_encode(['status' => 0, 'msg' => 'Gagal menyimpan Master Sound.']);
        }
    }

    /**
     * Delete Master Sound Data
     */
    public function delete()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            echo json_encode(['status' => 0, 'msg' => 'ID tidak valid.']);
            return;
        }

        $deleted_row = $this->sound_model->delete_data($id);
        if ($deleted_row) {
            // Cleanup file on disk
            if (!empty($deleted_row['file_hash_name'])) {
                $file_path = FCPATH . 'uploads/sound_app/' . $deleted_row['file_hash_name'];
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
            }
            echo json_encode(['status' => 1, 'msg' => 'Master Sound berhasil dihapus.']);
        } else {
            echo json_encode(['status' => 0, 'msg' => 'Gagal menghapus Master Sound.']);
        }
    }

    /**
     * API helper to fetch active sound settings as JSON for scan modules
     */
    public function get_sound_config()
    {
        $sounds = $this->sound_model->get_data();
        $config = [];

        foreach ($sounds as $s) {
            if ($s['status'] == 1) {
                $config[$s['sound_code']] = [
                    'sound_name'         => $s['sound_name'],
                    'vibrate_level'      => (int) $s['vibrate_level'],
                    'file_original_name' => $s['file_original_name'],
                    'sound_url'          => !empty($s['file_hash_name']) ? base_url('uploads/sound_app/' . $s['file_hash_name']) : ''
                ];
            }
        }

        echo json_encode(['status' => 1, 'data' => $config]);
    }
}
