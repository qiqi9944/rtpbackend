-- Run this once against an existing database before deploying the backend code.
ALTER TABLE `lt_news`
  ADD COLUMN `read_page_limit` int(10) UNSIGNED NOT NULL DEFAULT '0'
  COMMENT '报告可阅读页数，0表示不限'
  AFTER `content`;
