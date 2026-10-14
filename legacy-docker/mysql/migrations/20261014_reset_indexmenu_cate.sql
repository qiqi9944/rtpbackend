-- 重建首页市场数据菜单（九宫格）及其品类绑定。
-- 用于已存在的数据库：先确保绑定表存在，再整体重置菜单与绑定（幂等，可重复执行）。
-- 变更：原 9 个菜单（影音娱乐/电子教育/商务办公/智能穿戴/安防监控/运动户外/商用显示/品质生活/核心器件）
--       → 6 个品类组（家电家居/教育办公/智能数码/运动户外/商用显示/上游供应链）；
--       仅绑定后台已存在的品类（type 见 config 的 Lt_datascate2），未新增品类。

CREATE TABLE IF NOT EXISTS `lt_categroup` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `menu_id` int(10) UNSIGNED DEFAULT '0' COMMENT '菜单id',
  `type` int(10) UNSIGNED DEFAULT '0' COMMENT '品类type',
  `addtime` int(10) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='首页菜单绑定品类';

-- 整表重置
DELETE FROM `lt_categroup`;
DELETE FROM `lt_indexmenu`;

INSERT INTO `lt_indexmenu` (`id`,`name`,`type`,`pic`,`isrecommand`,`displayorder`,`aid`,`addtime`) VALUES
(1,'家电家居',3,'',1,6,0,0),
(2,'教育办公',14,'',1,5,0,0),
(3,'智能数码',1,'',1,4,0,0),
(4,'运动户外',0,'',1,3,0,0),
(5,'商用显示',5,'',1,2,0,0),
(6,'上游供应链',9,'',1,1,0,0);

-- 各菜单绑定的品类（仅绑定后台已存在的品类）
INSERT INTO `lt_categroup` (`menu_id`,`type`,`addtime`) VALUES
(1,3,0),(1,16,0),
(2,14,0),
(3,1,0),(3,2,0),(3,13,0),(3,15,0),(3,19,0),(3,20,0),
(5,5,0),(5,7,0),(5,18,0),
(6,9,0),(6,10,0),(6,11,0),(6,12,0),(6,17,0),(6,21,0);
