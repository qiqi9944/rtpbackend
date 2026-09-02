-- 观研-小视频 数据表
-- 使用方式：在 MySQL 中执行本文件（线上环境亦可）
CREATE TABLE IF NOT EXISTS `lt_video` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL COMMENT '视频标题',
  `url` varchar(255) DEFAULT '' COMMENT '视频播放地址',
  `pic` varchar(100) DEFAULT NULL COMMENT '封面图',
  `description` text COMMENT '视频描述（可多行）',
  `isrecommand` tinyint(1) UNSIGNED DEFAULT '0' COMMENT '是否显示 1是 0否',
  `displayorder` int(10) UNSIGNED DEFAULT '0' COMMENT '推荐度/排序',
  `aid` int(10) UNSIGNED DEFAULT '0' COMMENT '录入人',
  `addtime` int(10) UNSIGNED DEFAULT '0' COMMENT '录入时间',
  `click` int(10) UNSIGNED DEFAULT '0' COMMENT '播放次数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;