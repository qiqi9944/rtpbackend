<?php
namespace System\Controller;
use Think\Controller;
class IndexController extends CheckController {
	public function _initialize(){
		parent::_initialize();
	}
	//框架首页
    public function index(){
        $this->display();
    }
	
	//欢迎页
	public function welcome(){
		//小程序显示数据月份和年份，查看前两个月的数据
		//两个月之前的时间戳
		$this->nowtime=strtotime('-2 month');
		//当前季度的前两个季度，六个月前的季度
		$this->nownd = date('Y',strtotime('-6 month'));
		$this->nowjd = ceil(date('n',strtotime('-6 month')) / 3);
		//智能硬件IOT
		//年份和月份
		$nowyd=date('y.m',$this->nowtime);
		$nowyd4=date('Y.m',$this->nowtime);
		$nownd=date('Y',$this->nowtime);
		
		//商用显示PID
		$return_data['arr_jg2']="数据截止到".$this->nownd."年第".$this->nowjd."季度";
		//年份和季度
		$nowjd='Q'.$this->nowjd;
		$nownd2=$this->nownd;
		//大尺寸显示TVS
		//年份和月份
		$nowyd2=date('M',$this->nowtime);
		$nowyd3=date('y.m',$this->nowtime);
		
		//数据统计
		$arr_sj=array();
		foreach($this->arr_datascate2 as $k=>$v){
			foreach($v as $lk=>$lv){
				if($k==1){
					$arr=array();
					$arr['id']=$lk;
					$arr['name']=$lv;
					$arr['sjtime']=date('Y年m月',$this->nowtime);
					$arr['num']=M('shuju_iot')->where("type='$lk' AND (yd='".$nowyd."' OR yd='".$nowyd4."')")->count();
					$arr_sj[]=$arr;
				}elseif($k==2){
					$arr=array();
					$arr['id']=$lk;
					$arr['name']=$lv;
					$arr['sjtime']=$this->nownd."年第".$this->nowjd."季度";
					if($lk==5){
						$arr['num']=M('shuju_pid')->where("type='$lk' AND type2=1 AND nd='".$nownd2."' AND jd='".$nowjd."'")->count();
					}else{
						$arr['num']=M('shuju_pid')->where("type='$lk' AND nd='".$nownd2."' AND jd='".$nowjd."'")->count();
					}
					$arr_sj[]=$arr;
				}elseif($k==3){
					$arr=array();
					$arr['id']=$lk;
					$arr['name']=$lv;
					$arr['sjtime']=date('Y年m月',$this->nowtime);
					if($lk==9){
						$num1=M('shuju_tvs')->where("type='$lk' AND type2=1 AND nd='".$nownd."' AND jd='".$nowjd."'")->count();
						$num2=M('shuju_tvs')->where("type='$lk' AND type2!=1 AND nd='".$nownd."' AND (yd='".$nowyd2."' OR yd='".$nowyd3."')")->count();
						if($num1 && $num2){
							$arr['num']=$num1+$num2;
						}else{
							$arr['num']=0;
						}
					}else{
						$arr['num']=M('shuju_tvs')->where("type='$lk' AND nd='".$nownd."' AND (yd='".$nowyd2."' OR yd='".$nowyd3."')")->count();
					}
					$arr_sj[]=$arr;
				}
			}
		}
		$this->arr_sj=$arr_sj;
		$arr_day=array();
		//新增用户数
		$arr_user=array();
		//小程序每日浏览量
		$arr_liulan=array();
		$nowday=strtotime(date('Y-m-d'));
		for($i=14;$i>=0;$i--){
			$now=strtotime('-'.$i.' day',$nowday);
			$next=strtotime('+1 day',$now);
			$arr_day[]=date('m-d',$now);
			$arr_user[]=M('user')->where("type=2 AND status>0 AND addtime >= '$now' AND addtime < '$next'")->count();
			$arr_liulan[]=M('llhistory')->where("addtime >= '$now' AND addtime < '$next'")->count();
		}
		$this->arr_day="'".implode("','",$arr_day)."'";
		$this->arr_user=implode(",",$arr_user);
		$this->arr_liulan=implode(",",$arr_liulan);
        $this->display();
    }
	//个人信息
	public function myinfo(){
	    $data=$this->hdadmin;
		if($data['lanmu']){
			$data['lanmu']=explode(',',$data['lanmu']);
		}
		$this->data=$data;
		$this->myinfo=1;
		$this->display('User:adminadd');
	}
	public function domyinfo(){
		$id=$this->hdadmin['id'];
		$arr=array();
		$arr['username']=I('username','',dhtmlspecialchars);
		if(!$arr['username']){
			$this->error("请输入用户名！");
		}
		$password=I('password','',dhtmlspecialchars);
		$password1=I('password1','',dhtmlspecialchars);
		if($password || $password1){
			if($password != $password1){
				$this->error("两次密码不一致！");
			}
			$arr['password']=md5($password);
		}
		$arr['name']=I('name','',dhtmlspecialchars);
		if(!$arr['name']){
			$this->error("请输入姓名！");
		}
		$arr['phone']=I('phone','',dhtmlspecialchars);
		if(!$arr['phone']){
			$this->error("请输入手机号码！");
		}
		$is_exsit=M('user')->where("username ='".$arr['username']."' AND type=1 AND id !='$id'")->find();
		if($is_exsit){
			$this->error("该用户名已存在，请更换！");
		}
		M('user')->where("id='$id'")->save($arr);
		$this->success('保存成功！');
		
	}
	
}