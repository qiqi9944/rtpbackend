<?php
namespace System\Controller;
use Think\Controller;
// 首页市场数据菜单配置
class IndexmenuController extends CheckController {
	public function _initialize(){
		parent::_initialize();
		if(!in_array('7',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
	}
	public function index(){
		$sql="1";
		$this->keywords=I('keywords','',dhtmlspecialchars);
		if($this->keywords){
			$sql .=" AND name LIKE '%".$this->keywords."%'";
		}
		$count=M('indexmenu')->where($sql)->count();
		$page=new \Think\Page($count,10);
		$page->rollPage=5;
		if(!empty($this->keywords)){
			$Page->parameter['keywords']=$this->keywords;
		}
		$show=$page->show();
		$this->page=$show;
		$datalist=M('indexmenu')->where($sql)->order('displayorder DESC,id DESC')->limit($page->firstRow.','.$page->listRows)->select();
		$arr_type_names=$this->getTypeNames();
		foreach($datalist as $k=>$v){
			$v['fbr']=M('user')->where("id='".$v['aid']."'")->getfield('name');
			$v['cate_names']=$this->getMenuTypeNames($v['id'],$arr_type_names);
			$datalist[$k]=$v;
		}
		$this->assign('datalist',$datalist);
		$this->assign('count',$count);
		$this->display();
	}

	//全部可绑定品类 {type=>name}
	protected function getTypeNames(){
		$arr=array();
		foreach($this->arr_datascate2 as $dk=>$dv){
			foreach($dv as $lk=>$lv){
				$arr[$lk]=$lv;
			}
		}
		return $arr;
	}
	//某菜单已绑定品类的名称（逗号分隔）
	protected function getMenuTypeNames($menu_id,$arr_type_names){
		$ids=M('categroup')->where("menu_id='$menu_id'")->order('type ASC')->getfield('type',true);
		$names=array();
		foreach($ids as $tid){
			if(isset($arr_type_names[$tid])){
				$names[]=$arr_type_names[$tid];
			}
		}
		return implode('、',$names);
	}
	public function dosave(){
		$displayorder=I('displayorder');
		foreach($displayorder as $k=>$v){
			$arr=array();
			$arr['displayorder']=$v;
			M('indexmenu')->where("id='$k'")->save($arr);
		}
		$this->success("提交成功！");
	}
	public function add(){
		$id=I('id','',intval);
		if($id){
			$data=M('indexmenu')->where("id='$id'")->find();
			if(!$data){
				$this->error("该菜单不存在！",1);
			}
			$this->data=$data;
		}
		//可选数据type（Lt_datascate2 子类），供选择
		$arr_sjtype=array();
		foreach($this->arr_datascate2 as $k=>$v){
			foreach($v as $lk=>$lv){
				$arr_sjtype[$lk]=$lv;
			}
		}
		$this->arr_sjtype=$arr_sjtype;
		//已绑定品类（多选）
		$cate_bind=array();
		if($id){
			$cate_bind=M('categroup')->where("menu_id='$id'")->getfield('type',true);
		}
		$this->cate_bind=$cate_bind;
		$this->display();
	}
	public function doadd(){
		$id=I('id','',intval);
		if($id){
			$data=M('indexmenu')->where("id='$id'")->find();
			if(!$data){
				$this->error("该菜单不存在！");
			}
		}
		$arr=array();
		$arr['name']=I('name','',dhtmlspecialchars);
		if(!$arr['name']){
			$this->error("请输入菜单名称！");
		}
		$arr['type']=I('type','',intval);
		$arr['pic']=I('pic','',dhtmlspecialchars);
		$arr['isrecommand']=I('isrecommand','',intval);
		$arr['displayorder']=I('displayorder','',intval);
		if($id){
			M('indexmenu')->where("id='$id'")->save($arr);
		}else{
			$arr['aid']=$this->hdadmin['id'];
			$arr['addtime']=time();
			$id=M('indexmenu')->add($arr);
		}
		//绑定品类（多选）：先清空再写入
		M('categroup')->where("menu_id='$id'")->delete();
		$cate_arr=I('cates');
		if(is_array($cate_arr)){
			foreach($cate_arr as $ct){
				$ct=intval($ct);
				if($ct>0){
					M('categroup')->add(array('menu_id'=>$id,'type'=>$ct,'addtime'=>time()));
				}
			}
		}
		$this->success("保存成功！");
	}
	public function del(){
		$id=I('id','',intval);
		$data=M('indexmenu')->where("id='$id'")->find();
		if(!$data){
			$this->error("该菜单不存在！");
		}
		M('categroup')->where("menu_id='$id'")->delete();
		M('indexmenu')->where("id='$id'")->delete();
		$this->success("删除成功！");
	}
}
