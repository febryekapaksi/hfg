# Walkthrough: Refactor Confirm SPK Coil ke Request List

Modul `confirm_spk_coil` yang sebelumnya berjalan sebagai halaman/modul terpisah kini telah berhasil digabungkan (di-refactor) ke dalam modul utama `request_list`.

## Ringkasan Perubahan Utama

1. **Penghapusan Modul Lama**
   Modul `application/modules/confirm_spk_coil` telah dihapus secara keseluruhan untuk membersihkan sistem dari fungsi redundan.
   
2. **Integrasi Controller & Model**
   Seluruh fungsi konfirmasi (pengurangan stock, transit produksi, dan scan QR) dari `Confirm_spk_coil_model.php` telah dipindahkan ke `Request_list_model.php`. Controller `Request_list.php` kini memiliki endpoint AJAX baru untuk menangani pengambilan data SPK Coil, list coil, scan, hingga konfirmasi pengeluaran.

3. **UI/UX Modal Per SPK Coil**
   - Pada halaman **Request List**, jika terdapat SPK dengan status "Material On Load", maka tombol **Confirm** (hijau dengan icon ceklis) akan muncul di baris tersebut.
   - Saat tombol ditekan, sebuah **Modal** (Pop-up) akan muncul.
   - Karena satu SPK bisa memiliki lebih dari satu SPK Coil (Request), pengguna diharuskan memilih "Nomor SPK Coil" dari dropdown yang disediakan di dalam modal.
   - Setelah SPK Coil dipilih, tabel daftar coil yang harus di-scan dan kolom input scan akan muncul.

4. **Fitur Scan Otomatis**
   Pengguna dapat langsung men-scan menggunakan barcode scanner (input disubmit otomatis dengan menekan *Enter*), atau menggunakan fitur kamera (HTML5 QR Code) yang tersedia di dalam modal. Suara 'beep' (indikator sukses) akan diputar saat QR Code valid di-scan, dan statusnya langsung berubah.

## Pengujian dan Verifikasi (Manual)

Untuk menguji bahwa semua fitur berjalan normal:
1. Pastikan Anda memiliki SPK Material berstatus **Material On Load** (bisa dibuat dengan klik Create SPK Coil).
2. Klik tombol **Confirm** (hijau) di datatable Request List pada baris SPK tersebut.
3. Pilih Request ID (SPK Coil) pada dropdown di Modal.
4. Lakukan scan QR (Gunakan Kamera atau input manual dengan format `kode_internal/gudang` lalu enter).
5. Pastikan semua coil tercentang/statusnya berubah hijau menjadi "Sudah". Tombol submit `Confirm Pengeluaran` akan otomatis aktif.
6. Klik submit untuk memindahkan coil ke production transit dan mengubah status material menjadi **Material Confirmed**.

> [!TIP]
> Jika ada SPK Coil lain dalam SPK yang sama yang belum terkonfirmasi, tombol Confirm di SPK tersebut masih akan ada (karena status SPK belum menjadi Material Confirmed sepenuhnya sampai semua SPK Coil dikonfirmasi).

Semoga perubahan ringkas dan terpadu ini mempermudah proses operasional Gudang/Produksi.
