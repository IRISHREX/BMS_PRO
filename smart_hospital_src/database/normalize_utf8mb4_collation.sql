-- Run in Hostinger phpMyAdmin after taking a database backup.
-- Replace YOUR_DATABASE_NAME with the actual database name.
ALTER DATABASE `YOUR_DATABASE_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- This SELECT produces one ALTER statement for each text column that is not yet utf8mb4_unicode_ci.
-- Copy its result and execute the generated ALTER TABLE statements in phpMyAdmin.
SELECT CONCAT(
  'ALTER TABLE `', TABLE_NAME, '` MODIFY `', COLUMN_NAME, '` ', COLUMN_TYPE,
  ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
  IF(IS_NULLABLE = 'NO', ' NOT NULL', ' NULL'),
  IF(COLUMN_DEFAULT IS NULL, '', CONCAT(' DEFAULT ', QUOTE(COLUMN_DEFAULT))),
  IF(EXTRA = '', '', CONCAT(' ', EXTRA)), ';'
) AS alter_statement
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'YOUR_DATABASE_NAME'
  AND DATA_TYPE IN ('char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext')
  AND (CHARACTER_SET_NAME <> 'utf8mb4' OR COLLATION_NAME <> 'utf8mb4_unicode_ci');
