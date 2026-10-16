-- 观研-活动：新增「分组」能力（幂等，可重复执行）。
-- 变更：新增 lt_activity_group 分组表；lt_news 增加 group_id 字段（仅活动 type=1 使用）；
--       初始化三个分组（行业会议 / 秒懂趋势 / 权威发布）。
-- 注意：MySQL 5.x 无 ADD COLUMN IF NOT EXISTS，group_id 已存在时会报
--       Duplicate column name 'group_id'，可忽略后继续（部署脚本已忽略该错误）。

CREATE TABLE IF NOT EXISTS `lt_activity_group` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '分组名称',
  `isrecommand` tinyint(1) UNSIGNED DEFAULT '1' COMMENT '是否显示 1显示 0隐藏',
  `displayorder` int(10) UNSIGNED DEFAULT '0' COMMENT '排序，越大越靠前',
  `aid` int(10) UNSIGNED DEFAULT '0' COMMENT '录入人',
  `addtime` int(10) UNSIGNED DEFAULT '0' COMMENT '录入时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='观研-活动分组';

-- lt_news 增加活动分组字段
ALTER TABLE `lt_news` ADD COLUMN `group_id` int(10) UNSIGNED DEFAULT '0' COMMENT '活动分组id（0=未分组，仅活动type=1使用）' AFTER `read_page_limit`;

-- 初始化三个分组（固定 id + INSERT IGNORE，重复执行不产生脏数据）
INSERT IGNORE INTO `lt_activity_group` (`id`,`name`,`isrecommand`,`displayorder`,`aid`,`addtime`) VALUES
(1,'行业会议',1,3,0,0),
(2,'秒懂趋势',1,2,0,0),
(3,'权威发布',1,1,0,0);
