<?php
namespace System\Controller;
use Think\Controller;
class WxapiController extends Controller {
	public function _initialize(){
		$this->arr_datascate1=C('Lt_datascate1');
		$this->arr_datascate2=C('Lt_datascate2');
		$this->arr_datascate3=C('Lt_datascate3');
		$this->arr_lm=C('Lt_lm');
		$this->arr_usertype=C('Lt_usertype');
		$this->arr_newstype=C('Lt_newstype');
		$this->arr_bannerwz=C('Lt_bannerwz');
		
		$arr_sjtype=array();
		foreach($this->arr_datascate2 as $k=>$v){
			foreach($v as $lk=>$lv){
				$arr_sjtype[$lk]=$lv;
			}
		}
		$this->arr_sjtype=$arr_sjtype;
		
		/*//小程序显示数据月份和年份，查看前两个月的数据
		//两个月之前的时间戳
		$this->nowtime=strtotime('-2 month');
		//当前季度的前两个季度，六个月前的季度
		$this->nownd = date('Y',strtotime('-6 month'));
		$this->nowjd = ceil(date('n',strtotime('-6 month')) / 3);*/
	}
	//登录
	public function dowxlogin(){
		$return_data = array();
		$appid = 'wx4d4ce62d4d883da5';
		$secret = 'f689586f43b54eeeb12a4d453110df14';
		$js_code = I('code','',dhtmlspecialchars);
		/*$username = I('nickName','',dhtmlspecialchars);
		$tx = I('avatarUrl','',dhtmlspecialchars);*/
		$phone = trim(I('phone','',dhtmlspecialchars));
		if(stripos($phone,'no_phone')!==false){
			$phone='';
		}
		if($js_code){
			$weixin_json = file_get_contents("https://api.weixin.qq.com/sns/jscode2session?appid=".$appid."&secret=".$secret."&js_code=".$js_code."&grant_type=authorization_code");
			$weixin_json = json_decode($weixin_json);
			$openid=$weixin_json->openid;
			if($openid){
				$hduser = M('user')->where("type=2 AND openid='".$openid."'")->find();
				if(empty($hduser)){
					$arr=array();
					$arr['type']=2;
					$arr['openid']=$openid;
					//$arr['username']=$username;
					//$arr['tx']=$tx;
					$arr['phone']=$phone;
					$arr['username']='游客'.GetfourStr(4);
					$arr['name']=$arr['username'];
					$arr['status']=1;
					$arr['addtime']=time();
					$id=M('user')->add($arr);
					$hduser = M('user')->where("id='$id'")->find();
				}
				if(empty($hduser['username'])){
					$hduser['username']='游客'.$hduser['id'];
					M('user')->where("id=".$hduser['id'])->save(array('username'=>$hduser['username']));
				}
				if(empty($hduser['name'])){
					$hduser['name']=$hduser['username'];
				}
				if($hduser['status']!=1){
					$return_data['status'] = 0;//失败
					$return_data['msg'] = '该账号已被禁用，不能登录！';
					echo json_encode($return_data);
					exit;
				}else{
					//登录记录
					$harr=array();
					$harr['type']=0;//登录记录
					$harr['aid']=$hduser['id'];
					$harr['addtime']=time();
					$harr['ip']=$_SERVER["REMOTE_ADDR"];
					M('history')->add($harr);
					
					$return_data['status'] = 1;//成功
					$return_data['userdata'] = $hduser;
					echo json_encode($return_data);
					exit;
				}
			}else{
				$return_data['status'] = 0;//失败
				$return_data['msg'] = '参数错误！';
				echo json_encode($return_data);
				exit;
			}
		}else{
			$return_data['status'] = 0;//失败
			$return_data['msg'] = '参数错误！';
			echo json_encode($return_data);
			exit;
		}
		
	}
	//获取用户信息
	public function getuser(){
		$return_data = array();
		$id=I('id','',intval);
		$data=M('user')->where("id='$id' AND type=2")->find();
		if(!$data){
			$return_data['status'] = 0;//失败
			$return_data['msg'] = '该账号不存在！';
			echo json_encode($return_data);
			exit;
		}
		if($data['status']!=1){
			$return_data['status'] = 0;//失败
			$return_data['msg'] = '该账号已被禁用！';
			echo json_encode($return_data);
			exit;
		}
		$return_data['status'] = 1;//成功
		//是否完善信息
		if($data['phone'] && $data['companyname']){
			$data['is_xx']=1;
		}else{
			$data['is_xx']=0;
		}
		$return_data['userdata'] = $data;
		echo json_encode($return_data);
		exit;
	}
	
	//更新用户信息
	public function updateuser(){
		$return_data = array();
		$arr = array();
		$id = I('id','',intval);
		$arr['username'] = I('username','',dhtmlspecialchars);
		$arr['tx'] = I('tx','',dhtmlspecialchars);
		$arr['companyname'] = I('companyname','',dhtmlspecialchars);
		if(!$arr['companyname']){
			$return_data['status'] = 0;
			$return_data['msg'] = '请输入公司名称！';
			echo json_encode($return_data);
			exit;
		}
		$arr['name'] = I('name','',dhtmlspecialchars);
		/*if(!$arr['name']){
			$return_data['status'] = 0;
			$return_data['msg'] = '请输入姓名！';
			echo json_encode($return_data);
			exit;
		}*/
		$arr['phone'] = I('phone','',dhtmlspecialchars);
		if(!$arr['phone']){
			$return_data['status'] = 0;
			$return_data['msg'] = '请输入手机号！';
			echo json_encode($return_data);
			exit;
		}else{
			/* if(!preg_match("/^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(17[0-9]{1})|(18[0-9]{1})|(19[0-9]{1}))+\d{8})$/",$arr['phone'])){
				$return_data['status'] = 0;
				$return_data['msg'] = '请输入正确的手机号码！';
				echo json_encode($return_data);
				exit;
			} */
		}
		$arr['zhiwu'] = I('zhiwu','',dhtmlspecialchars);
		$arr['email'] = I('email','',dhtmlspecialchars);
		if($arr['email']){
			if(!preg_match("/^([\s\S]+?)@([\s\S]+)(\.(\w{1,5}))$/",$arr['email'])){
				$return_data['status'] = 0;
				$return_data['msg'] = '请输入正确的邮箱！';
				echo json_encode($return_data);
				exit;
			}
		}
		
		//判断修改的手机号是否重复
		$is_exsit=M('user')->where("type=2 AND phone='".$arr['phone']."' AND status>0 AND id!='$id'")->find();
		if($is_exsit){
			$return_data['status'] = 0;
			$return_data['msg'] = '该手机号已存在，请重新输入手机号！';
			echo json_encode($return_data);
			exit;
		}

		//查询用户是否存在
		$hduser = M('user')->where("id='".$id."' AND type=2 AND status=1")->find();
		if($hduser){
			M('user')->where("id='".$id."'")->save($arr);
			$userdata = M('user')->where("id='".$id."'")->find();
			$return_data['status'] = 1;//成功
			$return_data['msg'] = '提交成功！';
			$return_data['userdata'] = $userdata;
		}else{
			$return_data['status'] = 0;
			$return_data['msg'] = '该账号不存在或已被禁用！';
		}
		echo json_encode($return_data);
		exit;
	}
	
	//上传图片
	public function douploud(){
		$return_data = array();	
		$file = $_FILES['file'];
		$width = I('width','',intval);//宽
		$height = I('height','',intval);//高
		if($file['name']!=''){
			$return=$this->upload($width,$height);
			if($return['status']==1){
				$return_data = $return['filepath'];
			}else{
				$return_data = 0;//失败
			}
		}else{
			$return_data = 0;//失败
		}
		//排坑，小程序wx.uploadFile的返回值是字符串，不是json
		echo $return_data;
	}
	
	public function upload($w,$h){
		$return = array();
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =     8388608 ;// 设置附件上传大小
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
		$upload->savePath  ='/images/'; // 设置附件上传根目录

		// 上传文件
		$info   =   $upload->upload();
		if($info){
			if($w && $h){
				$pic="/Uploads".$info['file']['savepath'].$info['file']['savename'];
				$thumb = new \Think\Image();
				$thumb->open('.' .$pic);
				$arr_src = explode('.',$pic);
				$arr_src[0]=$arr_src[0].'_thumb';
				$thumbname = implode('.',$arr_src);
				$thumb->thumb($w, $h,\Think\Image::IMAGE_THUMB_CENTER)->save('.' . $thumbname);
				$return['filepath'] = $thumbname;
			}else{
				$return['filepath'] = "/Uploads".$info['file']['savepath'].$info['file']['savename'];
			}
			$return['status'] = 1;//成功
			return $return;
		}else{
			$return['status'] = 2;//失败
			return $return;
		}
	}
	
	//banner，首页或研报页面
	public function getbanner(){
		$wz=I('wz','',intval);
		$datalist = array();
		$datalist = M('banner')->where("isrecommand=1 AND find_in_set('$wz',weizhi)")->limit(3)->order("displayorder DESC,id DESC")->select();
		foreach($datalist as $k=>$v){
			if($v['pic']){
				$v['pic']=C('SITEURL').$v['pic'];
			}
			$datalist[$k]=$v;
		}
		$return_data=array();
		$return_data['status'] = 1;
		$return_data['datalist'] = $datalist;
		echo json_encode($return_data);
	}
	//按时间戳查询 iot 某月数据（兼容 yd 的 Y.m 与 y.m 两种格式）
	protected function qiot($type,$ts,$flt=''){
		$a=date('Y.m',$ts);$b=date('y.m',$ts);
		return M('shuju_iot')->where("type='$type' AND (yd='$a' OR yd='$b')".$flt)->select();
	}

	//图表变化率；无可比基数时返回 null，让折线在该点断开
	protected function chartRate($current,$previous){
		if(empty($previous)){return null;}
		return round((($current-$previous)/$previous)*100,1);
	}

	//shuju_tvs 表：某 type(+type2) 在指定年份的最新一期行（可能 jd 或 yd），跨年份则为该类型全局最新一期
	protected function tvsLatestOfYear($type,$year,$type2=0){
		$pre="type='$type'";
		if($type2){$pre.=" AND type2='$type2'";}
		$r=M('shuju_tvs')->where($pre." AND nd='$year'")->order('jd DESC,yd DESC,id DESC')->find();
		if(!$r){
			$r=M('shuju_tvs')->where($pre)->order('nd DESC,jd DESC,yd DESC,id DESC')->find();
		}
		return $r;
	}

	//筛选页数据：市场/品径/品类/时间/年累计 + 完整图表
	public function getfilterdata(){
		$return_data=array();
		$uid=I('uid','',intval);
		$user=M('user')->where("type=2 AND id='$uid'")->find();
		if(!$user['companyname']){
			$return_data['status']=0;
			$return_data['msg']='您暂时不能进入此页面，请先注册公司！';
			echo json_encode($return_data);
			exit;
		}
		if($user['huiyuan']==1){
			$return_data['is_ck']=1;
		}else{
			$return_data['is_ck']=0;
		}
		//筛选参数
		$type=I('type','',intval);          //品类
		$group=I('group','',intval);        //品类组（首页菜单id），优先用于明细表分组
		$market=I('market','',dhtmlspecialchars); //市场：中国/全球
		$lx=I('lx','',dhtmlspecialchars);        //品径：零售/出货
		$xl=I('xl','',intval);             //1销量 2销额
		if(!$xl){$xl=1;}
		$jd=I('jd','',intval);            //1月度 2季度
		if(!$jd){$jd=1;}
		$ytd=I('ytd','',intval);          //1年累计
		$year=I('year','',intval);        //选定年份
		$month=I('month','',intval);      //月度选定月份 1-12
		$quarter=I('quarter','',intval);  //季度选定 1-4

		//品类下拉
		$arr_type=array();
		foreach($this->arr_datascate2 as $dk=>$dv){
			foreach($dv as $k=>$v){
				$arr=array();$arr['id']=$k;$arr['name']=$v;$arr_type[]=$arr;
			}
		}
		$return_data['arr_type']=$arr_type;

		//市场/品径where：中国/零售兼容未标注的存量数据(存量口径=中国市场线上零售)
		$flt='';
		if($market && $market!='不限'){
			if($market=='中国'){
				$flt .= " AND (market='中国' OR market='')";
			}else{
				$flt .= " AND market='$market'";
			}
		}
		if($lx && $lx!='不限'){
			if($lx=='零售'){
				$flt .= " AND (lx='零售' OR lx='')";
			}else{
				$flt .= " AND lx='$lx'";
			}
		}

		//判断该品类属于月度表(shuju_iot)还是季度表(shuju_pid)或供应链表(shuju_tvs)
		$month_types=array(1,2,3,4,13,14,15,16,19);
		$quarter_types=array(5,6,7,8,18);
		$tvs_types=array(9,10,11,12,17,21);
		$src=in_array($type,$quarter_types)?'pid':'iot';
		if(in_array($type,$tvs_types)){$src='tvs';}
		if(in_array($type,$quarter_types)){$jd=2;}

		//最新可用时间（按品类，不受市场/品径筛选影响，供默认定位）
		$nowtime=time();$latest_year='';$latest_month='';$latest_quarter='';
		if(!$year){$year=date('Y',$nowtime);}
		if($src=='iot'){
			$tmp=M('shuju_iot')->where("type='$type'")->order('nd DESC,yd DESC')->find();
			if($tmp){$nowtime=strtotime($tmp['nd'].'-'.(substr($tmp['yd'],-2)).'-01');}
		}else{
			$tmp=M('shuju_pid')->where("type='$type'")->order('nd DESC,jd DESC')->find();
			if($tmp){$nowtime=strtotime($tmp['nd'].'-'.((substr($tmp['jd'],1,1)*3)-2).'-01');}
		}
		if($nowtime>0){
			$latest_year=date('Y',$nowtime);
			if($src=='iot'){$latest_month=intval(date('m',$nowtime));}
			else{$latest_quarter=intval(ceil(date('n',$nowtime)/3));}
		}

		$arr_sj1=array();
		$arr_sj2=array();
		$arr_sj3=array();
		$arr_sj6=array();
		$arr_sj2_num=0;
		$arr_sj2_dw='';

		if($src=='iot'){
			//yd 有两种格式：Y.m(2026.04) 与 y.m(26.04)，查询时都要匹配
			$mkd=function($ts){return date('Y.m',$ts);};
			$mks=function($ts){return date('y.m',$ts);};
			$ydcl=function($ts){
				$a=$mkd($ts);$b=$mks($ts);
				return "(yd='$a' OR yd='$b')";
			};
			if($ytd==1){
				//年累计
				$sum_now=0;$sum_last=0;$summall=0;
				for($i=0;$i<12;$i++){
					if($i==0){$aa=$nowtime;}else{$aa=strtotime('-'.$i.' month',$nowtime);}
					if(date('Y',$aa)!=date('Y',$nowtime)){break;}
					$ml=date('Y.m',strtotime('-1 year',$aa));
					foreach($this->qiot($type,$aa,$flt) as $r){$sum_now+=$r['xl'];$summall+=$r['xe'];}
					foreach(M('shuju_iot')->where("type='$type' AND (yd='$ml' OR yd='".date('y.m',strtotime('-1 year',$aa))."')".$flt)->select() as $r){$sum_last+=$r['xl'];}
					$arr=array();$arr['id']=$mkd($aa);$arr['num']=round(($summall/100000000),1);$arr_sj2[]=$arr;
				}
				$xl_now=$sum_now;$xe_now=$summall;$xl_last=$sum_last;
			}else{
				//月度：当前选中月
				if($month){$nowtime=strtotime($year.'-'.$month.'-01');}
				$xl_now=0;$xe_now=0;$xl_last=0;$xe_last=0;
				foreach($this->qiot($type,$nowtime,$flt) as $r){$xl_now+=$r['xl'];$xe_now+=$r['xe'];}
				foreach($this->qiot($type,strtotime('-1 year',$nowtime),$flt) as $r){$xl_last+=$r['xl'];$xe_last+=$r['xe'];}
				//市场规模近12个月 x/y
				$arr_sort=array();
				for($i=0;$i<12;$i++){
					if($i==0){$aa=$nowtime;}else{$aa=strtotime('-'.$i.' month',$nowtime);}
					$sum=0;
					foreach($this->qiot($type,$aa,$flt) as $r){$sum+=($xl==1?$r['xl']:$r['xe']);}
					$arr=array();$arr['id']=date('y/m',$aa);$arr['num']=round(($sum/($xl==1?10000:100000000)),1);$arr['i']=$i+1;
					$arr_sj2[]=$arr;$arr_sort[]=$arr['i'];
				}
				array_multisort($arr_sort,SORT_DESC,$arr_sj2);
			}
			//三项指标卡
			$pjjg=!empty($xl_now)?round(($xe_now/$xl_now),0):0;
			$pjjgl=!empty($xl_last)?round(($xe_last/$xl_last),0):0;
			$bfb=function($a,$b){return !empty($b)?(($a>=$b)?'+'.round((($a-$b)/$b)*100,0).'%':'-'.round((($b-$a)/$b)*100,0).'%'):'+0%';};
			$arr=array();$arr['t1']='销量';$arr['t2']=!empty($xl_now)?sprintf('%.1f',round(($xl_now/10000),1)):0;$arr['t3']=$bfb($xl_now,$xl_last);$arr['dw']='万台';$arr_sj1[]=$arr;
			$arr=array();$arr['t1']='销额';$arr['t2']=!empty($xe_now)?sprintf('%.1f',round(($xe_now/100000000),1)):0;$arr['t3']=$bfb($xe_now,$xe_last);$arr['dw']='亿元';$arr_sj1[]=$arr;
			$arr=array();$arr['t1']='平均价格';$arr['t2']=$pjjg;$arr['t3']=$bfb($pjjg,$pjjgl);$arr['dw']='元';$arr_sj1[]=$arr;

			//品牌份额
			$bl=array();
			if($ytd==1){
				foreach(M('shuju_iot')->where("type='$type' AND (nd='$year' OR nd='".$year."年')".$flt)->select() as $r){
					$bl[$r['pp']]=$bl[$r['pp']]+($xl==1?$r['xl']:$r['xe']);
				}
			}else{
				foreach($this->qiot($type,$nowtime,$flt) as $r){
					$bl[$r['pp']]=$bl[$r['pp']]+($xl==1?$r['xl']:$r['xe']);
				}
			}
			arsort($bl);
			$i=0;$Other=0;$bx=array();$by=array();
			foreach($bl as $k=>$v){ if($i<9){$bx[]=$k;$by[]=$v;$i++;}else{$Other+=$v;} }
			if($Other){$bx[]='Others';$by[]=$Other;}
			$arr_sj3=array('x'=>$bx,'y'=>$by,'data'=>$bl);

			//结构（价格档位 gy2 汇总）
			$gs=array();
			if($ytd==1){
				foreach(M('shuju_iot')->where("type='$type' AND (nd='$year' OR nd='".$year."年')".$flt)->select() as $r){
					$k=$r['gy2']?$r['gy2']:'其他';$gs[$k]=$gs[$k]+($xl==1?$r['xl']:$r['xe']);
				}
			}else{
				foreach($this->qiot($type,$nowtime,$flt) as $r){
					$k=$r['gy2']?$r['gy2']:'其他';$gs[$k]=$gs[$k]+($xl==1?$r['xl']:$r['xe']);
				}
			}
			$arr_sj6=array('data'=>$gs);
		}elseif($src=='pid'){
			//季度数据（shuju_pid，字段为 chl销量/xse销额，type2=品类下子类）
			if($quarter && $year){$nowtime=strtotime($year.'-'.(($quarter*3)-2).'-01');}
			$jdstr='Q'.$quarter;
			$nd=date('Y',$nowtime); $ndl=date('Y',strtotime('-1 year',$nowtime));
			$xl_now=0;$xe_now=0;$xl_last=0;$xe_last=0;
			foreach(M('shuju_pid')->where("type='$type' AND nd='$nd' AND jd='$jdstr'".$flt)->select() as $r){$xl_now+=($r['chl']/1000);$xe_now+=($r['xse']/100);}
			foreach(M('shuju_pid')->where("type='$type' AND nd='$ndl' AND jd='$jdstr'".$flt)->select() as $r){$xl_last+=($r['chl']/1000);$xe_last+=($r['xse']/100);}
			//市场规模近12季 x/y
			$arr_sort=array();
			for($i=0;$i<8;$i++){
				$q=$quarter-((int)floor(($i-($quarter-1))/4)*4);
				$yy=$nd-$i;
				if($q<1){$q+=4;$yy=$yy-1;}
				$sum=0;
				foreach(M('shuju_pid')->where("type='$type' AND nd='$yy' AND jd='Q$q'".$flt)->select() as $r){$sum+=($xl==1?($r['chl']/1000):($r['xse']/100));}
				$arr=array();$arr['id']=$yy.'Q'.$q;$arr['num']=round($sum,1);$arr_sj2[]=$arr;
			}
			array_reverse($arr_sj2);
			$pjjg=!empty($xl_now)?round(($xe_now/$xl_now),0):0;
			$pjjgl=!empty($xl_last)?round(($xe_last/$xl_last),0):0;
			$bfb=function($a,$b){return !empty($b)?(($a>=$b)?'+'.round((($a-$b)/$b)*100,0).'%':'-'.round((($b-$a)/$b)*100,0).'%'):'+0%';};
			$arr=array();$arr['t1']='销量';$arr['t2']=!empty($xl_now)?sprintf('%.1f',round($xl_now,1)):0;$arr['t3']=$bfb($xl_now,$xl_last);$arr['dw']='万台';$arr_sj1[]=$arr;
			$arr=array();$arr['t1']='销额';$arr['t2']=!empty($xe_now)?sprintf('%.1f',round($xe_now,1)):0;$arr['t3']=$bfb($xe_now,$xe_last);$arr['dw']='亿元';$arr_sj1[]=$arr;
			$arr=array();$arr['t1']='平均价格';$arr['t2']=$pjjg;$arr['t3']=$bfb($pjjg,$pjjgl);$arr['dw']='元';$arr_sj1[]=$arr;
			//品牌份额（bp）
			$bl=array();
			foreach(M('shuju_pid')->where("type='$type' AND nd='$nd' AND jd='$jdstr'".$flt)->select() as $r){
				$bl[$r['pp']]=$bl[$r['pp']]+($xl==1?($r['chl']/1000):($r['xse']/100));
			}
			arsort($bl);
			$i=0;$Other=0;$bx=array();$by=array();
			foreach($bl as $k=>$v){ if($k=='Others'||$k=='others'){ $Other+=$v; continue;} if($i<9){$bx[]=$k;$by[]=$v;$i++;}else{$Other+=$v;} }
			if($Other){$bx[]='Others';$by[]=$Other;}
			$arr_sj3=array('x'=>$bx,'y'=>$by,'data'=>$bl);
		}else{
			//供应链数据（shuju_tvs，字段 ch出货/xe销额，type2=子口径），按所选年份取数
			$arr_sj1=array();
			$arr_sj2=array();
			$arr_sj6=array();
			//定位该品类在所选年份的最新一期（jd/ yd 取较大者）
			$tmp=$this->tvsLatestOfYear($type,$year);
			if($tmp){
				$pre="type='$type'";
				$ch_now=0;$xe_now=0;$ch_last=0;
				$nowc='';
				$lastc='';
				if($ytd==1){
					//年累计：全年聚合 vs 去年全年
					$nowc=" AND nd='".$year."'";
					$lastc=" AND nd='".($year-1)."'";
				}else{
					if($tmp['jd']!=''){
						$nowc=" AND nd='".$year."' AND jd='".$tmp['jd']."'";
						$lastc=" AND nd='".($year-1)."' AND jd='".$tmp['jd']."'";
					}else{
						$nowc=" AND nd='".$year."' AND yd='".$tmp['yd']."'";
						$lastc=" AND nd='".($year-1)."' AND yd='".$tmp['yd']."'";
					}
				}
				foreach(M('shuju_tvs')->where($pre.$nowc.$flt)->select() as $r){$ch_now+=$r['ch'];$xe_now+=$r['xe'];}
				foreach(M('shuju_tvs')->where($pre.$lastc.$flt)->select() as $r){$ch_last+=$r['ch'];}
				$k=$ch_now; if($tmp['type2']==11){$k=$k/1000;} $kl=$ch_last; if($tmp['type2']==11){$kl=$kl/1000;}
				$div=(in_array($type,array(17,21)))?10000:10;
				$bfb1=function($a,$b){return !empty($b)?(($a>=$b)?'+'.round((($a-$b)/$b)*100,0).'%':'-'.round((($b-$a)/$b)*100,0).'%'):'+0%';};
				$arr=array();$arr['t1']='出货数量';$arr['t2']=!empty($k)?sprintf('%.1f',round(($k/$div),1)):0;$arr['t3']=$bfb1($k,$kl);$arr['dw']='万台';$arr_sj1[]=$arr;
				$p1=!empty($ch_now)?($xe_now/$ch_now):0;$p2=!empty($ch_last)?($xe_now/$ch_last):0;
				$arr=array();$arr['t1']='平均价格';$arr['t2']=round($p1,0);$arr['t3']=$bfb1($p1,$p2);$arr['dw']='元';$arr_sj1[]=$arr;
			}
		}

		// 同组品类明细表：用于下方列表展示
		$arr_sub=array();
		$sub_group=array();
		$cur_menu=array(); //命中的品类组菜单，用于判定展示模式
		//品类名映射
		$type_names=array();
		foreach($this->arr_datascate2 as $dk=>$dv){
			foreach($dv as $lk=>$lv){
				$type_names[$lk]=$lv;
			}
		}
		$menus=M('indexmenu')->where("isrecommand=1")->order('displayorder DESC,id DESC')->select();
		//优先按 前端显式传入的品类组(group=菜单id) 定位
		if($group){
			foreach($menus as $mu){
				if($mu['id']==$group){
					$ids=M('categroup')->where("menu_id='".$mu['id']."'")->order('type ASC')->getfield('type',true);
					if(!$ids){$ids=array($mu['type']);}
					$sub_group=array();
					foreach($ids as $tid){
						$sub_group[$tid]=isset($type_names[$tid])?$type_names[$tid]:$tid;
					}
					$cur_menu=$mu;
					break;
				}
			}
		}
		//未传 group 或未命中时，按 type 反查所属绑定组
		if(!$sub_group){
			foreach($menus as $mu){
				$ids=M('categroup')->where("menu_id='".$mu['id']."'")->order('type ASC')->getfield('type',true);
				if(!$ids){$ids=array($mu['type']);}
				if(in_array($type,$ids)){
					$sub_group=array(); //键=type,值=name
					foreach($ids as $tid){
						$sub_group[$tid]=isset($type_names[$tid])?$type_names[$tid]:$tid;
					}
					$cur_menu=$mu;
					break;
				}
			}
		}
		//未命中绑定则回退到配置分组 Lt_datascate2
		if(!$sub_group){
			foreach($this->arr_datascate2 as $dk=>$dv){
				foreach($dv as $k=>$v){
					if($type==$k){
						$sub_group=$dv;
						break 2;
					}
				}
			}
		}
		//明细表展示模式：商用显示用【出货数量+同比/行业均价+同比】；供应链(核心器件)用【出货数量+同比】；其余用【销量同比/销额同比/平均价格/均价同比】
		if($src=='tvs'){$sub_mode='tvs';}
		else{$sub_mode=((isset($cur_menu['name']) && strpos($cur_menu['name'],'商用显示')!==false))?'qty':'yoy';}
		$bfb=function($a,$b){return !empty($b)?(($a>=$b)?'+'.round((($a-$b)/$b)*100,0).'%':'-'.round((($b-$a)/$b)*100,0).'%'):'+0%';};
		if($sub_group){
			foreach($sub_group as $sid=>$sname){
				if($src=='tvs'){
					//供应链表：按子口径(type2)按所选年份取数 + 去年同期/去年全年
					$mods=isset($this->arr_datascate3[$sid])?$this->arr_datascate3[$sid]:array();
					if(!$mods){
						//无子口径配置时，整类聚合
						$mods=array(0=>$sname);
					}
				// 该 (sid,t2) 的可用期数列表，按每个组合只查一次
				if(!isset($tvs_avail_years)){$tvs_avail_years=array();}
				// 同一供应链内按维度分组：group_key = sid + dim（monthly/quarterly）
				// dim 由第一个 type2 的存储方式决定（保持跨 t2 复用）
				$dim_cache=array(); // sid -> 'q'|'m'
				foreach($mods as $t2=>$t2name){
					$row_key=$sid.'-'.$t2;
					if(!isset($tvs_avail_years[$row_key])){
						$where_t2="type='$sid'";
						if($t2!=0){$where_t2.=" AND type2='$t2'";}
						// 按该 type2 判断季度还是月度（只取 jd 字段非空的记录）
						$t2_has_jd=M('shuju_tvs')->where($where_t2." AND jd!='' AND yd=''")->find();
						$plist=array();
						if($t2_has_jd){
							$dim_cache[$row_key]='q';
							$pr_rows=M('shuju_tvs')->distinct(true)->where($where_t2." AND jd!='' AND yd=''")->field('nd,jd')->select();
							foreach($pr_rows as $pr){
								$lbl=$pr['nd'].'/'.$pr['jd'];
								$plist[$lbl]=array('label'=>$lbl,'nd'=>$pr['nd'],'jd'=>$pr['jd'],'yd'=>'');
							}
						}else{
							$dim_cache[$row_key]='m';
							$pr_rows=M('shuju_tvs')->distinct(true)->where($where_t2." AND yd!='' AND yd IS NOT NULL")->field('nd,yd')->select();
							foreach($pr_rows as $pr){
								$yyd=$pr['yd'];
								if(preg_match('/^(\d{2})\.(\d{1,2})$/i',$yyd,$m2)){
									$lbl=$pr['nd'].'/'.str_pad($m2[2],2,'0',STR_PAD_LEFT);
								}else{
									$lbl=$pr['nd'].'/'.$yyd;
								}
								$plist[$lbl]=array('label'=>$lbl,'nd'=>$pr['nd'],'jd'=>'','yd'=>$yyd);
							}
						}
						krsort($plist);
						$clean=array();
						foreach(array_values($plist) as $pe){
							if(preg_match('/^\d{4}\/(Q[1-4]|\d{2})$/',$pe['label'])){
								$clean[]=$pe;
							}
						}
						$tvs_avail_years[$row_key]=$clean;
					}
					// 同维度分组 key：供前端把同一供应链的月度/季度子类归到同一个框
					$dim=$dim_cache[$row_key];
					$sub_group_key=$sid.'-'.$dim; // 如 "9-m" / "9-q"
					// 前端可传 tvs_period_<sub_group_key（下划线替换-）> 控制整组时间
					$param_key='tvs_period_'.str_replace('-','_',$sub_group_key);
					$sel_period=I($param_key,'',dhtmlspecialchars);
					$plist_row=$tvs_avail_years[$row_key];
					$use_year_raw=$year; $use_jd=''; $use_yd='';
					$chosen_pe=null;
					if($sel_period){
						foreach($plist_row as $pe){
							if($pe['label']==$sel_period){$chosen_pe=$pe; break;}
						}
					}
					if(!$chosen_pe && !empty($plist_row)){$chosen_pe=$plist_row[0];}
					if($chosen_pe){
						$use_year_raw=$chosen_pe['nd'];
						$use_jd=$chosen_pe['jd'];
						$use_yd=$chosen_pe['yd'];
					}
					$where_pre="type='$sid'";
					if($t2!=0){$where_pre.=" AND type2='$t2'";}
					// 构造查询条件
					if($use_jd!=''){
						$nowc=" AND nd='".$use_year_raw."' AND jd='".$use_jd."'";
						$lastc=" AND nd='".($use_year_raw-1)."' AND jd='".$use_jd."'";
						$period_label=$use_year_raw.'/'.$use_jd;
					}elseif($use_yd!=''){
						$nowc=" AND nd='".$use_year_raw."' AND yd='".$use_yd."'";
						$lastc=" AND nd='".($use_year_raw-1)."' AND yd='".$use_yd."'";
						if(preg_match('/^(\d{2})\.(\d{1,2})$/i',$use_yd,$m2)){
							$period_label=$use_year_raw.'/'.str_pad($m2[2],2,'0',STR_PAD_LEFT);
						}else{
							$period_label=$use_year_raw.'/'.$use_yd;
						}
					}else{
						continue;
					}
					$year_has=M('shuju_tvs')->where($where_pre." AND nd='".$use_year_raw."'")->find();
					if(!$year_has){continue;}
					$ch_now=0;$xe_now=0;$ch_last=0;
					foreach(M('shuju_tvs')->where($where_pre.$nowc.$flt)->select() as $r){$ch_now+=$r['ch'];$xe_now+=$r['xe'];}
					foreach(M('shuju_tvs')->where($where_pre.$lastc.$flt)->select() as $r){$ch_last+=$r['ch'];}
					$div=10;
					if($sid==17 || $sid==21){$div=10000;}
					$k2=$ch_now;
					if($t2==11){$k2=$k2/1000;}
					$k2l=$ch_last;
					if($t2==11){$k2l=$k2l/1000;}
					$qty=!empty($k2)?sprintf('%.1f',round(($k2/$div),1)):0;
					$avg=!empty($ch_now)?round(($xe_now/$ch_now),0):0;
					$avgl=!empty($ch_last)?round(($xe_now/$ch_last),0):0;
					// 返回该行及其所在维度分组的期数列表
					$avail_periods=array_column($plist_row,'label');
					$arr_sub[] = array(
						'id'=>$row_key,
						'group'=>$sname,
						'group_sid'=>$sid,
						'sub_group'=>$sub_group_key,  // 用于前端分组（月度/季度框）
						'period'=>$period_label,
						'avail_periods'=>$avail_periods,
						'name'=>$t2name,
						'xl_yoy'=>$bfb($k2,$k2l),
						'avg_price'=>$avg,
						'avg_yoy'=>$bfb($avg,$avgl),
						'qty'=>$qty
					);
				}
				}elseif($src=='iot'){
					$xl_now2=0;$xe_now2=0;$xl_last2=0;$xe_last2=0;
					if($ytd==1){
						for($i=0;$i<12;$i++){
							if($i==0){$aa=$nowtime;}else{$aa=strtotime('-'.$i.' month',$nowtime);}
							if(date('Y',$aa)!=date('Y',$nowtime)){break;}
							foreach($this->qiot($sid,$aa,$flt) as $r){$xl_now2+=$r['xl'];$xe_now2+=$r['xe'];}
							$ml=date('Y.m',strtotime('-1 year',$aa));
							foreach(M('shuju_iot')->where("type='$sid' AND (yd='$ml' OR yd='".date('y.m',strtotime('-1 year',$aa))."')".$flt)->select() as $r){$xl_last2+=$r['xl'];$xe_last2+=$r['xe'];}
						}
					}else{
						foreach($this->qiot($sid,$nowtime,$flt) as $r){$xl_now2+=$r['xl'];$xe_now2+=$r['xe'];}
						foreach($this->qiot($sid,strtotime('-1 year',$nowtime),$flt) as $r){$xl_last2+=$r['xl'];$xe_last2+=$r['xe'];}
					}
					$pjjg2=!empty($xl_now2)?round(($xe_now2/$xl_now2),0):0;
					$pjjgl2=!empty($xl_last2)?round(($xe_last2/$xl_last2),0):0;
					$arr_sub[] = array(
						'id' => $sid,
						'name' => $sname,
						'xl_yoy' => $bfb($xl_now2,$xl_last2),
						'xe_yoy' => $bfb($xe_now2,$xe_last2),
						'avg_price' => $pjjg2,
						'avg_yoy' => $bfb($pjjg2,$pjjgl2),
						'qty' => !empty($xl_now2)?sprintf('%.1f',round(($xl_now2/10000),1)):0
					);
				}else{
					$nd=date('Y',$nowtime); $ndl=date('Y',strtotime('-1 year',$nowtime));
					$jdstr='Q'.$quarter;
					$xl_now2=0;$xe_now2=0;$xl_last2=0;$xe_last2=0;
					foreach(M('shuju_pid')->where("type='$sid' AND nd='$nd' AND jd='$jdstr'".$flt)->select() as $r){$xl_now2+=($r['chl']/1000);$xe_now2+=($r['xse']/100);}
					foreach(M('shuju_pid')->where("type='$sid' AND nd='$ndl' AND jd='$jdstr'".$flt)->select() as $r){$xl_last2+=($r['chl']/1000);$xe_last2+=($r['xse']/100);}
					$pjjg2=!empty($xl_now2)?round(($xe_now2/$xl_now2),0):0;
					$pjjgl2=!empty($xl_last2)?round(($xe_last2/$xl_last2),0):0;
					$arr_sub[] = array(
						'id' => $sid,
						'name' => $sname,
						'xl_yoy' => $bfb($xl_now2,$xl_last2),
						'xe_yoy' => $bfb($xe_now2,$xe_last2),
						'avg_price' => $pjjg2,
						'avg_yoy' => $bfb($pjjg2,$pjjgl2),
						'qty' => !empty($xl_now2)?sprintf('%.1f',round($xl_now2,1)):0
					);
				}
			}
		}

		$return_data['arr_sj1']=$arr_sj1;
		$return_data['arr_sj2']=$arr_sj2;
		$return_data['arr_sj2_num']=$arr_sj2_num;
		$return_data['arr_sj2_dw']=($xl==1)?'万台':'亿元';
		$return_data['arr_sj3']=$arr_sj3;
		$return_data['arr_sj6']=$arr_sj6;
		$return_data['arr_sub']=$arr_sub;
		$return_data['sub_mode']=$sub_mode;
		if($src=='tvs' && !empty($tvs_avail_years)){
			// tvs_avail_years 现在按 "sid-t2" 键存期数，不再输出给前端
			// 每行数据的 avail_periods 已内嵌在 arr_sub 每条里了
		}
		//品类组（首页菜单）及其绑定品类，供筛选项/下拉读取
		$arr_group=array();
		foreach($menus as $mu){
			$ids=M('categroup')->where("menu_id='".$mu['id']."'")->order('type ASC')->getfield('type',true);
			if(!$ids){$ids=array($mu['type']);}
			$types=array();
			foreach($ids as $tid){
				$types[]=array('id'=>$tid,'name'=>isset($type_names[$tid])?$type_names[$tid]:$tid);
			}
			$arr_group[]=array('id'=>$mu['id'],'name'=>$mu['name'],'type'=>$mu['type'],'types'=>$types);
		}
		$return_data['arr_group']=$arr_group;
		$return_data['latest_year']=$latest_year;
		$return_data['latest_month']=$latest_month;
		$return_data['latest_quarter']=$latest_quarter;
		$return_data['status']=1;
		echo json_encode($return_data);
	}

