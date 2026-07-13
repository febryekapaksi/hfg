# Rencana Refactoring Receive Invoice (09-07-2026)

Rencana ini bertujuan untuk menggabungkan tabel `tr_receive_invoice_dp` dan `tr_receive_invoice_imp_lok` menjadi satu tabel tunggal `tr_receive_invoice` dengan mengakomodir field akuntansi seperti selisih kurs dan unbill.

## Analisis & Desain Solusi

### 1. Struktur Tabel Tunggal `tr_receive_invoice` (Revisi 1)
```sql
CREATE TABLE `tr_receive_invoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipe` enum('dp','import','local') NOT NULL,
  
  -- Relasi Dokumen
  `id_po` varchar(125) DEFAULT NULL,         
  `no_po` varchar(50) NOT NULL,              
  `no_surat` varchar(100) NOT NULL,
  `id_top` int(11) DEFAULT NULL,             
  `tipe_top` varchar(50) DEFAULT NULL,       
  `id_ros` varchar(125) DEFAULT NULL,        
  `id_incoming` varchar(125) DEFAULT NULL,   
  
  -- Detail Invoice
  `nomor_invoice` varchar(100) NOT NULL,
  `invoice_date` date NOT NULL,
  `invoice_date_real` date DEFAULT NULL,
  `nomor_faktur_pajak` varchar(100) DEFAULT NULL,
  `tanggal_faktur_pajak` date DEFAULT NULL,
  `file_invoice` varchar(255) DEFAULT NULL,
  
  -- Nilai Tagihan
  `currency` varchar(10) DEFAULT 'IDR',
  `kurs` decimal(18,2) DEFAULT '1.00',
  `nilai_invoice` decimal(18,4) DEFAULT '0.0000', 
  `nilai_ppn` decimal(18,2) DEFAULT '0.00',
  `jumlah_rupiah` decimal(20,4) DEFAULT '0.0000', 
  
  -- Data Akuntansi & Selisih
  `value_ros_by_po` decimal(18,4) DEFAULT '0.0000', 
  `selisih_kurs` decimal(18,4) DEFAULT '0.0000',    
  `unbill` decimal(18,4) DEFAULT '0.0000',          
  
  -- Pembayaran & Status
  `bank` varchar(100) NOT NULL,
  `no_bank` varchar(50) NOT NULL,
  `nm_acc_bank` varchar(100) NOT NULL,
  `status` int(11) DEFAULT NULL,             
  
  -- Log Audit
  `created_by` int(11) DEFAULT NULL,
  `created_on` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(255) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  KEY `idx_no_po` (`no_po`),
  KEY `idx_id_top` (`id_top`),
  KEY `idx_id_ros` (`id_ros`),
  KEY `idx_id_incoming` (`id_incoming`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2. Alur Transaksi & Penggunaan Field Baru
- **`nilai_invoice`**: Menggantikan kolom redundan seperti `value_dp` dan `sisa_nilai`. Saat insert tipe `dp`, field ini diisi nilai DP. Saat pelunasan, diisi sisa tagihannya.
- **`unbill` dan `selisih_kurs`**: Akan diisi oleh kalkulasi pada saat controller memproses *save_import* / *save_local*, di mana sistem membandingkan nilai kurs saat DP (atau faktur) dengan kurs pelunasan.
- **`id_incoming`**: Menyimpan referensi penerimaan barang untuk vendor lokal, serupa dengan fungsi `id_ros` pada transaksi import.

## Proposed Changes

### Database
- Melakukan eksekusi `DROP TABLE IF EXISTS tr_receive_invoice` dan membuat ulang tabel `tr_receive_invoice` dengan struktur baru ini.

### Controller & Model

#### [MODIFY] [Purchase_order_payment.php](file:///var/www/middle74/hfg/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php)
- **`save_dp()`**: Menghitung `nilai_invoice` dan membuang logic insert `outstanding`.
- **`save_import()`** & **`save_local()`**: Menyertakan penyimpanan `id_incoming` (jika local), `selisih_kurs`, `unbill`, dan `value_ros_by_po` ke dalam row tabel receive invoice saat disave, lalu diikatkan dengan jurnal (*Gl_interface_model*).

### View
- Menyesuaikan form input untuk menangkap atau menampilkan `id_incoming`, `unbill` dan `selisih_kurs` jika diperlukan (biasanya dihitung di backend, tapi fieldnya perlu dikirim/ditampilkan).

## Verification Plan
1. Re-create tabel `tr_receive_invoice` dengan DDL yang direvisi.
2. Cek penyimpanan record baru, memastikan kolom `unbill` dan `selisih_kurs` terisi pada kasus perbedaan kurs antara PO/DP dan Pelunasan.
