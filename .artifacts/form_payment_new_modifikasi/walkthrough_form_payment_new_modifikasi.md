# Walkthrough Modifikasi Form Payment New

**Tanggal**: 09-07-2026  
**File**: `application/modules/pembayaran_material/views/form_payment_new.php`  
**File**: `application/modules/pembayaran_material/controllers/Pembayaran_material.php`

## Ringkasan Perubahan

### 1. Flatpickr untuk Tanggal
- Input tanggal `tgl_bayar` diubah dari `type="date"` ke `type="text"` dengan library Flatpickr (CDN)
- CDN CSS: `https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css`
- CDN JS: `https://cdn.jsdelivr.net/npm/flatpickr`
- Format: `Y-m-d`

### 2. Header - Label "Nilai Bank"
- Label "Payment Bank" diganti menjadi **"Nilai Bank"**
- Field input tetap sama (`input_payment_bank`)

### 3. Header - Nilai Bank IDR (Baru)
- Ditambahkan field readonly **"Nilai Bank IDR"** di bawah Nilai Bank (sejajar dengan Kurs)
- Kalkulasi otomatis: `Nilai Bank × Kurs Payment`
- Background abu-abu (readonly)

### 4. Kolom "Invoice" (sebelumnya "Request Payment")
- Label header kolom diganti dari "Request Payment" → "Invoice"
- Nilai di kolom ini TIDAK berubah saat kurs diubah — tetap menampilkan nilai asli (foreign currency)

### 5. Footer Tabel - Layout Baru
Urutan baris footer sekarang:
1. **Subtotal** (sebelumnya "Total Payment")
2. **PPN** (hidden jika import)
3. **PPH** (hidden jika import)
4. **Bank Charge** (input)
5. **Grand Total Payment** = Subtotal + PPN - PPH + Bank Charge
6. **Selisih Kurs** (selalu tampil, 0 jika tidak ada perbedaan)

Colspan footer dinamis: 5 (lokal) atau 2 (import, karena 3 kolom PPH+PPN hidden).

### 6. PPN & PPH Hidden untuk Import
- Pengecekan import berdasarkan field `loi` di `tr_purchase_order`
- Jika `loi = 'Import'`, maka:
  - Kolom PPH dan PPN di header tabel di-hidden (`style="display:none !important"`)
  - Kolom PPH dan PPN di detail per baris di-hidden
  - Baris PPN dan PPH di footer di-hidden
- Fallback: juga cek via `tipe === 'invoice_import'`

### 7. Selisih Kurs (Selalu Tampil)
- Formula: `(Nilai Bank × Kurs Payment) - (Nilai Bank × Kurs Receive Invoice)`
- Kurs receive invoice diambil dari `tr_receive_invoice.kurs` via field `ids` di `request_payment`
- Selalu ditampilkan (0.00 jika tidak ada perbedaan)

## Perubahan Controller: save_payment_po()

### Mapping Penyimpanan ke payment_approve (header):
| Form | Kolom |
|---|---|
| Nilai Bank | `payment_bank` |
| Nilai Bank IDR | `nominal_asli_idr` |
| Kurs Payment | `kurs_payment` |
| Subtotal | `total_payment` |
| PPN | `total_ppn` |
| PPH | `total_pph` |
| Bank Charge | `bank_charge` |
| Grand Total Payment | `tagihan_idr` |
| Selisih Kurs | `selisih_kurs_idr` |

### Insert ke payment_approve_details (per PO):
| Field | Keterangan |
|---|---|
| `payment_id` | ID payment paid |
| `no_doc` / `no_po` | Nomor PO |
| `nilai_invoice` | Nilai invoice (foreign currency) |
| `nilai_ppn` | PPN per PO |
| `nilai_pph` | PPH per PO |
| `tipe_pph` | Tipe PPH |
| `kurs_invoice` | Kurs saat receive invoice |
| `id_receive_invoice` | ID di tr_receive_invoice |

## Migration SQL
File: `.artifacts/form_payment_new_modifikasi/migration_payment_approve_details.sql`

```sql
ALTER TABLE payment_approve_details
    ADD COLUMN no_po VARCHAR(50) NULL AFTER no_doc,
    ADD COLUMN nilai_invoice DECIMAL(20,2) NULL DEFAULT 0 AFTER total,
    ADD COLUMN nilai_ppn DECIMAL(20,2) NULL DEFAULT 0 AFTER nilai_invoice,
    ADD COLUMN nilai_pph DECIMAL(20,2) NULL DEFAULT 0 AFTER nilai_ppn,
    ADD COLUMN tipe_pph VARCHAR(20) NULL AFTER nilai_pph,
    ADD COLUMN kurs_invoice DECIMAL(20,2) NULL DEFAULT 0 AFTER tipe_pph,
    ADD COLUMN id_receive_invoice INT NULL AFTER kurs_invoice;
```
