CREATE TABLE IF NOT EXISTS `sage_intacct_report_records` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `run_id` BIGINT UNSIGNED NOT NULL,
    `report_identifier` VARCHAR(255) NOT NULL,
    `source_key` VARCHAR(255) NOT NULL,
    `source_checksum` CHAR(64) NOT NULL,
    `source_payload` JSON NOT NULL,
    `normalized_payload` JSON NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `sage_intacct_report_records_report_source_unique` (`report_identifier`, `source_key`),
    KEY `sage_intacct_report_records_run_checksum_index` (`run_id`, `source_checksum`),
    CONSTRAINT `sage_intacct_report_records_run_id_foreign`
        FOREIGN KEY (`run_id`) REFERENCES `sage_intacct_report_runs` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
