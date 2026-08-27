<?php
namespace System\Controller;
use Think\Controller;
class UserController extends CheckController {
	public function _initialize(){
		parent::_initialize();
	}
	//管理员
	public function admin(){
		if(!in_array('4',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
		$sql="type=1 AND status > 0";
		$this->keywords=I('keywords','',dhtmlspecialchars);
		$this->sel_status=I('sel_status','',intval);
		if($this->keywords){
			$sql .=" AND (username LIKE '%".$this->keywords."%' OR name LIKE '%".$this->keywords."%')";
		}
		if($this->sel_status){
			$sql .=" AND status='".$this->sel_status."'";
		}
		//分页
		$count=M('user')->where($sql)->count();
		$page=new \Think\Page($count,10);
		$page->rollPage=5;
		if(!empty($this->keywords)){
			 $Page->parameter['keywords']   =   $this->keywords;
		}
		if(!empty($this->sel_status)){
			 $Page->parameter['sel_status']   =   $this->sel_status;
		}
		$show=$page->show();
		$this->page=$show;
		
		$datalist=M('user')->where($sql)->order('addtime DESC')->limit($page->firstRow.','.$page->listRows)->select();
		foreach($datalist as $k=>$v){
			$lanmu=explode(',',$v['lanmu']);
			$lm=array();
			foreach($lanmu as $lk=>$lv){
				$lm[]=$this->arr_lm[$lv];
			}
			$v['lm']=implode('，',$lm);
			$datalist[$k]=$v;						
		}
		$this->assign('datalist',$datalist);
		$this->assign('count',$count);
        $this->display();
    }
	public function adminadd(){
		if(!in_array('4',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
		$id=I('id','',intval);
		if($id){
			$data=M('user')->where("id='$id' AND type=1")->find();
			if(!$data){
				$this->error("该用户不存在！");
			}
			$data['lanmu']=explode(',',$data['lanmu']);
			$this->data=$data;
		}
        $this->display();
	}
	public function doadminadd(){
		if(!in_array('4',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
		$id=I('id','',intval);
		if($id){
			$data=M('user')->where("id='$id' AND type=1")->find();
			if(!$data){
				$this->error("该用户不存在！");
			}
		}
		$arr=array();
		$arr['type']=1;
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
		}else{
			if(!$id){
				$this->error("请输入密码！");
			}
		}
		$arr['name']=I('name','',dhtmlspecialchars);
		if(!$arr['name']){
			$this->error("请输入姓名！");
		}
		$arr['phone']=I('phone','',dhtmlspecialchars);
		if(!$arr['phone']){
			$this->error("请输入手机号码！");
		}
		$lanmu=I('lanmu','',dhtmlspecialchars);
		$arr['lanmu']=implode(',',$lanmu);
		$arr['status']=I('status','',intval);
		if($id){
			$is_exsit=M('user')->where("username ='".$arr['username']."' AND type=1 AND status>0 AND id !='$id'")->find();
			if($is_exsit){
				$this->error("该用户名已存在，请更换！");
			}
			M('user')->where("id='$id'")->save($arr);
		}else{
			$is_exsit=M('user')->where("username ='".$arr['username']."' AND type=1 AND status>0")->find();
			if($is_exsit){
				$this->error("该用户名已存在，请更换！");
			}
			$arr['addtime']=time();
			M('user')->add($arr);
		}
		$this->success("保存成功！");
	}
	public function admindel(){
		if(!in_array('4',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
		$id=I('id','',intval);
		$data=M('user')->where("id='$id' AND type=1")->find();
		if(!$data){
			$this->error("该用户不存在！");
		}
		M('user')->where("id='$id'")->setfield('status','-1');
		$this->success("删除成功！");
	}
	
	//用户
	public function user(){
		if(!in_array('3',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
		$sql="type=2 AND status > 0";
		$this->keywords=I('keywords','',dhtmlspecialchars);
		$this->sel_status=I('sel_status','',intval);
		$this->sel_huiyuan=I('sel_huiyuan','',intval);
		if($this->keywords){
			$sql .=" AND (username LIKE '%".$this->keywords."%' OR name LIKE '%".$this->keywords."%' OR phone ='".$this->keywords."')";
		}
		if($this->sel_status){
			$sql .=" AND status='".$this->sel_status."'";
		}
		if($this->sel_huiyuan==1){
			$sql .=" AND huiyuan=1";
		}elseif($this->sel_huiyuan==2){
			$sql .=" AND huiyuan<>1";
		}
		//分页
		$count=M('user')->where($sql)->count();
		$page=new \Think\Page($count,10);
		$page->rollPage=5;
		if(!empty($this->keywords)){
			 $Page->parameter['keywords']   =   $this->keywords;
		}
		if(!empty($this->sel_status)){
			 $Page->parameter['sel_status']   =   $this->sel_status;
		}
		$show=$page->show();
		$this->page=$show;
		
		$datalist=M('user')->where($sql)->order('addtime DESC')->limit($page->firstRow.','.$page->listRows)->select();
		/*foreach($datalist as $k=>$v){
			$datalist[$k]=$v;						
		}*/
		$this->assign('datalist',$datalist);
		$this->assign('count',$count);
        $this->display();
    }
	public function useradd(){
		if(!in_array('3',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
		$id=I('id','',intval);
		if($id){
			$data=M('user')->where("id='$id' AND type=2")->find();
			if(!$data){
				$this->error("该用户不存在！");
			}
			$this->data=$data;
		}
        $this->display();
	}
	public function douseradd(){
		if(!in_array('3',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
		$id=I('id','',intval);
		if($id){
			$data=M('user')->where("id='$id' AND type=2")->find();
			if(!$data){
				$this->error("该用户不存在！");
			}
		}
		$arr=array();
		$arr['type']=2;
		$arr['companyname']=I('companyname','',dhtmlspecialchars);
		$arr['name']=I('name','',dhtmlspecialchars);
		$arr['phone']=I('phone','',dhtmlspecialchars);
		if($arr['phone']){
		    if(!preg_match("/^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(17[0-9]{1})|(18[0-9]{1})|(19[0-9]{1}))+\d{8})$/",$arr['phone'])){
				$this->error("请输入正确的手机号码！");
			}
		}
		$arr['zhiwu']=I('zhiwu','',dhtmlspecialchars);
		$arr['email']=I('email','',dhtmlspecialchars);
		if($arr['email']){
		    if(!preg_match("/^([\s\S]+?)@([\s\S]+)(\.(\w{1,5}))$/",$arr['email'])){
				$this->error("请输入正确的邮箱！");
			}
		}
		$arr['huiyuan']=I('huiyuan','',intval);
		$arr['status']=I('status','',intval);
		if($id){
			if($arr['phone']){
				$is_exsit=M('user')->where("type=2 AND phone='".$arr['phone']."' AND status>0 AND id!='$id'")->find();
				if($is_exsit){
					$this->error("手机号已存在！");
				}
			}
			M('user')->where("id='$id'")->save($arr);
		}else{
			$this->error("参数错误！");
		}
		$this->success("保存成功！");
	}
}