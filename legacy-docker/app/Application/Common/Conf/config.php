<?php
return array(
	'DB_TYPE' => 'mysql', // 数据库类型
    'DB_HOST' => 'legacy-mysql', // 服务器地址
    'DB_NAME' => 'luotu', // 数据库名
    'DB_USER' => 'luotu', // 用户名
    'DB_PWD' => 'luotu123', // 密码
    'DB_PORT' => '3306', // 端口
    'DB_PREFIX' => 'lt_', // 数据库表前缀

	'MODULE_ALLOW_LIST'    =>    array('System'),
	'DEFAULT_MODULE'       =>    'System',  // 默认模块
	'TMPL_FILE_DEPR' => '_', //模板文件CONTROLLER_NAME与ACTION_NAME之间的分割符
	'URL_MODEL'             =>  2,
	'TMPL_ACTION_ERROR' => APP_PATH . 'Common/dispatch_jump.html',
    'TMPL_ACTION_SUCCESS' => APP_PATH . 'Common/dispatch_jump.html',
	
	'SITEURL' => 'https://api.runtotech.com',
	'SITENAME' => '洛图科技后台管理系统',
	
	'Lt_datascate1' =>array('1'=>'智能硬件IOT','2'=>'商用显示PID','3'=>'显示供应链DSC'),
	'Lt_datascate2' =>array(
		'1'=>array('1'=>'智能投影','2'=>'智能音箱','14'=>'智能平板','3'=>'智能门锁','13'=>'摄像头','15'=>'AR设备','19'=>'VR设备'/* ,'4'=>'智能盒子' */,'16'=>'回音壁','20'=>'无线蓝牙音箱'),
		'2'=>array('5'=>'交互平板','6'=>'数字标牌','7'=>'商用激光投影'/* ,'8'=>'商用电视' */,'18'=>'小间距LED'),
		'3'=>array('9'=>'电视供应链','17'=>'显示器供应链','21'=>'笔记本电脑供应链','11'=>'商用显示供应链','12'=>'电子纸供应链','10'=>'手机供应链'),
	),
	'Lt_datascate3' =>array(
		'9'=>array('2'=>'TV代工出货','3'=>'TV面板出货','11'=>'TV整机出口','6'=>'TV品牌中国出货','1'=>'TV品牌全球出货'),
		'11'=>array('9'=>'交互平板面板'),
		'12'=>array('4'=>'模组出货'/* ,'5'=>'平板线上零售' */),
		'17'=>array('7'=>'显示器出货','8'=>'显示器零售','10'=>'显示器出口'),
		'21'=>array('12'=>'笔记本电脑零售'),
	),
	'Lt_lm' =>array('1'=>'数据管理','2'=>'新闻管理','3'=>'用户管理','4'=>'后台账号','7'=>'Banner管理','5'=>'操作记录','6'=>'系统设置'),
	'Lt_usertype' =>array('1'=>'后台账号','2'=>'用户账号'),
	'Lt_newstype' =>array('1'=>'活动','2'=>'热点','3'=>'月报','4'=>'季报','5'=>'年报'),
	'Lt_newstag' =>array('2'=>'new','1'=>'hot'),
	'Lt_bannerwz' =>array('1'=>'首页','2'=>'洞察页面'),
);