	//首页 市场数据 菜单，后台可配置
	public function getindexmenu(){		$datalist = M('indexmenu')->where("isrecommand=1")->order("displayorder DESC,id DESC")->select();
		//品类组名称映射（同一组下所有品类用于明细表）
		$type_names=array();
		foreach($this->arr_datascate2 as $dk=>$dv){
			foreach($dv as $lk=>$lv){
				$type_names[$lk]=$lv;
			}
		}
		foreach($datalist as $k=>$v){
			if($v['pic'] && strpos($v['pic'],'/Uploads')===0){
				$v['pic']=C('SITEURL').$v['pic'];
			}
			//该菜单绑定的品类（用于筛选页品类明细表）
			$v['types']=array();
			$ids=M('categroup')->where("menu_id='".$v['id']."'")->order('type ASC')->getfield('type',true);
			if(!$ids){$ids=array($v['type']);}
			foreach($ids as $tid){
				$v['types'][]=array('id'=>$tid,'name'=>isset($type_names[$tid])?$type_names[$tid]:$tid);
			}
			$datalist[$k]=$v;
		}
		$return_data=array();
		$return_data['status'] = 1;
		$return_data['datalist'] = $datalist;
		echo json_encode($return_data);
	}

	//news 首页获取新闻，行业洞察
	public function getindexnews(){
		$datalist = array();
		$datalist = M('news')->where("isrecommand=1")->field('id,name,url,addtime,tag')->limit(6)->order("displayorder DESC,addtime DESC,id DESC")->select();
		foreach($datalist as $k=>$v){
			if($k==0){
				$v['tag']=1;
			}else{
				$v['tag']=2;
			}
			$v['addtime']=date('Y-m-d',$v['addtime']);
			$datalist[$k]=$v;
		}
		$return_data=array();
		$return_data['status'] = 1;
		$return_data['datalist'] = $datalist;
		echo json_encode($return_data);
	}
	//月报，季报，年报列表页
	public function getybnews(){
		$sjtype=I('sjtype','',intval);//产品分类
		$sql="isrecommand=1";
		if($sjtype){
			$sql .=" AND sjtype='$sjtype'";
		}
		//月报
		$yuebao = M('news')->where($sql." AND type=3")->field('id,name,url,addtime,tag')->order("displayorder DESC,addtime DESC,id DESC")->limit(4)->select();
		foreach($yuebao as $k=>$v){
			$v['addtime']=date('Y-m-d',$v['addtime']);
			$yuebao[$k]=$v;
		}
		//季报
		$jibao = M('news')->where($sql." AND type=4")->field('id,name,url,addtime,tag')->order("displayorder DESC,addtime DESC,id DESC")->limit(4)->select();
		foreach($jibao as $k=>$v){
			$v['addtime']=date('Y-m-d',$v['addtime']);
			$jibao[$k]=$v;
		}
		//年报
		$nianbao = M('news')->where($sql." AND type=5")->field('id,name,url,addtime,tag')->order("displayorder DESC,addtime DESC,id DESC")->limit(4)->select();
		foreach($nianbao as $k=>$v){
			$v['addtime']=date('Y-m-d',$v['addtime']);
			$nianbao[$k]=$v;
		}
		$return_data=array();
		$return_data['status'] = 1;
		$return_data['datalist']['yuebao'] = $yuebao;
		$return_data['datalist']['jibao'] = $jibao;
		$return_data['datalist']['nianbao'] = $nianbao;
		echo json_encode($return_data);
	}
	//所有新闻列表页
	public function getnews(){
		$type=I('type','',intval);//新闻分类
		$sjtype=I('sjtype','',intval);//产品分类
		$p=I('p','',intval);//分页
		$page=$p?$p:1;
		if($type==2){//热点第一页20条，其他10条
		    if($page==1){
				$limit=($page-1)*20;
				$lm=20;
			}else{
				$page=$page+1;
				$limit=($page-1)*10;
				$lm=10;
			}
		}else{
			$limit=($page-1)*10;
			$lm=10;
		}
		$sql="isrecommand=1 AND type='$type'";
		if($sjtype){
			$sql .=" AND sjtype='$sjtype'";
		}
		$datalist = array();
		$datalist = M('news')->where($sql)->field('id,name,url,addtime,tag,pic')->order("displayorder DESC,addtime DESC,id DESC")->limit($limit,$lm)->select();
		foreach($datalist as $k=>$v){
			$v['addtime']=date('Y-m-d',$v['addtime']);
			if($v['pic']){
				$v['pic']=C('SITEURL').$v['pic'];
			}
			$datalist[$k]=$v;
		}
		$return_data=array();
		//是否为最后一页
		$count=M('news')->where($sql)->count();
		if($count <= ($limit+$lm)){
			$return_data['lastpage']=1;
		}else{
			$return_data['lastpage']=0;
		}
		$return_data['status'] = 1;
		$return_data['datalist'] = $datalist;
		echo json_encode($return_data);
	}
	//观研-小视频列表（url + 封面 + 多行描述）
	public function getvideo(){
		$p=I('p','',intval);//分页
		$page=$p?$p:1;
		$lm=12;
		$limit=($page-1)*$lm;
		$sql="isrecommand=1";
		$datalist = M('video')->where($sql)->field('id,name,url,pic,description,addtime')->order("displayorder DESC,id DESC")->limit($limit,$lm)->select();
		foreach($datalist as $k=>$v){
			$v['addtime']=date('Y-m-d',$v['addtime']);
			if($v['pic']){
				$v['pic']=C('SITEURL').$v['pic'];
			}
			$datalist[$k]=$v;
		}
		$return_data=array();
		//是否为最后一页
		$count=M('video')->where($sql)->count();
		if($count <= ($limit+$lm)){
			$return_data['lastpage']=1;
		}else{
			$return_data['lastpage']=0;
		}
		$return_data['status'] = 1;
		$return_data['datalist'] = $datalist;
		echo json_encode($return_data);
	}
	//观研-小视频播放量+1
	public function getvideoclick(){
		$id=I('id','',intval);
		if($id){
			M('video')->where("id='$id'")->setInc('click');
		}
		$return_data=array();
		$return_data['status'] = 1;
		echo json_encode($return_data);
	}
	//新闻详情页
	public function getnewscon(){
		$id=I('id','',intval);
		$uid=I('uid','',intval);
		$data=M('news')->where("id='$id'")->find();
		if($data['isrecommand']!=1){
		    $return_data=array();
			$return_data['status'] = 0;
			$return_data['msg'] = '该新闻不存在！';
		    echo json_encode($return_data);
		}else{
			$arr=array();
			$arr['click']=$data['click']+1;
			M('news')->where("id='$id'")->save($arr);
			$data['click']=$arr['click'];
			$data['addtime']=date('Y-m-d',$data['addtime']);
			if($data['pic']){
				$data['pic']=C('SITEURL').$data['pic'];
			}
			$data['content'] = preg_replace('/&lt;img([\s\S]*?)src\=&quot;\/Uploads([\s\S]*?)&quot;([\s\S]*?)&gt;/i','&lt;img\\1src\=&quot;'.C('SITEURL').'/Uploads\\2&quot;\\3&gt;', $data['content']);
		    $data['content'] = preg_replace('/&lt;a([\s\S]*?)href\=&quot;\/Uploads([\s\S]*?)&quot;([\s\S]*?)&gt;/i','&lt;a\\1href\=&quot;'.C('SITEURL').'/Uploads\\2&quot;\\3&gt;', $data['content']);
		    $data['content'] = preg_replace('/&lt;video([\s\S]*?)src\=&quot;\/Uploads([\s\S]*?)&quot;([\s\S]*?)&gt;([\s\S]*?)&lt;source([\s\S]*?)src\=&quot;\/Uploads([\s\S]*?)&quot;([\s\S]*?)&gt;([\s\S]*?)&lt;\/video&gt;/i','&lt;video\\1src\=&quot;'.C('SITEURL').'/Uploads\\2&quot;\\3&gt;\\4&lt;source\\5src\=&quot;'.C('SITEURL').'/Uploads\\6&quot;\\7&gt;\\8&lt;\/video&gt;', $data['content']);
		    $data['content'] = preg_replace('/&lt;video([\s\S]*?)&lt;source([\s\S]*?)src\=&quot;\/Uploads([\s\S]*?)&quot;([\s\S]*?)&gt;([\s\S]*?)&lt;\/video&gt;/i','&lt;video\\1&lt;source\\2src\=&quot;'.C('SITEURL').'/Uploads\\3&quot;\\4&gt;\\5&lt;\/video&gt;', $data['content']);
		
			$data['content']=htmlspecialchars_decode(stripslashes($data['content']));
		    $return_data=array();
			$return_data['status'] = 1;
			$return_data['data'] = $data;
			//收藏或点赞
			$arr=array();
			$arr['sc']=M('news_cz')->where("uid='$uid' AND nid='$id' AND type=1")->count();
			$arr['dz']=M('news_cz')->where("uid='$uid' AND nid='$id' AND type=2")->count();
			$arr['scnum']=M('news_cz')->where("nid='$id' AND type=1")->count();
			$arr['dznum']=M('news_cz')->where("nid='$id' AND type=2")->count();
			$return_data['scdz'] = $arr;
			echo json_encode($return_data);
		}
	}
	//搜索新闻
	public function getsearch(){
		$keywords=trim(I('keywords','',dhtmlspecialchars));
		$sql="isrecommand=1";
		if($keywords){
			$sql .=" AND name like '%$keywords%'";
		}
		$p=I('p','',intval);//分页
		$page=$p?$p:1;
		$limit=($page-1)*10;
		
		$datalist = array();
		$datalist = M('news')->where($sql)->field('id,name,url,addtime,tag')->order("displayorder DESC,addtime DESC,id DESC")->limit($limit,10)->select();
		foreach($datalist as $k=>$v){
			$v['addtime']=date('Y-m-d',$v['addtime']);
			$datalist[$k]=$v;
		}
		$return_data=array();
		//是否为最后一页
		$count=M('news')->where($sql)->count();
		if($count <= ($limit+10)){
			$return_data['lastpage']=1;
		}else{
			$return_data['lastpage']=0;
		}
		$return_data['status'] = 1;
		$return_data['datalist'] = $datalist;
		$return_data['keywords'] = $keywords;
		echo json_encode($return_data);
	}
	//数据总览页面
	public function getdatas(){
		//每个组的时间戳
		foreach($this->arr_datascate2 as $dk=>$dv){
			$maxsj=0;
			foreach($dv as $k=>$v){
				if(in_array($k,array(1,2,3,4,13,14,15,16,19))){
					//最新日期
					$sj=M('shuju_iot')->where("type='$k'")->order("nd DESC,yd DESC")->find();
					if($sj){
						$sjc=explode('.',$sj['yd']);
						$sjt=strtotime($sj['nd'].'-'.$sjc[1].'-01');
						if($sjt){
							if($maxsj==0){
								$maxsj=$sjt;
							}elseif($maxsj > $sjt){
								$maxsj=$sjt;
							}
						}
					}
				    $this->nowtime=$maxsj;
				}elseif(in_array($k,array(5,6,7,8,18))){
					//最新日期
					$sj=M('shuju_pid')->where("type='$k'")->order("nd DESC,jd DESC")->find();
					if($sj){
						$a=strtotime($this->nownd.'-'.($this->nowjd*3-2).'-01');//当前季度时间戳
						$sjc=explode('Q',$sj['jd']);
						$sjt=strtotime($sj['nd'].'-'.($sjc[1]*3-2).'-01');
						if($sjt){
							if($maxsj==0){
								$maxsj=$sjt;
							}elseif($maxsj > $sjt){
								$maxsj=$sjt;
							}
						}
					}
					$this->nownd = date('Y',$maxsj);
		            $this->nowjd = ceil(date('n',$maxsj) / 3);
				}elseif($k==9){
					$maxsj=0;
					$maxsj1=0;
					foreach($this->arr_datascate3[$k] as $ck=>$cv){
						//最新日期
						if($ck==1){
							$sj=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->order("nd DESC,jd DESC")->find();
							if($sj){
								$a=strtotime($this->nownd.'-'.($this->nowjd*3-2).'-01');//当前季度时间戳
								$sjc=explode('Q',$sj['jd']);
								$sjt1=strtotime($sj['nd'].'-'.($sjc[1]*3-2).'-01');
								if($sjt1){
									if($maxsj1==0){
										$maxsj1=$sjt1;
									}elseif($maxsj1 > $sjt1){
										$maxsj1=$sjt1;
									}
								}
							}
							$this->nownd91 = date('Y',$maxsj1);
							$this->nowjd91 = ceil(date('n',$maxsj1) / 3);							
						}elseif($ck==2){
							$sjnd=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->order("nd DESC")->find();
							$arr_yf=array('Dec'=>'12','Nov'=>'11','Oct'=>'10','Sep'=>'09','Aug'=>'08','Jul'=>'07','Jun'=>'06','May'=>'05','Apr'=>'04','Mar'=>'03','Feb'=>'02','Jan'=>'01');
							foreach($arr_yf as $yk=>$yv){
								$sj=M('shuju_tvs')->where("type='$k' AND type2='$ck' AND nd='".$sjnd['nd']."' AND yd='$yk'")->find();
								if($sj){
									break;
								}
							}
							if($sj){
								$sjt=strtotime($sj['nd'].'-'.$arr_yf[$sj['yd']].'-01');
							}
						}elseif($ck==3 || $ck==6){
							$sj=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->order("nd DESC,yd DESC")->find();
							$sjc=explode('.',$sj['yd']);
							$sjt=strtotime($sj['nd'].'-'.$sjc[1].'-01');
						}
						if($sjt){
							if($maxsj==0){
								$maxsj=$sjt;
							}elseif($maxsj > $sjt){
								$maxsj=$sjt;
							}
						}
					}
					$this->nowtime3=$maxsj;
				}elseif($k==12){
					$maxsj=0;
					$maxsj1=0;
					foreach($this->arr_datascate3[$k] as $ck=>$cv){
						if($ck==4){
							$sj=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->order("nd DESC,jd DESC")->find();
							
							if($sj){
								$a=strtotime($this->nownd.'-'.($this->nowjd*3-2).'-01');//当前季度时间戳
								$sjc=explode('Q',$sj['jd']);
								$sjt1=strtotime($sj['nd'].'-'.($sjc[1]*3-2).'-01');
								if($sjt1){
									if($maxsj1==0){
										$maxsj1=$sjt1;
									}elseif($maxsj1 > $sjt1){
										$maxsj1=$sjt1;
									}
								}
							}
							$this->nownd121 = date('Y',$maxsj1);
							$this->nowjd121 = ceil(date('n',$maxsj1) / 3);		
						}elseif($ck==5){
							$sj=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->order("nd DESC,yd DESC")->find();
							$sjc=explode('.',$sj['yd']);
							$sjt=strtotime($sj['nd'].'-'.$sjc[1].'-01');
						}
						if($sjt){
							if($maxsj==0){
								$maxsj=$sjt;
							}elseif($maxsj > $sjt){
								$maxsj=$sjt;
							}
						}
					}
					$this->nowtime4=$maxsj;
				}elseif($k==17){
					$maxsj=0;
					$maxsj1=0;
					foreach($this->arr_datascate3[$k] as $ck=>$cv){
						if($ck==8 || $ck==10){
							$sj=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->order("nd DESC,yd DESC")->find();
							$sjc=explode('.',$sj['yd']);
							$sjt=strtotime($sj['nd'].'-'.$sjc[1].'-01');
						}else{
							$sjt=0;
						}
						if($sjt){
							if($maxsj==0){
								$maxsj=$sjt;
							}elseif($maxsj > $sjt){
								$maxsj=$sjt;
							}
						}
					}
					$this->nowtime5=$maxsj;
				}elseif($k==11){
					$maxsj=0;
					$maxsj1=0;
					foreach($this->arr_datascate3[$k] as $ck=>$cv){
						//最新日期
						if($ck==9){
							$sj=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->order("nd DESC,jd DESC")->find();
							if($sj){
								$a=strtotime($this->nownd.'-'.($this->nowjd*3-2).'-01');//当前季度时间戳
								$sjc=explode('Q',$sj['jd']);
								$sjt1=strtotime($sj['nd'].'-'.($sjc[1]*3-2).'-01');
								if($sjt1){
									if($maxsj1==0){
										$maxsj1=$sjt1;
									}elseif($maxsj1 > $sjt1){
										$maxsj1=$sjt1;
									}
								}
							}
							$this->nownd119 = date('Y',$maxsj1);
							$this->nowjd119 = ceil(date('n',$maxsj1) / 3);
						}
						if($sjt){
							if($maxsj==0){
								$maxsj=$sjt;
							}elseif($maxsj > $sjt){
								$maxsj=$sjt;
							}
						}
					}
					$this->nowtime6=$maxsj;
				}elseif($k==21){
					$maxsj=0;
					$maxsj1=0;
					foreach($this->arr_datascate3[$k] as $ck=>$cv){
						if($ck==12){
							$sj=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->order("nd DESC,yd DESC")->find();
							$sjc=explode('.',$sj['yd']);
							$sjt=strtotime($sj['nd'].'-'.$sjc[1].'-01');
						}else{
							$sjt=0;
						}
						if($sjt){
							if($maxsj==0){
								$maxsj=$sjt;
							}elseif($maxsj > $sjt){
								$maxsj=$sjt;
							}
						}
					}
					$this->nowtime7=$maxsj;
				}
			}
		}					
		
		$return_data=array();
		$sel_yd=I('sel_yd','',intval);
		if(!$sel_yd){
			$sel_yd=1;//1月度，2年度
		}
		//智能硬件IOT
		//$return_data['arr_jg1']="数据截止到".date('Y年m月',$this->nowtime);
		$return_data['arr_jg1']="数据口径为线上监测和同比";
		
		
		//搜索数组
		$arr_ss1=array();
		for($i=0;$i<12;$i++){
			$j=$i+1;
			$arr_ss1[$j]=date('Y/m',strtotime('-'.$i.' month',$this->nowtime));
		}
		$j=$i+1;
		$arr_ss1[$j]='年累计';
		$arr_sss1=array();
		foreach($arr_ss1 as $k=>$v){
			$arr=array();
			$arr['id']=$k;
			$arr['name']=$v;
			$arr_sss1[]=$arr;
		}
		$return_data['arr_ss1']=$arr_sss1;
		if($sel_yd==13){
			//年份和月份
			$nowyd=date('y.m',$this->nowtime);
			$nowyd4=date('Y.m',$this->nowtime);
			$nownd=date('Y',$this->nowtime);
			//上一年这个月
			$lastm=date('y.m',strtotime('-1 year',$this->nowtime));
			$lastm4=date('Y.m',strtotime('-1 year',$this->nowtime));
		}else{
			$sel_yd_s=$sel_yd-1;
			$nowtime=strtotime('-'.$sel_yd_s.' month',$this->nowtime);
		    //年份和月份
			$nowyd=date('y.m',$nowtime);
			$nowyd4=date('Y.m',$nowtime);
			$nownd=date('Y',$nowtime);
			//上一年这个月
			$lastm=date('y.m',strtotime('-1 year',$nowtime));
			$lastm4=date('Y.m',strtotime('-1 year',$nowtime));
		}
		if($sel_yd!=13){
			$iotsql =" AND (yd='".$nowyd."' OR yd='".$nowyd4."')";
			//同比，上一年这个月
			$iotsql1 =" AND (yd='".$lastm."' OR yd='".$lastm4."')";
		}elseif($sel_yd==13){
			//月的数组
			$arr_m1=array();
			$arr_m2=array();
			$la=strtotime($nownd.'-'.date('m',$this->nowtime).'-01');//当前月时间戳
			$la1=strtotime($nownd.'-01-01');//当前年1月时间戳
			for($i=0;$i<12;$i++){
				if($i==0){
					$aa=$la;
				}else{
					$aa=strtotime('-'.$i.' month',$la);
				}
				if($aa < $la1){
					break;
				}else{
					$m1=date('Y.m',$aa);//今年月时间戳
					$m14=date('y.m',$aa);//今年月时间戳
					
					$m2=date('Y.m',strtotime('-1 year',$aa));//去年月时间戳
					$m24=date('y.m',strtotime('-1 year',$aa));//去年月时间戳
					
					$arr_m1[]="yd='".$m1."' OR yd='".$m14."'";
					$arr_m2[]="yd='".$m2."' OR yd='".$m24."'";
				}
			}
			$tmp1=implode(' OR ',$arr_m1);
			$tmp2=implode(' OR ',$arr_m2);
			$iotsql =" AND (".$tmp1.")";//当前年
			$iotsql1 =" AND (".$tmp2.")";//上一年
		}
		$iotdatalist=M('shuju_iot')->where("1".$iotsql)->order('type ASC,nd DESC,yd DESC,id DESC')->select();
		$iotxl=array();
		$iotxe=array();
		$iotpjjg=array();
		foreach($iotdatalist as $k=>$v){
			$iotxl[0]=$iotxl[0]+$v['xl'];//所有的销量
			$iotxl[$v['type']]=$iotxl[$v['type']]+$v['xl'];//分类型销量
			$iotxe[0]=$iotxe[0]+$v['xe'];//所有的销额
			$iotxe[$v['type']]=$iotxe[$v['type']]+$v['xe'];//分类型销额
		}
		foreach($iotxl as $k=>$v){
			$iotpjjg[$k]=!empty($iotxl[$k])?round(($iotxe[$k]/$iotxl[$k]),0):0;
		}
		//同比：一般情况下是今年第n月与去年第n月比；环比：表示连续2个单位周期（比如连续两月）内量的变化比
		//此次为同比
		$iotlastdata=M('shuju_iot')->where("1".$iotsql1)->order('type ASC,nd DESC,yd DESC,id DESC')->select();
		$iotlastxl=array();//销量
		$iotlastxe=array();//销额
		$iotlastpjjg=array();//平均价格
		foreach($iotlastdata as $k=>$v){
			$iotlastxl[0]=$iotlastxl[0]+$v['xl'];//所有的销量
			$iotlastxl[$v['type']]=$iotlastxl[$v['type']]+$v['xl'];//分类型销量
			$iotlastxe[0]=$iotlastxe[0]+$v['xe'];//所有的销额
			$iotlastxe[$v['type']]=$iotlastxe[$v['type']]+$v['xe'];//分类型销额
		}
		foreach($iotlastxl as $k=>$v){
			$iotlastpjjg[$k]=!empty($iotlastxl[$k])?round(($iotlastxe[$k]/$iotlastxl[$k]),0):0;
		}
		//总的智能硬件数据
		if($iotxl[0] > $iotlastxl[0]){
			$iotcxl=$iotxl[0]-$iotlastxl[0];
			$iotxlxb='+';//增长
		}else{
			$iotcxl=$iotlastxl[0]-$iotxl[0];
			$iotxlxb='-';//减少
		}
		if($iotxe[0] > $iotlastxe[0]){
			$iotcxe=$iotxe[0]-$iotlastxe[0];
			$iotxexb='+';//增长
		}else{
			$iotcxe=$iotlastxe[0]-$iotxe[0];
			$iotxexb='-';//减少
		} 
		$iotxlbfb=!empty($iotlastxl[0])?$iotxlxb.round(($iotcxl/$iotlastxl[0])*100,0).'%':'+0%';//销量百分比
		$iotxebfb=!empty($iotlastxe[0])?$iotxexb.round(($iotcxe/$iotlastxe[0])*100,0).'%':'+0%';//销额百分比
		$arr=array();
		$arr['xl']=sprintf('%.1f',round(($iotxl[0]/10000),1));
		$arr['xlbfb']=$iotxlbfb;
		$arr['xlfh']=$iotxlxb;
		$arr['xe']=sprintf('%.1f',round(($iotxe[0]/100000000),1));
		$arr['xebfb']=$iotxebfb;
		$arr['xefh']=$iotxexb;
		$return_data['arr_sj1']=$arr;
		//智能硬件分类数据
		$arr_sj2=array();
		$s=0;
		foreach($this->arr_datascate2[1] as $k=>$v){
			if($iotxl[$k] > $iotlastxl[$k]){
				$iotcxl=$iotxl[$k]-$iotlastxl[$k];
				$iotxlxb='+';//增长
			}else{
				$iotcxl=$iotlastxl[$k]-$iotxl[$k];
				$iotxlxb='-';//减少
			}
			if($iotpjjg[$k] > $iotlastpjjg[$k]){
				$iotcpjjg=$iotpjjg[$k]-$iotlastpjjg[$k];
				$iotpjjgxb='+';//增长
			}else{
				$iotcpjjg=$iotlastpjjg[$k]-$iotpjjg[$k];
				$iotpjjgxb='-';//减少
			} 
			$arr=array();
			$arr['id']=$k;
			$arr['ids']=$s;
			$arr['t1']=$v;
			$arr['t2']=sprintf('%.1f',round(($iotxl[$k]/10000),1));//销量
			$arr['t3']=!empty($iotlastxl[$k])?$iotxlxb.round(($iotcxl/$iotlastxl[$k])*100,0).'%':'+0%';//销量百分比 day959 20220128 分母不能为0
			$arr['t4']=round($iotpjjg[$k],0);//平均价格
			$arr['t5']=!empty($iotlastpjjg[$k])?$iotpjjgxb.round(($iotcpjjg/$iotlastpjjg[$k])*100,0).'%':'+0%';//平均价格百分比 day959 20220128 分母不能为0
			$arr_sj2[]=$arr;
			$s++;
		}
		$return_data['arr_sj2']=$arr_sj2;
		//商用显示PID
		//$return_data['arr_jg2']="数据截止到".$this->nownd."年第".$this->nowjd."季度";
		$return_data['arr_jg2']="数据口径为全渠道出货和同比";
		$sel_yd2=I('sel_yd2','',intval);
		if(!$sel_yd2){
			$sel_yd2=1;//1月度，2年度
		}
		//搜索数组
		$arr_ss2=array();
		$a=strtotime($this->nownd.'-'.($this->nowjd*3-2).'-01');//当前季度时间戳
		for($i=0;$i<4;$i++){
			$j=$i+1;
			if($i==0){
				$aa=$a;
			}else{
				$aa=strtotime('-'.($i*3).' month',$a);
			}
			$nd=date('Y',$aa);
			$jd='Q'.ceil(date('n',$aa) / 3);
			$arr_ss2[$j]=$nd.'/'.$jd;
		}
		$j=$i+1;
		$arr_ss2[$j]='年累计';
		$arr_sss2=array();
		foreach($arr_ss2 as $k=>$v){
			$arr=array();
			$arr['id']=$k;
			$arr['name']=$v;
			$arr_sss2[]=$arr;
		}
		$return_data['arr_ss2']=$arr_sss2;
		if($sel_yd2==5){
			//年份和季度
			$nowjd='Q'.$this->nowjd;
			$nownd=$this->nownd;
		}else{
			if($sel_yd2==1){
				$aa=$a;
			}else{
				$aa=strtotime('-'.(($sel_yd2-1)*3).' month',$a);
			}
			$nowjd='Q'.ceil(date('n',$aa) / 3);
			$nownd=date('Y',$aa);
		}
		$lastnd=$nownd-1;
		
		if($sel_yd2!=5){
			$pidsql =" AND ((type=5 AND type2=1) OR type!=5) AND nd='".$nownd."' AND jd='".$nowjd."'";
			//同比，上一年这个季度
			$pidsql1 =" AND ((type=5 AND type2=1) OR type!=5) AND nd='".$lastnd."' AND jd='".$nowjd."'";
		}elseif($sel_yd2==5){
			//季度的数组
			$arr_m=array();
			for($i=0;$i<4;$i++){
				if($i==0){
					$aa=$nowjd;
				}else{
					$aa='Q'.($this->nowjd-$i);
				}
				if($this->nowjd-$i==0){
					break;
				}else{
					$arr_m[]="jd='".$aa."'";
				}
			}
			$tmp=implode(' OR ',$arr_m);
			$pidsql =" AND ((type=5 AND type2=1) OR type!=5) AND nd='".$nownd."' AND (".$tmp.")";//当前年
			$pidsql1 =" AND ((type=5 AND type2=1) OR type!=5) AND nd='".$lastnd."' AND (".$tmp.")";//上一年
		}
		$piddatalist=M('shuju_pid')->where("1".$pidsql)->order('type ASC,nd DESC,jd DESC,id DESC')->select();
		$pidxl=array();
		$pidxe=array();
		$pidpjjg=array();
		foreach($piddatalist as $k=>$v){
			if($v['type']==7 || $v['type']==18){
				$v['chl']=$v['chl']/1000;//变成K
				$v['xse']=$v['xse']/100;//由万变为Mn
			}
			$pidxl[0]=$pidxl[0]+$v['chl'];//所有的销量
			$pidxl[$v['type']]=$pidxl[$v['type']]+$v['chl'];//分类型销量
			$pidxe[0]=$pidxe[0]+$v['xse'];//所有的销额
			$pidxe[$v['type']]=$pidxe[$v['type']]+$v['xse'];//分类型销额
		}
		foreach($pidxl as $k=>$v){
			$pidpjjg[$k]=!empty($pidxl[$k])?round((($pidxe[$k]*1000000)/($pidxl[$k]*1000)),0):0;//day959 20220128 分母不能为0
		}
		//同比：一般情况下是今年第n月与去年第n月比；环比：表示连续2个单位周期（比如连续两月）内量的变化比
		//此次为同比
		$pidlastdata=M('shuju_pid')->where("1".$pidsql1)->order('type ASC,nd DESC,jd DESC,id DESC')->select();
		$pidlastxl=array();//销量
		$pidlastxe=array();//销额
		$pidlastpjjg=array();//平均价格
		foreach($pidlastdata as $k=>$v){
			if($v['type']==7 || $v['type']==18){
				$v['chl']=$v['chl']/1000;
				$v['xse']=$v['xse']/100;
			}
			$pidlastxl[0]=$pidlastxl[0]+$v['chl'];//所有的销量
			$pidlastxl[$v['type']]=$pidlastxl[$v['type']]+$v['chl'];//分类型销量
			$pidlastxe[0]=$pidlastxe[0]+$v['xse'];//所有的销额
			$pidlastxe[$v['type']]=$pidlastxe[$v['type']]+$v['xse'];//分类型销额
		}
		foreach($pidlastxl as $k=>$v){
			$pidlastpjjg[$k]=!empty($pidlastxl[$k])?round((($pidlastxe[$k]*1000000)/($pidlastxl[$k]*1000)),0):0;//day959 20220128 分母不能为0
		}
		//总的商用显示数据
		if($pidxl[0] > $pidlastxl[0]){
			$pidcxl=$pidxl[0]-$pidlastxl[0];
			$pidxlxb='+';//增长
		}else{
			$pidcxl=$pidlastxl[0]-$pidxl[0];
			$pidxlxb='-';//减少
		}
		if($pidxe[0] > $pidlastxe[0]){
			$pidcxe=$pidxe[0]-$pidlastxe[0];
			$pidxexb='+';//增长
		}else{
			$pidcxe=$pidlastxe[0]-$pidxe[0];
			$pidxexb='-';//减少
		} 
		$pidxlbfb=!empty($pidlastxl[0])?$pidxlxb.round(($pidcxl/$pidlastxl[0])*100,0).'%':'+0%';//销量百分比 day959 20220128 分母不能为0
		$pidxebfb=!empty($pidlastxe[0])?$pidxexb.round(($pidcxe/$pidlastxe[0])*100,0).'%':'+0%';//销额百分比 day959 20220128 分母不能为0
		$arr=array();
		$arr['xl']=sprintf('%.1f',round(($pidxl[0]/10),1));//默认是K，改为万
		$arr['xlbfb']=$pidxlbfb;
		$arr['xlfh']=$pidxlxb;
		$arr['xe']=sprintf('%.1f',round(($pidxe[0]/100),1));//默认是Mn，改为亿
		$arr['xebfb']=$pidxebfb;
		$arr['xefh']=$pidxexb;
		
		$return_data['arr_sj3']=$arr;
		//商用显示分类数据
		$arr_sj4=array();
		$s=0;
		foreach($this->arr_datascate2[2] as $k=>$v){
			if($pidxl[$k] > $pidlastxl[$k]){
				$pidcxl=$pidxl[$k]-$pidlastxl[$k];
				$pidxlxb='+';//增长
			}else{
				$pidcxl=$pidlastxl[$k]-$pidxl[$k];
				$pidxlxb='-';//减少
			}
			if($pidpjjg[$k] > $pidlastpjjg[$k]){
				$pidcpjjg=$pidpjjg[$k]-$pidlastpjjg[$k];
				$pidpjjgxb='+';//增长
			}else{
				$pidcpjjg=$pidlastpjjg[$k]-$pidpjjg[$k];
				$pidpjjgxb='-';//减少
			} 
			$arr=array();
			$arr['id']=$k;
			$arr['ids']=$s;
			$arr['t1']=$v;
			$arr['t2']=!empty($pidxl[$k])?sprintf('%.1f',round(($pidxl[$k]/10),1)):0;//销量 默认是K，改为万
			$arr['t3']=!empty($pidlastxl[$k])?$pidxlxb.round(($pidcxl/$pidlastxl[$k])*100,0).'%':'+0%';//销量百分比 day959 20220128 分母不能为0
			$arr['t4']=round($pidpjjg[$k],0);//平均价格
			$arr['t5']=!empty($pidlastpjjg[$k])?$pidpjjgxb.round(($pidcpjjg/$pidlastpjjg[$k])*100,0).'%':'+0%';//平均价格百分比 day959 20220128 分母不能为0
			$arr_sj4[]=$arr;
			$s++;
		}
		$return_data['arr_sj4']=$arr_sj4;
		//大尺寸显示TVS
		//$return_data['arr_jg3']="数据截止到".date('Y年m月',$this->nowtime);
		$return_data['arr_jg3']="数据口径为未特指的数据口径为全渠道出货和同比";
		$sel_yd3=I('sel_yd3','',intval);
		if(!$sel_yd3){
			$sel_yd3=1;//1月度，2年度
		}
		//搜索数组
		$arr_ss3=array();
		for($i=0;$i<12;$i++){
			$j=$i+1;
			$arr_ss3[$j]=date('Y/m',strtotime('-'.$i.' month',$this->nowtime3));
		}
		$j=$i+1;
		$arr_ss3[$j]='年累计';
		$arr_sss3=array();
		foreach($arr_ss3 as $k=>$v){
			$arr=array();
			$arr['id']=$k;
			$arr['name']=$v;
			$arr_sss3[]=$arr;
		}
		$return_data['arr_ss3']=$arr_sss3;
		if($sel_yd3==13){
			//年份和月份
			$nowyd2=date('M',$this->nowtime3);
			$nowyd3=date('y.m',$this->nowtime3);
			$nownd=date('Y',$this->nowtime3);
			//上一年这个月
			$lastm2=date('M',strtotime('-1 year',$this->nowtime3));
			$lastm3=date('y.m',strtotime('-1 year',$this->nowtime3));
			$lasty=date('Y',strtotime('-1 year',$this->nowtime3));
		}else{
			$sel_yd3_s=$sel_yd3-1;
			$nowtime=strtotime('-'.$sel_yd3_s.' month',$this->nowtime3);
		    //年份和月份
			$nowyd2=date('M',$nowtime);
			$nowyd3=date('y.m',$nowtime);
			$nownd=date('Y',$nowtime);
			//上一年这个月
			$lastm2=date('M',strtotime('-1 year',$nowtime));
			$lastm3=date('y.m',strtotime('-1 year',$nowtime));
			$lasty=date('Y',strtotime('-1 year',$nowtime));
		}
		
		
		if($sel_yd3!=13){
			$tvssql =" AND nd='".$nownd."' AND (yd='".$nowyd2."' OR yd='".$nowyd3."')";
			//同比，上一年这个月
			$tvssql1 =" AND nd='".$lasty."' AND (yd='".$lastm2."' OR yd='".$lastm3."')";
		}elseif($sel_yd3==13){
			//月的数组
			$arr_m1=array();
			$arr_m2=array();
			$la=strtotime($nownd.'-'.date('m',$this->nowtime3).'-01');//当前月时间戳
			$la1=strtotime($nownd.'-01-01');//当前年1月时间戳
			for($i=0;$i<12;$i++){
				if($i==0){
					$aa=$la;
				}else{
					$aa=strtotime('-'.$i.' month',$la);
				}
				if($aa < $la1){
					break;
				}else{
					$m12=date('M',$aa);//今年月时间戳
					$m13=date('y.m',$aa);//今年月时间戳
					
					$m22=date('M',strtotime('-1 year',$aa));//去年月时间戳
					$m23=date('y.m',strtotime('-1 year',$aa));//去年月时间戳
					
					$arr_m1[]="yd='".$m12."' OR yd='".$m13."'";
					$arr_m2[]="yd='".$m22."' OR yd='".$m23."'";
				}
			}
			$tmp1=implode(' OR ',$arr_m1);
			$tmp2=implode(' OR ',$arr_m2);
			$tvssql =" AND nd='".$nownd."' AND (".$tmp1.")";//当前年
			$tvssql1 =" AND nd='".$lasty."' AND (".$tmp2.")";//上一年
		}
		$tvsdatalist=M('shuju_tvs')->where("type=9".$tvssql)->order('type2 ASC,nd DESC,yd DESC,id DESC')->select();
		
		$tvsxl=array();
		foreach($tvsdatalist as $k=>$v){
			if($v['type2']==11){
				$v['ch']=$v['ch']/1000;
			}
			$tvsxl[$v['type2']]=$tvsxl[$v['type2']]+$v['ch'];//分类型销量
		}
		//同比：一般情况下是今年第n月与去年第n月比；环比：表示连续2个单位周期（比如连续两月）内量的变化比
		//此次为同比
		$tvslastdata=M('shuju_tvs')->where("type=9".$tvssql1)->order('type2 ASC,nd DESC,yd DESC,id DESC')->select();
		$tvslastxl=array();//销量
		foreach($tvslastdata as $k=>$v){
			if($v['type2']==11){
				$v['ch']=$v['ch']/1000;
			}
			$tvslastxl[$v['type2']]=$tvslastxl[$v['type2']]+$v['ch'];//分类型销量
		}
		//电视供应链分类数据
		$arr_sj5=array();
		$s=0;
		foreach($this->arr_datascate3[9] as $k=>$v){
			if($k>1){
				if($tvsxl[$k] > $tvslastxl[$k]){
					$tvscxl=$tvsxl[$k]-$tvslastxl[$k];
					$tvsxlxb='+';//增长
				}else{
					$tvscxl=$tvslastxl[$k]-$tvsxl[$k];
					$tvsxlxb='-';//减少
				}
				$arr=array();
				$arr['id']=9;
				$arr['ids']=0;
				$arr['ds']=$k;
				$arr['dss']=$s;
				$arr['t1']=$v;
				$arr['t2']=!empty($tvsxl[$k])?sprintf('%.1f',round(($tvsxl[$k]/10),1)):0;//销量 默认是K，改为万
				$arr['t3']=!empty($tvslastxl[$k])?$tvsxlxb.round(($tvscxl/$tvslastxl[$k])*100,0).'%':'+0%';//销量百分比 day959 20220128 分母不能为0
				$arr_sj5[]=$arr;
				$s++;
			}
		}
		//echo '<pre>';print_r($arr_sj5);exit;
		$return_data['arr_sj5']=$arr_sj5;

		//TV品牌出货
		$sel_yd4=I('sel_yd4','',intval);
		if(!$sel_yd4){
			$sel_yd4=1;//1月度，2年度
		}
		//搜索数组
		$arr_ss4=array();
		$a=strtotime($this->nownd91.'-'.($this->nowjd91*3-2).'-01');//当前季度时间戳
		for($i=0;$i<4;$i++){
			$j=$i+1;
			if($i==0){
				$aa=$a;
			}else{
				$aa=strtotime('-'.($i*3).' month',$a);
			}
			$nd=date('Y',$aa);
			$jd='Q'.ceil(date('n',$aa) / 3);
			$arr_ss4[$j]=$nd.'/'.$jd;
		}
		$j=$i+1;
		$arr_ss4[$j]='年累计';
		$arr_sss4=array();
		foreach($arr_ss4 as $k=>$v){
			$arr=array();
			$arr['id']=$k;
			$arr['name']=$v;
			$arr_sss4[]=$arr;
		}
		$return_data['arr_ss4']=$arr_sss4;
		
		if($sel_yd4==5){
			//年份和季度
			$nowjd='Q'.$this->nowjd91;
			$nownd=$this->nownd91;
		}else{
			if($sel_yd4==1){
				$aa=$a;
			}else{
				$aa=strtotime('-'.(($sel_yd4-1)*3).' month',$a);
			}
			$nowjd='Q'.ceil(date('n',$aa) / 3);
			$nownd=date('Y',$aa);
		}
		$lastnd=$nownd-1;
		
		if($sel_yd4!=5){
			$tvs1sql =" AND type2=1 AND nd='".$nownd."' AND jd='".$nowjd."'";
			//同比，上一年这个季度
			$tvs1sql1 =" AND type2=1 AND nd='".$lastnd."' AND jd='".$nowjd."'";
		}elseif($sel_yd4==5){
			//季度的数组
			$arr_m=array();
			for($i=0;$i<4;$i++){
				if($i==0){
					$aa=$nowjd;
				}else{
					$aa='Q'.($this->nowjd91-$i);
				}
				if($this->nowjd91-$i==0){
					break;
				}else{
					$arr_m[]="jd='".$aa."'";
				}
			}
			$tmp=implode(' OR ',$arr_m);
			$tvs1sql =" AND type2=1 AND nd='".$nownd."' AND (".$tmp.")";//当前年
			$tvs1sql1 =" AND type2=1 AND nd='".$lastnd."' AND (".$tmp.")";//上一年
		}
		$tvsdatalist=M('shuju_tvs')->where("type=9".$tvs1sql)->order('nd DESC,jd DESC,id DESC')->select();
		
		$tvsxl=array();
		foreach($tvsdatalist as $k=>$v){
			$tvsxl[$v['type2']]=$tvsxl[$v['type2']]+$v['ch'];//分类型销量
		}
		//同比：一般情况下是今年第n月与去年第n月比；环比：表示连续2个单位周期（比如连续两月）内量的变化比
		//此次为同比
		$tvslastdata=M('shuju_tvs')->where("type=9".$tvs1sql1)->order('nd DESC,jd DESC,id DESC')->select();
		$tvslastxl=array();//销量
		foreach($tvslastdata as $k=>$v){
			$tvslastxl[$v['type2']]=$tvslastxl[$v['type2']]+$v['ch'];//分类型销量
		}
		//电视供应链分类数据
		$arr_sj6=array();
		foreach($this->arr_datascate3[9] as $k=>$v){
			if($k==1){
				if($tvsxl[$k] > $tvslastxl[$k]){
					$tvscxl=$tvsxl[$k]-$tvslastxl[$k];
					$tvsxlxb='+';//增长
				}else{
					$tvscxl=$tvslastxl[$k]-$tvsxl[$k];
					$tvsxlxb='-';//减少
				}
				$arr=array();
				$arr['id']=9;
				$arr['ids']=0;
				$arr['ds']=$k;
				$arr['dss']=4;
				$arr['t1']=$v;
				$arr['t2']=!empty($tvsxl[$k])?sprintf('%.1f',round(($tvsxl[$k]/10),1)):0;//销量 默认是K，改为万
				$arr['t3']=!empty($tvslastxl[$k])?$tvsxlxb.sprintf('%.1f',round(($tvscxl/$tvslastxl[$k])*100,0)).'%':'+0%';//销量百分比 day959 20220128 分母不能为0
				$arr_sj6[]=$arr;
			}
		}
		$return_data['arr_sj6']=$arr_sj6;

		//电子纸供应链
		//模板出货
		$sel_yd5=I('sel_yd5','',intval);
		if(!$sel_yd5){
			$sel_yd5=1;//1月度，2年度
		}
		//搜索数组
		$arr_ss5=array();
		$a=strtotime($this->nownd121.'-'.($this->nowjd121*3-2).'-01');//当前季度时间戳
		for($i=0;$i<4;$i++){
			$j=$i+1;
			if($i==0){
				$aa=$a;
			}else{
				$aa=strtotime('-'.($i*3).' month',$a);
			}
			$nd=date('Y',$aa);
			$jd='Q'.ceil(date('n',$aa) / 3);
			$arr_ss5[$j]=$nd.'/'.$jd;
		}
		$j=$i+1;
		$arr_ss5[$j]='年累计';
		$arr_sss5=array();
		foreach($arr_ss5 as $k=>$v){
			$arr=array();
			$arr['id']=$k;
			$arr['name']=$v;
			$arr_sss5[]=$arr;
		}
		$return_data['arr_ss5']=$arr_sss5;
		
		if($sel_yd5==5){
			//年份和季度
			$nowjd='Q'.$this->nowjd121;
			$nownd=$this->nownd121;
		}else{
			if($sel_yd5==1){
				$aa=$a;
			}else{
				$aa=strtotime('-'.(($sel_yd5-1)*3).' month',$a);
			}
			$nowjd='Q'.ceil(date('n',$aa) / 3);
			$nownd=date('Y',$aa);
		}
		$lastnd=$nownd-1;
		
		if($sel_yd5!=5){
			$tvs1sql =" AND type2=4 AND nd='".$nownd."' AND jd='".$nowjd."'";
			//同比，上一年这个季度
			$tvs1sql1 =" AND type2=4 AND nd='".$lastnd."' AND jd='".$nowjd."'";
		}elseif($sel_yd5==5){
			//季度的数组
			$arr_m=array();
			for($i=0;$i<4;$i++){
				if($i==0){
					$aa=$nowjd;
				}else{
					$aa='Q'.($this->nowjd121-$i);
				}
				if($this->nowjd121-$i==0){
					break;
				}else{
					$arr_m[]="jd='".$aa."'";
				}
			}
			$tmp=implode(' OR ',$arr_m);
			$tvs1sql =" AND type2=4 AND nd='".$nownd."' AND (".$tmp.")";//当前年
			$tvs1sql1 =" AND type2=4 AND nd='".$lastnd."' AND (".$tmp.")";//上一年
		}
		$tvsdatalist=M('shuju_tvs')->where("type=12".$tvs1sql)->order('nd DESC,jd DESC,id DESC')->select();
		
		$tvsxl=array();
		foreach($tvsdatalist as $k=>$v){
			$tvsxl[$v['type2']]=$tvsxl[$v['type2']]+$v['ch'];//分类型销量
		}
		//同比：一般情况下是今年第n月与去年第n月比；环比：表示连续2个单位周期（比如连续两月）内量的变化比
		//此次为同比
		$tvslastdata=M('shuju_tvs')->where("type=12".$tvs1sql1)->order('nd DESC,jd DESC,id DESC')->select();
		$tvslastxl=array();//销量
		foreach($tvslastdata as $k=>$v){
			$tvslastxl[$v['type2']]=$tvslastxl[$v['type2']]+$v['ch'];//分类型销量
		}
		//模板出货数据
		$arr_sj7=array();
		foreach($this->arr_datascate3[12] as $k=>$v){
			if($k==4){
				if($tvsxl[$k] > $tvslastxl[$k]){
					$tvscxl=$tvsxl[$k]-$tvslastxl[$k];
					$tvsxlxb='+';//增长
				}else{
					$tvscxl=$tvslastxl[$k]-$tvsxl[$k];
					$tvsxlxb='-';//减少
				}
				$arr=array();
				$arr['id']=12;
				$arr['ids']=3;
				$arr['ds']=$k;
				$arr['dss']=0;
				$arr['t1']=$v;
				$arr['t2']=!empty($tvsxl[$k])?sprintf('%.1f',round(($tvsxl[$k]/10),1)):0;//销量 默认是K，改为万
				$arr['t3']=!empty($tvslastxl[$k])?$tvsxlxb.sprintf('%.1f',round(($tvscxl/$tvslastxl[$k])*100,0)).'%':'+0%';//销量百分比 day959 20220128 分母不能为0
				$arr_sj7[]=$arr;
			}
		}
		$return_data['arr_sj7']=$arr_sj7;
		//平板零售
		/* $sel_yd6=I('sel_yd6','',intval);
		if(!$sel_yd6){
			$sel_yd6=1;//1月度，2年度
		}
		//搜索数组
		$arr_ss6=array();
		for($i=0;$i<12;$i++){
			$j=$i+1;
			$arr_ss6[$j]=date('Y/m',strtotime('-'.$i.' month',$this->nowtime4));
		}
		$j=$i+1;
		$arr_ss6[$j]='年累计';
		$arr_sss6=array();
		foreach($arr_ss6 as $k=>$v){
			$arr=array();
			$arr['id']=$k;
			$arr['name']=$v;
			$arr_sss6[]=$arr;
		}
		$return_data['arr_ss6']=$arr_sss6;
		if($sel_yd6==13){
			//年份和月份
			$nowyd3=date('y.m',$this->nowtime4);
			$nownd=date('Y',$this->nowtime4);
			//上一年这个月
			$lastm3=date('y.m',strtotime('-1 year',$this->nowtime4));
			$lasty=date('Y',strtotime('-1 year',$this->nowtime4));
		}else{
			$sel_yd6_s=$sel_yd6-1;
			$nowtime=strtotime('-'.$sel_yd6_s.' month',$this->nowtime4);
		    //年份和月份
			$nowyd3=date('y.m',$nowtime);
			$nownd=date('Y',$nowtime);
			//上一年这个月
			$lastm3=date('y.m',strtotime('-1 year',$nowtime));
			$lasty=date('Y',strtotime('-1 year',$nowtime));
		}
		
		
		if($sel_yd6!=13){
			$tvssql =" AND nd='".$nownd."' AND yd='".$nowyd3."'";
			//同比，上一年这个月
			$tvssql1 =" AND nd='".$lasty."' AND yd='".$lastm3."'";
		}elseif($sel_yd6==13){
			//月的数组
			$arr_m1=array();
			$arr_m2=array();
			$la=strtotime($nownd.'-'.date('m',$this->nowtime4).'-01');//当前月时间戳
			$la1=strtotime($nownd.'-01-01');//当前年1月时间戳
			for($i=0;$i<12;$i++){
				if($i==0){
					$aa=$la;
				}else{
					$aa=strtotime('-'.$i.' month',$la);
				}
				if($aa < $la1){
					break;
				}else{
					$m13=date('y.m',$aa);//今年月时间戳
					
					$m23=date('y.m',strtotime('-1 year',$aa));//去年月时间戳
					
					$arr_m1[]="yd='".$m13."'";
					$arr_m2[]="yd='".$m23."'";
				}
			}
			$tmp1=implode(' OR ',$arr_m1);
			$tmp2=implode(' OR ',$arr_m2);
			$tvssql =" AND nd='".$nownd."' AND (".$tmp1.")";//当前年
			$tvssql1 =" AND nd='".$lasty."' AND (".$tmp2.")";//上一年
		}
		$tvsdatalist=M('shuju_tvs')->where("type=12 AND type2=5".$tvssql)->order('nd DESC,yd DESC,id DESC')->select();
		
		$tvsxl=array();
		foreach($tvsdatalist as $k=>$v){
			$tvsxl[$v['type2']]=$tvsxl[$v['type2']]+$v['ch'];//分类型销量
		}
		//同比：一般情况下是今年第n月与去年第n月比；环比：表示连续2个单位周期（比如连续两月）内量的变化比
		//此次为同比
		$tvslastdata=M('shuju_tvs')->where("type=12 AND type2=5".$tvssql1)->order('type2 ASC,nd DESC,yd DESC,id DESC')->select();
		$tvslastxl=array();//销量
		foreach($tvslastdata as $k=>$v){
			$tvslastxl[$v['type2']]=$tvslastxl[$v['type2']]+$v['ch'];//分类型销量
		}
		
		//平板零售数据
		$arr_sj8=array();
		foreach($this->arr_datascate3[12] as $k=>$v){
			if($k==5){
				if($tvsxl[$k] > $tvslastxl[$k]){
					$tvscxl=$tvsxl[$k]-$tvslastxl[$k];
					$tvsxlxb='+';//增长
				}else{
					$tvscxl=$tvslastxl[$k]-$tvsxl[$k];
					$tvsxlxb='-';//减少
				}
				$arr=array();
				$arr['t1']=$v;
				$arr['t2']=!empty($tvsxl[$k])?sprintf('%.1f',round(($tvsxl[$k]/10000),1)):0;//销量 默认是个，改为万
				$arr['t3']=!empty($tvslastxl[$k])?$tvsxlxb.round(($tvscxl/$tvslastxl[$k])*100,0).'%':'+0%';//销量百分比 day959 20220128 分母不能为0
				$arr_sj8[]=$arr;
			}
		}
		$return_data['arr_sj8']=$arr_sj8; */
		//显示器供应链
		//显示器零售
		$sel_yd7=I('sel_yd7','',intval);
		if(!$sel_yd7){
			$sel_yd7=1;//1月度，2年度
		}
		//搜索数组
		$arr_ss7=array();
		for($i=0;$i<12;$i++){
			$j=$i+1;
			$arr_ss7[$j]=date('Y/m',strtotime('-'.$i.' month',$this->nowtime5));
		}
		$j=$i+1;
		$arr_ss7[$j]='年累计';
		$arr_sss7=array();
		foreach($arr_ss7 as $k=>$v){
			$arr=array();
			$arr['id']=$k;
			$arr['name']=$v;
			$arr_sss7[]=$arr;
		}
		$return_data['arr_ss7']=$arr_sss7;
		if($sel_yd7==13){
			//年份和月份
			$nowyd3=date('y.m',$this->nowtime5);
			$nownd=date('Y',$this->nowtime5);
			//上一年这个月
			$lastm3=date('y.m',strtotime('-1 year',$this->nowtime5));
			$lasty=date('Y',strtotime('-1 year',$this->nowtime5));
		}else{
			$sel_yd7_s=$sel_yd7-1;
			$nowtime=strtotime('-'.$sel_yd7_s.' month',$this->nowtime5);
		    //年份和月份
			$nowyd3=date('y.m',$nowtime);
			$nownd=date('Y',$nowtime);
			//上一年这个月
			$lastm3=date('y.m',strtotime('-1 year',$nowtime));
			$lasty=date('Y',strtotime('-1 year',$nowtime));
		}
		
		
		if($sel_yd7!=13){
			$tvssql =" AND nd='".$nownd."' AND yd='".$nowyd3."'";
			//同比，上一年这个月
			$tvssql1 =" AND nd='".$lasty."' AND yd='".$lastm3."'";
		}elseif($sel_yd7==13){
			//月的数组
			$arr_m1=array();
			$arr_m2=array();
			$la=strtotime($nownd.'-'.date('m',$this->nowtime5).'-01');//当前月时间戳
			$la1=strtotime($nownd.'-01-01');//当前年1月时间戳
			for($i=0;$i<12;$i++){
				if($i==0){
					$aa=$la;
				}else{
					$aa=strtotime('-'.$i.' month',$la);
				}
				if($aa < $la1){
					break;
				}else{
					$m13=date('y.m',$aa);//今年月时间戳
					
					$m23=date('y.m',strtotime('-1 year',$aa));//去年月时间戳
					
					$arr_m1[]="yd='".$m13."'";
					$arr_m2[]="yd='".$m23."'";
				}
			}
			$tmp1=implode(' OR ',$arr_m1);
			$tmp2=implode(' OR ',$arr_m2);
			$tvssql =" AND nd='".$nownd."' AND (".$tmp1.")";//当前年
			$tvssql1 =" AND nd='".$lasty."' AND (".$tmp2.")";//上一年
		}
		$tvsdatalist=M('shuju_tvs')->where("type=17".$tvssql)->order('nd DESC,yd DESC,id DESC')->select();
		
		$tvsxl=array();
		foreach($tvsdatalist as $k=>$v){
			$tvsxl[$v['type2']]=$tvsxl[$v['type2']]+$v['ch'];//分类型销量
		}
		//同比：一般情况下是今年第n月与去年第n月比；环比：表示连续2个单位周期（比如连续两月）内量的变化比
		//此次为同比
		$tvslastdata=M('shuju_tvs')->where("type=17".$tvssql1)->order('type2 ASC,nd DESC,yd DESC,id DESC')->select();
		$tvslastxl=array();//销量
		foreach($tvslastdata as $k=>$v){
			$tvslastxl[$v['type2']]=$tvslastxl[$v['type2']]+$v['ch'];//分类型销量
		}
		
		//显示器零售数据
		$arr_sj9=array();
		$s=0;
		foreach($this->arr_datascate3[17] as $k=>$v){
			if($k==8 || $k==10){
				if($tvsxl[$k] > $tvslastxl[$k]){
					$tvscxl=$tvsxl[$k]-$tvslastxl[$k];
					$tvsxlxb='+';//增长
				}else{
					$tvscxl=$tvslastxl[$k]-$tvsxl[$k];
					$tvsxlxb='-';//减少
				}
				$arr=array();
				$arr['id']=17;
				$arr['ids']=1;
				$arr['ds']=$k;
				$arr['dss']=$s;
				$arr['t1']=$v;
				$arr['t2']=!empty($tvsxl[$k])?sprintf('%.1f',round(($tvsxl[$k]/10000),1)):0;//销量 默认是个，改为万
				$arr['t3']=!empty($tvslastxl[$k])?$tvsxlxb.round(($tvscxl/$tvslastxl[$k])*100,0).'%':'+0%';//销量百分比 day959 20220128 分母不能为0
				$arr_sj9[]=$arr;
				$s++;
			}
		}
		$return_data['arr_sj9']=$arr_sj9;

		//商显供应链
		//交互平板面板
		$sel_yd8=I('sel_yd8','',intval);
		if(!$sel_yd8){
			$sel_yd8=1;//1月度，2年度
		}
		//搜索数组
		$arr_ss8=array();
		$a=strtotime($this->nownd119.'-'.($this->nowjd119*3-2).'-01');//当前季度时间戳
		for($i=0;$i<4;$i++){
			$j=$i+1;
			if($i==0){
				$aa=$a;
			}else{
				$aa=strtotime('-'.($i*3).' month',$a);
			}
			$nd=date('Y',$aa);
			$jd='Q'.ceil(date('n',$aa) / 3);
			$arr_ss8[$j]=$nd.'/'.$jd;
		}
		$j=$i+1;
		$arr_ss8[$j]='年累计';
		$arr_sss8=array();
		foreach($arr_ss8 as $k=>$v){
			$arr=array();
			$arr['id']=$k;
			$arr['name']=$v;
			$arr_sss8[]=$arr;
		}
		$return_data['arr_ss8']=$arr_sss8;
		
		if($sel_yd8==5){
			//年份和季度
			$nowjd='Q'.$this->nowjd119;
			$nownd=$this->nownd119;
		}else{
			if($sel_yd8==1){
				$aa=$a;
			}else{
				$aa=strtotime('-'.(($sel_yd8-1)*3).' month',$a);
			}
			$nowjd='Q'.ceil(date('n',$aa) / 3);
			$nownd=date('Y',$aa);
		}
		$lastnd=$nownd-1;
		
		if($sel_yd8!=5){
			$tvs1sql =" AND type2=9 AND nd='".$nownd."' AND jd='".$nowjd."'";
			//同比，上一年这个季度
			$tvs1sql1 =" AND type2=9 AND nd='".$lastnd."' AND jd='".$nowjd."'";
		}elseif($sel_yd8==5){
			//季度的数组
			$arr_m=array();
			for($i=0;$i<4;$i++){
				if($i==0){
					$aa=$nowjd;
				}else{
					$aa='Q'.($this->nowjd119-$i);
				}
				if($this->nowjd119-$i==0){
					break;
				}else{
					$arr_m[]="jd='".$aa."'";
				}
			}
			$tmp=implode(' OR ',$arr_m);
			$tvs1sql =" AND type2=9 AND nd='".$nownd."' AND (".$tmp.")";//当前年
			$tvs1sql1 =" AND type2=9 AND nd='".$lastnd."' AND (".$tmp.")";//上一年
		}
		$tvsdatalist=M('shuju_tvs')->where("type=11".$tvs1sql)->order('nd DESC,jd DESC,id DESC')->select();
		
		$tvsxl=array();
		foreach($tvsdatalist as $k=>$v){
			$tvsxl[$v['type2']]=$tvsxl[$v['type2']]+$v['ch'];//分类型销量
		}
		//同比：一般情况下是今年第n月与去年第n月比；环比：表示连续2个单位周期（比如连续两月）内量的变化比
		//此次为同比
		$tvslastdata=M('shuju_tvs')->where("type=11".$tvs1sql1)->order('nd DESC,jd DESC,id DESC')->select();
		$tvslastxl=array();//销量
		foreach($tvslastdata as $k=>$v){
			$tvslastxl[$v['type2']]=$tvslastxl[$v['type2']]+$v['ch'];//分类型销量
		}
		//模板出货数据
		$arr_sj10=array();
		foreach($this->arr_datascate3[11] as $k=>$v){
			if($k==9){
				if($tvsxl[$k] > $tvslastxl[$k]){
					$tvscxl=$tvsxl[$k]-$tvslastxl[$k];
					$tvsxlxb='+';//增长
				}else{
					$tvscxl=$tvslastxl[$k]-$tvsxl[$k];
					$tvsxlxb='-';//减少
				}
				$arr=array();
				$arr['id']=11;
				$arr['ids']=2;
				$arr['ds']=$k;
				$arr['dss']=0;
				$arr['t1']=$v;
				$arr['t2']=!empty($tvsxl[$k])?sprintf('%.1f',round(($tvsxl[$k]/10),1)):0;//销量 默认是K，改为万
				$arr['t3']=!empty($tvslastxl[$k])?$tvsxlxb.sprintf('%.1f',round(($tvscxl/$tvslastxl[$k])*100,0)).'%':'+0%';//销量百分比 day959 20220128 分母不能为0
				$arr_sj10[]=$arr;
			}
		}
		$return_data['arr_sj10']=$arr_sj10;

		//笔记本电脑供应链
		//笔记本电脑零售
		$sel_yd9=I('sel_yd9','',intval);
		if(!$sel_yd9){
			$sel_yd9=1;//1月度，2年度
		}
		//搜索数组
		$arr_ss9=array();
		for($i=0;$i<12;$i++){
			$j=$i+1;
			$arr_ss9[$j]=date('Y/m',strtotime('-'.$i.' month',$this->nowtime7));
		}
		$j=$i+1;
		$arr_ss9[$j]='年累计';
		$arr_sss9=array();
		foreach($arr_ss9 as $k=>$v){
			$arr=array();
			$arr['id']=$k;
			$arr['name']=$v;
			$arr_sss9[]=$arr;
		}
		$return_data['arr_ss9']=$arr_sss9;
		if($sel_yd9==13){
			//年份和月份
			$nowyd3=date('y.m',$this->nowtime7);
			$nownd=date('Y',$this->nowtime7);
			//上一年这个月
			$lastm3=date('y.m',strtotime('-1 year',$this->nowtime7));
			$lasty=date('Y',strtotime('-1 year',$this->nowtime7));
		}else{
			$sel_yd9_s=$sel_yd9-1;
			$nowtime=strtotime('-'.$sel_yd9_s.' month',$this->nowtime7);
		    //年份和月份
			$nowyd3=date('y.m',$nowtime);
			$nownd=date('Y',$nowtime);
			//上一年这个月
			$lastm3=date('y.m',strtotime('-1 year',$nowtime));
			$lasty=date('Y',strtotime('-1 year',$nowtime));
		}
		
		
		if($sel_yd9!=13){
			$tvssql =" AND nd='".$nownd."' AND yd='".$nowyd3."'";
			//同比，上一年这个月
			$tvssql1 =" AND nd='".$lasty."' AND yd='".$lastm3."'";
		}elseif($sel_yd9==13){
			//月的数组
			$arr_m1=array();
			$arr_m2=array();
			$la=strtotime($nownd.'-'.date('m',$this->nowtime7).'-01');//当前月时间戳
			$la1=strtotime($nownd.'-01-01');//当前年1月时间戳
			for($i=0;$i<12;$i++){
				if($i==0){
					$aa=$la;
				}else{
					$aa=strtotime('-'.$i.' month',$la);
				}
				if($aa < $la1){
					break;
				}else{
					$m13=date('y.m',$aa);//今年月时间戳
					
					$m23=date('y.m',strtotime('-1 year',$aa));//去年月时间戳
					
					$arr_m1[]="yd='".$m13."'";
					$arr_m2[]="yd='".$m23."'";
				}
			}
			$tmp1=implode(' OR ',$arr_m1);
			$tmp2=implode(' OR ',$arr_m2);
			$tvssql =" AND nd='".$nownd."' AND (".$tmp1.")";//当前年
			$tvssql1 =" AND nd='".$lasty."' AND (".$tmp2.")";//上一年
		}
		$tvsdatalist=M('shuju_tvs')->where("type=21".$tvssql)->order('nd DESC,yd DESC,id DESC')->select();
		
		$tvsxl=array();
		foreach($tvsdatalist as $k=>$v){
			$tvsxl[$v['type2']]=$tvsxl[$v['type2']]+$v['ch'];//分类型销量
		}
		//同比：一般情况下是今年第n月与去年第n月比；环比：表示连续2个单位周期（比如连续两月）内量的变化比
		//此次为同比
		$tvslastdata=M('shuju_tvs')->where("type=21".$tvssql1)->order('type2 ASC,nd DESC,yd DESC,id DESC')->select();
		$tvslastxl=array();//销量
		foreach($tvslastdata as $k=>$v){
			$tvslastxl[$v['type2']]=$tvslastxl[$v['type2']]+$v['ch'];//分类型销量
		}
		
		//笔记本电脑零售数据
		$arr_sj11=array();
		$s=0;
		foreach($this->arr_datascate3[21] as $k=>$v){
			if($k==12){
				if($tvsxl[$k] > $tvslastxl[$k]){
					$tvscxl=$tvsxl[$k]-$tvslastxl[$k];
					$tvsxlxb='+';//增长
				}else{
					$tvscxl=$tvslastxl[$k]-$tvsxl[$k];
					$tvsxlxb='-';//减少
				}
				$arr=array();
				$arr['id']=21;
				$arr['ids']=1;
				$arr['ds']=$k;
				$arr['dss']=$s;
				$arr['t1']=$v;
				$arr['t2']=!empty($tvsxl[$k])?sprintf('%.1f',round(($tvsxl[$k]/10000),1)):0;//销量 默认是个，改为万
				$arr['t3']=!empty($tvslastxl[$k])?$tvsxlxb.round(($tvscxl/$tvslastxl[$k])*100,0).'%':'+0%';//销量百分比 day959 20220128 分母不能为0
				$arr_sj11[]=$arr;
				$s++;
			}
		}
		$return_data['arr_sj11']=$arr_sj11;
		
		/* echo '<pre>';
		print_r($return_data);exit; */
		echo json_encode($return_data);
	}
	//数据页面，分类型
	public function getshuju(){
		//查询当前用户是否注册
		$uid=I('uid','',intval);
		$user=M('user')->where("type=2 AND id='$uid'")->find();
		if(!$user['companyname']){
			$return_data['status'] = 0;//失败
			$return_data['msg'] = '您暂时不能进入此页面，请先注册公司！';
			echo json_encode($return_data);
			exit;
		}
		$return_data=array();
		//TEMP 预览会员效果：暂时将用户视为会员（看效果后请移除这两行）
		$user['huiyuan']=1;
		//是否可以查看产品结构
		//if(in_array($user['phone'],array('13401133225','13401039598','15012345678'))){
		if($user['huiyuan']==1){
			$return_data['is_ck']=1;
		}else{
			$return_data['is_ck']=0;
		}
		//每个组的时间戳
		foreach($this->arr_datascate2 as $dk=>$dv){
			$maxsj=0;
			foreach($dv as $k=>$v){
				if(in_array($k,array(1,2,3,4,13,14,15,16,19))){
					//最新日期
					$sj=M('shuju_iot')->where("type='$k'")->order("nd DESC,yd DESC")->find();
					if($sj){
						$sjc=explode('.',$sj['yd']);
						$sjt=strtotime($sj['nd'].'-'.$sjc[1].'-01');
						if($sjt){
							if($maxsj==0){
								$maxsj=$sjt;
							}elseif($maxsj > $sjt){
								$maxsj=$sjt;
							}
						}
					}
				    $this->nowtime=$maxsj;
				}elseif(in_array($k,array(5,6,7,8,18))){
					//最新日期
					$sj=M('shuju_pid')->where("type='$k'")->order("nd DESC,jd DESC")->find();
					if($sj){
						$a=strtotime($this->nownd.'-'.($this->nowjd*3-2).'-01');//当前季度时间戳
						$sjc=explode('Q',$sj['jd']);
						$sjt=strtotime($sj['nd'].'-'.($sjc[1]*3-2).'-01');
						if($sjt){
							if($maxsj==0){
								$maxsj=$sjt;
							}elseif($maxsj > $sjt){
								$maxsj=$sjt;
							}
						}
					}
					
					$this->nownd = date('Y',$maxsj);
		            $this->nowjd = ceil(date('n',$maxsj) / 3);
				}elseif($k==9){
					$maxsj=0;
					$maxsj1=0;
					foreach($this->arr_datascate3[$k] as $ck=>$cv){
						//最新日期
						if($ck==1){
							$sj=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->order("nd DESC,jd DESC")->find();
							if($sj){
								$a=strtotime($this->nownd.'-'.($this->nowjd*3-2).'-01');//当前季度时间戳
								$sjc=explode('Q',$sj['jd']);
								$sjt1=strtotime($sj['nd'].'-'.($sjc[1]*3-2).'-01');
								if($sjt1){
									if($maxsj1==0){
										$maxsj1=$sjt1;
									}elseif($maxsj1 > $sjt1){
										$maxsj1=$sjt1;
									}
								}
							}
							$this->nownd91 = date('Y',$maxsj1);
							$this->nowjd91 = ceil(date('n',$maxsj1) / 3);
						}elseif($ck==2){
							$sjnd=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->order("nd DESC")->find();
							$arr_yf=array('Dec'=>'12','Nov'=>'11','Oct'=>'10','Sep'=>'09','Aug'=>'08','Jul'=>'07','Jun'=>'06','May'=>'05','Apr'=>'04','Mar'=>'03','Feb'=>'02','Jan'=>'01');
							foreach($arr_yf as $yk=>$yv){
								$sj=M('shuju_tvs')->where("type='$k' AND type2='$ck' AND nd='".$sjnd['nd']."' AND yd='$yk'")->find();
								if($sj){
									break;
								}
							}
							if($sj){
								$sjt=strtotime($sj['nd'].'-'.$arr_yf[$sj['yd']].'-01');
							}
						}elseif($ck==3 || $ck==6){
							$sj=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->order("nd DESC,yd DESC")->find();
							$sjc=explode('.',$sj['yd']);
							$sjt=strtotime($sj['nd'].'-'.$sjc[1].'-01');
						}
						if($sjt){
							if($maxsj==0){
								$maxsj=$sjt;
							}elseif($maxsj > $sjt){
								$maxsj=$sjt;
							}
						}
					}
					$this->nowtime3=$maxsj;
				}elseif($k==12){
					$maxsj=0;
					$maxsj1=0;
					foreach($this->arr_datascate3[$k] as $ck=>$cv){
						if($ck==4){
							$sj=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->order("nd DESC,jd DESC")->find();
							if($sj){
								$a=strtotime($this->nownd.'-'.($this->nowjd*3-2).'-01');//当前季度时间戳
								$sjc=explode('Q',$sj['jd']);
								$sjt1=strtotime($sj['nd'].'-'.($sjc[1]*3-2).'-01');
								if($sjt1){
									if($maxsj1==0){
										$maxsj1=$sjt1;
									}elseif($maxsj1 > $sjt1){
										$maxsj1=$sjt1;
									}
								}
							}
							$this->nownd121 = date('Y',$maxsj1);
							$this->nowjd121 = ceil(date('n',$maxsj1) / 3);							
						}elseif($ck==5){
							$sj=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->order("nd DESC,yd DESC")->find();
							$sjc=explode('.',$sj['yd']);
							$sjt=strtotime($sj['nd'].'-'.$sjc[1].'-01');
						}
						if($sjt){
							if($maxsj==0){
								$maxsj=$sjt;
							}elseif($maxsj > $sjt){
								$maxsj=$sjt;
							}
						}
					}
					$this->nowtime4=$maxsj;
				}elseif($k==17){
					$maxsj=0;
					$maxsj1=0;
					foreach($this->arr_datascate3[$k] as $ck=>$cv){
						if($ck==8 || $ck==10){
							$sj=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->order("nd DESC,yd DESC")->find();
							$sjc=explode('.',$sj['yd']);
							$sjt=strtotime($sj['nd'].'-'.$sjc[1].'-01');
						}else{
							$sjt=0;
						}
						
						if($sjt){
							if($maxsj==0){
								$maxsj=$sjt;
							}elseif($maxsj > $sjt){
								$maxsj=$sjt;
							}
						}
					}
					$this->nowtime5=$maxsj;
				}elseif($k==11){
					$maxsj=0;
					$maxsj1=0;
					foreach($this->arr_datascate3[$k] as $ck=>$cv){
						//最新日期
						if($ck==9){
							$sj=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->order("nd DESC,jd DESC")->find();
							if($sj){
								$a=strtotime($this->nownd.'-'.($this->nowjd*3-2).'-01');//当前季度时间戳
								$sjc=explode('Q',$sj['jd']);
								$sjt1=strtotime($sj['nd'].'-'.($sjc[1]*3-2).'-01');
								if($sjt1){
									if($maxsj1==0){
										$maxsj1=$sjt1;
									}elseif($maxsj1 > $sjt1){
										$maxsj1=$sjt1;
									}
								}
							}
							$this->nownd119 = date('Y',$maxsj1);
							$this->nowjd119 = ceil(date('n',$maxsj1) / 3);
						}
						if($sjt){
							if($maxsj==0){
								$maxsj=$sjt;
							}elseif($maxsj > $sjt){
								$maxsj=$sjt;
							}
						}
					}
					$this->nowtime6=$maxsj;
				}elseif($k==21){
					$maxsj=0;
					$maxsj1=0;
					foreach($this->arr_datascate3[$k] as $ck=>$cv){
						if($ck==12){
							$sj=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->order("nd DESC,yd DESC")->find();
							$sjc=explode('.',$sj['yd']);
							$sjt=strtotime($sj['nd'].'-'.$sjc[1].'-01');
						}else{
							$sjt=0;
						}
						
						if($sjt){
							if($maxsj==0){
								$maxsj=$sjt;
							}elseif($maxsj > $sjt){
								$maxsj=$sjt;
							}
						}
					}
					$this->nowtime7=$maxsj;
				}
			}
		}
		//echo '<pre>';print_r($this->nownd119);exit;
		
		$type=I('type','',intval);
		//根据type，查找分类数组
		$arr_datascate2=C('Lt_datascate2');
		$arr_type=array();
		foreach($arr_datascate2 as $dk=>$dv){
			foreach($dv as $k=>$v){
				if($type==$k){
					$arr_type=$arr_datascate2[$dk];
					break;
				}
			}
		}
		$arr_types=array();
		foreach($arr_type as $k=>$v){
			$arr=array();
			$arr['id']=$k;
			$arr['name']=$v;
			$arr_types[]=$arr;
		}
		$return_data['arr_type']=$arr_types;
		if($type==1 || $type==2 || $type==3 || $type==4 || $type==13 || $type==14 || $type==15 || $type==16 || $type==19){//'1'=>'智能投影','2'=>'智能音响','3'=>'智能门锁','4'=>'智能盒子','13'=>'摄像头','14'=>'智能平板','15'=>'AR设备','19'=>'VR设备','16'=>'回音壁'
			$sel_ds=I('sel_ds','',dhtmlspecialchars);
			if(!$sel_ds){
				$sel_ds='不限';
			}
			$sel_xl=I('sel_xl','',intval);
			if(!$sel_xl){
				$sel_xl=1;//1销量，2销额
			}
			$sel_yd=I('sel_yd','',intval);
			if(!$sel_yd){
				$sel_yd=1;//1月度，2年累计（年度）
			}
			if($sel_yd==13){
				//年份和月份
				if($type==3 || $type==4 || $type==13 || $type==15 || $type==19){
					$nowyd=date('Y.m',$this->nowtime);
					//上一年这个月
					$lastm=date('Y.m',strtotime('-1 year',$this->nowtime));
				}else{
					$nowyd=date('y.m',$this->nowtime);
					//上一年这个月
					$lastm=date('y.m',strtotime('-1 year',$this->nowtime));
				}
				$nownd=date('Y',$this->nowtime);
			}else{
				$sel_yd_s=$sel_yd-1;
			    $nowtime=strtotime('-'.$sel_yd_s.' month',$this->nowtime);
				//年份和月份
				if($type==3 || $type==4 || $type==13 || $type==15 || $type==19){
					$nowyd=date('Y.m',$nowtime);
					//上一年这个月
					$lastm=date('Y.m',strtotime('-1 year',$nowtime));
				}else{
					$nowyd=date('y.m',$nowtime);
					//上一年这个月
					$lastm=date('y.m',strtotime('-1 year',$nowtime));
				}
				$nownd=date('Y',$nowtime);
			}
			
			//$return_data['arr_sj']="数据截止到".date('Y年m月',$this->nowtime);
			$return_data['arr_sj']="数据口径为线上监测和同比";
			if($sel_yd!=13){
				$sql =" AND yd='".$nowyd."'";
				//同比，上一年这个月
				$sql1 =" AND yd='".$lastm."'";
			}elseif($sel_yd==13){
				//月的数组
				$arr_m1=array();
				$arr_m2=array();
				$la=strtotime($nownd.'-'.date('m',$this->nowtime).'-01');//当前月时间戳
				$la1=strtotime($nownd.'-01-01');//当前年1月时间戳
				for($i=0;$i<12;$i++){
					if($i==0){
						$aa=$la;
					}else{
						$aa=strtotime('-'.$i.' month',$la);
					}
					if($aa < $la1){
						break;
					}else{
						if($type==3 || $type==4 || $type==13 || $type==15 || $type==19){
							$m1=date('Y.m',$aa);//今年月时间戳
							$m2=date('Y.m',strtotime('-1 year',$aa));//去年月时间戳
						}else{
							$m1=date('y.m',$aa);//今年月时间戳
							$m2=date('y.m',strtotime('-1 year',$aa));//去年月时间戳
						}
						$arr_m1[]="yd='".$m1."'";
						$arr_m2[]="yd='".$m2."'";
					}
				}
				$tmp1=implode(' OR ',$arr_m1);
				$tmp2=implode(' OR ',$arr_m2);
				$sql =" AND (".$tmp1.")";//当前年
				$sql1 =" AND (".$tmp2.")";//上一年
			}
			
			//电商数组
			$ds=M('shuju_iot')->distinct(true)->where("type='$type'".$sql)->field('ds')->select();
			$arr_ds=array();
			$arr=array();
			$arr['id']='不限';
			$arr['name']='不限';
			$arr_ds[]=$arr;
			foreach($ds as $k=>$v){
				$arr=array();
				$arr['id']=$v['ds'];
				$arr['name']=$v['ds'];
				$arr_ds[]=$arr;
			}
			$return_data['arr_ds']=$arr_ds;
			//销量数组
			$arr_xl=array('0'=>array('id'=>'1','name'=>'销量'),'1'=>array('id'=>'2','name'=>'销额'));
			$return_data['arr_xl']=$arr_xl;
			//月度数组
			$arr_ss1=array();
			for($i=0;$i<12;$i++){
				$j=$i+1;
				$arr_ss1[$j]=date('Y/m',strtotime('-'.$i.' month',$this->nowtime));
			}
			$j=$i+1;
			$arr_ss1[$j]='年累计';
			$arr_sss1=array();
			foreach($arr_ss1 as $k=>$v){
				$arr=array();
				$arr['id']=$k;
				$arr['name']=$v;
				$arr_sss1[]=$arr;
			}
			$return_data['arr_yd']=$arr_sss1;
			
			if($sel_ds!='不限'){
				$sqlds =" AND ds='$sel_ds'";
			}else{
				$sqlds ='';
			}
			$datalist=M('shuju_iot')->where("type='$type'".$sql.$sqlds)->order('nd DESC,yd DESC,id DESC')->select();
		    $xl=0;//销量
			$xe=0;//销量
			$pjjg=0;//平均价格
			foreach($datalist as $k=>$v){
				$xl=$xl+$v['xl'];
				$xe=$xe+$v['xe'];
			}
			$pjjg=!empty($xl)?round(($xe/$xl),0):0;
			//同比：一般情况下是今年第n月与去年第n月比；环比：表示连续2个单位周期（比如连续两月）内量的变化比
			//此次为同比
			$lastdata=M('shuju_iot')->where("type='$type'".$sql1.$sqlds)->order('nd DESC,yd DESC,id DESC')->select();
			$lastxl=0;//销量
			$lastxe=0;//销额
			$lastpjjg=0;//平均价格
			foreach($lastdata as $k=>$v){
				$lastxl=$lastxl+$v['xl'];
				$lastxe=$lastxe+$v['xe'];
			}
			$lastpjjg=!empty($lastxl)?round(($lastxe/$lastxl),0):0;
			if($xl > $lastxl){
				$cxl=$xl-$lastxl;
				$xlxb='+';//增长
			}else{
				$cxl=$lastxl-$xl;
				$xlxb='-';//减少
			}
			if($xe > $lastxe){
				$cxe=$xe-$lastxe;
				$xexb='+';//增长
			}else{
				$cxe=$lastxe-$xe;
				$xexb='-';//减少
			}
			if($pjjg > $lastpjjg){
				$cpjjg=$pjjg-$lastpjjg;
				$pjjgxb='+';//增长
			}else{
				$cpjjg=$lastpjjg-$pjjg;
				$pjjgxb='-';//减少
			}
			$xlbfb=!empty($lastxl)?$xlxb.round(($cxl/$lastxl)*100,0).'%':'+0%';//销量百分比
			$xebfb=!empty($lastxe)?$xexb.round(($cxe/$lastxe)*100,0).'%':'+0%';//销额百分比
			$pjjgbfb=!empty($lastpjjg)?$pjjgxb.round(($cpjjg/$lastpjjg)*100,0).'%':'+0%';//平均价格百分比
			
			$arr_sj1=array();
			$arr=array();
			$arr['t1']='销量';
			$arr['t2']=!empty($xl)?sprintf('%.1f',round(($xl/10000),1)):0;
			$arr['t3']=$xlbfb;
			$arr['dw']='万台';
			$arr_sj1[]=$arr;
			$arr=array();
			$arr['t1']='销额';
			$arr['t2']=!empty($xe)?sprintf('%.1f',round(($xe/100000000),1)):0;
			$arr['t3']=$xebfb;
			$arr['dw']='亿元';
			$arr_sj1[]=$arr;
			$arr=array();
			$arr['t1']='平均价格';
			$arr['t2']=$pjjg;
			$arr['t3']=$pjjgbfb;
			$arr['dw']='元';
			$arr_sj1[]=$arr;
			$return_data['arr_sj1']=$arr_sj1;
			
			//市场规模变化
			$arr_sj2=array();
			$arr_sort2=array();
			if($sel_yd!=13){//月度
				$a=strtotime(date('Y-m',$nowtime).'-01');//当前月时间戳
				$arr_sj2_num=11;
				for($i=0;$i<12;$i++){
					if($i==0){
						$aa=$a;
					}else{
						$aa=strtotime('-'.$i.' month',$a);
					}
					if($type==3 || $type==4 || $type==13 || $type==15 || $type==19){
						$month=date('Y.m',$aa);
					}else{
						$month=date('y.m',$aa);
					}
					$arr=array();
					$chart_xl=0;$chart_xe=0;$year_xl=0;$year_xe=0;$prev_xl=0;$prev_xe=0;
					foreach($this->qiot($type,$aa,$sqlds) as $row){$chart_xl+=$row['xl'];$chart_xe+=$row['xe'];}
					foreach($this->qiot($type,strtotime('-1 year',$aa),$sqlds) as $row){$year_xl+=$row['xl'];$year_xe+=$row['xe'];}
					foreach($this->qiot($type,strtotime('-1 month',$aa),$sqlds) as $row){$prev_xl+=$row['xl'];$prev_xe+=$row['xe'];}
					$chart_value=($sel_xl==1)?$chart_xl:$chart_xe;
					$year_value=($sel_xl==1)?$year_xl:$year_xe;
					$prev_value=($sel_xl==1)?$prev_xl:$prev_xe;
					$arr['num']=round(($chart_value/($sel_xl==1?10000:100000000)),1);
					$arr['yoy']=$this->chartRate($chart_value,$year_value);
					$arr['mom']=$this->chartRate($chart_value,$prev_value);
					$arr['avg']=!empty($chart_xl)?round(($chart_xe/$chart_xl),0):0;
					$year_avg=!empty($year_xl)?($year_xe/$year_xl):0;
					$prev_avg=!empty($prev_xl)?($prev_xe/$prev_xl):0;
					$arr['avg_yoy']=$this->chartRate($arr['avg'],$year_avg);
					$arr['avg_mom']=$this->chartRate($arr['avg'],$prev_avg);
					$arr['id']=date('y/m',$aa);
					$arr['i']=$i+1;
					$arr_sj2[]=$arr;
					$arr_sort2[]=$arr['i'];
					if(date('Y',$aa) !=date('Y',$nowtime) && $arr_sj2_num==11){
						$arr_sj2_num=11-$i;//颜色分界值
					}
				}
			}elseif($sel_yd==13){//累计
				for($i=0;$i<12;$i++){
					if($i==0){
						$year=$nownd;
					}else{
						$year=$nownd-$i;
					}
					$arr=array();
					$chart_xl=M('shuju_iot')->where("type='$type' AND nd='$year'".$sqlds)->sum('xl');
					$chart_xe=M('shuju_iot')->where("type='$type' AND nd='$year'".$sqlds)->sum('xe');
					$prev_xl=M('shuju_iot')->where("type='$type' AND nd='".($year-1)."'".$sqlds)->sum('xl');
					$prev_xe=M('shuju_iot')->where("type='$type' AND nd='".($year-1)."'".$sqlds)->sum('xe');
					$chart_value=($sel_xl==1)?$chart_xl:$chart_xe;
					$prev_value=($sel_xl==1)?$prev_xl:$prev_xe;
					$arr['num']=round(($chart_value/($sel_xl==1?10000:100000000)),1);
					$arr['yoy']=$this->chartRate($chart_value,$prev_value);
					$arr['mom']=null;
					$arr['avg']=!empty($chart_xl)?round(($chart_xe/$chart_xl),0):0;
					$prev_avg=!empty($prev_xl)?($prev_xe/$prev_xl):0;
					$arr['avg_yoy']=$this->chartRate($arr['avg'],$prev_avg);
					$arr['avg_mom']=null;
					$arr_sj2_num=$i;//颜色分界值
					if(!$arr['num']){
						break;
					}
					$arr['id']=$year;
					$arr['i']=$i+1;
					$arr_sj2[]=$arr;
					$arr_sort2[]=$arr['i'];
				}
			}
			//倒序数组
			array_multisort($arr_sort2, SORT_DESC, $arr_sj2);
			$arrx=array();
			$arry=array();
			$arryoy=array();
			$arrmom=array();
			$arravg=array();
			$arravgyoy=array();
			$arravgmom=array();
			foreach($arr_sj2 as $k=>$v){
				$arrx[]=$v['id'];
				$arry[]=$v['num'];
				$arryoy[]=$v['yoy'];
				$arrmom[]=$v['mom'];
				$arravg[]=$v['avg'];
				$arravgyoy[]=$v['avg_yoy'];
				$arravgmom[]=$v['avg_mom'];
			}
			$arr=array();
			$arr['x']=$arrx;
			$arr['y']=$arry;
			$arr['yoy']=$arryoy;
			$arr['mom']=$arrmom;
			$arr['avg']=$arravg;
			$arr['avg_yoy']=$arravgyoy;
			$arr['avg_mom']=$arravgmom;
			$return_data['arr_sj2']=$arr;
			$return_data['arr_sj2_num']=$arr_sj2_num;
			if($sel_xl==1){
				$return_data['arr_sj2_dw']='万台';
			}elseif($sel_xl==2){
				$return_data['arr_sj2_dw']='亿元';
			}
			
			//品牌份额变化
			$arr_sj3=array();
			$arr_sjs3=array();
			foreach($datalist as $k=>$v){
				if($sel_xl==1){
					$arr_sj3[$v['pp']]=$arr_sj3[$v['pp']]+$v['xl'];
				}elseif($sel_xl==2){
					$arr_sj3[$v['pp']]=$arr_sj3[$v['pp']]+$v['xe'];
				}	
			}
			arsort($arr_sj3);
			$i=0;
			$Others=0;
			$arr_sj3name=array();
			foreach($arr_sj3 as $k=>$v){
				if($k=='Others' || $k=='others'){
					$Others=$Others+$v;
				}else{
					if($i>9){
						$Others=$Others+$v;
					}else{
						$i++;
						$arr_sjs3[$k]=$v;
						$arr_sj3name[$k]=$i;
					}
				}
			}
			asort($arr_sjs3);
			$arrx=array();
			$arry=array();
			/*if($Others){
				$arrx[]='Others';
				if($sel_xl==1){
					$arry[]=round(($Others/$xl)*100,1);
				}elseif($sel_xl==2){
					$arry[]=round(($Others/$xe)*100,1);
				}
			}*/
			foreach($arr_sjs3 as $k=>$v){
				$arrx[]=$k;
				if($sel_xl==1){
					$arry[]=round(($v/$xl)*100,1);
				}elseif($sel_xl==2){
					$arry[]=round(($v/$xe)*100,1);
				}
			}
			$arrxs=array();
			//if(!in_array($user['phone'],array('13401133225','13401039598','15012345678'))){
			if($user['huiyuan']!=1){
				foreach($arrx as $k=>$v){
					if($arr_sj3name[$v]>3){
						$arrxs[$k]='品牌'.$arr_sj3name[$v];
					}else{
						$arrxs[$k]=$v;
					}
				}
				$arrx=$arrxs;
			}
			
			
			$arr=array();
			$arr['x']=$arrx;
			$arr['y']=$arry;
			$return_data['arr_sj3']=$arr;
			
			//品牌竞争表格：市占率/市占率增减/平均价格/均价变化
			$arr_sj8=array();
			$brand_now=array();
			$brand_last=array();
			foreach($datalist as $v){
				if(empty($v['pp']) || strtolower($v['pp'])=='others'){continue;}
				if(!isset($brand_now[$v['pp']])){$brand_now[$v['pp']]=array('xl'=>0,'xe'=>0);}
				$brand_now[$v['pp']]['xl']+=$v['xl'];
				$brand_now[$v['pp']]['xe']+=$v['xe'];
			}
			foreach($lastdata as $v){
				if(empty($v['pp']) || strtolower($v['pp'])=='others'){continue;}
				if(!isset($brand_last[$v['pp']])){$brand_last[$v['pp']]=array('xl'=>0,'xe'=>0);}
				$brand_last[$v['pp']]['xl']+=$v['xl'];
				$brand_last[$v['pp']]['xe']+=$v['xe'];
			}
			foreach($brand_now as $k=>$v){
				$bn=$v['xl'];$be=$v['xe'];
				$ln=isset($brand_last[$k])?$brand_last[$k]['xl']:0;
				$le=isset($brand_last[$k])?$brand_last[$k]['xe']:0;
				$share_now=!empty($xl)?(($sel_xl==1?$bn:$be)/($sel_xl==1?$xl:$xe))*100:0;
				$share_last=!empty($lastxl)?(($sel_xl==1?$ln:$le)/($sel_xl==1?$lastxl:$lastxe))*100:0;
				$share_c=$share_now-$share_last;
				$price_now=!empty($bn)?$be/$bn:0;
				$price_last=!empty($ln)?$le/$ln:0;
				$price_c=!empty($price_last)?(($price_now-$price_last)/$price_last)*100:0;
				$arr=array();
				$arr['name']=$k;
				$arr['share']=round($share_now,1);
				$arr['share_c']=($share_c>=0?'+':'').round($share_c,1);
				$arr['price']=round($price_now,0);
				$arr['price_c']=($price_c>=0?'+':'').round($price_c,0);
				$arr_sj8[]=$arr;
			}
			usort($arr_sj8,function($a,$b){return $b['share']-$a['share'];});
			//非会员品牌匿名：份额排名>3 显示 品牌N
			if($user['huiyuan']!=1){
				foreach($arr_sj8 as $rk=>$rv){
					$rank=$rk+1;
					if($rank>3){$arr_sj8[$rk]['name']='品牌'.$rank;}
				}
			}
			$return_data['arr_sj8']=$arr_sj8;
			
			//产品结构变化
			//投影技术
			$arr_sj4=array();
			//亮度范围
			$arr_sj5=array();
			foreach($datalist as $k=>$v){
				if($sel_xl==1){
					$arr_sj4[$v['gy1']]=$arr_sj4[$v['gy1']]+$v['xl'];
					$arr_sj5[$v['gy2']]=$arr_sj5[$v['gy2']]+$v['xl'];
				}elseif($sel_xl==2){
					$arr_sj4[$v['gy1']]=$arr_sj4[$v['gy1']]+$v['xe'];
					$arr_sj5[$v['gy2']]=$arr_sj5[$v['gy2']]+$v['xe'];
				}	
			}
			//价格段同比（市占率增减）
			$arr_sj5_last=array();
			foreach($lastdata as $k=>$v){
				if($sel_xl==1){
					$arr_sj5_last[$v['gy2']]=$arr_sj5_last[$v['gy2']]+$v['xl'];
				}elseif($sel_xl==2){
					$arr_sj5_last[$v['gy2']]=$arr_sj5_last[$v['gy2']]+$v['xe'];
				}
			}
				
			if($type!=3 && $type!=13){
				arsort($arr_sj4);
				$arr_sjs4=array();
				foreach($arr_sj4 as $k=>$v){
					$arr=array();
					if($sel_xl==1){
						$arr['value']=$v/10000;
					}elseif($sel_xl==2){
						$arr['value']=$v/100000000;
					}
					$arr['name']=$k;
					$arr_sjs4[]=$arr;
				}
				$return_data['arr_sj4']=$arr_sjs4;
			}
			arsort($arr_sj5);
			$arr_sjs5=array();
			foreach($arr_sj5 as $k=>$v){
				$arr=array();
				if($sel_xl==1){
					$arr['value']=$v/10000;
				}elseif($sel_xl==2){
					$arr['value']=$v/100000000;
				}
				if($type==2){
					$arr['name']=$k.'麦';
				}elseif($type==3){
					$arr['name']=str_replace(array('A-', 'B-', 'C-', 'D-', 'E-', 'F-', '0-499', '500-999', '1000-1499', '1500-1999', '2000-2499', '2000-2999', '2500-2999', '3000-3499', '3000+', '3500-3999', '4000-4499', '4000+'), array('', '', '', '', '', '', '0-0.5k', '0.5-1k', '1-1.5k', '1.5-2k', '2-2.5k', '2-3k', '2.5-3k', '3-3.5k', '3k+', '3.5-4k', '4-4.5k', '4k+'), $k);
					//$arr['name']=substr($k,2);
					//$arr['name']=$k;
				}else{
					$arr['name']=$k;
				}
				//价格段市占率增减（同比）
				$last_v=isset($arr_sj5_last[$k])?$arr_sj5_last[$k]:0;
				if($sel_xl==1){
					$cur_share=!empty($xl)?($v/$xl)*100:0;
					$last_share=!empty($lastxl)?($last_v/$lastxl)*100:0;
				}else{
					$cur_share=!empty($xe)?($v/$xe)*100:0;
					$last_share=!empty($lastxe)?($last_v/$lastxe)*100:0;
				}
				$share_c=$cur_share-$last_share;
				$arr['t3']=($share_c>=0?'+':'').round($share_c,1).'%';
				$arr_sjs5[]=$arr;
			}
			$return_data['arr_sj5']=$arr_sjs5;
			$arr=array();
			if($type==3 || $type==13){
				$arr['t2']='价格段';
			}elseif($type==1){
				$arr['t1']='投影技术';
				$arr['t2']='亮度';
			}elseif($type==2){
				$arr['t1']='屏幕';
				$arr['t2']='麦克风阵列';
			}elseif($type==4){
				$arr['t1']='分辨率';
				$arr['t2']='CPU';
			}elseif($type==14){
				$arr['t1']='平板类型';
				$arr['t2']='屏幕技术';
			}elseif($type==15 || $type==19){
				$arr['t1']='屏幕类型';
				$arr['t2']='光学方案';
			}elseif($type==16){
				$arr['t1']='产品类型';
				$arr['t2']='音频解码';
			}
			$return_data['arr_sj6']=$arr;
			/*echo '<pre>';
			print_r($return_data);exit;*/
		}elseif($type==5 || $type==6 || $type==7 || $type==18){//'5'=>'交互平板','6'=>'数字标牌','7'=>'激光投影','18'=>'小间距LED'
			$sel_ds=I('sel_ds','',dhtmlspecialchars);//5产品场景6企业类型
			if(!$sel_ds){
				$sel_ds='不限';
			}
			$sel_xl=I('sel_xl','',intval);
			if(!$sel_xl){
				$sel_xl=1;//1销量，2销额
			}
			$sel_yd=I('sel_yd','',intval);
			if(!$sel_yd){
				$sel_yd=1;//1季度，2年累计（年度）
			}
			
			if($sel_yd==5){
				//年份和季度
				$nowjd='Q'.$this->nowjd;
				$nownd=$this->nownd;
			}else{
				$a=strtotime($this->nownd.'-'.($this->nowjd*3-2).'-01');//当前季度时间戳
				if($sel_yd==1){
					$aa=$a;
				}else{
					$aa=strtotime('-'.(($sel_yd-1)*3).' month',$a);
				}
				$nowjds=ceil(date('n',$aa) / 3);
				$nowjd='Q'.ceil(date('n',$aa) / 3);
				$nownd=date('Y',$aa);
			}
			$lastnd=$nownd-1;
			//$return_data['arr_sj']="数据截止到".$nownd."年第".$this->nowjd."季度";
		    $return_data['arr_sj']="数据口径为全渠道出货和同比";
			
			if($sel_yd!=5){
				$sql =" AND nd='".$nownd."' AND jd='".$nowjd."'";
				//同比，上一年这个季度
				$sql1 =" AND nd='".$lastnd."' AND jd='".$nowjd."'";
			}elseif($sel_yd==5){
				//季度的数组
				$arr_m=array();
				for($i=0;$i<4;$i++){
					if($i==0){
						$aa=$nowjd;
					}else{
						$aa='Q'.($this->nowjd-$i);
					}
					if($this->nowjd-$i==0){
						break;
					}else{
						$arr_m[]="jd='".$aa."'";
					}
				}
				$tmp=implode(' OR ',$arr_m);
				$sql =" AND nd='".$nownd."' AND (".$tmp.")";//当前年
				$sql1 =" AND nd='".$lastnd."' AND (".$tmp.")";//上一年
			}
			//5产品场景6企业类型
			if($type==5){
				$sqlcj=" AND type2=1";//交互平板excel中含有两个sheet，每个的出货量是相同的；
				$ds=M('shuju_pid')->distinct(true)->where("type='$type'".$sqlcj.$sql)->field('cpcj')->select();
			}else{
				$sqlcj="";
				$ds=M('shuju_pid')->distinct(true)->where("type='$type'".$sqlcj.$sql)->field('xslx')->select();
			}
			$arr_ds=array();
			$arr=array();
			$arr['id']='不限';
			$arr['name']='不限';
			$arr_ds[]=$arr;
			foreach($ds as $k=>$v){
				$arr=array();
				if($type==5){
					$arr['id']=$v['cpcj'];
					$arr['name']=$v['cpcj'];
				}else{
					$arr['id']=$v['xslx'];
					$arr['name']=$v['xslx'];
				}
				$arr_ds[]=$arr;
			}
			$return_data['arr_ds']=$arr_ds;
			//销量数组
			$arr_xl=array('0'=>array('id'=>'1','name'=>'出货量'),'1'=>array('id'=>'2','name'=>'销售额'));
			$return_data['arr_xl']=$arr_xl;
			//月度数组
			$arr_ss2=array();
			$a=strtotime($this->nownd.'-'.($this->nowjd*3-2).'-01');//当前季度时间戳
			for($i=0;$i<4;$i++){
				$j=$i+1;
				if($i==0){
					$aa=$a;
				}else{
					$aa=strtotime('-'.($i*3).' month',$a);
				}
				$nd=date('Y',$aa);
				$jd='Q'.ceil(date('n',$aa) / 3);
				$arr_ss2[$j]=$nd.'/'.$jd;
			}
			$j=$i+1;
			$arr_ss2[$j]='年累计';
			$arr_sss2=array();
			foreach($arr_ss2 as $k=>$v){
				$arr=array();
				$arr['id']=$k;
				$arr['name']=$v;
				$arr_sss2[]=$arr;
			}
			$return_data['arr_yd']=$arr_sss2;
			
			if($sel_ds!='不限'){
				if($type==5){
					$sqlds =" AND cpcj='$sel_ds'";
				}elseif($type==6){
					$sqlds =" AND xslx='$sel_ds'";
				}
			}else{
				$sqlds ='';
			}
			$datalist=M('shuju_pid')->where("type='$type'".$sqlcj.$sql.$sqlds)->order('nd DESC,jd DESC,id DESC')->select();
		    $xl=0;//出货量
			$xe=0;//销售额
			$pjjg=0;//平均价格
			foreach($datalist as $k=>$v){
				if($type==7 || $type==18){
					$v['chl']=$v['chl']/1000;
					$v['xse']=$v['xse']/100;
				}
				$xl=$xl+$v['chl'];
				$xe=$xe+$v['xse'];
			}
			$pjjg=!empty($xl)?round((($xe*1000000)/($xl*1000)),0):0;//数据中的出货量单位为K，销售额为Mn；
			//同比：一般情况下是今年第n月与去年第n月比；环比：表示连续2个单位周期（比如连续两月）内量的变化比
			//此次为同比
			$lastdata=M('shuju_pid')->where("type='$type'".$sqlcj.$sql1.$sqlds)->order('nd DESC,jd DESC,id DESC')->select();
			$lastxl=0;//出货量
			$lastxe=0;//销售额
			$lastpjjg=0;//平均价格
			foreach($lastdata as $k=>$v){
				if($type==7 || $type==18){
					$v['chl']=$v['chl']/1000;
					$v['xse']=$v['xse']/100;
				}
				$lastxl=$lastxl+$v['chl'];
				$lastxe=$lastxe+$v['xse'];
			}
			$lastpjjg=!empty($lastxl)?round((($lastxe*1000000)/($lastxl*1000)),0):0;
			if($xl > $lastxl){
				$cxl=$xl-$lastxl;
				$xlxb='+';//增长
			}else{
				$cxl=$lastxl-$xl;
				$xlxb='-';//减少
			}
			if($xe > $lastxe){
				$cxe=$xe-$lastxe;
				$xexb='+';//增长
			}else{
				$cxe=$lastxe-$xe;
				$xexb='-';//减少
			}
			if($pjjg > $lastpjjg){
				$cpjjg=$pjjg-$lastpjjg;
				$pjjgxb='+';//增长
			}else{
				$cpjjg=$lastpjjg-$pjjg;
				$pjjgxb='-';//减少
			}
			$xlbfb=!empty($lastxl)?$xlxb.round(($cxl/$lastxl)*100,0).'%':'+0%';//销量百分比
			$xebfb=!empty($lastxe)?$xexb.round(($cxe/$lastxe)*100,0).'%':'+0%';//销额百分比
			$pjjgbfb=!empty($lastpjjg)?$pjjgxb.round(($cpjjg/$lastpjjg)*100,0).'%':'+0%';//平均价格百分比
			
			$arr_sj1=array();
			$arr=array();
			$arr['t1']='出货量';
			$arr['t2']=sprintf('%.1f',round(($xl/10),1));//默认是K，改为万
			$arr['t3']=$xlbfb;
			$arr['dw']='万台';
			$arr_sj1[]=$arr;
			/*$arr=array();
			$arr['t1']='销售额';
			$arr['t2']=round(($xe/100),1);//默认是Mn，改为亿
			$arr['t3']=$xebfb;
			$arr['dw']='亿元';
			$arr_sj1[]=$arr;
			$arr=array();
			$arr['t1']='平均价格';
			$arr['t2']=$pjjg;
			$arr['t3']=$pjjgbfb;
			$arr['dw']='元';
			$arr_sj1[]=$arr;*/
			$return_data['arr_sj1']=$arr_sj1;
			
			//市场规模变化
			$arr_sj2=array();
			$arr_sort2=array();
			if($sel_yd!=5){//季度
				$a=strtotime($nownd.'-'.($nowjds*3-2).'-01');//当前季度时间戳
				$arr_sj2_num=7;
				for($i=0;$i<8;$i++){
					if($i==0){
						$aa=$a;
					}else{
						$aa=strtotime('-'.($i*3).' month',$a);
					}
					$nd=date('Y',$aa);
					$jd='Q'.ceil(date('n',$aa) / 3);
					$arr=array();
					$chart_where="type='$type' AND nd='$nd' AND jd='$jd'".$sqlcj.$sqlds;
					$year_where="type='$type' AND nd='".($nd-1)."' AND jd='$jd'".$sqlcj.$sqlds;
					$prev_ts=strtotime('-3 month',$aa);
					$prev_nd=date('Y',$prev_ts);
					$prev_jd='Q'.ceil(date('n',$prev_ts) / 3);
					$prev_where="type='$type' AND nd='$prev_nd' AND jd='$prev_jd'".$sqlcj.$sqlds;
					$chart_xl=M('shuju_pid')->where($chart_where)->sum('chl');
					$chart_xe=M('shuju_pid')->where($chart_where)->sum('xse');
					$year_xl=M('shuju_pid')->where($year_where)->sum('chl');
					$year_xe=M('shuju_pid')->where($year_where)->sum('xse');
					$prev_xl=M('shuju_pid')->where($prev_where)->sum('chl');
					$prev_xe=M('shuju_pid')->where($prev_where)->sum('xse');
					$chart_value=($sel_xl==1)?$chart_xl:$chart_xe;
					$year_value=($sel_xl==1)?$year_xl:$year_xe;
					$prev_value=($sel_xl==1)?$prev_xl:$prev_xe;
					$chart_divisor=($type==7 || $type==18)?10000:($sel_xl==1?10:100);
					$arr['num']=round(($chart_value/$chart_divisor),1);
					$arr['yoy']=$this->chartRate($chart_value,$year_value);
					$arr['mom']=$this->chartRate($chart_value,$prev_value);
					$price_factor=($type==7 || $type==18)?10000:1000;
					$arr['avg']=!empty($chart_xl)?round(($chart_xe*$price_factor/$chart_xl),0):0;
					$year_avg=!empty($year_xl)?($year_xe*$price_factor/$year_xl):0;
					$prev_avg=!empty($prev_xl)?($prev_xe*$price_factor/$prev_xl):0;
					$arr['avg_yoy']=$this->chartRate($arr['avg'],$year_avg);
					$arr['avg_mom']=$this->chartRate($arr['avg'],$prev_avg);
					$arr['id']=$nd.$jd;
					$arr['i']=$i+1;
					$arr_sj2[]=$arr;
					$arr_sort2[]=$arr['i'];
					
					if($nd !=date('Y',$a) && $arr_sj2_num==7){
						$arr_sj2_num=7-$i;//颜色分界值
					}
				}
			}elseif($sel_yd==5){//累计
				for($i=0;$i<8;$i++){
					if($i==0){
						$year=$nownd;
					}else{
						$year=$nownd-$i;
					}
					$arr=array();
					$chart_where="type='$type' AND nd='$year'".$sqlcj.$sqlds;
					$prev_where="type='$type' AND nd='".($year-1)."'".$sqlcj.$sqlds;
					$chart_xl=M('shuju_pid')->where($chart_where)->sum('chl');
					$chart_xe=M('shuju_pid')->where($chart_where)->sum('xse');
					$prev_xl=M('shuju_pid')->where($prev_where)->sum('chl');
					$prev_xe=M('shuju_pid')->where($prev_where)->sum('xse');
					$chart_value=($sel_xl==1)?$chart_xl:$chart_xe;
					$prev_value=($sel_xl==1)?$prev_xl:$prev_xe;
					$chart_divisor=($type==7 || $type==18)?10000:($sel_xl==1?10:100);
					$arr['num']=round(($chart_value/$chart_divisor),1);
					$arr['yoy']=$this->chartRate($chart_value,$prev_value);
					$arr['mom']=null;
					$price_factor=($type==7 || $type==18)?10000:1000;
					$arr['avg']=!empty($chart_xl)?round(($chart_xe*$price_factor/$chart_xl),0):0;
					$prev_avg=!empty($prev_xl)?($prev_xe*$price_factor/$prev_xl):0;
					$arr['avg_yoy']=$this->chartRate($arr['avg'],$prev_avg);
					$arr['avg_mom']=null;
					$arr_sj2_num=$i;//颜色分界值
					if(!$arr['num']){
						break;
					}
					$arr['id']=$year;
					$arr['i']=$i+1;
					$arr_sj2[]=$arr;
					$arr_sort2[]=$arr['i'];
				}
			}
			//倒序数组
			array_multisort($arr_sort2, SORT_DESC, $arr_sj2);
			$arrx=array();
			$arry=array();
			$arryoy=array();
			$arrmom=array();
			$arravg=array();
			$arravgyoy=array();
			$arravgmom=array();
			foreach($arr_sj2 as $k=>$v){
				$arrx[]=$v['id'];
				$arry[]=$v['num'];
				$arryoy[]=$v['yoy'];
				$arrmom[]=$v['mom'];
				$arravg[]=$v['avg'];
				$arravgyoy[]=$v['avg_yoy'];
				$arravgmom[]=$v['avg_mom'];
			}
			$arr=array();
			$arr['x']=$arrx;
			$arr['y']=$arry;
			$arr['yoy']=$arryoy;
			$arr['mom']=$arrmom;
			$arr['avg']=$arravg;
			$arr['avg_yoy']=$arravgyoy;
			$arr['avg_mom']=$arravgmom;
			$return_data['arr_sj2']=$arr;
			$return_data['arr_sj2_num']=$arr_sj2_num;
			if($sel_xl==1){
				$return_data['arr_sj2_dw']='万台';
			}elseif($sel_xl==2){
				$return_data['arr_sj2_dw']='亿元';
			}
			//品牌份额变化
			$arr_sj3=array();
			$arr_sjs3=array();
			foreach($datalist as $k=>$v){
				if($sel_xl==1){
					if($type==7 || $type==18){
						$v['chl']=$v['chl']/1000;
					}
					if($type==5 || $type==7 || $type==18){
						$arr_sj3[$v['pp']]=$arr_sj3[$v['pp']]+$v['chl'];
					}elseif($type==6){
						$arr_sj3[$v['qy']]=$arr_sj3[$v['qy']]+$v['chl'];
					}
				}elseif($sel_xl==2){
					if($type==7 || $type==18){
						$v['xse']=$v['xse']/100;
					}
					if($type==5 || $type==7 || $type==18){
						$arr_sj3[$v['pp']]=$arr_sj3[$v['pp']]+$v['xse'];
					}elseif($type==6){
						$arr_sj3[$v['qy']]=$arr_sj3[$v['qy']]+$v['xse'];
					}
				}	
			}
			arsort($arr_sj3);
			
			$i=0;
			$Others=0;
			$arr_sj3name=array();
			foreach($arr_sj3 as $k=>$v){
				if($k=='Others' || $k=='others'){
					$Others=$Others+$v;
				}else{
					if($i>9){
						$Others=$Others+$v;
					}else{
						$i++;
						$arr_sjs3[$k]=$v;
						$arr_sj3name[$k]=$i;
					}
				}
			}
			asort($arr_sjs3);
			
			$arrx=array();
			$arry=array();
			/*if($Others){
				$arrx[]='Others';
				if($sel_xl==1){
					$arry[]=round(($Others/$xl)*100,1);
				}elseif($sel_xl==2){
					$arry[]=round(($Others/$xe)*100,1);
				}
			}*/
			foreach($arr_sjs3 as $k=>$v){
				$arrx[]=$k;
				if($sel_xl==1){
					$arry[]=round(($v/$xl)*100,1);
				}elseif($sel_xl==2){
					$arry[]=round(($v/$xe)*100,1);
				}
			}
			$arrxs=array();
			//if(!in_array($user['phone'],array('13401133225','13401039598','15012345678'))){
			if($user['huiyuan']!=1){
				foreach($arrx as $k=>$v){
					if($arr_sj3name[$v]>3){
						$arrxs[$k]='品牌'.$arr_sj3name[$v];
					}else{
						$arrxs[$k]=$v;
					}
				}
				$arrx=$arrxs;
			}
			
			$arr=array();
			$arr['x']=$arrx;
			$arr['y']=$arry;
			$return_data['arr_sj3']=$arr;
			//品牌竞争表格：市占率/市占率增减/平均价格/均价变化
			$arr_sj8=array();
			$brand_now=array();
			$brand_last=array();
			foreach($datalist as $v){
				$bk=!empty($v['pp'])?$v['pp']:$v['qy'];
				if(empty($bk) || strtolower($bk)=='others'){continue;}
				if(!isset($brand_now[$bk])){$brand_now[$bk]=array('chl'=>0,'xse'=>0);}
				$brand_now[$bk]['chl']+=$v['chl'];
				$brand_now[$bk]['xse']+=$v['xse'];
			}
			foreach($lastdata as $v){
				$bk=!empty($v['pp'])?$v['pp']:$v['qy'];
				if(empty($bk) || strtolower($bk)=='others'){continue;}
				if(!isset($brand_last[$bk])){$brand_last[$bk]=array('chl'=>0,'xse'=>0);}
				$brand_last[$bk]['chl']+=$v['chl'];
				$brand_last[$bk]['xse']+=$v['xse'];
			}
			foreach($brand_now as $k=>$v){
				$bn=$v['chl'];$be=$v['xse'];
				$ln=isset($brand_last[$k])?$brand_last[$k]['chl']:0;
				$le=isset($brand_last[$k])?$brand_last[$k]['xse']:0;
				$share_now=!empty($xl)?($bn/$xl)*100:0;
				$share_last=!empty($lastxl)?($ln/$lastxl)*100:0;
				$share_c=$share_now-$share_last;
				$price_now=!empty($bn)?($be/$bn)*1000:0;
				$price_last=!empty($ln)?($le/$ln)*1000:0;
				$price_c=!empty($price_last)?(($price_now-$price_last)/$price_last)*100:0;
				$arr=array();
				$arr['name']=$k;
				$arr['share']=round($share_now,1);
				$arr['share_c']=($share_c>=0?'+':'').round($share_c,1);
				$arr['price']=round($price_now,0);
				$arr['price_c']=($price_c>=0?'+':'').round($price_c,0);
				$arr_sj8[]=$arr;
			}
			usort($arr_sj8,function($a,$b){return $b['share']-$a['share'];});
			//非会员品牌匿名：份额排名>3 显示 品牌N
			if($user['huiyuan']!=1){
				foreach($arr_sj8 as $rk=>$rv){
					$rank=$rk+1;
					if($rank>3){$arr_sj8[$rk]['name']='品牌'.$rank;}
				}
			}
			$return_data['arr_sj8']=$arr_sj8;
			//产品结构变化
			//5触控技术6产品类型7亮度范围
			$arr_sj4=array();
			//尺寸范围7分辨率规格
			$arr_sj5=array();
			
			foreach($datalist as $k=>$v){
				if($sel_xl==1){
					if($type==7 || $type==18){
						$v['chl']=$v['chl']/1000;
					}
					if($type==5){
						$arr_sj4[$v['ckjs']]=$arr_sj4[$v['ckjs']]+$v['chl'];
					}elseif($type==6 || $type==7){
						$arr_sj4[$v['cplx']]=$arr_sj4[$v['cplx']]+$v['chl'];
					}
					$arr_sj5[$v['chcd']]=$arr_sj5[$v['chcd']]+$v['chl'];
				}elseif($sel_xl==2){
					if($type==7 || $type==18){
						$v['xse']=$v['xse']/100;
					}
					if($type==5){
						$arr_sj4[$v['ckjs']]=$arr_sj4[$v['ckjs']]+$v['xse'];
					}elseif($type==6 || $type==7){
						$arr_sj4[$v['cplx']]=$arr_sj4[$v['cplx']]+$v['xse'];
					}
					$arr_sj5[$v['chcd']]=$arr_sj5[$v['chcd']]+$v['xse'];
				}	
			}
			//价格段同比（市占率增减）
			$arr_sj5_last=array();
			foreach($lastdata as $k=>$v){
				if($sel_xl==1){
					if($type==7 || $type==18){$v['chl']=$v['chl']/1000;}
					$arr_sj5_last[$v['chcd']]=$arr_sj5_last[$v['chcd']]+$v['chl'];
				}elseif($sel_xl==2){
					if($type==7 || $type==18){$v['xse']=$v['xse']/100;}
					$arr_sj5_last[$v['chcd']]=$arr_sj5_last[$v['chcd']]+$v['xse'];
				}
			}
			if($type==5){//用第二个表
			    $datalist2=M('shuju_pid')->where("type='$type' AND type2=2".$sql.$sqlds)->order('nd DESC,jd DESC,id DESC')->select();
				$arr_sj4=array();
				foreach($datalist2 as $k=>$v){
					if($sel_xl==1){
						$arr_sj4[$v['ckjs']]=$arr_sj4[$v['ckjs']]+$v['chl'];
					}elseif($sel_xl==2){
						$arr_sj4[$v['ckjs']]=$arr_sj4[$v['ckjs']]+$v['xse'];
					}	
				}
			}
			if($arr_sj4){
				arsort($arr_sj4);
				$arr_sjs4=array();
				foreach($arr_sj4 as $k=>$v){
					$arr=array();
					if($sel_xl==1){
						$arr['value']=$v/10;
					}elseif($sel_xl==2){
						$arr['value']=$v/100;
					}
					$arr['name']=$k;
					$arr_sjs4[]=$arr;
				}
				$return_data['arr_sj4']=$arr_sjs4;
			}
			
			if($arr_sj5){
				arsort($arr_sj5);
				$arr_sjs5=array();
				foreach($arr_sj5 as $k=>$v){
					$arr=array();
					if($sel_xl==1){
						$arr['value']=$v/10;
					}elseif($sel_xl==2){
						$arr['value']=$v/100;
					}
					if($type==5 || $type==6){
						$arr['name']=str_replace('"','',$k).'"';
					}else{
						$arr['name']=$k;
					}
					//价格段市占率增减（同比）
					$last_v=isset($arr_sj5_last[$k])?$arr_sj5_last[$k]:0;
					if($sel_xl==1){
						$cur_share=!empty($xl)?($v/$xl)*100:0;
						$last_share=!empty($lastxl)?($last_v/$lastxl)*100:0;
					}else{
						$cur_share=!empty($xe)?($v/$xe)*100:0;
						$last_share=!empty($lastxe)?($last_v/$lastxe)*100:0;
					}
					$share_c=$cur_share-$last_share;
					$arr['t3']=($share_c>=0?'+':'').round($share_c,1).'%';
					$arr_sjs5[]=$arr;
				}
				$return_data['arr_sj5']=$arr_sjs5;
			}
			$arr=array();
			if($type==5){
				$arr['t1']='触控技术';
			    $arr['t2']='尺寸';
			}elseif($type==6){
				$arr['t1']='产品类型';
			    $arr['t2']='尺寸';
			}elseif($type==7){
				$arr['t1']='亮度';
				$arr['t2']='分辨率';
			}elseif($type==18){
				$arr['t1']='';
				$arr['t2']='尺寸';
			}
			$return_data['arr_sj6']=$arr;
		}elseif($type==9 || $type==11 || $type==12 || $type==17 || $type==21){//'9'=>'电视供应链','10'=>'手机供应链','11'=>'LED供应链','12'=>'电子纸供应链','17'=>'显示器供应链'
			$sel_ds=I('sel_ds','',dhtmlspecialchars);//9细分 '1'=>'TV品牌出货','2'=>'TV代工出货','3'=>'TV面板出货'
			if(!$sel_ds && $type==9){
				$sel_ds='2';
			}
			if(!$sel_ds && $type==12){
				$sel_ds='4';
			}
			if(!$sel_ds && $type==17){
				$sel_ds='8';
			}
			if(!$sel_ds && $type==11){
				$sel_ds='9';
			}
			if(!$sel_ds && $type==21){
				$sel_ds='12';
			}
			$sel_xl=I('sel_xl','',intval);
			if(!$sel_xl){
				$sel_xl=1;//1销量，2销额
			}
			$sel_yd=I('sel_yd','',intval);
			if(!$sel_yd){
				$sel_yd=1;//1月度，2年累计（年度）
			}
			if($sel_ds==4){
				$this->nowjd91=$this->nowjd121;
				$this->nownd91=$this->nownd121;
			}
			if($sel_ds==5){
				$this->nowtime3=$this->nowtime4;
			}
			if($sel_ds==8 || $sel_ds==10){
				$this->nowtime3=$this->nowtime5;
			}
			if($sel_ds==9){
				$this->nowjd91=$this->nowjd119;
				$this->nownd91=$this->nownd119;
			}
			if($sel_ds==12){
				$this->nowtime3=$this->nowtime7;
			}
			if($sel_ds==1 || $sel_ds==4 || $sel_ds==9){//品牌出货和模组出货按季度
			    if($sel_yd==5){
					//年份和季度
					$nowjd='Q'.$this->nowjd91;
					$nownd=$this->nownd91;
				}else{
					$a=strtotime($this->nownd91.'-'.($this->nowjd91*3-2).'-01');//当前季度时间戳
					if($sel_yd==1){
						$aa=$a;
					}else{
						$aa=strtotime('-'.(($sel_yd-1)*3).' month',$a);
					}
					$nowjds=ceil(date('n',$aa) / 3);
					$nowjd='Q'.ceil(date('n',$aa) / 3);
					$nownd=date('Y',$aa);
				}
				$lastnd=$nownd-1;
				
				if($sel_yd!=5){
					$sql =" AND nd='".$nownd."' AND jd='".$nowjd."'";
					//同比，上一年这个季度
					$sql1 =" AND nd='".$lastnd."' AND jd='".$nowjd."'";
				}elseif($sel_yd==5){
					//季度的数组
					$arr_m=array();
					for($i=0;$i<4;$i++){
						if($i==0){
							$aa=$nowjd;
						}else{
							$aa='Q'.($this->nowjd91-$i);
						}
						if($this->nowjd91-$i==0){
							break;
						}else{
							$arr_m[]="jd='".$aa."'";
						}
					}
					$tmp=implode(' OR ',$arr_m);
					$sql =" AND nd='".$nownd."' AND (".$tmp.")";//当前年
					$sql1 =" AND nd='".$lastnd."' AND (".$tmp.")";//上一年
				}
			}else{
				if($sel_yd==13){
					//年份和月份
					if($sel_ds==2){
						$nowyd=date('M',$this->nowtime3);
						//上一年这个月
						$lastm=date('M',strtotime('-1 year',$this->nowtime3));
					}else{
						$nowyd=date('y.m',$this->nowtime3);
						//上一年这个月
						$lastm=date('y.m',strtotime('-1 year',$this->nowtime3));
					}
					$nownd=date('Y',$this->nowtime3);
					$lasty=date('Y',strtotime('-1 year',$this->nowtime3));
				}else{
					$sel_yd_s=$sel_yd-1;
					$nowtime=strtotime('-'.$sel_yd_s.' month',$this->nowtime3);
					if($sel_ds==2){
						$nowyd=date('M',$nowtime);
						//上一年这个月
						$lastm=date('M',strtotime('-1 year',$nowtime));
					}else{
						$nowyd=date('y.m',$nowtime);
						//上一年这个月
						$lastm=date('y.m',strtotime('-1 year',$nowtime));
					}
					$nownd=date('Y',$nowtime);
					$lasty=date('Y',strtotime('-1 year',$nowtime));
				}
			}
			
			
			//$return_data['arr_sj']="数据截止到".date('Y年m月',$this->nowtime);
			if($sel_ds==10 || $type==9){//2025-01-02电视供应链去掉qiejing
				$return_data['arr_sj']="";
			}else{
				$return_data['arr_sj']="数据口径为线上监测和同比";
			}
			if($sel_ds==1 || $sel_ds==4 || $sel_ds==9){//品牌出货按季度
				if($sel_yd!=5){
					$sql =" AND nd='".$nownd."' AND jd='".$nowjd."'";
					//同比，上一年这个季度
					$sql1 =" AND nd='".$lastnd."' AND jd='".$nowjd."'";
				}elseif($sel_yd==5){
					//季度的数组
					$arr_m=array();
					for($i=0;$i<4;$i++){
						if($i==0){
							$aa=$nowjd;
						}else{
							$aa='Q'.($this->nowjd91-$i);
						}
						if($this->nowjd91-$i==0){
							break;
						}else{
							$arr_m[]="jd='".$aa."'";
						}
					}
					$tmp=implode(' OR ',$arr_m);
					$sql =" AND nd='".$nownd."' AND (".$tmp.")";//当前年
					$sql1 =" AND nd='".$lastnd."' AND (".$tmp.")";//上一年
				}
			}else{
				if($sel_yd!=13){
					$sql =" AND nd='".$nownd."' AND yd='".$nowyd."'";
					//同比，上一年这个月
					$sql1 =" AND nd='".$lasty."' AND yd='".$lastm."'";
				}elseif($sel_yd==13){
					//月的数组
					$arr_m1=array();
					$arr_m2=array();
					$la=strtotime($nownd.'-'.date('m',$this->nowtime3).'-01');//当前月时间戳
					$la1=strtotime($nownd.'-01-01');//当前年1月时间戳
					for($i=0;$i<12;$i++){
						if($i==0){
							$aa=$la;
						}else{
							$aa=strtotime('-'.$i.' month',$la);
						}
						if($aa < $la1){
							break;
						}else{
							if($sel_ds==2){
								$m1=date('M',$aa);//今年月时间戳
								$m2=date('M',strtotime('-1 year',$aa));//去年月时间戳
							}else{
								$m1=date('y.m',$aa);//今年月时间戳
								$m2=date('y.m',strtotime('-1 year',$aa));//去年月时间戳
							}
							$arr_m1[]="yd='".$m1."'";
							$arr_m2[]="yd='".$m2."'";
						}
					}
					$tmp1=implode(' OR ',$arr_m1);
					$tmp2=implode(' OR ',$arr_m2);
					$sql ="AND nd='".$nownd."' AND (".$tmp1.")";//当前年
					$sql1 ="AND nd='".$lasty."' AND (".$tmp2.")";//上一年
				}
			}
			//'1'=>'TV品牌出货','2'=>'TV代工出货','3'=>'TV面板出货'
			$arr_ds=array();
			foreach($this->arr_datascate3[$type] as $k=>$v){
				if(($type==17 && $k!=7) || $type!=17){
					$arr=array();
					$arr['id']=$k;
					$arr['name']=str_replace('TV','',$v);
					$arr_ds[]=$arr;
				}
			}
			$return_data['arr_ds']=$arr_ds;
			//销量数组
			//$arr_xl=array('0'=>array('id'=>'1','name'=>'出货量'));
			$arr_xl=array('0'=>array('id'=>'1','name'=>'销量'),'1'=>array('id'=>'2','name'=>'销额'));
			$return_data['arr_xl']=$arr_xl;
			//月度数组
			$arr_ss1=array();
			if($sel_ds==1 || $sel_ds==4 || $sel_ds==9){//品牌出货按季度
				$a=strtotime($this->nownd91.'-'.($this->nowjd91*3-2).'-01');//当前季度时间戳
				for($i=0;$i<4;$i++){
					$j=$i+1;
					if($i==0){
						$aa=$a;
					}else{
						$aa=strtotime('-'.($i*3).' month',$a);
					}
					$nd=date('Y',$aa);
					$jd='Q'.ceil(date('n',$aa) / 3);
					$arr_ss1[$j]=$nd.'/'.$jd;
				}
			}else{
				for($i=0;$i<12;$i++){
					$j=$i+1;
					$arr_ss1[$j]=date('Y/m',strtotime('-'.$i.' month',$this->nowtime3));
			    }
			}
			$j=$i+1;
			$arr_ss1[$j]='年累计';
			$arr_sss1=array();
			foreach($arr_ss1 as $k=>$v){
				$arr=array();
				$arr['id']=$k;
				$arr['name']=$v;
				$arr_sss1[]=$arr;
			}
			$return_data['arr_yd']=$arr_sss1;
			
			if($sel_ds){
				$sqlds =" AND type2='$sel_ds'";
			}else{
				$sqlds ='';
			}
			$datalist=M('shuju_tvs')->where("type='$type'".$sql.$sqlds)->order('nd DESC,jd DESC,yd DESC,id DESC')->select();
			
			//echo '<pre>';print_r($datalist);exit;
		    $xl=0;//出货量
			if($sel_ds==5 || $sel_ds==8 || $sel_ds==10 || $sel_ds==12){
				$xe=0;//销售额 
				$pjjg=0;//平均价格 
			}
			
			foreach($datalist as $k=>$v){
				if($v['type2']==11){
					$v['ch']=$v['ch']/1000;
				}
				$xl=$xl+$v['ch'];
				if($sel_ds==5 || $sel_ds==8 || $sel_ds==10 || $sel_ds==12){
				   $xe=$xe+$v['xe'];
				}
			}
			if($sel_ds==5 || $sel_ds==8 || $sel_ds==10 || $sel_ds==12){
				$pjjg=!empty($xl)?round(($xe/$xl),0): 0;
			}
			//同比：一般情况下是今年第n月与去年第n月比；环比：表示连续2个单位周期（比如连续两月）内量的变化比
			//此次为同比
			$lastdata=M('shuju_tvs')->where("type='$type'".$sql1.$sqlds)->order('nd DESC,jd DESC,yd DESC,id DESC')->select();
			$lastxl=0;//出货量
			if($sel_ds==5 || $sel_ds==8 || $sel_ds==10 || $sel_ds==12){
				$lastxe=0;//销售额 
				$lastpjjg=0;//平均价格 
			}
			foreach($lastdata as $k=>$v){
				if($v['type2']==11){
					$v['ch']=$v['ch']/1000;
				}
				$lastxl=$lastxl+$v['ch'];
				if($sel_ds==5 || $sel_ds==8 || $sel_ds==10 || $sel_ds==12){
					$lastxe=$lastxe+$v['xe'];
				}
			}
			if($sel_ds==5 || $sel_ds==8 || $sel_ds==10 || $sel_ds==12){
				$lastpjjg=!empty($lastxl)?round(($lastxe/$lastxl),0): 0;
			}
			if($xl > $lastxl){
				$cxl=$xl-$lastxl;
				$xlxb='+';//增长
			}else{
				$cxl=$lastxl-$xl;
				$xlxb='-';//减少
			}
			if($sel_ds==5 || $sel_ds==8 || $sel_ds==10 || $sel_ds==12){
				if($xe > $lastxe){
					$cxe=$xe-$lastxe;
					$xexb='+';//增长
				}else{
					$cxe=$lastxe-$xe;
					$xexb='-';//减少
				}
				if($pjjg > $lastpjjg){
					$cpjjg=$pjjg-$lastpjjg;
					$pjjgxb='+';//增长
				}else{
					$cpjjg=$lastpjjg-$pjjg;
					$pjjgxb='-';//减少
				}
			}
			$xlbfb=!empty($lastxl)?$xlxb.round(($cxl/$lastxl)*100,0).'%':'+0%';//销量百分比
			if($sel_ds==5 || $sel_ds==8 || $sel_ds==10 || $sel_ds==12){
				$xebfb=!empty($lastxe)?$xexb.round(($cxe/$lastxe)*100,0).'%':'+0%';//销额百分比
				$pjjgbfb=!empty($lastpjjg)?$pjjgxb.round(($cpjjg/$lastpjjg)*100,0).'%':'+0%';//平均价格百分比
			}
			$arr_sj1=array();
			if($sel_ds!=5 && $sel_ds!=8 && $sel_ds!=10 && $sel_ds!=12){
				$arr=array();
				$arr['t1']='出货量';
				$arr['t2']=round(($xl/10),1);//默认是K，改为万
				$arr['t3']=$xlbfb;
				$arr['dw']='万台';
				$arr_sj1[]=$arr;
			}else{
				$arr=array();
				$arr['t1']='销量';
				$arr['t2']=round($xl/10000,1);//默认是个，改为万
				$arr['t3']=$xlbfb;
				$arr['dw']='万台';
				$arr_sj1[]=$arr;
				$arr=array();
				$arr['t1']='销额';
				$arr['t2']=round($xe/100000000,1);//默认是元，改为亿元
				$arr['t3']=$xebfb;
				$arr['dw']='亿元';
				$arr_sj1[]=$arr;
				$arr=array();
				$arr['t1']='平均价格';
				$arr['t2']=$pjjg;
				$arr['t3']=$pjjgbfb;
				$arr['dw']='元';
				$arr_sj1[]=$arr;
			}
			//echo '<pre>';print_r($arr_sj1);exit;
			$return_data['arr_sj1']=$arr_sj1;
			
			//市场规模变化
			$arr_sj2=array();
			$arr_sort2=array();
			if($sel_ds==1 || $sel_ds==4 || $sel_ds==9){//品牌出货按季度
				if($sel_yd!=5){//季度
					$a=strtotime($nownd.'-'.($nowjds*3-2).'-01');//当前季度时间戳
					$arr_sj2_num=7;
					for($i=0;$i<8;$i++){
						if($i==0){
							$aa=$a;
						}else{
							$aa=strtotime('-'.($i*3).' month',$a);
						}
						$nd=date('Y',$aa);
						$jd='Q'.ceil(date('n',$aa) / 3);
						$arr=array();
						$chart_where="type='$type' AND nd='$nd' AND jd='$jd'".$sqlds;
						$year_where="type='$type' AND nd='".($nd-1)."' AND jd='$jd'".$sqlds;
						$prev_ts=strtotime('-3 month',$aa);
						$prev_nd=date('Y',$prev_ts);
						$prev_jd='Q'.ceil(date('n',$prev_ts) / 3);
						$prev_where="type='$type' AND nd='$prev_nd' AND jd='$prev_jd'".$sqlds;
						$chart_xl=M('shuju_tvs')->where($chart_where)->sum('ch');
						$chart_xe=M('shuju_tvs')->where($chart_where)->sum('xe');
						$year_xl=M('shuju_tvs')->where($year_where)->sum('ch');
						$year_xe=M('shuju_tvs')->where($year_where)->sum('xe');
						$prev_xl=M('shuju_tvs')->where($prev_where)->sum('ch');
						$prev_xe=M('shuju_tvs')->where($prev_where)->sum('xe');
						$chart_value=($sel_xl==1)?$chart_xl:$chart_xe;
						$year_value=($sel_xl==1)?$year_xl:$year_xe;
						$prev_value=($sel_xl==1)?$prev_xl:$prev_xe;
						$small_unit=($sel_ds==5 || $sel_ds==8 || $sel_ds==10 || $sel_ds==11 || $sel_ds==12);
						$chart_divisor=$sel_xl==1?($small_unit?10000:10):($small_unit?100000000:100);
						$arr['num']=round(($chart_value/$chart_divisor),1);
						$arr['yoy']=$this->chartRate($chart_value,$year_value);
						$arr['mom']=$this->chartRate($chart_value,$prev_value);
						$has_price=($sel_ds==5 || $sel_ds==8 || $sel_ds==10 || $sel_ds==12);
						$arr['avg']=$has_price && !empty($chart_xl)?round(($chart_xe/$chart_xl),0):null;
						$year_avg=$has_price && !empty($year_xl)?($year_xe/$year_xl):0;
						$prev_avg=$has_price && !empty($prev_xl)?($prev_xe/$prev_xl):0;
						$arr['avg_yoy']=$has_price?$this->chartRate($arr['avg'],$year_avg):null;
						$arr['avg_mom']=$has_price?$this->chartRate($arr['avg'],$prev_avg):null;
						$arr['id']=$nd.$jd;
						$arr['i']=$i+1;
						$arr_sj2[]=$arr;
						$arr_sort2[]=$arr['i'];
						
						if($nd !=date('Y',$a) && $arr_sj2_num==7){
							$arr_sj2_num=7-$i;//颜色分界值
						}
					}
				}elseif($sel_yd==5){//累计
					for($i=0;$i<8;$i++){
						if($i==0){
							$year=$nownd;
						}else{
							$year=$nownd-$i;
						}
						$arr=array();
						$chart_xl=M('shuju_tvs')->where("type='$type' AND nd='$year'".$sqlds)->sum('ch');
						$prev_xl=M('shuju_tvs')->where("type='$type' AND nd='".($year-1)."'".$sqlds)->sum('ch');
						$arr['num']=round(($chart_xl/10),1);
						$arr['yoy']=$this->chartRate($chart_xl,$prev_xl);
						$arr['mom']=null;
						$arr['avg']=null;
						$arr['avg_yoy']=null;
						$arr['avg_mom']=null;
						$arr_sj2_num=$i;//颜色分界值
						if(!$arr['num']){
							break;
						}
						$arr['id']=$year;
						$arr['i']=$i+1;
						$arr_sj2[]=$arr;
						$arr_sort2[]=$arr['i'];
					}
				}
			}else{
				if($sel_yd!=13){//月度
					$a=strtotime(date('Y-m',$nowtime).'-01');//当前月时间戳
					$arr_sj2_num=11;
					for($i=0;$i<12;$i++){
						if($i==0){
							$aa=$a;
						}else{
							$aa=strtotime('-'.$i.' month',$a);
						}
						if($sel_ds==2){
							$month=date('M',$aa);
						}else{
							$month=date('y.m',$aa);
						}
						$year=date('Y',$aa);
						$arr=array();
						$year_ts=strtotime('-1 year',$aa);
						$prev_ts=strtotime('-1 month',$aa);
						$year_month=($sel_ds==2)?date('M',$year_ts):date('y.m',$year_ts);
						$prev_month=($sel_ds==2)?date('M',$prev_ts):date('y.m',$prev_ts);
						$chart_where="type='$type' AND nd='$year' AND yd='$month'".$sqlds;
						$year_where="type='$type' AND nd='".date('Y',$year_ts)."' AND yd='$year_month'".$sqlds;
						$prev_where="type='$type' AND nd='".date('Y',$prev_ts)."' AND yd='$prev_month'".$sqlds;
						$chart_xl=M('shuju_tvs')->where($chart_where)->sum('ch');
						$chart_xe=M('shuju_tvs')->where($chart_where)->sum('xe');
						$year_xl=M('shuju_tvs')->where($year_where)->sum('ch');
						$year_xe=M('shuju_tvs')->where($year_where)->sum('xe');
						$prev_xl=M('shuju_tvs')->where($prev_where)->sum('ch');
						$prev_xe=M('shuju_tvs')->where($prev_where)->sum('xe');
						$chart_value=($sel_xl==1)?$chart_xl:$chart_xe;
						$year_value=($sel_xl==1)?$year_xl:$year_xe;
						$prev_value=($sel_xl==1)?$prev_xl:$prev_xe;
						$small_unit=($sel_ds==5 || $sel_ds==8 || $sel_ds==10 || $sel_ds==11 || $sel_ds==12);
						$chart_divisor=$sel_xl==1?($small_unit?10000:10):($small_unit?100000000:100);
						$arr['num']=round(($chart_value/$chart_divisor),1);
						$arr['yoy']=$this->chartRate($chart_value,$year_value);
						$arr['mom']=$this->chartRate($chart_value,$prev_value);
						$has_price=($sel_ds==5 || $sel_ds==8 || $sel_ds==10 || $sel_ds==12);
						$arr['avg']=$has_price && !empty($chart_xl)?round(($chart_xe/$chart_xl),0):null;
						$year_avg=$has_price && !empty($year_xl)?($year_xe/$year_xl):0;
						$prev_avg=$has_price && !empty($prev_xl)?($prev_xe/$prev_xl):0;
						$arr['avg_yoy']=$has_price?$this->chartRate($arr['avg'],$year_avg):null;
						$arr['avg_mom']=$has_price?$this->chartRate($arr['avg'],$prev_avg):null;
						$arr['id']=date('y/m',$aa);
						$arr['i']=$i+1;
						$arr_sj2[]=$arr;
						$arr_sort2[]=$arr['i'];
						if($year !=date('Y',$nowtime) && $arr_sj2_num==11){
							$arr_sj2_num=11-$i;//颜色分界值
						}
					}
				}elseif($sel_yd==13){//累计
					for($i=0;$i<12;$i++){
						if($i==0){
							$year=$nownd;
						}else{
							$year=$nownd-$i;
						}
						$arr=array();
						$chart_where="type='$type' AND nd='$year'".$sqlds;
						$prev_where="type='$type' AND nd='".($year-1)."'".$sqlds;
						$chart_xl=M('shuju_tvs')->where($chart_where)->sum('ch');
						$chart_xe=M('shuju_tvs')->where($chart_where)->sum('xe');
						$prev_xl=M('shuju_tvs')->where($prev_where)->sum('ch');
						$prev_xe=M('shuju_tvs')->where($prev_where)->sum('xe');
						$chart_value=($sel_xl==1)?$chart_xl:$chart_xe;
						$prev_value=($sel_xl==1)?$prev_xl:$prev_xe;
						$small_unit=($sel_ds==5 || $sel_ds==8 || $sel_ds==10 || $sel_ds==11 || $sel_ds==12);
						$chart_divisor=$sel_xl==1?($small_unit?10000:10):($small_unit?100000000:100);
						$arr['num']=round(($chart_value/$chart_divisor),1);
						$arr['yoy']=$this->chartRate($chart_value,$prev_value);
						$arr['mom']=null;
						$has_price=($sel_ds==5 || $sel_ds==8 || $sel_ds==10 || $sel_ds==12);
						$arr['avg']=$has_price && !empty($chart_xl)?round(($chart_xe/$chart_xl),0):null;
						$prev_avg=$has_price && !empty($prev_xl)?($prev_xe/$prev_xl):0;
						$arr['avg_yoy']=$has_price?$this->chartRate($arr['avg'],$prev_avg):null;
						$arr['avg_mom']=null;
						$arr_sj2_num=$i;//颜色分界值
						
						if(!$arr['num']){
							break;
						}
						$arr['id']=$year;
						$arr['i']=$i+1;
						$arr_sj2[]=$arr;
						$arr_sort2[]=$arr['i'];
					}
				}
			}
			//倒序数组
			array_multisort($arr_sort2, SORT_DESC, $arr_sj2);
			$arrx=array();
			$arry=array();
			$arryoy=array();
			$arrmom=array();
			$arravg=array();
			$arravgyoy=array();
			$arravgmom=array();
			foreach($arr_sj2 as $k=>$v){
				$arrx[]=$v['id'];
				$arry[]=$v['num'];
				$arryoy[]=$v['yoy'];
				$arrmom[]=$v['mom'];
				$arravg[]=$v['avg'];
				$arravgyoy[]=$v['avg_yoy'];
				$arravgmom[]=$v['avg_mom'];
			}
			$arr=array();
			$arr['x']=$arrx;
			$arr['y']=$arry;
			$arr['yoy']=$arryoy;
			$arr['mom']=$arrmom;
			$arr['avg']=$arravg;
			$arr['avg_yoy']=$arravgyoy;
			$arr['avg_mom']=$arravgmom;
			$return_data['arr_sj2']=$arr;
			$return_data['arr_sj2_num']=$arr_sj2_num;
			if($sel_xl==1){
				$return_data['arr_sj2_dw']='万台';
			}elseif($sel_xl==2){
				$return_data['arr_sj2_dw']='亿元';
			}
			//echo '<pre>';print_r($return_data);exit;
			//品牌份额变化
			if($sel_ds!=6 && $sel_ds!=11){
				if($sel_ds!=10){
					$arr_sj3=array();
					$arr_sjs3=array();
					foreach($datalist as $k=>$v){
						if($sel_xl==1){
							if($sel_ds==1 || $sel_ds==5 || $sel_ds==8 || $sel_ds==9 || $sel_ds==12){
								$arr_sj3[$v['gy1']]=$arr_sj3[$v['gy1']]+$v['ch'];
							}elseif($sel_ds==2 || $sel_ds==4){
								$arr_sj3[$v['odm']]=$arr_sj3[$v['odm']]+$v['ch'];
							}else{
								$arr_sj3[$v['gy2']]=$arr_sj3[$v['gy2']]+$v['ch'];
							}
						}elseif($sel_xl==2){
							if($sel_ds==5 || $sel_ds==8 || $sel_ds==12){
								$arr_sj3[$v['gy1']]=$arr_sj3[$v['gy1']]+$v['xe'];
							}
							//$arr_sj3[$v['gy2']]=$arr_sj3[$v['gy2']]+$v['xe'];//暂不用
						}	
					}
					arsort($arr_sj3);
					$i=0;
					$Others=0;
					foreach($arr_sj3 as $k=>$v){
						if($v >0){
							if($k=='Others' || $k=='others'){
								$Others=$Others+$v;
							}else{
								if($i>9){
									$Others=$Others+$v;
								}else{
									$i++;
									$arr_sjs3[$k]=$v;
								}
							}
						}
					}
					asort($arr_sjs3);
					$arrx=array();
					$arry=array();
					/*if($Others){
						$arrx[]='Others';
						if($sel_xl==1){
							$arry[]=round(($Others/$xl)*100,1);
						}elseif($sel_xl==2){
							$arry[]=round(($Others/$xe)*100,1);
						}
					}*/
					$i=count($arr_sjs3);
					foreach($arr_sjs3 as $k=>$v){
						//if(!in_array($user['phone'],array('13401133225','13401039598','15012345678'))){
						if($user['huiyuan']!=1){
							if($i>3){
								$arrx[]='品牌'.$i;
							}else{
								$arrx[]=$k;
							}
							$i--;
						}else{
							$arrx[]=$k;
						}
						
						if($sel_xl==1){
							$arry[]=round(($v/$xl)*100,1);
						}elseif($sel_xl==2){
							$arry[]=round(($v/$xe)*100,1);
						}
					}
					$arr=array();
					$arr['x']=$arrx;
					$arr['y']=$arry;
					$return_data['arr_sj3']=$arr;
				}
				//品牌竞争表格：市占率/市占率增减/平均价格/均价变化
				$arr_sj8=array();
				$brand_now=array();
				$brand_last=array();
				$has_xe=in_array($sel_ds,array(5,8,10,12));
				if($sel_ds!=6 && $sel_ds!=10 && $sel_ds!=11 && !($sel_xl==2 && !$has_xe)){
					if($sel_ds==1 || $sel_ds==5 || $sel_ds==8 || $sel_ds==9 || $sel_ds==12){
						$brand_field='gy1';
					}elseif($sel_ds==2 || $sel_ds==4){
						$brand_field='odm';
					}else{
						$brand_field='gy2';
					}
					foreach($datalist as $v){
						if(empty($v[$brand_field]) || strtolower($v[$brand_field])=='others'){continue;}
						$ch=$v['ch'];
						if($v['type2']==11){$ch=$ch/1000;}
						if(!isset($brand_now[$v[$brand_field]])){$brand_now[$v[$brand_field]]=array('ch'=>0,'xe'=>0);}
						$brand_now[$v[$brand_field]]['ch']+=$ch;
						$brand_now[$v[$brand_field]]['xe']+=$v['xe'];
					}
					foreach($lastdata as $v){
						if(empty($v[$brand_field]) || strtolower($v[$brand_field])=='others'){continue;}
						$ch=$v['ch'];
						if($v['type2']==11){$ch=$ch/1000;}
						if(!isset($brand_last[$v[$brand_field]])){$brand_last[$v[$brand_field]]=array('ch'=>0,'xe'=>0);}
						$brand_last[$v[$brand_field]]['ch']+=$ch;
						$brand_last[$v[$brand_field]]['xe']+=$v['xe'];
					}
					foreach($brand_now as $k=>$v){
						$bn=$v['ch'];$be=$v['xe'];
						$ln=isset($brand_last[$k])?$brand_last[$k]['ch']:0;
						$le=isset($brand_last[$k])?$brand_last[$k]['xe']:0;
						if($sel_xl==2 && $has_xe){
							$share_now=!empty($xe)?($be/$xe)*100:0;
							$share_last=!empty($lastxe)?($le/$lastxe)*100:0;
						}else{
							$share_now=!empty($xl)?($bn/$xl)*100:0;
							$share_last=!empty($lastxl)?($ln/$lastxl)*100:0;
						}
						$share_c=$share_now-$share_last;
						$arr=array();
						$arr['name']=$k;
						$arr['share']=round($share_now,1);
						$arr['share_c']=($share_c>=0?'+':'').round($share_c,1);
						if($has_xe){
							$price_now=!empty($bn)?$be/$bn:0;
							$price_last=!empty($ln)?$le/$ln:0;
							$price_c=!empty($price_last)?(($price_now-$price_last)/$price_last)*100:0;
							$arr['price']=round($price_now,0);
							$arr['price_c']=($price_c>=0?'+':'').round($price_c,0);
						}else{
							$arr['price']='--';
							$arr['price_c']='--';
						}
						$arr_sj8[]=$arr;
					}
					usort($arr_sj8,function($a,$b){return $b['share']-$a['share'];});
					//非会员品牌匿名：份额排名>3 显示 品牌N
					if($user['huiyuan']!=1){
						foreach($arr_sj8 as $rk=>$rv){
							$rank=$rk+1;
							if($rank>3){$arr_sj8[$rk]['name']='品牌'.$rank;}
						}
					}
				}
				$return_data['arr_sj8']=$arr_sj8;
				//产品结构变化
				//1技术2Domestic/Overseas 3尺寸段
				$arr_sj4=array();
				//1尺寸段2size 3无
				$arr_sj5=array();
				foreach($datalist as $k=>$v){
					if($sel_xl==1){
						if($sel_ds==1){
							$arr_sj4[$v['odm']]=$arr_sj4[$v['odm']]+$v['ch'];
							$arr_sj5[$v['gy2']]=$arr_sj5[$v['gy2']]+$v['ch'];
						}elseif($sel_ds==4){
							$arr_sj4[$v['gy2']]=$arr_sj4[$v['gy2']]+$v['ch'];
							//$arr_sj5[$v['chc']]=$arr_sj5[$v['chc']]+$v['ch'];
						}elseif($sel_ds==5 || $sel_ds==9){
							$arr_sj4[$v['chc']]=$arr_sj4[$v['chc']]+$v['ch'];
						}elseif($sel_ds==8 || $sel_ds==12){
							$arr_sj4[$v['gy2']]=$arr_sj4[$v['gy2']]+$v['ch'];
							$arr_sj5[$v['chc']]=$arr_sj5[$v['chc']]+$v['ch'];
						}else{
							$arr_sj4[$v['gy1']]=$arr_sj4[$v['gy1']]+$v['ch'];
							if($sel_ds==2){
								$arr_sj5[$v['chc']]=$arr_sj5[$v['chc']]+$v['ch'];
							}
						}
					}elseif($sel_xl==2){
						if($sel_ds==5){
							$arr_sj4[$v['chc']]=$arr_sj4[$v['chc']]+$v['xe'];
						}elseif($sel_ds==8 || $sel_ds==12){
							$arr_sj4[$v['gy2']]=$arr_sj4[$v['gy2']]+$v['xe'];
							$arr_sj5[$v['chc']]=$arr_sj5[$v['chc']]+$v['xe'];
						}elseif($sel_ds==10){
							$arr_sj4[$v['gy1']]=$arr_sj4[$v['gy1']]+$v['xe'];
						}
						/*$arr_sj4[$v['gy1']]=$arr_sj4[$v['gy1']]+$v['xe'];
						if($sel_ds==2){
							$arr_sj5[$v['chc']]=$arr_sj5[$v['chc']]+$v['xe'];
						}*/
					}	
				}
				//尺寸段同比（市占率增减）
				$arr_sj5_last=array();
				foreach($lastdata as $k=>$v){
					$ch=$v['ch'];
					if($v['type2']==11){$ch=$ch/1000;}
					if($sel_xl==1){
						if($sel_ds==1){
							$arr_sj5_last[$v['gy2']]=$arr_sj5_last[$v['gy2']]+$ch;
						}elseif($sel_ds==2 || $sel_ds==8 || $sel_ds==12){
							$arr_sj5_last[$v['chc']]=$arr_sj5_last[$v['chc']]+$ch;
						}
					}elseif($sel_xl==2){
						if($sel_ds==8 || $sel_ds==12){
							$arr_sj5_last[$v['chc']]=$arr_sj5_last[$v['chc']]+$v['xe'];
						}
					}
				}
				arsort($arr_sj4);
				$arr_sjs4=array();
				foreach($arr_sj4 as $k=>$v){
					$arr=array();
					if($sel_xl==1){
						$arr['value']=$v/10;
					}elseif($sel_xl==2){
						$arr['value']=$v/100;
					}
					if($sel_ds==3){
						$arr['name']=str_replace('"','',$k).'"';
					}else{
						if($sel_ds==2){
							if($k=='Domestic'){
								$arr['name']='国内';
							}elseif($k=='Oversea'){
								$arr['name']='海外';
							}
						}else{
							$arr['name']=$k;
						}
					}
					$arr_sjs4[]=$arr;
				}
				$return_data['arr_sj4']=$arr_sjs4;
				
				//2尺寸段数组
				arsort($arr_sj5);
				$arr_sjs5=array();
				$arr_chcd=array();
				foreach($arr_sj5 as $k=>$v){
					$arr=array();
					if($sel_xl==1){
						$arr['value']=$v/10;
					}elseif($sel_xl==2){
						$arr['value']=$v/100;
					}
					if($sel_ds==2){
						if($k<32){
							$arr_chcd['32-"']=$arr_chcd['32-"']+$arr['value'];
						}elseif($k >= 35 && $k <= 40){
							$arr_chcd['35-40"']=$arr_chcd['35-40"']+$arr['value'];
						}elseif($k >= 41 && $k <= 45){
							$arr_chcd['41-45"']=$arr_chcd['41-45"']+$arr['value'];
						}elseif($k >= 46 && $k <= 50){
							$arr_chcd['46-50"']=$arr_chcd['46-50"']+$arr['value'];
						}elseif($k > 75){
							$arr_chcd['75+"']=$arr_chcd['75+"']+$arr['value'];
						}else{
							$arr_chcd[$k.'"']=$arr['value'];
						}
					}else{
						if($sel_ds==12){
							$arr['name']=$k;
						}else{
							$arr['name']=str_replace('"','',$k).'"';
						}
						//尺寸段市占率增减（同比）
						$last_v=isset($arr_sj5_last[$k])?$arr_sj5_last[$k]:0;
						if($sel_xl==1){
							$cur_share=!empty($xl)?($v/$xl)*100:0;
							$last_share=!empty($lastxl)?($last_v/$lastxl)*100:0;
						}else{
							$cur_share=!empty($xe)?($v/$xe)*100:0;
							$last_share=!empty($lastxe)?($last_v/$lastxe)*100:0;
						}
						$share_c=$cur_share-$last_share;
						$arr['t3']=($share_c>=0?'+':'').round($share_c,1).'%';
						$arr_sjs5[]=$arr;
					}
				}
				if($sel_ds==2){
					arsort($arr_chcd);
					$arr_chcd_last=array();
					foreach($arr_sj5_last as $k=>$v){
						$val=$v/10;
						if($k<32){
							$arr_chcd_last['32-"']=$arr_chcd_last['32-"']+$val;
						}elseif($k >= 35 && $k <= 40){
							$arr_chcd_last['35-40"']=$arr_chcd_last['35-40"']+$val;
						}elseif($k >= 41 && $k <= 45){
							$arr_chcd_last['41-45"']=$arr_chcd_last['41-45"']+$val;
						}elseif($k >= 46 && $k <= 50){
							$arr_chcd_last['46-50"']=$arr_chcd_last['46-50"']+$val;
						}elseif($k > 75){
							$arr_chcd_last['75+"']=$arr_chcd_last['75+"']+$val;
						}else{
							$arr_chcd_last[$k.'"']=$val;
						}
					}
					foreach($arr_chcd as $k=>$v){
						$arr=array();
						$arr['value']=$v;
						$arr['name']=$k;
						//尺寸段市占率增减（同比）
						$last_v=isset($arr_chcd_last[$k])?$arr_chcd_last[$k]:0;
						$cur_share=!empty($xl)?($v*10/$xl)*100:0;
						$last_share=!empty($lastxl)?($last_v*10/$lastxl)*100:0;
						$share_c=$cur_share-$last_share;
						$arr['t3']=($share_c>=0?'+':'').round($share_c,1).'%';
						$arr_sjs5[]=$arr;
					}
				}
				$return_data['arr_sj5']=$arr_sjs5;
				
				$arr=array();
				if($sel_ds==1){
					$arr['t1']='技术';
					$arr['t2']='尺寸';
				}elseif($sel_ds==2){
					$arr['t1']='国内/海外';
					$arr['t2']='尺寸';
				}elseif($sel_ds==3){
					$arr['t1']='尺寸';
				}elseif($sel_ds==4){
					$arr['t1']='应用场景';
					//$arr['t2']='尺寸';
				}elseif($sel_ds==5){
					$arr['t1']='产品类型';
				}elseif($sel_ds==8){
					$arr['t1']='产品类型';
					$arr['t2']='屏幕尺寸';
				}elseif($sel_ds==9){
					$arr['t1']='尺寸';
				}elseif($sel_ds==10){
					$arr['t1']='地区';
				}elseif($sel_ds==12){
					$arr['t1']='游戏本';
					$arr['t2']='屏幕尺寸';
				}
				$return_data['arr_sj6']=$arr;
			}
		}
		/* echo '<pre>';
		print_r($return_data);exit; */
		echo json_encode($return_data);
	}
	//浏览记录显示
	public function getliulanlist(){
		$uid=I('uid','',intval);
		$p=I('p','',intval);//分页
		$page=$p?$p:1;
		if($page==1){
			$limit=($page-1)*20;
			$lm=20;
		}else{
			$page=$page+1;
			$limit=($page-1)*10;
			$lm=10;
		}
		$sql="uid='$uid'";
		$datalist = array();
		$datalist = M('llhistory')->where($sql)->field('name,url,type,addtime')->order("addtime DESC")->limit($limit,$lm)->select();
		foreach($datalist as $k=>$v){
			$v['addtime']=date('Y-m-d H:i:s',$v['addtime']);
			$datalist[$k]=$v;
		}
		$return_data=array();
		//是否为最后一页
		$count=M('llhistory')->where($sql)->count();
		if($count <= ($limit+$lm)){
			$return_data['lastpage']=1;
		}else{
			$return_data['lastpage']=0;
		}
		$return_data['status'] = 1;
		$return_data['datalist'] = $datalist;
		echo json_encode($return_data);
	}
    //浏览记录
	public function getliulan(){
		$uid=I('uid','',intval);
		$name=I('name','',dhtmlspecialchars);
		$url=I('url','',dhtmlspecialchars);
		$type=I('type','',intval);
		if($uid && $name){
			$arr=array();
			$arr['uid']=$uid;
			$arr['name']=$name;
			$arr['type']=$type;
			$arr['url']=$url;
			$arr['addtime']=time();
			M('llhistory')->add($arr);
		}
	}
	//收藏或点赞显示
	public function getscdzlist(){
		$type=I('type','',intval);//1收藏2点赞
		$uid=I('uid','',intval);
		$p=I('p','',intval);//分页
		$page=$p?$p:1;
		$limit=($page-1)*10;
		$sql="lt_news_cz.uid='$uid' AND lt_news_cz.type='$type' AND lt_news.isrecommand=1";
		$datalist = array();
		$datalist = M('news_cz')->join('lt_news ON lt_news_cz.nid=lt_news.id')->where($sql)->field('lt_news.*')->order("lt_news_cz.addtime DESC")->limit($limit,10)->select();
		foreach($datalist as $k=>$v){
			$v['addtime']=date('Y-m-d',$v['addtime']);
			$v['type']=$this->arr_newstype[$v['type']];
			if($v['pic']){
				$v['pic']=C('SITEURL').$v['pic'];
			}
			$datalist[$k]=$v;
		}
		$return_data=array();
		//是否为最后一页
		$count=M('news_cz')->join('lt_news ON lt_news_cz.nid=lt_news.id')->where($sql)->count();
		if($count <= ($limit+10)){
			$return_data['lastpage']=1;
		}else{
			$return_data['lastpage']=0;
		}
		$return_data['status'] = 1;
		$return_data['datalist'] = $datalist;
		echo json_encode($return_data);
	}
	//收藏或点赞
	public function getscdz(){
		$uid=I('uid','',intval);
		$nid=I('nid','',intval);
		$type=I('type','',intval);//1收藏2点赞
		if($uid && $nid && $type){
			//查询是否收藏或点赞
			$data=M('news_cz')->where("uid='$uid' AND nid='$nid' AND type='$type'")->find();
			if($data){//取消
			    M('news_cz')->where("id='".$data['id']."'")->delete();
			}else{
				$arr=array();
				$arr['uid']=$uid;
				$arr['nid']=$nid;
				$arr['type']=$type;
				$arr['addtime']=time();
				M('news_cz')->add($arr);
			}
			$return_data=array();
			//收藏或点赞
			$arr=array();
			$arr['sc']=M('news_cz')->where("uid='$uid' AND nid='$nid' AND type=1")->count();
			$arr['dz']=M('news_cz')->where("uid='$uid' AND nid='$nid' AND type=2")->count();
			$arr['scnum']=M('news_cz')->where("nid='$nid' AND type=1")->count();
			$arr['dznum']=M('news_cz')->where("nid='$nid' AND type=2")->count();
			$return_data['scdz'] = $arr;
			echo json_encode($return_data);
		}
	}
	//关于洛图
	public function getgylt(){
		$return_data=array();
		$data=M('setting')->where("k='sys_about'")->find();
		$data['v'] = preg_replace('/&lt;img([\s\S]*?)src\=&quot;\/Uploads([\s\S]*?)&quot;([\s\S]*?)&gt;/i','&lt;img\\1 style=&quot;max-width:100%&quot; src\=&quot;'.C('SITEURL').'/Uploads\\2&quot;\\3&gt;', $data['v']);
		    $data['v'] = preg_replace('/&lt;a([\s\S]*?)href\=&quot;\/Uploads([\s\S]*?)&quot;([\s\S]*?)&gt;/i','&lt;a\\1href\=&quot;'.C('SITEURL').'/Uploads\\2&quot;\\3&gt;', $data['v']);
		    $data['v'] = preg_replace('/&lt;video([\s\S]*?)src\=&quot;\/Uploads([\s\S]*?)&quot;([\s\S]*?)&gt;([\s\S]*?)&lt;source([\s\S]*?)src\=&quot;\/Uploads([\s\S]*?)&quot;([\s\S]*?)&gt;([\s\S]*?)&lt;\/video&gt;/i','&lt;video\\1src\=&quot;'.C('SITEURL').'/Uploads\\2&quot;\\3&gt;\\4&lt;source\\5src\=&quot;'.C('SITEURL').'/Uploads\\6&quot;\\7&gt;\\8&lt;\/video&gt;', $data['v']);
		    $data['v'] = preg_replace('/&lt;video([\s\S]*?)&lt;source([\s\S]*?)src\=&quot;\/Uploads([\s\S]*?)&quot;([\s\S]*?)&gt;([\s\S]*?)&lt;\/video&gt;/i','&lt;video\\1&lt;source\\2src\=&quot;'.C('SITEURL').'/Uploads\\3&quot;\\4&gt;\\5&lt;\/video&gt;', $data['v']);
		
		$data['v']=htmlspecialchars_decode(stripslashes($data['v']));
		$return_data['status'] = 1;
		$return_data['data'] = $data;
		echo json_encode($return_data);
	}
	public function httpRequest($url,$data='',$method='GET'){
		$curl = curl_init(); 
		curl_setopt($curl, CURLOPT_URL,$url); 
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); 
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); 
		curl_setopt($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']); 
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); 
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1); 
		if($method=='POST')
		{
			curl_setopt($curl, CURLOPT_POST, 1);
			if ($data !='')
			{
				curl_setopt($curl, CURLOPT_POSTFIELDS,$data); 
			}
		}
		curl_setopt($curl, CURLOPT_TIMEOUT, 30); 
		curl_setopt($curl, CURLOPT_HEADER, 0); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		$result = curl_exec($curl); 
		curl_close($curl); 
		return $result;
	}
	public function test(){
		$return_data = array();
		$appid = 'wx4d4ce62d4d883da5';
		$secret = 'f689586f43b54eeeb12a4d453110df14';
		$js_code = I('code','',dhtmlspecialchars);
		if(empty($js_code)){
			$return_data['status']=0;
			$return_data['msg']='手机号授权参数错误';
			$this->ajaxReturn($return_data);
		}
		$weixin_json = httpget("https://api.weixin.qq.com/cgi-bin/token?appid=".$appid."&secret=".$secret."&grant_type=client_credential");
		$weixin_json = json_decode($weixin_json);
		$access_token=$weixin_json->access_token;
		if(empty($access_token)){
			$return_data['status']=0;
			$return_data['msg']='获取微信 access_token 失败';
			$return_data['weixin']=$weixin_json;
			$this->ajaxReturn($return_data);
		}
		$data=array();
		$data['code']=$js_code;
		$res=$this->httpRequest('https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token='.$access_token,json_encode($data),'POST');
		$res_json=json_decode($res);
		if(empty($res_json) || $res_json->errcode!=0 || empty($res_json->phone_info)){
			$return_data['status']=0;
			$return_data['msg']='未获取手机号，请重试';
			$return_data['weixin']=$res_json ? $res_json : $res;
			$this->ajaxReturn($return_data);
		}
		$return_data['status']=1;
		$return_data['phone_info']=$res_json->phone_info;
		$this->ajaxReturn($return_data);
	}
}
