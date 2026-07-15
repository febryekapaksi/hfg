-- ============================================================
-- SPK Material Module - Database Migration
-- Creates tables for SPK Material and Warehouse Request
-- ============================================================

DROP TABLE IF EXISTS tr_warehouse_request_detail;
DROP TABLE IF EXISTS tr_warehouse_request_header;
DROP TABLE IF EXISTS tr_spk_material_detail;
DROP TABLE IF EXISTS tr_spk_material_header;

CREATE TABLE tr_spk_material_header (
    spk_no          VARCHAR(30) PRIMARY KEY,
    tgl_spk         DATE NOT NULL,
    due_date        DATE DEFAULT NULL COMMENT 'Tanggal batas penyelesaian SPK',
    shift_ids       VARCHAR(200) NOT NULL COMMENT 'Comma-separated shift IDs',
    shift_names     VARCHAR(500) NOT NULL COMMENT 'Comma-separated shift names',
    catatan         TEXT,
    status          ENUM('Material Requested','Material Confirmed','Released','Cancelled')
                    DEFAULT 'Material Requested',
    created_by      INT NOT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by      INT,
    updated_at      DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_tgl_spk (tgl_spk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE tr_spk_material_detail (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    spk_no          VARCHAR(30) NOT NULL,
    urut            INT NOT NULL DEFAULT 1,
    id_produk_fg    VARCHAR(50) NOT NULL,
    nm_produk_fg    VARCHAR(200),
    target_qty      INT NOT NULL DEFAULT 0,
    berat_per_unit  DECIMAL(10,4) DEFAULT 0,
    total_weight    DECIMAL(12,2) DEFAULT 0,
    FOREIGN KEY (spk_no) REFERENCES tr_spk_material_header(spk_no) ON DELETE CASCADE,
    INDEX idx_spk_no (spk_no),
    INDEX idx_produk (id_produk_fg)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE tr_warehouse_request_header (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    spk_no          VARCHAR(30) NOT NULL,
    request_date    DATETIME DEFAULT CURRENT_TIMESTAMP,
    status          ENUM('Pending','Confirmed','Rejected') DEFAULT 'Pending',
    confirmed_by    INT,
    confirmed_at    DATETIME,
    created_by      INT NOT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (spk_no) REFERENCES tr_spk_material_header(spk_no) ON DELETE CASCADE,
    INDEX idx_spk_no (spk_no),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE tr_warehouse_request_detail (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    request_id      INT NOT NULL,
    id_produk_fg    VARCHAR(50) NOT NULL,
    nm_produk_fg    VARCHAR(200),
    id_material     VARCHAR(50) NOT NULL,
    nm_material     VARCHAR(200),
    qty_needed      DECIMAL(12,4) NOT NULL DEFAULT 0 COMMENT 'BOM qty x target_qty',
    id_unit         VARCHAR(20),
    nm_unit         VARCHAR(50),
    FOREIGN KEY (request_id) REFERENCES tr_warehouse_request_header(id) ON DELETE CASCADE,
    INDEX idx_request_id (request_id),
    INDEX idx_material (id_material)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
