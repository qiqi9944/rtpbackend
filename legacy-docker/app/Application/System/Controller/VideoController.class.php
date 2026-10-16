<?php
namespace System\Controller;
use Think\Controller;
// 观研-小视频管理（url + 封面 + 多行描述）
class VideoController extends CheckController {
	public function _initialize(){
		parent::_initialize();
		if(!in_array('2',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
	}
	public function index(){
		$sql="1";
		$this->keywords=I('keywords','',dhtmlspecialchars);
		$this->sel_xs=I('sel_xs','',intval);
		$this->sel_group=I('sel_group','',intval);
		if($this->keywords){
			$sql .=" AND (name LIKE '%".$this->keywords."%' OR description LIKE '%".$this->keywords."%')";
		}
		if($this->sel_xs==1){
			$sql .=" AND isrecommand=1";
		}elseif($this->sel_xs==2){
			$sql .=" AND isrecommand=0";
		}
		if($this->sel_group){
			$sql .=" AND group_id='".$this->sel_group."'";
		}
		$groups=M('video_group')->order('displayorder DESC,id DESC')->select();
		$group_names=array();
		foreach($groups as $g){
			$group_names[$g['id']]=$g['name'];
		}
		$count=M('video')->where($sql)->count();
		$page=new \Think\Page($count,10);
		$page->rollPage=5;
		if(!empty($this->keywords)){
			$Page->parameter['keywords']=$this->keywords;
		}
		if(!empty($this->sel_xs)){
			$Page->parameter['sel_xs']=$this->sel_xs;
		}
		if(!empty($this->sel_group)){
			$Page->parameter['sel_group']=$this->sel_group;
		}
		$show=$page->show();
		$this->page=$show;
		$datalist=M('video')->where($sql)->order('displayorder DESC,id DESC')->limit($page->firstRow.','.$page->listRows)->select();
		foreach($datalist as $k=>$v){
			$v['fbr']=M('user')->where("id='".$v['aid']."'")->getfield('name');
			$v['group_name']=isset($group_names[$v['group_id']])?$group_names[$v['group_id']]:'未分组';
			$datalist[$k]=$v;
		}
		$this->assign('datalist',$datalist);
		$this->assign('groups',$groups);
		$this->assign('count',$count);
		$this->display();
	}
	public function dosave(){
		$displayorder=I('displayorder');
		if(is_array($displayorder)){
			foreach($displayorder as $k=>$v){
				$arr=array();
				$arr['displayorder']=$v;
				M('video')->where("id='$k'")->save($arr);
			}
		}
		$this->success("提交成功！");
	}
	public function add(){
		$id=I('id','',intval);
		$data=array();
		if($id){
			$data=M('video')->where("id='$id'")->find();
			if(!$data){
				$this->error("该视频不存在！",1);
			}
		}
		if(empty($data['finder_user_name'])){
			$data['finder_user_name']=C('VIDEO_FINDER');
		}
		$this->data=$data;
		$this->groups=M('video_group')->where("isrecommand=1")->order('displayorder DESC,id DESC')->select();
		$this->display();
	}
	public function doadd(){
		$id=I('id','',intval);
		if($id){
			$data=M('video')->where("id='$id'")->find();
			if(!$data){
				$this->error("该视频不存在！");
			}
		}
		$arr=array();
		$arr['name']=I('name','',dhtmlspecialchars);
		$arr['url']=I('url','',dhtmlspecialchars);
		$arr['finder_user_name']=I('finder_user_name','',dhtmlspecialchars);
		if(!$arr['finder_user_name']){
			$arr['finder_user_name']=C('VIDEO_FINDER');
		}
		$arr['feed_id']=I('feed_id','',dhtmlspecialchars);
		$arr['feed_token']=I('feed_token','',dhtmlspecialchars);
		$arr['pic']=I('pic','',dhtmlspecialchars);
		$arr['description']=I('description','',dhtmlspecialchars);
		$arr['group_id']=I('group_id','',intval);
		$arr['isrecommand']=I('isrecommand','',intval);
		$arr['displayorder']=I('displayorder','',intval);
		if(!$arr['name']){
			$this->error("请输入视频标题！");
		}
		if(!$arr['url'] && !$arr['feed_id'] && !$arr['feed_token']){
			$this->error("请输入视频地址，或视频号 feedId / feedToken！");
		}
		if($id){
			M('video')->where("id='$id'")->save($arr);
		}else{
			$arr['aid']=$this->hdadmin['id'];
			$arr['addtime']=time();
			M('video')->add($arr);
		}
		$this->success("保存成功！");
	}
	public function del(){
		$id=I('id','',intval);
		$data=M('video')->where("id='$id'")->find();
		if(!$data){
			$this->error("该视频不存在！");
		}
		M('video')->where("id='$id'")->delete();
		$this->success("删除成功！");
	}
}