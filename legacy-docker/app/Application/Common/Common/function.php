<?php

function dhtmlspecialchars($string) {
    if(is_array($string)) {
        foreach($string as $key => $val) {
            $string[$key] = dhtmlspecialchars($val);
        }
    } else {
        $string = str_replace(array('&', '"', '<', '>', "'"), array('&amp;', '&quot;', '&lt;', '&gt;',''), $string);
        if(strpos($string, '&amp;#') !== false) {
            $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
        }
    }
    return $string;
}

//无空格
function ab_replace_content($str){
	$str = htmlspecialchars_decode($str);
	$find = array('&amp;','&quot;','&#039;','&lt;','&gt;',"\\\"","\'","&nbsp;","　");
	$replace = array('&','"',"'",'<','>','"',"'","","");
	return str_replace($find,$replace,$str);
}
//有空格
function hd_replace_content($str){
	$str = htmlspecialchars_decode($str);
	$find = array('&amp;','&quot;','&#039;','&lt;','&gt;',"\\\"","\'");
	$replace = array('&','"',"'",'<','>','"',"'","");
	return str_replace($find,$replace,$str);
}

//密码校验函数
/**
 * @param $pwd
 * @return int
 */
function checkpwd($pwd){
    $r1='/[a-zA-Z]+/';  //lowercase
    $r2='/[0-9]+/';  //numbers
    $r3='/[~!#\$%\^&\*\(\)\{\}\[\]\'";\:\.,\?\/\+]+/';  // special if
    if(preg_match($r1,$pwd) && preg_match($r2,$pwd)){
        return true;
    }
    if(preg_match($r1,$pwd) && preg_match($r3,$pwd)){
        return true;
    }
    if(preg_match($r2,$pwd)&& preg_match($r3,$pwd)){
        return true;
    }
    return false;
}

function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){

    if(function_exists("mb_substr")){

        if(strlen($str)>$length && $suffix){

            return mb_substr($str, $start, $length, $charset)."...";

        }else{

            return mb_substr($str, $start, $length, $charset);

        }

    }elseif(function_exists('iconv_substr')) {

        if(strlen($str)>$length && $suffix){

            return iconv_substr($str,$start,$length,$charset)."...";

        }else{

            return iconv_substr($str,$start,$length,$charset);

        }

    }

    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";

    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";

    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";

    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";

    preg_match_all($re[$charset], $str, $match);

    $slice = join("",array_slice($match[0], $start, $length));

    if(strlen($str)>$length && $suffix){

        return $slice."...";

    }else{

        return $slice;

    }
}
//去除html标签
function deletehtml($str){
    $find = array('/<[^>]*>|<\/[^>]*>/');
    /*$find = array('/<[^>]*>/');*/
    $replace = array('');
    return preg_replace($find,$replace,$str);
}
function trimall($str)//删除空格
{
    $qian=array(" ","　","&nbsp;","\t","\n","\r");
    $hou=array("","","","","","","");
    return str_replace($qian,$hou,$str);
}
//判断客户端设备
function is_mobile(){
    $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
    $mobile_browser = '0';
    if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
        $mobile_browser++;
    if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))
        $mobile_browser++;
    if(isset($_SERVER['HTTP_X_WAP_PROFILE']))
        $mobile_browser++;
    if(isset($_SERVER['HTTP_PROFILE']))
        $mobile_browser++;
    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
    $mobile_agents = array(
        'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
        'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
        'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
        'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
        'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
        'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
        'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
        'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
        'wapr','webc','winw','winw','xda','xda-'
    );
    if(in_array($mobile_ua, $mobile_agents))
        $mobile_browser++;
    if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)
        $mobile_browser++;
    // Pre-final check to reset everything if the user is on Windows
    if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)
        $mobile_browser=0;
    // But WP7 is also Windows, with a slightly different characteristic
    if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)
        $mobile_browser++;
    if($mobile_browser>0)
        return true;
    else
        return false;
}
function checkmobile() {
    $mobile = array();
    static $touchbrowser_list =array('iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi', 'opera mini',
        'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony', 'blackberry', 'dopod', 'nokia', 'samsung',
        'palmsource', 'xda', 'pieplus', 'meizu', 'midp', 'cldc', 'motorola', 'foma', 'docomo', 'up.browser',
        'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad', 'webos', 'techfaith', 'palmsource',
        'alcatel', 'amoi', 'ktouch', 'nexian', 'ericsson', 'philips', 'sagem', 'wellcom', 'bunjalloo', 'maui', 'smartphone',
        'iemobile', 'spice', 'bird', 'zte-', 'longcos', 'pantech', 'gionee', 'portalmmm', 'jig browser', 'hiptop',
        'benq', 'haier', '^lct', '320x320', '240x320', '176x220', 'windows phone');
    static $wmlbrowser_list = array('cect', 'compal', 'ctl', 'lg', 'nec', 'tcl', 'alcatel', 'ericsson', 'bird', 'daxian', 'dbtel', 'eastcom',
        'pantech', 'dopod', 'philips', 'haier', 'konka', 'kejian', 'lenovo', 'benq', 'mot', 'soutec', 'nokia', 'sagem', 'sgh',
        'sed', 'capitel', 'panasonic', 'sonyericsson', 'sharp', 'amoi', 'panda', 'zte');

    static $pad_list = array('ipad');

    $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);

    if(dstrpos($useragent, $pad_list)) {
        return false;
    }
    if(($v = dstrpos($useragent, $touchbrowser_list, true))){
        $is_mobile = $v;
        return true;
    }
    if(($v = dstrpos($useragent, $wmlbrowser_list))) {
        $is_mobile = $v;
        return true; //wml版
    }
    $brower = array('mozilla', 'chrome', 'safari', 'opera', 'm3gate', 'winwap', 'openwave', 'myop');
    if(dstrpos($useragent, $brower)) return false;

}
function dstrpos($string, $arr, $returnvalue = false) {
    if(empty($string)) return false;
    foreach((array)$arr as $v) {
        if(strpos($string, $v) !== false) {
            $return = $returnvalue ? $v : true;
            return $return;
        }
    }
    return false;
}
//微信
function url_encode($str) {
	if(is_array($str)) {
		foreach($str as $key=>$value) {
			$str[urlencode($key)] = url_encode($value);
		}
	} else {
		$str = urlencode($str);
	}

	return $str;
}
function subtext($text, $length){
   if(mb_strlen($text, 'utf8') > $length)
   return mb_substr($text, 0, $length, 'utf8').'...';
   return $text;
}
//textarea去掉换行，空格
function hd_textarea($str){
	$str = htmlspecialchars_decode($str);
	$find = array("\r\n","\n"," ");
	$replace = array("","","");
	return str_replace($find,$replace,$str);
}

