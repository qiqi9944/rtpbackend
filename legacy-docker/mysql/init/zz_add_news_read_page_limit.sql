-- Add the report page limit when the database is initialized from scratch.
SET @add_read_page_limit = (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.columns
      WHERE table_schema = DATABASE()
        AND table_name = 'lt_news'
        AND column_name = 'read_page_limit'
    ),
    'SELECT 1',
    'ALTER TABLE `lt_news` ADD COLUMN `read_page_limit` int(10) UNSIGNED NOT NULL DEFAULT ''0'' COMMENT ''报告可阅读页数，0表示不限'' AFTER `content`'
  )
);
PREPARE add_read_page_limit_stmt FROM @add_read_page_limit;
EXECUTE add_read_page_limit_stmt;
DEALLOCATE PREPARE add_read_page_limit_stmt;
