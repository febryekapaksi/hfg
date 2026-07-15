-- ============================================================
-- Production Planning Module - SPK Plan Tables Migration
-- Creates tr_spk_plan_header and tr_spk_plan_detail tables
-- for the new BOM-based SPK planning flow
-- ============================================================

CREATE TABLE IF NOT EXISTS tr_spk_plan_header (
    spk_no      VARCHAR(30) PRIMARY KEY,
    tgl_spk     DATE NOT NULL,
    shift_ids   VARCHAR(200) NOT NULL COMMENT 'Comma-separated shift IDs',
    shift_names VARCHAR(500) NOT NULL COMMENT 'Comma-separated shift names',
    catatan     TEXT,
    status      ENUM('Material Requested','Material Confirmed','Released','Cancelled')
                DEFAULT 'Material Requested',
    created_by  INT NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by  INT,
    updated_at  DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_tgl_spk (tgl_spk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tr_spk_plan_detail (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    spk_no          VARCHAR(30) NOT NULL,
    urut            INT NOT NULL DEFAULT 1,
    id_produk_fg    VARCHAR(50) NOT NULL,
    nm_produk_fg    VARCHAR(200),
    target_qty      INT NOT NULL DEFAULT 0,
    berat_per_unit  DECIMAL(10,4) DEFAULT 0,
    total_weight    DECIMAL(12,2) DEFAULT 0,
    FOREIGN KEY (spk_no) REFERENCES tr_spk_plan_header(spk_no) ON DELETE CASCADE,
    INDEX idx_spk_no (spk_no),
    INDEX idx_produk (id_produk_fg)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
