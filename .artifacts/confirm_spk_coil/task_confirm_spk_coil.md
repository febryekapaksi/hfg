# Task List: Refactor Confirm SPK Coil

- `[/]` 1. **Model Updates** (`Request_list_model.php`)
  - `[ ]` 1.1 Pindahkan fungsi-fungsi terkait scan dari `Confirm_spk_coil_model.php` (seperti `find_coil_by_kode_internal`, `update_scan_status`, dll).
  - `[ ]` 1.2 Pindahkan fungsi terkait konfirmasi dan stock (`reduce_coil_stock`, `insert_production_transit_record`, `get_coil_source_data`).
  - `[ ]` 1.3 Tambahkan fungsi `get_pending_spkc_by_spk($spk_no)`.

- `[ ]` 2. **Controller Updates** (`Request_list.php`)
  - `[ ]` 2.1 Tambahkan endpoint `get_pending_spkc($spk_no)`.
  - `[ ]` 2.2 Tambahkan endpoint `get_coils_to_confirm($request_id)` (atau perbarui yang lama).
  - `[ ]` 2.3 Tambahkan endpoint AJAX `scan_coil_spk()`.
  - `[ ]` 2.4 Tambahkan endpoint AJAX `confirm_spk_coil($request_id)`.
  - `[ ]` 2.5 Modifikasi `data_side()` untuk menambahkan tombol Confirm di action table.

- `[ ]` 3. **View Updates** (`request_list/views/index.php`)
  - `[ ]` 3.1 Tambahkan markup Modal konfirmasi (Dropdown pilihan SPK Coil, field input barcode, tabel coil).
  - `[ ]` 3.2 Tambahkan script JavaScript/AJAX untuk handle modal, select SPKC, scan QR, dan submit.

- `[ ]` 4. **Cleanup Module**
  - `[ ]` 4.1 Hapus seluruh isi dari `application/modules/confirm_spk_coil`.
