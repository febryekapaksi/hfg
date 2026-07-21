# Walkthrough: Pemindahan Coil & Disable Scanned Coil

Berikut adalah ringkasan dari fitur terbaru yang telah diimplementasikan sesuai dengan instruksi revisi:

## 1. Menampilkan Semua Coil di Form Create
- Coil yang **sudah dipilih** di SPKC lain namun **belum di-scan** kini akan tetap muncul di daftar coil, namun diberikan keterangan/badge **"Terdaftar di SPKC-XXXX"**.
- Coil yang **sudah di-scan** (`scan_status = 1`) di SPKC lain juga akan tetap muncul di daftar, tetapi checkbox-nya telah di-disable secara permanen dan ditambahkan badge hijau **"Scanned di SPKC-XXXX"**. Dengan ini, coil tersebut sama sekali tidak bisa dipilih ulang.

## 2. Peringatan Pemindahan Coil (Warning)
- Jika Anda mencentang coil yang sedang **"Terdaftar"** di SPKC lain, sistem akan membatalkan centang otomatis sesaat dan memunculkan pop-up konfirmasi (SweetAlert).
- Pop-up berbunyi: *"Coil ini sudah ada di SPKC-XXXX dan belum discan. Apakah Anda ingin mengeluarkannya dan memasukannya ke SPK Coil baru ini?"*
- Jika Anda klik **Ya, pindahkan**, barulah coil tersebut tercentang dan siap diproses ke SPKC baru. Jika klik batal, coil tidak akan tercentang.

## 3. Pemindahan Coil dan Update Status SPKC Lama
- Ketika tombol **Save & Create SPK** ditekan, sistem (melalui controller dan model) akan mendeteksi apakah coil yang disimpan berasal dari SPKC lain.
- Jika iya, sistem akan secara otomatis **menghapus baris detail coil tersebut** dari SPKC asalnya.
- Sistem juga melakukan pengecekan: Jika penghapusan coil tersebut menyebabkan SPKC lama menjadi benar-benar kosong (0 coil), maka status SPKC lama tersebut akan secara otomatis diubah menjadi **"Cancelled"**.

Silakan coba simulasikan pembuatan SPK Coil baru, centang coil yang sudah ada di SPKC sebelumnya, lalu klik simpan untuk melihat perpindahannya.
