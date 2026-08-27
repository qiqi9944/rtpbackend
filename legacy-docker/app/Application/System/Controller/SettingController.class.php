<?php
namespace System\Controller;
use Think\Controller;
class SettingController extends CheckController {
	public function _initialize(){
		parent::_initialize();
	}
	//框架首页
    public function index(){
		if(!in_array('6',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
		$this->sys_name=M('setting')->where("k='sys_name'")->getField('v');
		$this->sys_url=M('setting')->where("k='sys_url'")->getField('v');
		$this->sys_logo=M('setting')->where("k='sys_logo'")->getField('v');
		$this->sys_about=M('setting')->where("k='sys_about'")->getField('v');
        $this->display();
    }
	public function doindex(){
		if(!in_array('6',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
		$sys_name=I('sys_name','',dhtmlspecialchars);
		$arr=array();
		$arr['v']=$sys_name;
		$oldsys_name=M('setting')->field('v')->where("k='sys_name'")->find();
		if($oldsys_name){
			M('setting')->where("k='sys_name'")->save($arr);
		}else{
			 $arr['k']='sys_name';
			 M('setting')->add($arr);
		}
		
		$sys_url=I('sys_url','',dhtmlspecialchars);
		if($sys_url){
			$url=substr($sys_url,0,4);
			$url=strtolower($url);
			if($url !='http'){
				$sys_url='http://'.$sys_url;
			}
		}
		$arr=array();
		$arr['v']=$sys_url;
		$oldsys_url=M('setting')->field('v')->where("k='sys_url'")->find();
		if($oldsys_url){
			M('setting')->where("k='sys_url'")->save($arr);
		}else{
			 $arr['k']='sys_url';
			 M('setting')->add($arr);
		}
		
		$sys_logo=I('sys_logo','',dhtmlspecialchars);
		$arr=array();
		$arr['v']=$sys_logo;
		$oldsys_logo=M('setting')->field('v')->where("k='sys_logo'")->find();
		if($oldsys_logo){
			M('setting')->where("k='sys_logo'")->save($arr);
		}else{
			 $arr['k']='sys_logo';
			 M('setting')->add($arr);
		}
		
		$sys_about=I('sys_about','',dhtmlspecialchars);
		$arr=array();
		$arr['v']=$sys_about;
		$oldsys_about=M('setting')->field('v')->where("k='sys_about'")->find();
		if($oldsys_about){
			M('setting')->where("k='sys_about'")->save($arr);
		}else{
			 $arr['k']='sys_about';
			 M('setting')->add($arr);
		}
		
		$this->success("保存成功！");
	}
	public function history(){
		if(!in_array('5',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
		$sql="1";
		$this->starttime=I('starttime','',dhtmlspecialchars);
		$this->endtime=I('endtime','',dhtmlspecialchars);
		$this->sel_sf=I('sel_sf','',intval);
		$this->keywords=I('keywords','',dhtmlspecialchars);
		if($this->starttime){
			$starttime=strtotime($this->starttime);
			$sql .=" AND lt_history.addtime >='$starttime'";
		}
		if($this->endtime){
			$endtime=strtotime($this->endtime.' 23:59:59');
			$sql .=" AND lt_history.addtime <'$endtime'";
		}
		if($this->sel_sf){
			$sql .=" AND lt_user.type = '".$this->sel_sf."'";
		}
		if($this->keywords){
			$sql .=" AND (lt_user.name LIKE '%".$this->keywords."%' OR lt_user.username LIKE '%".$this->keywords."%')";
		}
		//分页
		$count=M('history')->join("lt_user ON lt_history.aid=lt_user.id")->where($sql)->count();
		$page=new \Think\Page($count,10);
		$page->rollPage=5;
		if(!empty($this->starttime)){
			 $Page->parameter['starttime']   =   $this->starttime;
		}
		if(!empty($this->endtime)){
			 $Page->parameter['endtime']   =   $this->endtime;
		}
		if(!empty($this->keywords)){
			 $Page->parameter['keywords']   =   $this->keywords;
		}
		$show=$page->show();
		$this->page=$show;
		
		$datalist=M('history')->join("lt_user ON lt_history.aid=lt_user.id")->where($sql)->field('lt_history.*,lt_user.name as aname,lt_user.type as atype')->order('lt_history.addtime DESC')->limit($page->firstRow.','.$page->listRows)->select();
		foreach($datalist as $k=>$v){
			$v['atype']=$this->arr_usertype[$v['atype']];
			if($v['type']==0){
				$v['content']='登录';
			}elseif($v['type']==1){
				
			}
			$datalist[$k]=$v;
		}
		$this->assign('datalist',$datalist);
		$this->assign('count',$count);
        $this->display();
	}
	public function banner(){
		if(!in_array('7',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
		
		$sql="1";
		
		$this->starttime=I('starttime','',dhtmlspecialchars);
		$this->endtime=I('endtime','',dhtmlspecialchars);
		$this->keywords=I('keywords','',dhtmlspecialchars);
		$this->sel_xs=I('sel_xs','',intval);
		$this->sel_wz=I('sel_wz','',intval);
		if($this->starttime){
			$starttime=strtotime($this->starttime);
			$sql .=" AND addtime >='$starttime'";
		}
		if($this->endtime){
			$endtime=strtotime($this->endtime.' 23:59:59');
			$sql .=" AND addtime <'$endtime'";
		}
		if($this->keywords){
			$sql .=" AND name LIKE '%".$this->keywords."%'";
		}
		if($this->sel_xs==1){
			$sql .=" AND isrecommand=1";
		}elseif($this->sel_xs==2){
			$sql .=" AND isrecommand=0";
		}
		if($this->sel_wz){
			$sql .=" AND find_in_set('".$this->sel_wz."',weizhi)";
		}
		//分页
		$count=M('banner')->where($sql)->count();
		$page=new \Think\Page($count,10);
		$page->rollPage=5;
		if(!empty($this->starttime)){
			 $Page->parameter['starttime']   =   $this->starttime;
		}
		if(!empty($this->endtime)){
			 $Page->parameter['endtime']   =   $this->endtime;
		}
		if(!empty($this->keywords)){
			 $Page->parameter['keywords']   =   $this->keywords;
		}
		if(!empty($this->sel_xs)){
			 $Page->parameter['sel_xs']   =   $this->sel_xs;
		}
		if(!empty($this->sel_wz)){
			 $Page->parameter['sel_wz']   =   $this->sel_wz;
		}
		$show=$page->show();
		$this->page=$show;
		
		$datalist=M('banner')->where($sql)->order('displayorder DESC,addtime DESC')->limit($page->firstRow.','.$page->listRows)->select();
		foreach($datalist as $k=>$v){
			$v['fbr']=M('user')->where("id='".$v['aid']."'")->getfield('name');
			$weizhi=explode(',',$v['weizhi']);
			$wz=array();
			foreach($weizhi as $lk=>$lv){
				$wz[]=$this->arr_bannerwz[$lv];
			}
			$v['wz']=implode('，',$wz);
			$datalist[$k]=$v;
		}
		$this->assign('datalist',$datalist);
		$this->assign('count',$count);
        $this->display();
	}
	public function bannersave(){
		$displayorder=I('displayorder');
		foreach($displayorder as $k=>$v){
			$arr=array();
			$arr['displayorder']=$v;
			M('banner')->where("id='$k'")->save($arr);
		}
		$this->success("提交成功！");
	}
	public function banneradd(){
		if(!in_array('7',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
		$id=I('id','',intval);
		if($id){
			$data=M('banner')->where("id='$id'")->find();
			if(!$data){
				$this->error("该Banner不存在！",1);
			}
			$data['weizhi']=explode(',',$data['weizhi']);
			$this->data=$data;
		}
		$this->display();
	}
	public function dobanneradd(){
		if(!in_array('7',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！");
		}
		$id=I('id','',intval);
		if($id){
			$data=M('banner')->where("id='$id'")->find();
			if(!$data){
				$this->error("该banner不存在！");
			}
		}
		$arr=array();
		$arr['name']=I('name','',dhtmlspecialchars);
		$arr['pic']=I('pic','',dhtmlspecialchars);
		
		if(!$arr['name']){
			$this->error("请输入banner名称！");
		}
		if(!$arr['pic']){
			$this->error("请上传图片！");
		}
		$arr['url']=I('url','',dhtmlspecialchars);
		//小程序url不需要加http；
		/*$urls=I('url','',dhtmlspecialchars);
		if($urls){
			$url=substr($urls,0,4);
			$url=strtolower($url);
			if($url !='http'){
				$urls='http://'.$urls;
			}
			$arr['url']=$urls;
		}else{
			$arr['url']='';
		}*/
		$arr['weizhi']=implode(',',I('weizhi'));
		if(!$arr['weizhi']){
			$this->error("请选择显示位置！");
		}
		$arr['displayorder']=I('displayorder','',intval);
		$arr['isrecommand']=I('isrecommand','',intval);
		if($id){
			M('banner')->where("id='$id'")->save($arr);
		}else{
			$arr['aid']=$this->hdadmin['id'];
			$arr['addtime']=time();
			M('banner')->add($arr);
		}
		$this->success("保存成功！");
	}
	public function bannerdel(){
		if(!in_array('7',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
		$id=I('id','',intval);
		$data=M('banner')->where("id='$id'")->find();
		if(!$data){
			$this->error("该banner不存在！");
		}
		M('banner')->where("id='$id'")->delete();
		$this->success("删除成功！");
	}
}