-- 观研-小视频 数据表
-- 使用方式：在 MySQL 中执行本文件（线上环境亦可）
CREATE TABLE IF NOT EXISTS `lt_video` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL COMMENT '视频标题',
  `url` varchar(255) DEFAULT '' COMMENT '视频播放地址（普通URL，兼容旧数据）',
  `finder_user_name` varchar(100) DEFAULT '' COMMENT '视频号ID（sph开头）',
  `feed_id` varchar(100) DEFAULT '' COMMENT '视频号视频feedId（与小程序同主体时使用）',
  `feed_token` varchar(255) DEFAULT '' COMMENT '视频号视频feedToken（与小程序非同主体时使用）',
  `pic` varchar(100) DEFAULT NULL COMMENT '封面图',
  `description` text COMMENT '视频描述（可多行）',
  `isrecommand` tinyint(1) UNSIGNED DEFAULT '0' COMMENT '是否显示 1是 0否',
  `displayorder` int(10) UNSIGNED DEFAULT '0' COMMENT '推荐度/排序',
  `aid` int(10) UNSIGNED DEFAULT '0' COMMENT '录入人',
  `addtime` int(10) UNSIGNED DEFAULT '0' COMMENT '录入时间',
  `click` int(10) UNSIGNED DEFAULT '0' COMMENT '播放次数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
