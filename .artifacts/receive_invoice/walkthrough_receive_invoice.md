# Walkthrough: Refactoring Receive Invoice (DP, Import, Local)

## Ringkasan Perubahan
Kita telah berhasil melakukan refactoring untuk menyatukan struktur tabel *Receive Invoice* (`tr_receive_invoice_dp` dan `tr_receive_invoice_imp_lok`) ke dalam satu tabel terpusat yaitu `tr_receive_invoice`. 

Perubahan ini juga mengakomodasi kalkulasi akuntansi untuk selisih kurs dan *unbill*, sehingga mempermudah proses pembuatan jurnal (GL Interface).

## Detail Implementasi

### 1. Database Schema (`tr_receive_invoice`)
- **Tabel Baru**: Membuat `tr_receive_invoice` untuk semua tipe dokumen (dp, import, local).
- **Kolom Kunci**:
  - `nilai_invoice`: Field universal yang menggantikan `value_dp` (untuk DP) dan `sisa_nilai` (untuk import/local).
  - `unbill`: Menyimpan nilai nominal Hutang Belum Ditagih (Unbill) yang diambil dari jurnal ROS terkait saat pelunasan import.
  - `selisih_kurs`: Menyimpan nilai selisih antara hutang awal dan nilai pelunasan aktual akibat perbedaan kurs.
  - `id_incoming`: Referensi kedatangan barang (khusus untuk tipe local).

### 2. Controller (`Purchase_order_payment.php`)
- **`save_dp()`**: Diperbarui untuk insert ke tabel `tr_receive_invoice` dan memetakan nominal DP ke kolom `nilai_invoice`. Kolom lawas seperti `outstanding`, `jumlah_po`, `persen_dp`, dan `dpp` tidak lagi disimpan di database.
- **`save_import()`**: 
  - Ditambahkan kalkulasi untuk mendapatkan nilai *Unbill* dari GL Interface Detail (ROS).
  - Dihitung *selisih kurs* = *Unbill* - (*Sisa Tagihan USD* * *Kurs Pelunasan*).
  - Menyimpan record ke `tr_receive_invoice`.
- **`save_local()`**: Menyimpan record ke `tr_receive_invoice` dengan tambahan data input `id_incoming`.

### 3. Views (`form_dp.php` & `form_il.php`)
- **`form_dp.php`**: Disesuaikan agar saat mode "View", data dibaca dari kolom `$d['nilai_invoice']` alih-alih `value_dp`. Kolom lama diset secara default agar tidak error (karena nilainya sudah dihitung by the fly atau tidak relevan lagi).
- **`form_il.php`**: Ditambahkan input *hidden* `id_incoming` agar nilai ID kedatangan lokal bisa diproses saat di-submit.

## Hasil Validasi
> [!TIP]
> Seluruh proses *saving* kini diarahkan ke satu tabel yang sama. Pencatatan akuntansi seperti selisih kurs dan unbill sudah otomatis dihitung sebelum data masuk ke tabel `tr_receive_invoice`, menjaga integritas riwayat transaksi.

Silakan ujicoba proses pembuatan invoice (DP, Local, Import) di menu aplikasi untuk memastikan tampilannya sudah pas dan data berhasil masuk ke database dengan format terbaru.
