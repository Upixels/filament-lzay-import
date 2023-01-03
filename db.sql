CREATE TABLE `excel_import_logs` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`file_type` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'To identify each file type uniquely.',
	`file_path` VARCHAR(500) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`total_no_of_records` INT(10) UNSIGNED NULL DEFAULT NULL,
	`no_of_records_passed` INT(10) UNSIGNED NULL DEFAULT NULL,
	`no_of_records_failed` INT(10) UNSIGNED NULL DEFAULT NULL,
	`empty_records` INT(10) UNSIGNED NULL DEFAULT NULL,
	`output_file_path` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`status` TINYINT(3) UNSIGNED NOT NULL,
	`created_by` BIGINT(20) UNSIGNED NOT NULL,
	`created_at` DATETIME NOT NULL,
	`updated_by` BIGINT(20) UNSIGNED NOT NULL,
	`updated_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`) USING BTREE
)
COLLATE='utf8mb4_0900_ai_ci'
ENGINE=InnoDB
;
