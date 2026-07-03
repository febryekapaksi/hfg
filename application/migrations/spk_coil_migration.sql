-- ============================================================
-- SPK Coil Confirmation - Database Migration
-- Perubahan schema untuk fitur SPK Coil Request & Confirmation
-- ============================================================

-- 1. ALTER tr_spk_material_header — tambah ENUM status "Material On Load"
ALTER TABLE tr_spk_material_header 
MODIFY COLUMN status ENUM(
    'Material Requested',
    'Material On Load',
    'Material Confirmed',
    'Released',
    'Cancelled'
) DEFAULT 'Material Requested';

-- 2. ALTER tr_warehouse_request_header — ADD COLUMN spk_coil_no, MODIFY status ENUM
ALTER TABLE tr_warehouse_request_header 
ADD COLUMN spk_coil_no VARCHAR(30) NULL AFTER spk_no;

ALTER TABLE tr_warehouse_request_header 
MODIFY COLUMN status ENUM(
    'Pending',
    'Material On Load',
    'Material Confirmed',
    'Rejected'
) DEFAULT 'Pending';

-- 3. CREATE TABLE tr_warehouse_request_coil_detail
CREATE TABLE tr_warehouse_request_coil_detail (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    request_id      INT NOT NULL,
    id_coil         INT NOT NULL,
    id_material     VARCHAR(50) NOT NULL,
    nm_material     VARCHAR(200),
    kode_internal   VARCHAR(100) NOT NULL,
    no_coil         VARCHAR(100),
    id_gudang_sumber INT NOT NULL COMMENT '1=Gudang Coil, 3=WIP',
    plan_use        DECIMAL(12,4) NOT NULL DEFAULT 0,
    scan_status     TINYINT(1) DEFAULT 0 COMMENT '0=belum scan, 1=sudah scan',
    scanned_at      DATETIME NULL,
    scanned_by      INT NULL,
    FOREIGN KEY (request_id) REFERENCES tr_warehouse_request_header(id) ON DELETE CASCADE,
    INDEX idx_request_id (request_id),
    INDEX idx_kode_internal (kode_internal),
    INDEX idx_id_coil (id_coil)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. CREATE TABLE warehouse_stock_wip
CREATE TABLE warehouse_stock_wip (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    id_material     VARCHAR(50),
    nm_material     VARCHAR(200),
    trade_name      VARCHAR(200),
    kode_internal   VARCHAR(100),
    id_gudang       INT DEFAULT 3,
    kd_gudang       VARCHAR(10) DEFAULT 'WIP',
    no_coil         VARCHAR(100),
    no_ipp          VARCHAR(100),
    no_po           VARCHAR(100),
    no_ros          VARCHAR(100),
    gross_weight    DECIMAL(12,4) DEFAULT 0,
    net_weight      DECIMAL(12,4) DEFAULT 0,
    length          DECIMAL(12,4) DEFAULT 0,
    qty             DECIMAL(12,4) DEFAULT 0,
    harga_beli      DECIMAL(15,2) DEFAULT 0,
    total_nilai     DECIMAL(15,2) DEFAULT 0,
    status          TINYINT(1) DEFAULT 1,
    type            ENUM('from_warehouse','coil_remains') NOT NULL,
    created_by      INT,
    created_on      DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_id_material (id_material),
    INDEX idx_kode_internal (kode_internal),
    INDEX idx_type (type),
    INDEX idx_id_gudang (id_gudang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 5. PERMISSIONS & MENU ENTRIES
-- ============================================================

-- STEP 5a: Insert Menu Entry for Request List
INSERT INTO `menus` (`title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`)
VALUES ('Request List', 'request_list', 'fa fa-clipboard-check', '_self', 1, 0, 0, 1, 100);

SET @menu_request_list_id = LAST_INSERT_ID();

-- STEP 5b: Insert Permissions for Request List
INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`) VALUES
('Request_List.View', @menu_request_list_id, 'Request List', 'View', NOW()),
('Request_List.Manage', @menu_request_list_id, 'Request List', 'Manage', NOW());

-- Update menu permission_id with the View permission
SET @view_perm_request_list = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Request_List.View' LIMIT 1);
UPDATE `menus` SET `permission_id` = @view_perm_request_list WHERE `id` = @menu_request_list_id;

-- STEP 5c: Insert Menu Entry for Confirm SPK Coil
INSERT INTO `menus` (`title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`)
VALUES ('Confirm SPK Coil', 'confirm_spk_coil', 'fa fa-qrcode', '_self', 1, 0, 0, 1, 101);

SET @menu_confirm_id = LAST_INSERT_ID();

-- STEP 5d: Insert Permissions for Confirm SPK Coil
INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`) VALUES
('Confirm_Spk_Coil.View', @menu_confirm_id, 'Confirm SPK Coil', 'View', NOW()),
('Confirm_Spk_Coil.Manage', @menu_confirm_id, 'Confirm SPK Coil', 'Manage', NOW());

-- Update menu permission_id with the View permission
SET @view_perm_confirm = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Confirm_Spk_Coil.View' LIMIT 1);
UPDATE `menus` SET `permission_id` = @view_perm_confirm WHERE `id` = @menu_confirm_id;

-- ============================================================
-- NOTE:
-- Setelah menjalankan migration ini, admin harus assign permission
-- ke role yang sesuai melalui menu User Management > Roles.
--
-- Permission yang tersedia:
--   - Request_List.View         : Lihat daftar SPK Material & SPK Coil
--   - Request_List.Manage       : Buat SPK Coil baru
--   - Confirm_Spk_Coil.View    : Lihat daftar SPK Coil untuk konfirmasi
--   - Confirm_Spk_Coil.Manage  : Melakukan scan QR dan konfirmasi pengeluaran coil
--
-- URL Routing (HMVC automatic):
--   - request_list              → Request_list::index()
--   - request_list/data_side    → Request_list::data_side()
--   - request_list/create_spk_coil/{spk_no}      → Request_list::create_spk_coil($spk_no)
--   - request_list/get_available_coils/{id_material} → Request_list::get_available_coils($id_material)
--   - request_list/save_spk_coil                  → Request_list::save_spk_coil()
--   - request_list/print_spk_coil/{id}            → Request_list::print_spk_coil($id)
--   - confirm_spk_coil          → Confirm_spk_coil::index()
--   - confirm_spk_coil/data_side → Confirm_spk_coil::data_side()
--   - confirm_spk_coil/detail/{id} → Confirm_spk_coil::detail($id)
--   - confirm_spk_coil/scan_coil → Confirm_spk_coil::scan_coil()
--   - confirm_spk_coil/confirm/{id} → Confirm_spk_coil::confirm($id)
-- ============================================================
