-- 观研-小视频：升级为「视频号视频」字段（在已有 lt_video 表上执行）
-- 使用方式：对已有数据库执行本文件。
--   若报 “Duplicate column name 'xxx'” 说明该列已存在，可忽略后继续。
ALTER TABLE `lt_video` ADD COLUMN `finder_user_name` varchar(100) DEFAULT '' COMMENT '视频号ID（sph开头）' AFTER `url`;
ALTER TABLE `lt_video` ADD COLUMN `feed_id` varchar(100) DEFAULT '' COMMENT '视频号视频feedId（与小程序同主体时使用）' AFTER `finder_user_name`;
ALTER TABLE `lt_video` ADD COLUMN `feed_token` varchar(255) DEFAULT '' COMMENT '视频号视频feedToken（与小程序非同主体时使用）' AFTER `feed_id`;
