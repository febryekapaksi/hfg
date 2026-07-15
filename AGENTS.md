# Project Instructions

## Aturan Artifact & Dokumentasi

### 1. Jangan Replace/Overwrite Artifact
- **JANGAN** menimpa file artifact yang sudah ada.
- **SELALU** buat file baru dengan nama yang sesuai tema atau request yang diberikan.
- Tujuan: agar history tetap tersimpan dan bisa di-review kembali.

### 2. Penamaan File Artifact
Gunakan nama deskriptif sesuai fitur/konteks, contoh:
- `status_user_permission.md`
- `analisis_bug_sync.md`
- `rencana_refactor_permission.md`

### 3. Penamaan Task File
File task **HARUS** diberi nama sesuai fitur yang sedang dikembangkan, contoh:
- `task_user_permission.md` (bukan generic `task.md`)
- `task_fitur_invoice.md`
- `task_bug_fix_shift.md`

### 4. Penamaan Implementation Plan
- `plan_fitur_invoice.md`
- `plan_refactor_permission.md`

### 5. Penamaan Walkthrough
- `walkthrough_fitur_invoice.md`
- `walkthrough_refactor_permission.md`

### 6. Set Dokumen Per Fitur
Setiap fitur harus punya set dokumen yang bisa di-trace:
```
plan_[nama_fitur].md → task_[nama_fitur].md → walkthrough_[nama_fitur].md
```

### 7. Penyimpanan artifact
- File artifact dibuat di folder root project dan di kumpulkan kedalam folder `.artifacts/` folder ini akan diabaikan oleh git (git ignore)
- Folder `.artifacts` akan di kelompokkan lagi berdasarkan nama fitur dan nama file folder ini akan diabaikan oleh git (git ignore)
- Contoh : `.artifacts/invoice_export/plan_invoice_export.md`, `.artifacts/proforma_invoice_export/task_proforma_invoice_export.md`, `.artifacts/walkthrough_invoice_export.md`
- **Nota**: jika fitur yang akan di kembangkan belum ada di dalam folder `.artifacts` maka buat dulu foldernya
- **Nota**: jangan ubah nama file artifact yang sudah ada
- **Nota**: selalu gunakan format tanggal `DD-MM-YYYY` untuk file artifact
- **Nota**: 


## Bahasa
- Gunakan **Bahasa Indonesia** untuk komunikasi dan dokumentasi, kecuali diminta lain.
- Kode dan komentar teknis boleh dalam Bahasa Inggris.
- Semua conversetion harus dalam Bahasa Indonesia, kecuali permintaan lain.

## Stack Teknologi
- Framework: **CodeIgniter 3** (PHP)
- Database: **MySQL**
- Frontend: **Bootstrap 4/5** + jQuery
- Pattern: **HMVC** (modules)
