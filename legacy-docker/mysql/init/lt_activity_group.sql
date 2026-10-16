-- 观研-活动 分组表 + lt_news 活动分组字段
-- 使用方式：在 MySQL 中执行本文件（线上环境亦可）
CREATE TABLE IF NOT EXISTS `lt_activity_group` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '分组名称',
  `isrecommand` tinyint(1) UNSIGNED DEFAULT '1' COMMENT '是否显示 1显示 0隐藏',
  `displayorder` int(10) UNSIGNED DEFAULT '0' COMMENT '排序，越大越靠前',
  `aid` int(10) UNSIGNED DEFAULT '0' COMMENT '录入人',
  `addtime` int(10) UNSIGNED DEFAULT '0' COMMENT '录入时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='观研-活动分组';

-- 初始化三个分组（展示顺序：行业会议 → 权威发布）
INSERT IGNORE INTO `lt_activity_group` (`id`,`name`,`isrecommand`,`displayorder`,`aid`,`addtime`) VALUES
(1,'行业会议',1,3,0,0),
(2,'秒懂趋势',1,2,0,0),
(3,'权威发布',1,1,0,0);

-- lt_news 增加活动分组字段（已存在时报错可忽略）
ALTER TABLE `lt_news` ADD COLUMN `group_id` int(10) UNSIGNED DEFAULT '0' COMMENT '活动分组id（0=未分组，仅活动type=1使用）' AFTER `read_page_limit`;
