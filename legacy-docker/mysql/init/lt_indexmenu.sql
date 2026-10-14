-- 首页「市场数据」菜单表
CREATE TABLE IF NOT EXISTS `lt_indexmenu` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `type` int(10) UNSIGNED DEFAULT '0' COMMENT '跳转数据type（该菜单默认品类，仅作兜底）',
  `pic` varchar(255) DEFAULT '' COMMENT '图标图片',
  `isrecommand` tinyint(1) UNSIGNED DEFAULT '1' COMMENT '是否显示 1显示 0隐藏',
  `displayorder` int(10) UNSIGNED DEFAULT '0' COMMENT '排序，越大越靠前',
  `aid` int(10) UNSIGNED DEFAULT '0',
  `addtime` int(10) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='首页市场数据菜单';

-- 首页菜单绑定的品类（一个菜单可绑定多个品类，type 取自 config 的 Lt_datascate2）
CREATE TABLE IF NOT EXISTS `lt_categroup` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `menu_id` int(10) UNSIGNED DEFAULT '0' COMMENT '菜单id',
  `type` int(10) UNSIGNED DEFAULT '0' COMMENT '品类type',
  `addtime` int(10) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='首页菜单绑定品类';

-- 初始化：6 个品类组（展示顺序：家电家居 → 上游供应链）
INSERT INTO `lt_indexmenu` (`id`,`name`,`type`,`pic`,`isrecommand`,`displayorder`,`aid`,`addtime`) VALUES
(1,'家电家居',3,'',1,6,0,0),
(2,'教育办公',14,'',1,5,0,0),
(3,'智能数码',1,'',1,4,0,0),
(4,'运动户外',0,'',1,3,0,0),
(5,'商用显示',5,'',1,2,0,0),
(6,'上游供应链',9,'',1,1,0,0);

-- 各菜单绑定的品类（仅绑定后台已存在的品类，type 见 config 的 Lt_datascate2）
INSERT INTO `lt_categroup` (`menu_id`,`type`,`addtime`) VALUES
-- 家电家居：智能门锁(3)、回音壁(16)
(1,3,0),(1,16,0),
-- 教育办公：智能平板(14)
(2,14,0),
-- 智能数码：智能投影(1)、智能音箱(2)、摄像头(13)、AR设备(15)、VR设备(19)、无线蓝牙音箱(20)
(3,1,0),(3,2,0),(3,13,0),(3,15,0),(3,19,0),(3,20,0),
-- 运动户外：清单中的品类后台暂不存在，暂不绑定
-- 商用显示：交互平板(5)、商用激光投影(7)、小间距LED(18)
(5,5,0),(5,7,0),(5,18,0),
-- 上游供应链：电视(9)、手机(10)、商用显示(11)、电子纸(12)、显示器(17)、笔记本电脑(21)供应链
(6,9,0),(6,10,0),(6,11,0),(6,12,0),(6,17,0),(6,21,0);
