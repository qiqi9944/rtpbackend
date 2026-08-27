<?php
namespace System\Controller;
use Think\Controller;
class NewsController extends CheckController {
	public function _initialize(){
		parent::_initialize();
		if(!in_array('2',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
		$arr_sjtype=array();
		foreach($this->arr_datascate2 as $k=>$v){
			foreach($v as $lk=>$lv){
				if($lk!='16'){
					if($lv=='智能音箱'){
						$lv='音频设备';
					}
					$arr_sjtype[$lk]=$lv;
				}
			}
		}
		$this->arr_sjtype=$arr_sjtype;
	}
	
    public function index(){
        $sql="1";
		$this->starttime=I('starttime','',dhtmlspecialchars);
		$this->endtime=I('endtime','',dhtmlspecialchars);
		$this->keywords=I('keywords','',dhtmlspecialchars);
		$this->sel_xs=I('sel_xs','',intval);
		$this->sel_lb=I('sel_lb','',intval);
		$this->sel_sjlb=I('sel_sjlb','',intval);
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
		if($this->sel_lb){
			$sql .=" AND type = '".$this->sel_lb."'";
		}
		if($this->sel_sjlb){
			if($this->sel_sjlb==100){
				$sql .=" AND sjtype = 0";
			}else{
				$sql .=" AND sjtype = '".$this->sel_sjlb."'";
			}
		}
		//分页
		$count=M('news')->where($sql)->count();
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
		if(!empty($this->sel_lb)){
			 $Page->parameter['sel_lb']   =   $this->sel_lb;
		}
		if(!empty($this->sel_sjlb)){
			 $Page->parameter['sel_sjlb']   =   $this->sel_sjlb;
		}
		$show=$page->show();
		$this->page=$show;
		
		$datalist=M('news')->where($sql)->order('displayorder DESC,addtime DESC')->limit($page->firstRow.','.$page->listRows)->select();
		foreach($datalist as $k=>$v){
			$v['fbr']=M('user')->where("id='".$v['aid']."'")->getfield('name');
			$datalist[$k]=$v;
		}
		$this->assign('datalist',$datalist);
		$this->assign('count',$count);
        $this->display();
    }
	public function save(){
		$choose=I('choose','',dhtmlspecialchars);
		$arr_choose=explode(',',$choose);
		foreach($arr_choose as $k=>$v){
			M('news')->where("id='$v'")->delete();
			//将收藏、点赞也删除
			M('news_cz')->where("nid='$id'")->delete();
		}
		$this->success("删除成功！");
	}
	public function dosave(){
		$displayorder=I('displayorder');
		foreach($displayorder as $k=>$v){
			$arr=array();
			$arr['displayorder']=$v;
			M('news')->where("id='$k'")->save($arr);
		}
		$this->success("提交成功！");
	}
	public function add(){
        $id=I('id','',intval);
		if($id){
			$data=M('news')->where("id='$id'")->find();
			if(!$data){
				$this->error("该新闻不存在！",1);
			}
			$this->data=$data;
		}
		$this->display();
    }
	public function doadd(){
		$id=I('id','',intval);
		if($id){
			$data=M('news')->where("id='$id'")->find();
			if(!$data){
				$this->error("该新闻不存在！");
			}
		}
		$arr=array();
		$arr['name']=I('name','',dhtmlspecialchars);
		$arr['type']=I('type','',intval);
		$arr['sjtype']=I('sjtype','',intval);
		$arr['source']=I('source','',dhtmlspecialchars);
		$arr['pic']=I('pic','',dhtmlspecialchars);
		$arr['content']=I('content','',dhtmlspecialchars);
		$arr['url']=I('url','',dhtmlspecialchars);
		$arr['isrecommand']=I('isrecommand','',intval);
		$arr['tag']=I('tag','',intval);
		$arr['addtime']=I('addtime','',dhtmlspecialchars);
		if($arr['addtime']){
			$arr['addtime']=strtotime($arr['addtime']);
		}else{
			$this->error("请输入发布时间！");
		}
		if(!$arr['name']){
			$this->error("请输入新闻标题！");
		}
		if(!$arr['type']){
			$this->error("请选择新闻分类！");
		}
		$is_gzh=I('isgzh','',intval);
		if($is_gzh==1){
			if(!$arr['url']){
				$this->error("请输入跳转链接！");
			}
		}else{
			if(!$arr['content']){
				$this->error("请输入新闻内容！");
			}
		}
		/*if(!$arr['pic']){
			$this->error("请上传封面图！");
		}*/
		
			
		if($id){ 
			$is_exsit=M('news')->where("id!='$id' AND name='".$arr['name']."'")->count();
			if($is_exsit){
				$this->error("该新闻标题已存在！");
			}
			if($arr['url']){
				$is_exsit1=M('news')->where("id!='$id' AND url='".$arr['url']."'")->count();
				if($is_exsit1){
					$this->error("该跳转链接已存在！");
				}
			}
			M('news')->where("id='$id'")->save($arr);
		}else{
			$is_exsit=M('news')->where("name='".$arr['name']."'")->count();
			if($is_exsit){
				$this->error("该新闻标题已存在！");
			}
			if($arr['url']){
				$is_exsit1=M('news')->where("url='".$arr['url']."'")->count();
				if($is_exsit1){
					$this->error("该跳转链接已存在！");
				}
			}
			$arr['aid']=$this->hdadmin['id'];
			M('news')->add($arr);
		}
		$this->success("保存成功！");
	}
	public function del(){
		$id=I('id','',intval);
		$data=M('news')->where("id='$id'")->find();
		if(!$data){
			$this->error("该新闻不存在！");
		}
		M('news')->where("id='$id'")->delete();
		//将收藏、点赞也删除
		M('news_cz')->where("nid='$id'")->delete();
		$this->success("删除成功！");
	}
}