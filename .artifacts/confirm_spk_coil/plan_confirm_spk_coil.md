# Rencana Implementasi: Warning & Move Coil ke SPKC Baru

## Analisis Kebutuhan
Berdasarkan permintaan Anda:
1. Coil yang sudah dipilih di SPK Coil lain (tapi **belum di-scan**) tetap muncul di daftar pilihan.
2. Jika user mencoba memilih coil tersebut, akan muncul **peringatan (konfirmasi)** yang memberi tahu bahwa coil tersebut sudah ada di SPK Coil nomor sekian.
3. Jika user setuju (Yes), coil tersebut akan dipindahkan (dikeluarkan dari SPK Coil lama dan dimasukkan ke SPK Coil yang baru dibuat).

## User Review Required & Open Questions

> [!WARNING]
> Mohon konfirmasi untuk beberapa detail berikut sebelum saya mengeksekusi kodenya:
> 
> 1. **Coil yang SUDAH di-scan:** Apakah setuju jika coil yang *sudah di-scan* di SPK Coil lain akan disembunyikan (tidak bisa dipilih sama sekali)?
> 2. **SPK Coil Lama Kosong:** Jika kita mengeluarkan coil dari SPKC lama, ada kemungkinan SPKC lama menjadi kosong (tidak punya coil). Apakah SPKC lama biarkan saja kosong, atau harus otomatis dihapus/dibatalkan? (Saya sarankan dibiarkan saja agar history nomornya tidak hilang, atau diubah statusnya jadi Cancelled jika kosong sama sekali).

## Proposed Changes

### 1. Model Updates (`Request_list_model.php`)
#### [MODIFY] `get_available_coils`
- Mengubah query agar **tidak mengecualikan** coil yang sudah dipilih (jika `scan_status = 0`).
- Menambahkan subquery untuk mengambil nomor `spk_coil_no` dari SPKC tempat coil tersebut saat ini berada (sebagai alias `assigned_spkc`).
- Tetap mengecualikan coil yang sudah di-scan (`scan_status = 1`).

#### [MODIFY] `save_spk_coil` (atau buat fungsi helper)
- Saat menyimpan SPK Coil baru, cek apakah coil-coil yang dipilih sebelumnya terdaftar di SPK Coil lain (berdasarkan `id_coil`).
- Jika ya, hapus baris coil tersebut dari tabel detail SPK Coil lama (`tr_warehouse_request_coil_detail`) sebelum meng-insert-nya ke SPK Coil baru ini.

### 2. View Updates (`form_create_spk_coil.php`)
#### [MODIFY] Event Checkbox `.coil-checkbox`
- Saat user meng-klik/mencentang checkbox coil, kita cek data `data-assigned-spkc`.
- Jika ada nilainya, cegah checklist sementara, lalu tampilkan pop-up `Swal.fire` berisi: 
  *"Coil ini sudah terdaftar di [Nomor SPKC] dan belum di-scan. Apakah Anda ingin mengeluarkannya dan memasukannya ke SPK Coil baru ini?"*
- Jika user klik **Ya**, biarkan checkbox tercentang.
- Jika user klik **Batal**, batalkan centang pada checkbox.

## Verification Plan
1. Buat SPK Coil (SPKC-A) dan pilih Coil X.
2. Buka form Create SPK Coil lagi untuk SPK yang sama. Coil X harusnya masih muncul.
3. Centang Coil X, maka akan muncul SweetAlert konfirmasi.
4. Klik Ya, lalu Save untuk membuat SPKC-B.
5. Cek di database, Coil X harusnya hilang dari detail SPKC-A dan berpindah ke SPKC-B.
