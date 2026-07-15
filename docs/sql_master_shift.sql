-- =============================================
-- SQL untuk tabel master_shift
-- Database: db_hfg_dev
-- =============================================

CREATE TABLE IF NOT EXISTS `master_shift` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_shift` VARCHAR(100) NOT NULL COMMENT 'Nama shift, contoh: Shift 1, Shift 2, Shift 3',
  `keterangan` TEXT DEFAULT NULL COMMENT 'Keterangan tambahan (optional)',
  `created_by` INT(11) DEFAULT NULL,
  `created_date` DATETIME DEFAULT NULL,
  `updated_by` INT(11) DEFAULT NULL,
  `updated_date` DATETIME DEFAULT NULL,
  `deleted_by` INT(11) DEFAULT NULL,
  `deleted_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Master data shift untuk production planning';

-- =============================================
-- Sample data (optional, bisa di-skip)
-- =============================================
INSERT INTO `master_shift` (`nama_shift`, `keterangan`, `created_by`, `created_date`) VALUES
('Shift 1', 'Jam 07:00 - 15:00', 1, NOW()),
('Shift 2', 'Jam 15:00 - 23:00', 1, NOW()),
('Shift 3', 'Jam 23:00 - 07:00', 1, NOW());
