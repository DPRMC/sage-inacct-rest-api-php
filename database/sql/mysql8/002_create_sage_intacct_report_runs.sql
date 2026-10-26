CREATE TABLE IF NOT EXISTS `sage_intacct_report_runs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `report_identifier` VARCHAR(255) NOT NULL,
    `report_name` VARCHAR(255) NULL,
    `status` VARCHAR(32) NOT NULL,
    `requested_at` DATETIME NOT NULL,
    `completed_at` DATETIME NULL,
    `request_checksum` CHAR(64) NOT NULL,
    `response_checksum` CHAR(64) NULL,
    `request_payload` JSON NOT NULL,
    `parser_version` VARCHAR(100) NOT NULL,
    `error_message` TEXT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `sage_intacct_report_runs_report_requested_index` (`report_identifier`, `requested_at`),
    KEY `sage_intacct_report_runs_status_requested_index` (`status`, `requested_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
