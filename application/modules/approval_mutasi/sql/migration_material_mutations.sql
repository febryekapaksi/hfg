-- =====================================================
-- MIGRATION: Material Mutations Tables
-- Date: 2026-06-30
-- Description: Create tables for material mutation system
-- =====================================================

-- 1. Tabel Header Mutasi
CREATE TABLE IF NOT EXISTS `material_mutations` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `mutation_number` VARCHAR(20) NOT NULL COMMENT 'Format: MUTYYMMXXX',
    `mutation_date` DATE NOT NULL,
    `no_berita_acara` VARCHAR(100) DEFAULT NULL,
    `file_name_original` VARCHAR(255) DEFAULT NULL,
    `file_name_hash` VARCHAR(255) DEFAULT NULL,
    `id_gudang_from` INT(11) NOT NULL,
    `kd_gudang_from` VARCHAR(20) DEFAULT NULL,
    `nm_gudang_from` VARCHAR(100) DEFAULT NULL COMMENT 'Snapshot nama gudang asal',
    `id_gudang_to` INT(11) NOT NULL,
    `kd_gudang_to` VARCHAR(20) DEFAULT NULL,
    `nm_gudang_to` VARCHAR(100) DEFAULT NULL COMMENT 'Snapshot nama gudang tujuan',
    `description` TEXT DEFAULT NULL,
    `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=Open, 1=Menunggu Approve, 2=Approved, 3=Rejected, 5=Cancelled, 6=Revisi',
    `reject_reason` TEXT DEFAULT NULL,
    `approved_by` VARCHAR(100) DEFAULT NULL,
    `approved_date` DATETIME DEFAULT NULL,
    `create_by` VARCHAR(100) DEFAULT NULL,
    `create_date` DATETIME DEFAULT NULL,
    `update_by` VARCHAR(100) DEFAULT NULL,
    `update_date` DATETIME DEFAULT NULL,
    `is_delete` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_mutation_number` (`mutation_number`),
    KEY `idx_status` (`status`),
    KEY `idx_gudang_from` (`id_gudang_from`),
    KEY `idx_gudang_to` (`id_gudang_to`),
    KEY `idx_is_delete` (`is_delete`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Tabel Detail Mutasi (per material group)
CREATE TABLE IF NOT EXISTS `material_mutation_details` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_material_mutation` INT(11) UNSIGNED NOT NULL,
    `id_warehouse_stock` INT(11) DEFAULT NULL,
    `id_material` VARCHAR(50) DEFAULT NULL,
    `nm_material` VARCHAR(255) DEFAULT NULL,
    `trade_name` VARCHAR(255) DEFAULT NULL,
    `code_lv4` VARCHAR(50) DEFAULT NULL,
    `id_unit` INT(11) DEFAULT NULL,
    `qty` DECIMAL(15,4) DEFAULT 0,
    `harga_beli` DECIMAL(18,2) DEFAULT 0,
    `total_nilai_mutasi` DECIMAL(18,2) DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_mutation_id` (`id_material_mutation`),
    CONSTRAINT `fk_detail_mutation` FOREIGN KEY (`id_material_mutation`) 
        REFERENCES `material_mutations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Tabel Detail Coil per Material
CREATE TABLE IF NOT EXISTS `material_mutation_details_coil` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_mutation_detail` INT(11) UNSIGNED NOT NULL,
    `id_warehouse_stock_coil` INT(11) DEFAULT NULL,
    `no_coil` VARCHAR(100) DEFAULT NULL,
    `no_ipp` VARCHAR(100) DEFAULT NULL,
    `no_po` VARCHAR(100) DEFAULT NULL,
    `no_ros` VARCHAR(100) DEFAULT NULL,
    `kode_internal` VARCHAR(100) DEFAULT NULL,
    `gross_weight` DECIMAL(15,4) DEFAULT 0,
    `net_weight` DECIMAL(15,4) DEFAULT 0,
    `length` DECIMAL(15,4) DEFAULT 0,
    `harga_beli` DECIMAL(18,2) DEFAULT 0,
    `total_nilai_mutasi` DECIMAL(18,2) DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_detail_id` (`id_mutation_detail`),
    CONSTRAINT `fk_coil_detail` FOREIGN KEY (`id_mutation_detail`) 
        REFERENCES `material_mutation_details` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- PERMISSIONS untuk modul Approval Mutasi
-- =====================================================
-- Jalankan query ini untuk menambahkan permission ke sistem
-- Sesuaikan id_permission dengan auto increment yang tersedia

INSERT INTO `permissions` (`nm_permission`, `description`) VALUES
('Approval_mutasi.View', 'Melihat daftar approval mutasi'),
('Approval_mutasi.Manage', 'Melakukan approve/reject/revisi mutasi');

-- =====================================================
-- NOTE: 
-- Untuk tabel warehouse_history, pastikan sudah ada.
-- Jika belum, berikut struktur minimal yang dibutuhkan:
-- =====================================================
-- CREATE TABLE IF NOT EXISTS `warehouse_history` (
--     `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
--     `id_material` VARCHAR(50) DEFAULT NULL,
--     `nm_material` VARCHAR(255) DEFAULT NULL,
--     `no_coil` VARCHAR(100) DEFAULT NULL,
--     `nm_category` VARCHAR(50) DEFAULT NULL,
--     `id_gudang` INT(11) DEFAULT NULL,
--     `kd_gudang` VARCHAR(20) DEFAULT NULL,
--     `id_gudang_dari` INT(11) DEFAULT NULL,
--     `kd_gudang_dari` VARCHAR(20) DEFAULT NULL,
--     `id_gudang_ke` INT(11) DEFAULT NULL,
--     `kd_gudang_ke` VARCHAR(20) DEFAULT NULL,
--     `no_ipp` VARCHAR(100) DEFAULT NULL,
--     `jumlah_mat` DECIMAL(15,4) DEFAULT 0,
--     `ket` TEXT DEFAULT NULL,
--     `harga_beli` DECIMAL(18,2) DEFAULT 0,
--     `total_harga` DECIMAL(18,2) DEFAULT 0,
--     PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
