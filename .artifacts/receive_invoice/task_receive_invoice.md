# Receive Invoice Refactoring Task List

- [x] Create/Update Implementation Plan (`plan_receive_invoice.md`)
- [x] Database
  - [x] Recreate `tr_receive_invoice` table with new unified schema (Revisi 1)
- [x] Refactor `Purchase_order_payment.php`
  - [x] Update `save_dp()` mapping
  - [x] Update `save_import()` mapping
  - [x] Update `save_local()` mapping
- [x] Update Views
  - [x] `form_dp.php` (Ganti value_dp dengan nilai_invoice, set field hapus jadi 0 mode view)
  - [x] `form_il.php` (Tambahkan id_incoming hidden input)
- [x] Update Walkthrough (`walkthrough_receive_invoice.md`)
