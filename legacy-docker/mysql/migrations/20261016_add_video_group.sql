-- 观研-小视频：新增「分组」能力（幂等，可重复执行）。
-- 变更：新增 lt_video_group 分组表；lt_video 增加 group_id 字段；
--       初始化三个分组（行业会议 / 秒懂趋势 / 权威发布）。
-- 注意：MySQL 5.x 无 ADD COLUMN IF NOT EXISTS，group_id 已存在时会报
--       Duplicate column name 'group_id'，可忽略后继续（部署脚本已忽略该错误）。

CREATE TABLE IF NOT EXISTS `lt_video_group` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '分组名称',
  `isrecommand` tinyint(1) UNSIGNED DEFAULT '1' COMMENT '是否显示 1显示 0隐藏',
  `displayorder` int(10) UNSIGNED DEFAULT '0' COMMENT '排序，越大越靠前',
  `aid` int(10) UNSIGNED DEFAULT '0' COMMENT '录入人',
  `addtime` int(10) UNSIGNED DEFAULT '0' COMMENT '录入时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='观研-小视频分组';

-- lt_video 增加所属分组字段
ALTER TABLE `lt_video` ADD COLUMN `group_id` int(10) UNSIGNED DEFAULT '0' COMMENT '所属分组id（0=未分组）' AFTER `description`;

-- 初始化三个分组（固定 id + INSERT IGNORE，重复执行不产生脏数据）
INSERT IGNORE INTO `lt_video_group` (`id`,`name`,`isrecommand`,`displayorder`,`aid`,`addtime`) VALUES
(1,'行业会议',1,3,0,0),
(2,'秒懂趋势',1,2,0,0),
(3,'权威发布',1,1,0,0);
