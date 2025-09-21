CREATE DATABASE IF NOT EXISTS `task_manager`;

USE `task_manager`;

CREATE TABLE IF NOT EXISTS `tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `mata_kuliah` VARCHAR(255) NOT NULL,
    `dosen` VARCHAR(255) NOT NULL,
    `tugas` VARCHAR(255) NOT NULL,
    `level_kesulitan` ENUM('low', 'medium', 'high', 'urgent') NOT NULL,
    `status` ENUM('not_done', 'still_working_on_it', 'done') NOT NULL,
    `tempat_pengumpulan` ENUM('GCR', 'google_drive', 'hardfile', 'vclass') NOT NULL,
    `notes` TEXT
);