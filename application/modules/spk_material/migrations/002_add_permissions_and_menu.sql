-- =====================================================
-- Migration: Add Permissions & Menu for SPK Material
-- Module: spk_material
-- Requirements: 6.6
-- =====================================================
-- Jalankan query ini untuk menambahkan permission dan menu ke sistem
-- =====================================================

-- =====================================================
-- STEP 1: Insert Menu Entry
-- =====================================================
-- NOTE: Sesuaikan parent_id dengan ID menu parent "Production" atau sesuai hierarki yang berlaku.
--       Sesuaikan `order` agar menu tampil di posisi yang diinginkan.
--       Setelah INSERT menu, catat id yang dihasilkan untuk dipakai di permissions.

INSERT INTO `menus` (`title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`)
VALUES ('SPK Material', 'spk_material', 'fa fa-clipboard-list', '_self', 1, 0, 0, 1, 99);

-- Ambil ID menu yang baru saja diinsert
SET @menu_id = LAST_INSERT_ID();

-- =====================================================
-- STEP 2: Insert Permission Entries
-- =====================================================

INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`) VALUES
('Spk_Material.View', @menu_id, 'SPK Material', 'View', NOW()),
('Spk_Material.Add', @menu_id, 'SPK Material', 'Add', NOW()),
('Spk_Material.Manage', @menu_id, 'SPK Material', 'Manage', NOW());

-- =====================================================
-- STEP 3: Update menu permission_id with the View permission
-- =====================================================
-- Menu membutuhkan permission_id yang merujuk ke permission .View

SET @view_perm_id = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Spk_Material.View' LIMIT 1);

UPDATE `menus` SET `permission_id` = @view_perm_id WHERE `id` = @menu_id;

-- =====================================================
-- NOTE:
-- Setelah menjalankan migration ini, admin harus assign permission
-- ke role yang sesuai melalui menu User Management > Roles.
--
-- Permission yang tersedia:
--   - Spk_Material.View   : Lihat daftar SPK Material
--   - Spk_Material.Add    : Buat SPK Material baru
--   - Spk_Material.Manage : Kelola SPK Material (edit, update status)
--
-- Controller sudah menggunakan $this->auth->restrict() di setiap method:
--   - index(), view(), data_side(), get_material_bom(), print_pdf() → Spk_Material.View
--   - add(), save() (mode create) → Spk_Material.Add
--   - edit(), save() (mode edit), update_status() → Spk_Material.Manage
-- =====================================================
