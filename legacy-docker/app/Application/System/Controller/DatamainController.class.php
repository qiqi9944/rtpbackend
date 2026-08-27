<?php
namespace System\Controller;
use Think\Controller;
// 市场数据简易维护页（iot 月度表：类型/市场/品径/时间/品牌/销量销额）
class DatamainController extends CheckController {
	public function _initialize(){
		parent::_initialize();
		if(!in_array('1',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
		$arr_sjtype=array();
		foreach($this->arr_datascate2 as $dk=>$dv){
			foreach($dv as $lk=>$lv){
				$arr_sjtype[$lk]=$lv;
			}
		}
		$this->arr_sjtype=$arr_sjtype;
		$this->marketArr=array('中国','全球');
		$this->lxArr=array('零售','出货');
	}
	public function index(){
		$sql="1";
		$this->market=I('market','',dhtmlspecialchars);
		$this->lx=I('lx','',dhtmlspecialchars);
		$this->keywords=I('keywords','',dhtmlspecialchars);
		if($this->market){$sql.=" AND market='$this->market'";}
		if($this->lx){$sql.=" AND lx='$this->lx'";}
		if($this->keywords){$sql.=" AND (pp LIKE '%$this->keywords%' OR nd LIKE '%$this->keywords%')";}
		$count=M('shuju_iot')->where($sql)->count();
		$page=new \Think\Page($count,10);
		$page->rollPage=5;
		$show=$page->show();
		$this->page=$show;
		$datalist=M('shuju_iot')->where($sql)->order('nd DESC,yd DESC,id DESC')->limit($page->firstRow.','.$page->listRows)->select();
		foreach($datalist as $k=>$v){
			$v['typename']=isset($this->arr_sjtype[$v['type']])?$this->arr_sjtype[$v['type']]:$v['type'];
			$datalist[$k]=$v;
		}
		$this->assign('datalist',$datalist);
		$this->assign('count',$count);
		$this->display();
	}
	public function add(){
		$this->display();
	}
	public function doadd(){
		$arr=array();
		$arr['type']=I('type','',intval);
		$arr['market']=I('market','',dhtmlspecialchars);
		$arr['lx']=I('lx','',dhtmlspecialchars);
		$arr['nd']=I('nd','',dhtmlspecialchars);
		$arr['yd']=I('yd','',dhtmlspecialchars);
		$arr['ds']=I('ds','',dhtmlspecialchars);
		$arr['pp']=I('pp','',dhtmlspecialchars);
		$arr['gy1']=I('gy1','',dhtmlspecialchars);
		$arr['gy2']=I('gy2','',dhtmlspecialchars);
		$arr['xl']=I('xl','',floatval);
		$arr['xe']=I('xe','',floatval);
		if(!$arr['type']||!$arr['market']||!$arr['lx']||!$arr['nd']||!$arr['yd']||!$arr['pp']){
			$this->error("请填写必填项(品类/市场/品径/年月/品牌)！");
		}
		$is=M('shuju_iot')->where("type='".$arr['type']."' AND market='".$arr['market']."' AND lx='".$arr['lx']."' AND nd='".$arr['nd']."' AND yd='".$arr['yd']."' AND pp='".$arr['pp']."'")->find();
		if($is){
			M('shuju_iot')->where("id='".$is['id']."'")->save($arr);
		}else{
			$arr['aid']=$this->hdadmin['id'];
			$arr['addtime']=time();
			M('shuju_iot')->add($arr);
		}
		$this->success("保存成功！");
	}
	public function del(){
		$id=I('id','',intval);
		M('shuju_iot')->where("id='$id'")->delete();
		$this->success("删除成功！");
	}
}