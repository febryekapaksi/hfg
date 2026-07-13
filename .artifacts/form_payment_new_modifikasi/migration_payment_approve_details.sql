-- Migration: Tambah kolom di payment_approve_details untuk menyimpan data per PO
-- Tanggal: 09-07-2026

ALTER TABLE payment_approve_details
    ADD COLUMN no_po VARCHAR(50) NULL AFTER no_doc,
    ADD COLUMN nilai_invoice DECIMAL(20,2) NULL DEFAULT 0 AFTER total,
    ADD COLUMN nilai_ppn DECIMAL(20,2) NULL DEFAULT 0 AFTER nilai_invoice,
    ADD COLUMN nilai_pph DECIMAL(20,2) NULL DEFAULT 0 AFTER nilai_ppn,
    ADD COLUMN tipe_pph VARCHAR(20) NULL AFTER nilai_pph,
    ADD COLUMN kurs_invoice DECIMAL(20,2) NULL DEFAULT 0 AFTER tipe_pph,
    ADD COLUMN id_receive_invoice INT NULL AFTER kurs_invoice;