//textarea格式显示
function hd_nl2br($content){
	$content = str_replace("\r\n","\n",$content);
	$content = str_replace("\n","<br/>",$content);
	$content = str_replace(" ","&nbsp;",$content);
	return $content;
}
function get_access_token($appid,$appsecret){
	$access_token_txt = file_get_contents('access_token.txt');
	$arr_access_token = explode('|||',$access_token_txt);
	if($arr_access_token[1]+7200<time()){//判断上次获取token的时间 是否大于7200
		//重新获取token
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
		$output = httpget($url);
		$jsoninfo = json_decode($output, true);
		$access_token = $jsoninfo["access_token"];
		file_put_contents('access_token.txt',$access_token.'|||'.time());
		return $access_token;
	}else{
		return $arr_access_token[0];
	}
}

function httpget($url) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, 500);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_URL, $url);

	$res = curl_exec($curl);
	curl_close($curl);

	return $res;
}

function httppost($url,$curlPost) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, 500);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, 1);//post提交方式
    curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);

	$res = curl_exec($curl);
	curl_close($curl);

	return $res;
}


//计算时间，返回小时分钟
function counttime($time1,$time2){
    $diff = abs($time2 - $time1);
	$hours = intval($diff/3600);
	$minute = intval(($diff%3600)/60);
	if($time2>$time1){
		return ($hours!=0?$hours.'小时':'').$minute.'分';
	}else{
		return '-'.($hours!=0?$hours.'小时':'').$minute.'分';
	}
}
//随机生成昵称
function GetfourStr($len){
  $chars_array = array(
    "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
    "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
    "w", "x", "y", "z"
  );
  $charsLen = count($chars_array) - 1;
  
  $outputstr = "";
  for ($i=0; $i<$len; $i++){
    $outputstr .= $chars_array[mt_rand(0, $charsLen)];
  }
  return $outputstr;
}




