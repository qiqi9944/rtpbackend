<?php
namespace System\Controller;
use Think\Controller;
// 观研-活动分组管理
class ActivityGroupController extends CheckController {
	public function _initialize(){
		parent::_initialize();
		if(!in_array('2',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
	}
	public function index(){
		$datalist=M('activity_group')->order('displayorder DESC,id DESC')->select();
		foreach($datalist as $k=>$v){
			$v['activitycount']=M('news')->where("group_id='".$v['id']."' AND type=1")->count();
			$datalist[$k]=$v;
		}
		$this->assign('datalist',$datalist);
		$this->display();
	}
	public function dosave(){
		$displayorder=I('displayorder');
		if(is_array($displayorder)){
			foreach($displayorder as $k=>$v){
				$arr=array();
				$arr['displayorder']=intval($v);
				M('activity_group')->where("id='$k'")->save($arr);
			}
		}
		$this->success("提交成功！");
	}
	public function add(){
		$id=I('id','',intval);
		$data=array();
		if($id){
			$data=M('activity_group')->where("id='$id'")->find();
			if(!$data){
				$this->error("该分组不存在！",1);
			}
		}
		$this->data=$data;
		$this->display();
	}
	public function doadd(){
		$id=I('id','',intval);
		$arr=array();
		$arr['name']=I('name','',dhtmlspecialchars);
		$arr['isrecommand']=I('isrecommand','',intval);
		$arr['displayorder']=I('displayorder','',intval);
		if(!$arr['name']){
			$this->error("请输入分组名称！");
		}
		$exists=M('activity_group')->where("name='".$arr['name']."' AND id<>'".$id."'")->count();
		if($exists){
			$this->error("该分组名称已存在！");
		}
		if($id){
			$data=M('activity_group')->where("id='$id'")->find();
			if(!$data){
				$this->error("该分组不存在！");
			}
			M('activity_group')->where("id='$id'")->save($arr);
		}else{
			$arr['aid']=$this->hdadmin['id'];
			$arr['addtime']=time();
			M('activity_group')->add($arr);
		}
		$this->success("保存成功！");
	}
	public function del(){
		$id=I('id','',intval);
		$data=M('activity_group')->where("id='$id'")->find();
		if(!$data){
			$this->error("该分组不存在！");
		}
		//分组下存在活动时，活动归入未分组，避免数据丢失
		M('news')->where("group_id='$id' AND type=1")->save(array('group_id'=>0));
		M('activity_group')->where("id='$id'")->delete();
		$this->success("删除成功！");
	}
}
