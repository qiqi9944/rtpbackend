-- 首页「市场数据」菜单表
CREATE TABLE IF NOT EXISTS `lt_indexmenu` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `type` int(10) UNSIGNED DEFAULT '0' COMMENT '跳转数据type',
  `pic` varchar(255) DEFAULT '' COMMENT '图标图片',
  `isrecommand` tinyint(1) UNSIGNED DEFAULT '1' COMMENT '是否显示 1显示 0隐藏',
  `displayorder` int(10) UNSIGNED DEFAULT '0' COMMENT '排序，越大越靠前',
  `aid` int(10) UNSIGNED DEFAULT '0',
  `addtime` int(10) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='首页市场数据菜单';

-- 初始化，与当前写死的9项一致
INSERT INTO `lt_indexmenu` (`id`,`name`,`type`,`pic`,`isrecommand`,`displayorder`,`aid`,`addtime`) VALUES
(1,'影音娱乐',1,'',1,9,0,0),
(2,'电子教育',14,'',1,8,0,0),
(3,'商务办公',2,'',1,7,0,0),
(4,'智能穿戴',3,'',1,6,0,0),
(5,'安防监控',5,'',1,5,0,0),
(6,'运动户外',6,'',1,4,0,0),
(7,'商用显示',7,'',1,3,0,0),
(8,'品质生活',18,'',1,2,0,0),
(9,'核心器件',9,'',1,1,0,0);
