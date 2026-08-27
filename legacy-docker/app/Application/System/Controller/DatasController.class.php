<?php
namespace System\Controller;
use Think\Controller;
class DatasController extends CheckController {
	public function _initialize(){
		parent::_initialize();
		
		if(!in_array('1',$this->hdadmin['lanmu'])){
			$this->error("您无权进入此页面！",1);
		}
	}
	
    public function index(){
		$this->sel_lb=I('sel_lb','',intval);
		$datalist=array();
		foreach($this->arr_datascate2 as $dk=>$dv){
			if(($this->sel_lb && $this->sel_lb==$dk) || !$this->sel_lb){
				$i=0;
				foreach($dv as $k=>$v){
					$data=array();
					$data['cate']=$dk;
					$data['catename']=$this->arr_datascate1[$dk];
					$data['id']=$k;
					$data['name']=$v;
					if(in_array($k,array(1,2,3,4,13,14,15,16,19))){
						$data['num']=M('shuju_iot')->where("type='$k'")->count();
						//所有日期
						$sj=M('shuju_iot')->distinct(true)->where("type='$k'")->field('nd')->order("nd DESC")->select();
						$arr_sj=array();
						foreach($sj as $sk=>$sv){
							$nd=M('shuju_iot')->distinct(true)->where("type='$k' AND nd='".$sv['nd']."'")->field('yd')->order("yd DESC")->select();
							foreach($nd as $nk=>$nv){
								$arr=array();
								$arr['id']=$sv['nd'].'-'.$nv['yd'];
								$arr['name']=$nv['yd'];
								$arr_sj[]=$arr;
							}
						}
						$data['sjrq']=$arr_sj;
					}elseif(in_array($k,array(5,6,7,8,18))){
						$data['num']=M('shuju_pid')->where("type='$k'")->count();
						//所有日期
						$sj=M('shuju_pid')->distinct(true)->where("type='$k'")->field('nd')->order("nd DESC")->select();
						$arr_sj=array();
						foreach($sj as $sk=>$sv){
							$nd=M('shuju_pid')->distinct(true)->where("type='$k' AND nd='".$sv['nd']."'")->field('jd')->order("jd DESC")->select();
							foreach($nd as $nk=>$nv){
								$arr=array();
								$arr['id']=$sv['nd'].'-'.$nv['jd'];
								$arr['name']=$sv['nd'].$nv['jd'];
								$arr_sj[]=$arr;
							}
						}
						$data['sjrq']=$arr_sj;
					}elseif(in_array($k,array(9,11,12,17,21))){
						foreach($this->arr_datascate3[$k] as $ck=>$cv){
							$data['num'][$ck]=M('shuju_tvs')->where("type='$k' AND type2='$ck'")->count();
							//所有日期
							if($ck==1 || $ck==4 || $ck==9){
								$sj=M('shuju_tvs')->distinct(true)->where("type='$k' AND type2='$ck'")->field('nd')->order("nd DESC")->select();
								$arr_sj=array();
								foreach($sj as $sk=>$sv){
									$nd=M('shuju_tvs')->distinct(true)->where("type='$k' AND type2='$ck' AND nd='".$sv['nd']."'")->field('jd')->order("jd DESC")->select();
									foreach($nd as $nk=>$nv){
										$arr=array();
										$arr['id']=$sv['nd'].'-'.$nv['jd'];
										$arr['name']=$sv['nd'].$nv['jd'];
										$arr_sj[]=$arr;
									}
								}
								$data['sjrq'][$ck]=$arr_sj;
							}elseif($ck==2){
								$sjnd=M('shuju_tvs')->distinct(true)->where("type='$k' AND type2='$ck'")->field('nd')->order("nd DESC")->select();
								$arr_yf=array('Dec','Nov','Oct','Sep','Aug','Jul','Jun','May','Apr','Mar','Feb','Jan');
								$arr_sj=array();
								foreach($sjnd as $sk=>$sv){
									foreach($arr_yf as $yk=>$yv){
										$nv=M('shuju_tvs')->where("type='$k' AND type2='$ck' AND nd='".$sv['nd']."' AND yd='$yv'")->find();
										if($nv){
											$arr=array();
											$arr['id']=$sv['nd'].'-'.$nv['yd'];
											$arr['name']=$sv['nd'].$nv['yd'];
											$arr_sj[]=$arr;
										}
									}
								}
								$data['sjrq'][$ck]=$arr_sj;
							}elseif($ck==3 || $ck==5 || $ck==6 || $ck==7 || $ck==8 || $ck==10 || $ck==11 || $ck==12){
								$sj=M('shuju_tvs')->distinct(true)->where("type='$k' AND type2='$ck'")->field('nd')->order("nd DESC")->select();
								$arr_sj=array();
								foreach($sj as $sk=>$sv){
									$nd=M('shuju_tvs')->distinct(true)->where("type='$k' AND type2='$ck' AND nd='".$sv['nd']."'")->field('yd')->order("yd DESC")->select();
									foreach($nd as $nk=>$nv){
										$arr=array();
										$arr['id']=$sv['nd'].'-'.$nv['yd'];
										$arr['name']=$nv['yd'];
										$arr_sj[]=$arr;
									}
								}
								$data['sjrq'][$ck]=$arr_sj;
							}
							
						}
					}else{
						$data['num']=0;
					}
					if($i==0 && count($dv)>1){
						$data['row']=count($dv);
					}
					if($i==0 && $this->arr_datascate3[$k]){
						$data['row']=0;
						foreach($dv as $ddk=>$ddv){
							$data['row']=$data['row']+1;
							if($this->arr_datascate3[$ddk]){
								$data['row']=$data['row']+count($this->arr_datascate3[$ddk])-1;
							}
						}
					}
					if($this->arr_datascate3[$k]){
						$data['cate2']=$this->arr_datascate3[$k];
						$data['row2']=count($data['cate2']);
					}
					$datalist[]=$data;
					$i++;
				}
			}
		}
		/* echo '<pre>';
		print_r($datalist); */
		$this->assign('count',count($datalist));
		$this->assign('datalist',$datalist);
		$this->display();
    }
	public function qk(){
		$id=I('id','',intval);
		$yd=I('yd','',dhtmlspecialchars);
		if($yd!='全部'){
			$date=explode('-',$yd);
		}
		if(in_array($id,array(1,2,3,4,13,14,15,16,19))){
			if($yd=='全部'){
				$sj=M('shuju_iot')->where("type='$id'")->count();
				if($sj){
					M('shuju_iot')->where("type='$id'")->delete();
				}
			}else{
				//日期
				$sj=M('shuju_iot')->where("type='$id' AND nd='$date[0]' AND yd='$date[1]'")->find();
				if($sj){
					M('shuju_iot')->where("type='$id' AND nd='".$sj['nd']."' AND yd='".$sj['yd']."'")->delete();
				}
			}
			
		}elseif(in_array($id,array(5,6,7,8,18))){
			if($yd=='全部'){
				$sj=M('shuju_pid')->where("type='$id'")->count();
				if($sj){
					M('shuju_pid')->where("type='$id'")->delete();
				}
			}else{
				//最新日期
				$sj=M('shuju_pid')->where("type='$id' AND nd='$date[0]' AND jd='$date[1]'")->find();
				if($sj){
					M('shuju_pid')->where("type='$id' AND nd='".$sj['nd']."' AND jd='".$sj['jd']."'")->delete();
				}
			}
		}elseif(in_array($id,array(9,11,12,17,21))){
			$type=I('type','',intval);
			if($yd=='全部'){
				$sj=M('shuju_tvs')->where("type='$id' AND type2='$type'")->count();
				if($sj){
					M('shuju_tvs')->where("type='$id' AND type2='$type'")->delete();
				}
			}else{
				if($type==1 || $type==4 || $type==9){
					$sj=M('shuju_tvs')->where("type='$id' AND type2='$type' AND nd='$date[0]' AND jd='$date[1]'")->find();
					if($sj){
						M('shuju_tvs')->where("type='$id' AND type2='$type' AND nd='".$sj['nd']."' AND jd='".$sj['jd']."'")->delete();
					}
				}elseif($type==2){
					$sj=M('shuju_tvs')->where("type='$id' AND type2='$type' AND nd='$date[0]' AND yd='$date[1]'")->find();
					if($sj){
						M('shuju_tvs')->where("type='$id' AND type2='$type' AND nd='".$sj['nd']."' AND yd='".$sj['yd']."'")->delete();
					}
				}elseif($type==3 || $type==5 || $type==6 || $type==8 || $type==10 || $type==11 || $type==12){
					$sj=M('shuju_tvs')->where("type='$id' AND type2='$type' AND nd='$date[0]' AND yd='$date[1]'")->find();
					if($sj){
						M('shuju_tvs')->where("type='$id' AND type2='$type' AND nd='".$sj['nd']."' AND yd='".$sj['yd']."'")->delete();
					}
				}
			}
		}
		if($sj){
			$this->success("清空成功！");
		}else{
			$this->error("该类别没有数据！");
		}
	}
	public function lists(){
		$id=I('id','',intval);
		$type=I('type','',intval);
		$sql="type='$id'";
		if(in_array($id,array(1,2,3,4,13,14,15,16,19))){
			$table=M('shuju_iot');
			$orders='nd DESC, yd DESC,id ASC';
		}elseif(in_array($id,array(5,6,7,8,18))){
			$table=M('shuju_pid');
			$orders='nd DESC, bnd ASC, jd ASC,id ASC';
			if($id==5 && !$type){
				$type=1;
			}
		}elseif(in_array($id,array(9,10,11,12,17,21))){
			$table=M('shuju_tvs');
			$orders='nd DESC, jd ASC, yd ASC,id ASC';
		}else{
			$this->error("暂无数据！");
		}
		if($type){
			$sql .=" AND type2='$type'";
		}
		$this->keywords=I('keywords','',dhtmlspecialchars);
		$this->sel_nd=I('sel_nd','',intval);
		
		if($this->sel_nd){
			$sql .=" AND nd = '".$this->sel_nd."'";
		}
		//分页
		$count=$table->where($sql)->count();
		$page=new \Think\Page($count,20);
		$page->rollPage=5;
		if(!empty($type)){
			 $Page->parameter['type']   =   $type;
		}
		if(!empty($this->keywords)){
			 $Page->parameter['keywords']   =   $this->keywords;
		}
		if(!empty($this->sel_nd)){
			 $Page->parameter['sel_nd']   =   $this->sel_nd;
		}
		$show=$page->show();
		$this->page=$show;
		
		$datalist=$table->where($sql)->order($orders)->limit($page->firstRow.','.$page->listRows)->select();
		
		$this->assign('datalist',$datalist);
		$this->assign('count',$count);
		$this->assign('id',$id);
		$this->assign('type',$type);
        $this->display();
		
	}
	public function html(){
		$id=I('id','',intval);
		$data=M('shuju_html')->where("type='$id'")->find();
		foreach($this->arr_datascate2 as $dk=>$dv){
			foreach($dv as $k=>$v){
				if($id==$k){
					$data['name']=$v;
					$data['catename']=$this->arr_datascate1[$dk];
				}
			}
		}
		$this->id=$id;
		$this->data=$data;
		$this->display();
	}
	public function dohtml(){
		$id=I('id','',intval);
		$data=array();
		$data['content']=I('content','',dhtmlspecialchars);
		$data['aid']=$this->hdadmin['id'];
		$data['addtime']=time();
		$is_exsit=M('shuju_html')->where("type='$id'")->find();
		if($is_exsit){
			M('shuju_html')->where("id='".$is_exsit['id']."'")->save($data);
		}else{
			$data['type']=$id;
			M('shuju_html')->add($data);
		}
		$this->success("保存成功！");
	}
	public function excel(){
		$id=I('id','',intval);
		$type=I('type','',intval);
		$data=array();
		foreach($this->arr_datascate2 as $dk=>$dv){
			foreach($dv as $k=>$v){
				if($id==$k){
					$data['id']=$k;
					$data['name']=$v;
					$data['catename']=$this->arr_datascate1[$dk];
					if($type){
						$data['catename2']=$this->arr_datascate3[$k][$type];
						$data['type']=$type;
					}
				}
			}
		}
		$this->assign('data',$data);
		$this->display();
	}
	public function doexcel(){
		$id=I('id','',intval);
		$type=I('type','',intval);
		if(!$id){
			$this->error("参数错误！");
		}
		$excel = I('excel','',dhtmlspecialchars);
		//$excel = '/Uploads/excel/2022-12-27/63aab23bddb04.xlsx';
		if($excel) {
			$excels=explode('.',$excel);
			$file = ".".$excel;
			$file_type = $excels[1];
			$Excel = new ExcelToArrary(); //实例化
			if($id==5){
				$res = $Excel->read2($file, "UTF-8", $file_type); //传参,判断office2007还是office2003
			}else{
				$res = $Excel->read($file, "UTF-8", $file_type); //传参,判断office2007还是office2003
			}
			if(in_array($id,array(1,2,3,4,13,14,15,16,19))){//智能硬件IOT
				if((trim($res[1][0])=='年度' && trim($res[1][1])=='月度（格式如：21.01）' && trim($res[1][2])=='电商类型' && trim($res[1][3])=='品牌' && trim($res[1][4])=='投影技术' && trim($res[1][5])=='亮度范围' && trim($res[1][6])=='销量' && trim($res[1][7])=='销额' && $id==1) || (trim($res[1][0])=='年度' && trim($res[1][1])=='月度（格式如：21.01）' && trim($res[1][2])=='电商类型' && trim($res[1][3])=='品牌' && trim($res[1][4])=='屏幕' && trim($res[1][5])=='麦克风阵列' && trim($res[1][6])=='销售量' && trim($res[1][7])=='销售额' && $id==2) || (trim($res[1][0])=='年度' && trim($res[1][1])=='月度（格式如：2021.01）' && trim($res[1][2])=='平台' && trim($res[1][3])=='品牌' && trim($res[1][4])=='成交单价' && trim($res[1][5])=='价格段' && trim($res[1][6])=='销量' && trim($res[1][7])=='销额' && $id==3) || (trim($res[1][0])=='年度' && trim($res[1][1])=='月度（格式如：2021.01）' && trim($res[1][2])=='电商' && trim($res[1][3])=='品牌' && trim($res[1][4])=='分辨率规格' && trim($res[1][5])=='CPU核数' && trim($res[1][6])=='销量' && trim($res[1][7])=='销额' && $id==4) || (trim($res[1][0])=='年度' && trim($res[1][1])=='月度（格式如：2021.01）' && trim($res[1][2])=='品牌' && trim($res[1][3])=='价格段' && trim($res[1][4])=='销量（台）' && trim($res[1][5])=='销额（元）' && $id==13) || (trim($res[1][0])=='年度' && trim($res[1][1])=='月度（格式如：21.01）' && trim($res[1][2])=='电商类型' && trim($res[1][3])=='品牌' && trim($res[1][4])=='平板类型' && trim($res[1][5])=='屏幕技术' && trim($res[1][6])=='销量' && trim($res[1][7])=='销额' && $id==14) || (trim($res[1][0])=='年度' && trim($res[1][1])=='月度（格式如：2021.01）' && trim($res[1][2])=='电商' && trim($res[1][3])=='品牌' && trim($res[1][4])=='屏幕类型' && trim($res[1][5])=='光学方案' && trim($res[1][6])=='销量' && trim($res[1][7])=='销额' && $id==15) || (trim($res[1][0])=='年度' && trim($res[1][1])=='月度（格式如：21.01）' && trim($res[1][2])=='电商类型' && trim($res[1][3])=='品牌' && trim($res[1][4])=='产品类型' && trim($res[1][5])=='音频解码' && trim($res[1][6])=='销售量' && trim($res[1][7])=='销售额' && $id==16) || (trim($res[1][0])=='年度' && trim($res[1][1])=='月度（格式如：2021.01）' && trim($res[1][2])=='电商' && trim($res[1][3])=='品牌' && trim($res[1][4])=='屏幕类型' && trim($res[1][5])=='光学方案' && trim($res[1][6])=='销量' && trim($res[1][7])=='销额' && $id==19)){
					array_shift($res);//删除数组中的第一个元素
					foreach($res as $value){
						$value[0]=trim($value[0]);
						$value[1]=trim($value[1]);
						$value[2]=trim($value[2]);
						$value[3]=trim($value[3]);
						$value[4]=trim($value[4]);
						$value[5]=trim($value[5]);
						$value[6]=trim($value[6]);
						if($value[0] && $value[1]){
							$arr=array();
							$arr['type']=$id;
							$arr['nd']=$value[0];
							$arr['yd']=$value[1];
							if($id==13){
								$arr['pp']=$value[2];
								$arr['gy2']=$value[3];
								$arr['xl']=$value[4];
								$arr['xe']=$value[5];
								$is_exsit=M('shuju_iot')->where("type='".$arr['type']."' AND nd='".$arr['nd']."' AND yd='".$arr['yd']."' AND pp='".$arr['pp']."' AND gy2='".$arr['gy2']."'")->find();
							}else{
								$arr['ds']=$value[2];
								$arr['pp']=$value[3];
								$arr['gy1']=$value[4];
								$arr['gy2']=$value[5];
								$arr['xl']=$value[6];
								$arr['xe']=$value[7];
								$arr['aid']=$this->hdadmin['id'];
								$arr['addtime']=time();
								$is_exsit=M('shuju_iot')->where("type='".$arr['type']."' AND nd='".$arr['nd']."' AND yd='".$arr['yd']."' AND ds='".$arr['ds']."' AND pp='".$arr['pp']."' AND gy1='".$arr['gy1']."' AND gy2='".$arr['gy2']."'")->find();
							}
							if($is_exsit){
								/* $arr['xl']=$arr['xl']+$is_exsit['xl'];
								$arr['xe']=$arr['xe']+$is_exsit['xe']; */
								M('shuju_iot')->where("id='".$is_exsit['id']."'")->save($arr);
							}else{
								M('shuju_iot')->add($arr);
							}
						}
					}
					$this->success("保存成功！");
				}else{
					$this->error("请按照模板上传文件！");
				}
			}elseif(in_array($id,array(5,6,7,8,18))){//商用显示PID
				if($id==5){
					foreach($res['sheetnames'] as $k=>$v){
						if($k==0){
							if(trim($res['sheet'.$k][1][0])!='年度' || trim($res['sheet'.$k][1][1])!='半年度' || trim($res['sheet'.$k][1][2])!='季度（格式如：Q1、Q2、Q3、Q4）' || trim($res['sheet'.$k][1][3])!='产品场景' || trim($res['sheet'.$k][1][4])!='产品显示类型' || trim($res['sheet'.$k][1][5])!='显示类型' || trim($res['sheet'.$k][1][6])!='企业' || trim($res['sheet'.$k][1][7])!='品牌' || trim($res['sheet'.$k][1][8])!='品牌简写' || trim($res['sheet'.$k][1][9])!='触控技术' || trim($res['sheet'.$k][1][10])!='触控技术-产品' || trim($res['sheet'.$k][1][11])!='尺寸段' || trim($res['sheet'.$k][1][12])!='尺寸' || trim($res['sheet'.$k][1][13])!='出货量（K）' || trim($res['sheet'.$k][1][14])!='市场价（元/台）' || trim($res['sheet'.$k][1][15])!='销售额（Mn）'){
								$this->error($v."，请按照模板上传文件！");
							}
						}elseif($k==1){
							if(trim($res['sheet'.$k][1][0])!='年度' || trim($res['sheet'.$k][1][1])!='半年度' || trim($res['sheet'.$k][1][2])!='季度' || trim($res['sheet'.$k][1][3])!='产品场景' || trim($res['sheet'.$k][1][4])!='产品显示类型' || trim($res['sheet'.$k][1][5])!='触控技术' || trim($res['sheet'.$k][1][6])!='细分电容技术' || trim($res['sheet'.$k][1][7])!='出货量（K）' || trim($res['sheet'.$k][1][8])!='销售额（Mn）' || trim($res['sheet'.$k][1][9])!='均价（元/台）'){
							  $this->error($v."，请按照模板上传文件！");
						  }
						}
					}
					//品牌尺寸库
					array_shift($res['sheet0']);
					foreach($res['sheet0'] as $value){
						$value[0]=trim($value[0]);
						$value[1]=trim($value[1]);
						$value[2]=trim($value[2]);
						$value[3]=trim($value[3]);
						$value[4]=trim($value[4]);
						$value[5]=trim($value[5]);
						$value[6]=trim($value[6]);
						$value[7]=trim($value[7]);
						$value[8]=trim($value[8]);
						$value[9]=trim($value[9]);
						$value[10]=trim($value[10]);
						$value[11]=trim($value[11]);
						$value[12]=trim($value[12]);
						$value[13]=trim($value[13]);
						$value[14]=trim($value[14]);
						$value[15]=trim($value[15]);
						if($value[0]){
							$arr=array();
							$arr['type']=$id;
							$arr['type2']='1';
							$arr['nd']=$value[0];//年度
							$arr['bnd']=$value[1];//半年度
							$arr['jd']=$value[2];//季度
							$arr['cpcj']=$value[3];//产品场景
							$arr['cplx']=$value[4];//产品显示类型
							$arr['xslx']=$value[5];//显示类型
							$arr['qy']=$value[6];//企业
							$arr['pp']=$value[7];//品牌
							$arr['ppjx']=$value[8];//品牌简写
							$arr['ckjs']=$value[9];//触控技术
							$arr['ckjscp']=$value[10];//触控技术-产品
							$arr['chcd']=$value[11];//尺寸段
							$arr['chc']=$value[12];//尺寸
							$arr['chl']=$value[13];//出货量（K）
							$arr['scj']=$value[14];//市场价（元/台）
							$arr['xse']=$value[15];//销售额（Mn）
							$arr['aid']=$this->hdadmin['id'];
							$arr['addtime']=time();
							$is_exsit=M('shuju_pid')->where("type='".$arr['type']."' AND type2='".$arr['type2']."' AND nd='".$arr['nd']."' AND bnd='".$arr['bnd']."' AND jd='".$arr['jd']."' AND cpcj='".$arr['cpcj']."' AND cplx='".$arr['cplx']."' AND xslx='".$arr['xslx']."' AND qy='".$arr['qy']."' AND pp='".$arr['pp']."' AND ppjx='".$arr['ppjx']."' AND ckjs='".$arr['ckjs']."' AND ckjscp='".$arr['ckjscp']."' AND chcd='".$arr['chcd']."' AND chc='".$arr['chc']."'")->find();
							if($is_exsit){
								/*$arr['chl']=$arr['chl']+$is_exsit['chl'];
								$arr['xse']=$arr['xse']+$is_exsit['xse'];*/
								M('shuju_pid')->where("id='".$is_exsit['id']."'")->save($arr);
							}else{
								M('shuju_pid')->add($arr);
							}
						}
					}
					//尺寸技术库
					array_shift($res['sheet1']);
					foreach($res['sheet1'] as $value){
						$value[0]=trim($value[0]);
						$value[1]=trim($value[1]);
						$value[2]=trim($value[2]);
						$value[3]=trim($value[3]);
						$value[4]=trim($value[4]);
						$value[5]=trim($value[5]);
						$value[6]=trim($value[6]);
						$value[7]=trim($value[7]);
						$value[8]=trim($value[8]);
						$value[9]=trim($value[9]);
						$value[10]=trim($value[10]);
						$value[11]=trim($value[11]);
						$value[12]=trim($value[12]);
						$value[13]=trim($value[13]);
						$value[14]=trim($value[14]);
						$value[15]=trim($value[15]);
						if($value[0]){
							$arr=array();
							$arr['type']=$id;
							$arr['type2']='2';
							$arr['nd']=$value[0];//年度
							$arr['bnd']=$value[1];//半年度
							$arr['jd']=$value[2];//季度
							$arr['cpcj']=$value[3];//产品场景
							$arr['cplx']=$value[4];//产品显示类型
							$arr['ckjs']=$value[5];//触控技术	
							$arr['ckjscp']=$value[6];//细分电容技术
							$arr['chl']=$value[7];//出货量（K）
							$arr['xse']=$value[8];//销售额（Mn）
							$arr['scj']=$value[9];//均价（元/台）
							$arr['aid']=$this->hdadmin['id'];
							$arr['addtime']=time();
							$is_exsit=M('shuju_pid')->where("type='".$arr['type']."' AND type2='".$arr['type2']."' AND nd='".$arr['nd']."' AND bnd='".$arr['bnd']."' AND jd='".$arr['jd']."' AND cpcj='".$arr['cpcj']."' AND cplx='".$arr['cplx']."' AND ckjs='".$arr['ckjs']."' AND ckjscp='".$arr['ckjscp']."'")->find();
							if($is_exsit){
								M('shuju_pid')->where("id='".$is_exsit['id']."'")->save($arr);
							}else{
								M('shuju_pid')->add($arr);
							}
						}
					}
					$this->success("保存成功！");
				}elseif($id==6){
					if(trim($res[1][0])=='年度' && trim($res[1][1])=='半年度' && trim($res[1][2])=='季度（格式如：Q1、Q2、Q3、Q4）' && trim($res[1][3])=='企业类型' && trim($res[1][4])=='企业' && trim($res[1][5])=='产品类型' && trim($res[1][6])=='尺寸' && trim($res[1][7])=='梯媒' && trim($res[1][8])=='产品' && trim($res[1][9])=='尺寸段' && trim($res[1][10])=='出货量（K台）' && trim($res[1][11])=='销售额(Mn RMB)' && trim($res[1][12])=='均价'){
						array_shift($res);//删除数组中的第一个元素
						foreach($res as $value){
							$value[0]=trim($value[0]);
							$value[1]=trim($value[1]);
							$value[2]=trim($value[2]);
							$value[3]=trim($value[3]);
							$value[4]=trim($value[4]);
							$value[5]=trim($value[5]);
							$value[6]=trim($value[6]);
							$value[7]=trim($value[7]);
							$value[8]=trim($value[8]);
							$value[9]=trim($value[9]);
							$value[10]=trim($value[10]);
							$value[11]=trim($value[11]);
							$value[12]=trim($value[12]);
							if($value[0] && $value[1] && $value[2]){
								$arr=array();
								$arr['type']=$id;
								$arr['nd']=$value[0];//年度
								$arr['bnd']=$value[1];//半年度
								$arr['jd']=$value[2];//季度
								$arr['xslx']=$value[3];//企业类型
								$arr['qy']=$value[4];//企业
								$arr['cplx']=$value[5];//产品类型
								$arr['chc']=$value[6];//尺寸
								$arr['ckjs']=$value[7];//梯媒
								$arr['cpcj']=$value[8];//产品
								$arr['chcd']=$value[9];//尺寸段
								$arr['chl']=$value[10];//出货量（K台）
								$arr['xse']=$value[11];//销售额(Mn RMB)	
								$arr['scj']=$value[12];//均价
								$arr['aid']=$this->hdadmin['id'];
								$arr['addtime']=time();
								$is_exsit=M('shuju_pid')->where("type='".$arr['type']."' AND nd='".$arr['nd']."' AND bnd='".$arr['bnd']."' AND jd='".$arr['jd']."' AND xslx='".$arr['xslx']."' AND qy='".$arr['qy']."' AND cplx='".$arr['cplx']."' AND chc='".$arr['chc']."' AND ckjs='".$arr['ckjs']."' AND cpcj='".$arr['cpcj']."' AND chcd='".$arr['chcd']."'")->find();
								if($is_exsit){
									M('shuju_pid')->where("id='".$is_exsit['id']."'")->save($arr);
								}else{
									M('shuju_pid')->add($arr);
								}
							}
						}
						$this->success("保存成功！");
					}else{
						$this->error("请按照模板上传文件！".$res[1][0].$res[1][1].$res[1][2]);
					}
				}elseif($id==7){
					if(trim($res[1][0])=='年度' && trim($res[1][1])=='半年度' && trim($res[1][2])=='季度（格式如：Q1、Q2、Q3、Q4）' && trim($res[1][3])=='品牌' && trim($res[1][4])=='应用行业' && trim($res[1][5])=='投影技术' && trim($res[1][6])=='亮度范围' && trim($res[1][7])=='分辨率规格' && trim($res[1][8])=='出货量（台）' && trim($res[1][9])=='均价（万元）' && trim($res[1][10])=='销售额（万元）'){
						array_shift($res);//删除数组中的第一个元素
						foreach($res as $value){
							$value[0]=trim($value[0]);
							$value[1]=trim($value[1]);
							$value[2]=trim($value[2]);
							$value[3]=trim($value[3]);
							$value[4]=trim($value[4]);
							$value[5]=trim($value[5]);
							$value[6]=trim($value[6]);
							$value[7]=trim($value[7]);
							$value[8]=trim($value[8]);
							$value[9]=trim($value[9]);
							$value[10]=trim($value[10]);
							if($value[0] && $value[1] && $value[2]){
								$arr=array();
								$arr['type']=$id;
								$arr['nd']=$value[0];//年度
								$arr['bnd']=$value[1];//半年度
								$arr['jd']=$value[2];//季度
								$arr['pp']=$value[3];//品牌
								$arr['xslx']=$value[4];//应用行业
								$arr['cpcj']=$value[5];//投影技术
								$arr['cplx']=$value[6];//亮度范围	
								$arr['chcd']=$value[7];//分辨率规格
								$arr['chl']=$value[8];//出货量（台）
								$arr['scj']=$value[9];//均价（万元）
								$arr['xse']=$value[10];//销售额（万元）
								$arr['aid']=$this->hdadmin['id'];
								$arr['addtime']=time();								
								$is_exsit=M('shuju_pid')->where("type='".$arr['type']."' AND nd='".$arr['nd']."' AND bnd='".$arr['bnd']."' AND jd='".$arr['jd']."' AND pp='".$arr['pp']."' AND xslx='".$arr['xslx']."' AND cpcj='".$arr['cpcj']."' AND cplx='".$arr['cplx']."' AND chcd='".$arr['chcd']."' AND scj='".$arr['scj']."'")->find();
								if($is_exsit){
									M('shuju_pid')->where("id='".$is_exsit['id']."'")->save($arr);
								}else{
									M('shuju_pid')->add($arr);
								}
							}
						}
						$this->success("保存成功！");
					}else{
						$this->error("请按照模板上传文件！");
					}
				}elseif($id==18){
					if(trim($res[1][0])=='年度' && trim($res[1][1])=='半年度' && trim($res[1][2])=='季度（格式如：Q1、Q2、Q3、Q4）' && trim($res[1][3])=='品牌' && trim($res[1][4])=='尺寸段' && trim($res[1][5])=='出货量（台）' && trim($res[1][6])=='均价（万元）' && trim($res[1][7])=='销售额（万元）'){
						array_shift($res);//删除数组中的第一个元素
						foreach($res as $value){
							$value[0]=trim($value[0]);
							$value[1]=trim($value[1]);
							$value[2]=trim($value[2]);
							$value[3]=trim($value[3]);
							$value[4]=trim($value[4]);
							$value[5]=trim($value[5]);
							$value[6]=trim($value[6]);
							$value[7]=trim($value[7]);
							if($value[0] && $value[1] && $value[2]){
								$arr=array();
								$arr['type']=$id;
								$arr['nd']=$value[0];//年度
								$arr['bnd']=$value[1];//半年度
								$arr['jd']=$value[2];//季度
								$arr['pp']=$value[3];//品牌
								$arr['chcd']=$value[4];//尺寸段
								$arr['chl']=$value[5];//出货量（台）
								$arr['scj']=$value[6];//均价（万元）
								$arr['xse']=$value[7];//销售额（万元）
								$arr['aid']=$this->hdadmin['id'];
								$arr['addtime']=time();								
								$is_exsit=M('shuju_pid')->where("type='".$arr['type']."' AND nd='".$arr['nd']."' AND bnd='".$arr['bnd']."' AND jd='".$arr['jd']."' AND pp='".$arr['pp']."' AND chcd='".$arr['chcd']."'")->find();
								if($is_exsit){
									M('shuju_pid')->where("id='".$is_exsit['id']."'")->save($arr);
								}else{
									M('shuju_pid')->add($arr);
								}
							}
						}
						$this->success("保存成功！");
					}else{
						$this->error("请按照模板上传文件！");
					}
				}
			}elseif(in_array($id,array(9,10,11,12,17,21))){//大尺寸显示TVS
			    if($id==9){
					if((trim($res[1][0])=='年份' && trim($res[1][1])=='季度（格式如：Q1、Q2、Q3、Q4）' && trim($res[1][2])=='技术' && trim($res[1][3])=='Brand' && trim($res[1][4])=='Size' && trim($res[1][5])=='尺寸段' && trim($res[1][6])=='出货量(千台）' && $type==1) || (trim($res[1][0])=='ODM Factory' && trim($res[1][1])=='Year' && trim($res[1][2])=='Quarter' && trim($res[1][3])=='Month（格式英文月份前三位，第一位大写，如：Jan）' && trim($res[1][4])=='Domestic/Overseas' && trim($res[1][5])=='Customer' && trim($res[1][6])=='Size' && trim($res[1][7])=='Shipments(K Pcs)' && $type==2) || (trim($res[1][0])=='Year' && trim($res[1][1])=='Quarter' && trim($res[1][2])=='Month（格式如：21.01）' && trim($res[1][3])=='Maker' && trim($res[1][4])=='Size' && trim($res[1][5])=='Shipment(K)' && trim($res[1][6])=='尺寸段' && $type==3) || (trim($res[1][0])=='年度' && trim($res[1][1])=='月度（格式如：21.01）' && trim($res[1][2])=='出货量（千台）' && $type==6) || (trim($res[1][0])=='年度' && trim($res[1][1])=='月度（格式如：21.01）' && trim($res[1][2])=='出口额/人民币' && trim($res[1][3])=='出口数量' && $type==11)){
						array_shift($res);//删除数组中的第一个元素
						if($type==1){
							foreach($res as $value){
								$value[0]=trim($value[0]);
								$value[1]=trim($value[1]);
								$value[2]=trim($value[2]);
								$value[3]=trim($value[3]);
								$value[4]=trim($value[4]);
								$value[5]=trim($value[5]);
								$value[6]=trim($value[6]);
								if($value[1] && $value[2] && $value[3]){
									$arr=array();
									$arr['type']=$id;
									$arr['type2']=$type;
									$arr['nd']=$value[0];
									$arr['jd']=$value[1];
									$arr['odm']=$value[2];
									$arr['gy1']=$value[3];
									$arr['chc']=$value[4];
									$arr['gy2']=$value[5];
									$arr['ch']=$value[6];
									$arr['aid']=$this->hdadmin['id'];
									$arr['addtime']=time();
									$is_exsit=M('shuju_tvs')->where("type='".$arr['type']."' AND type2='".$arr['type2']."' AND nd='".$arr['nd']."' AND jd='".$arr['jd']."' AND odm='".$arr['odm']."' AND gy1='".$arr['gy1']."' AND gy2='".$arr['gy2']."' AND chc='".$arr['chc']."'")->find();
									if($is_exsit){
										//$arr['ch']=$is_exsit['ch']+$arr['ch'];
										M('shuju_tvs')->where("id='".$is_exsit['id']."'")->save($arr);
									}else{
										M('shuju_tvs')->add($arr);
									}
								}
							}
						}elseif($type==2){
							foreach($res as $value){
								$value[0]=trim($value[0]);
								$value[1]=trim($value[1]);
								$value[2]=trim($value[2]);
								$value[3]=trim($value[3]);
								$value[4]=trim($value[4]);
								$value[5]=trim($value[5]);
								$value[6]=trim($value[6]);
								$value[7]=trim($value[7]);
								if($value[1] && $value[2] && $value[3]){
									$arr=array();
									$arr['type']=$id;
									$arr['type2']=$type;
									$arr['odm']=$value[0];
									$arr['nd']=$value[1];
									$arr['jd']=$value[2];
									$arr['yd']=$value[3];
									$arr['gy1']=$value[4];
									$arr['gy2']=$value[5];
									$arr['chc']=$value[6];
									$arr['ch']=$value[7];
									$arr['aid']=$this->hdadmin['id'];
									$arr['addtime']=time();
									$is_exsit=M('shuju_tvs')->where("type='".$arr['type']."' AND type2='".$arr['type2']."' AND odm='".$arr['odm']."' AND nd='".$arr['nd']."' AND jd='".$arr['jd']."' AND yd='".$arr['yd']."' AND gy1='".$arr['gy1']."' AND gy2='".$arr['gy2']."' AND chc='".$arr['chc']."'")->find();
									if($is_exsit){
										//$arr['ch']=$is_exsit['ch']+$arr['ch'];
										M('shuju_tvs')->where("id='".$is_exsit['id']."'")->save($arr);
									}else{
										M('shuju_tvs')->add($arr);
									}
								}
							}
						}elseif($type==3){
							foreach($res as $value){
								$value[0]=trim($value[0]);
								$value[1]=trim($value[1]);
								$value[2]=trim($value[2]);
								$value[3]=trim($value[3]);
								$value[4]=trim($value[4]);
								$value[5]=trim($value[5]);
								$value[6]=trim($value[6]);
								if($value[0] && $value[1] && $value[2]){
									$arr=array();
									$arr['type']=$id;
									$arr['type2']=$type;
									$arr['nd']=$value[0];
									$arr['jd']=$value[1];
									$arr['yd']=$value[2];
									$arr['gy1']=$value[6];
									$arr['gy2']=$value[3];
									$arr['chc']=$value[4];
									$arr['ch']=$value[5];
									$arr['aid']=$this->hdadmin['id'];
									$arr['addtime']=time();
									$is_exsit=M('shuju_tvs')->where("type='".$arr['type']."' AND type2='".$arr['type2']."' AND nd='".$arr['nd']."' AND jd='".$arr['jd']."' AND yd='".$arr['yd']."' AND gy1='".$arr['gy1']."' AND gy2='".$arr['gy2']."' AND chc='".$arr['chc']."'")->find();
									if($is_exsit){
										M('shuju_tvs')->where("id='".$is_exsit['id']."'")->save($arr);
									}else{
										M('shuju_tvs')->add($arr);
									}
								}
							}
						}elseif($type==6){
							foreach($res as $value){
								$value[0]=trim($value[0]);
								$value[1]=trim($value[1]);
								$value[2]=trim($value[2]);
								if($value[0] && $value[1] && $value[2]){
									$arr=array();
									$arr['type']=$id;
									$arr['type2']=$type;
									$arr['nd']=$value[0];
									$arr['yd']=$value[1];
									$arr['ch']=$value[2];
									$arr['aid']=$this->hdadmin['id'];
									$arr['addtime']=time();
									$is_exsit=M('shuju_tvs')->where("type='".$arr['type']."' AND type2='".$arr['type2']."' AND nd='".$arr['nd']."' AND yd='".$arr['yd']."'")->find();
									if($is_exsit){
										M('shuju_tvs')->where("id='".$is_exsit['id']."'")->save($arr);
									}else{
										M('shuju_tvs')->add($arr);
									}
								}
							}
						}elseif($type==11){
							foreach($res as $value){
								$value[0]=trim($value[0]);
								$value[1]=trim($value[1]);
								$value[2]=trim($value[2]);
								$value[3]=trim($value[3]);
								if($value[0] && $value[1] && $value[2]){
									$arr=array();
									$arr['type']=$id;
									$arr['type2']=$type;
									$arr['nd']=$value[0];
									$arr['yd']=$value[1];
									$arr['xe']=$value[2];
									$arr['ch']=$value[3];
									$arr['aid']=$this->hdadmin['id'];
									$arr['addtime']=time();
									$is_exsit=M('shuju_tvs')->where("type='".$arr['type']."' AND type2='".$arr['type2']."' AND nd='".$arr['nd']."' AND yd='".$arr['yd']."'")->find();
									if($is_exsit){
										M('shuju_tvs')->where("id='".$is_exsit['id']."'")->save($arr);
									}else{
										M('shuju_tvs')->add($arr);
									}
								}
							}
						}
						$this->success("保存成功！");
					}else{
						$this->error("请按照模板上传文件！");
					}
				}elseif($id==11){
					if((trim($res[1][0])=='Year' && trim($res[1][1])=='Quarter（格式如：Q1、Q2、Q3、Q4）' && trim($res[1][2])=='Month' && trim($res[1][3])=='Company' && trim($res[1][4])=='Size' && trim($res[1][5])=="Q'ty(Kpcs)" && trim($res[1][6])=='面积-K㎡' && $type==9)){
						array_shift($res);//删除数组中的第一个元素
						if($type==9){
							foreach($res as $value){
								$value[0]=trim($value[0]);
								$value[1]=trim($value[1]);
								$value[2]=trim($value[2]);
								$value[3]=trim($value[3]);
								$value[4]=trim($value[4]);
								$value[5]=trim($value[5]);
								$value[6]=trim($value[6]);
								if($value[0] && $value[1] && $value[3]){
									$arr=array();
									$arr['type']=$id;
									$arr['type2']=$type;
									$arr['nd']=$value[0];
									$arr['jd']=$value[1];
									$arr['yd']=$value[2];
									$arr['gy1']=$value[3];
									$arr['chc']=$value[4];
									$arr['ch']=$value[5];
									$arr['gy2']=$value[6];
									$arr['aid']=$this->hdadmin['id'];
									$arr['addtime']=time();
									$is_exsit=M('shuju_tvs')->where("type='".$arr['type']."' AND type2='".$arr['type2']."' AND nd='".$arr['nd']."' AND jd='".$arr['jd']."' AND yd='".$arr['yd']."' AND gy1='".$arr['gy1']."' AND gy2='".$arr['gy2']."' AND chc='".$arr['chc']."'")->find();
									if($is_exsit){
										//$arr['ch']=$is_exsit['ch']+$arr['ch'];
										M('shuju_tvs')->where("id='".$is_exsit['id']."'")->save($arr);
									}else{
										M('shuju_tvs')->add($arr);
									}
								}
							}
						}
						$this->success("保存成功！");
					}else{
						$this->error("请按照模板上传文件！");
					}
				}elseif($id==12){
					if((trim($res[1][0])=='年份' && trim($res[1][1])=='季度（格式如：Q1、Q2、Q3、Q4）' && trim($res[1][2])=='厂商' && trim($res[1][3])=='尺寸' && trim($res[1][4])=='颜色' && trim($res[1][5])=='出货量（K）' && trim($res[1][6])=='应用场景' && $type==4) || (trim($res[1][0])=='年度' && trim($res[1][1])=='月度（格式如：21.01）' && trim($res[1][2])=='电商平台' && trim($res[1][3])=='品牌' && trim($res[1][4])=='机型' && trim($res[1][5])=='产品类型' && trim($res[1][6])=='销量（台）' && trim($res[1][7])=='销额（元）' && $type==5)){
						array_shift($res);//删除数组中的第一个元素
						if($type==4){
							foreach($res as $value){
								$value[0]=trim($value[0]);
								$value[1]=trim($value[1]);
								$value[2]=trim($value[2]);
								$value[3]=trim($value[3]);
								$value[4]=trim($value[4]);
								$value[5]=trim($value[5]);
								$value[6]=trim($value[6]);
								if($value[1] && $value[2] && $value[3]){
									$arr=array();
									$arr['type']=$id;
									$arr['type2']=$type;
									$arr['nd']=$value[0];
									$arr['jd']=$value[1];
									$arr['odm']=$value[2];
									$arr['chc']=$value[3];
									$arr['gy1']=$value[4];
									$arr['ch']=$value[5];
									$arr['gy2']=$value[6];
									$arr['aid']=$this->hdadmin['id'];
									$arr['addtime']=time();
									$is_exsit=M('shuju_tvs')->where("type='".$arr['type']."' AND type2='".$arr['type2']."' AND nd='".$arr['nd']."' AND jd='".$arr['jd']."' AND odm='".$arr['odm']."' AND gy1='".$arr['gy1']."' AND gy2='".$arr['gy2']."' AND chc='".$arr['chc']."'")->find();
									if($is_exsit){
										//$arr['ch']=$is_exsit['ch']+$arr['ch'];
										M('shuju_tvs')->where("id='".$is_exsit['id']."'")->save($arr);
									}else{
										M('shuju_tvs')->add($arr);
									}
								}
							}
						}elseif($type==5){
							foreach($res as $value){
								$value[0]=trim($value[0]);
								$value[1]=trim($value[1]);
								$value[2]=trim($value[2]);
								$value[3]=trim($value[3]);
								$value[4]=trim($value[4]);
								$value[5]=trim($value[5]);
								$value[6]=trim($value[6]);
								$value[7]=trim($value[7]);
								if($value[1] && $value[2] && $value[3]){
									$arr=array();
									$arr['type']=$id;
									$arr['type2']=$type;
									$arr['nd']=$value[0];
									$arr['yd']=$value[1];
									$arr['odm']=$value[2];
									$arr['gy1']=$value[3];
									$arr['gy2']=$value[4];
									$arr['chc']=$value[5];
									$arr['ch']=$value[6];
									$arr['xe']=$value[7];
									$arr['aid']=$this->hdadmin['id'];
									$arr['addtime']=time();
									$is_exsit=M('shuju_tvs')->where("type='".$arr['type']."' AND type2='".$arr['type2']."' AND odm='".$arr['odm']."' AND nd='".$arr['nd']."' AND yd='".$arr['yd']."' AND gy1='".$arr['gy1']."' AND gy2='".$arr['gy2']."' AND chc='".$arr['chc']."'")->find();
									if($is_exsit){
										//$arr['ch']=$is_exsit['ch']+$arr['ch'];
										M('shuju_tvs')->where("id='".$is_exsit['id']."'")->save($arr);
									}else{
										M('shuju_tvs')->add($arr);
									}
								}
							}
						}
						$this->success("保存成功！");
					}else{
						$this->error("请按照模板上传文件！");
					}
				}elseif($id==17){
					if((trim($res[1][0])=='年度' && trim($res[1][1])=='月度（格式如：21.01）' && trim($res[1][2])=='电商类型' && trim($res[1][3])=='品牌' && trim($res[1][4])=='产品类型' && trim($res[1][5])=='屏幕尺寸' && trim($res[1][6])=='销量' && trim($res[1][7])=='销额' && $type==8) || (trim($res[1][0])=='年度' && trim($res[1][1])=='月度（格式如：21.01）' && trim($res[1][2])=='地区' && trim($res[1][3])=='国家' && trim($res[1][4])=='销量' && trim($res[1][5])=='销额' && $type==10)){
						array_shift($res);//删除数组中的第一个元素
						if($type==8){
							foreach($res as $value){
								$value[0]=trim($value[0]);
								$value[1]=trim($value[1]);
								$value[2]=trim($value[2]);
								$value[3]=trim($value[3]);
								$value[4]=trim($value[4]);
								$value[5]=trim($value[5]);
								$value[6]=trim($value[6]);
								$value[7]=trim($value[7]);
								if($value[1] && $value[2] && $value[3]){
									$arr=array();
									$arr['type']=$id;
									$arr['type2']=$type;
									$arr['nd']=$value[0];
									$arr['yd']=$value[1];
									$arr['odm']=$value[2];
									$arr['gy1']=$value[3];
									$arr['gy2']=$value[4];
									$arr['chc']=$value[5];
									$arr['ch']=$value[6];
									$arr['xe']=$value[7];
									$arr['aid']=$this->hdadmin['id'];
									$arr['addtime']=time();
									$is_exsit=M('shuju_tvs')->where("type='".$arr['type']."' AND type2='".$arr['type2']."' AND odm='".$arr['odm']."' AND nd='".$arr['nd']."' AND yd='".$arr['yd']."' AND gy1='".$arr['gy1']."' AND gy2='".$arr['gy2']."' AND chc='".$arr['chc']."'")->find();
									if($is_exsit){
										//$arr['ch']=$is_exsit['ch']+$arr['ch'];
										M('shuju_tvs')->where("id='".$is_exsit['id']."'")->save($arr);
									}else{
										M('shuju_tvs')->add($arr);
									}
								}
							}
						}elseif($type==10){
							foreach($res as $value){
								$value[0]=trim($value[0]);
								$value[1]=trim($value[1]);
								$value[2]=trim($value[2]);
								$value[3]=trim($value[3]);
								$value[4]=trim($value[4]);
								$value[5]=trim($value[5]);
								if($value[1] && $value[2] && $value[3]){
									$arr=array();
									$arr['type']=$id;
									$arr['type2']=$type;
									$arr['nd']=$value[0];
									$arr['yd']=$value[1];
									$arr['gy1']=$value[2];
									$arr['gy2']=$value[3];
									$arr['ch']=$value[4];
									$arr['xe']=$value[5];
									$arr['aid']=$this->hdadmin['id'];
									$arr['addtime']=time();
									$is_exsit=M('shuju_tvs')->where("type='".$arr['type']."' AND type2='".$arr['type2']."' AND nd='".$arr['nd']."' AND yd='".$arr['yd']."' AND gy1='".$arr['gy1']."' AND gy2='".$arr['gy2']."'")->find();
									if($is_exsit){
										//$arr['ch']=$is_exsit['ch']+$arr['ch'];
										M('shuju_tvs')->where("id='".$is_exsit['id']."'")->save($arr);
									}else{
										M('shuju_tvs')->add($arr);
									}
								}
							}
						}
						$this->success("保存成功！");
					}else{
						$this->error("请按照模板上传文件！");
					}
				}elseif($id==21){
					if((trim($res[1][0])=='年度' && trim($res[1][1])=='月度（格式如：21.01）' && trim($res[1][2])=='电商类型' && trim($res[1][3])=='品牌' && trim($res[1][4])=='游戏本' && trim($res[1][5])=='屏幕尺寸' && trim($res[1][6])=='销量' && trim($res[1][7])=='销额' && $type==12)){
						array_shift($res);//删除数组中的第一个元素
						if($type==12){
							foreach($res as $value){
								$value[0]=trim($value[0]);
								$value[1]=trim($value[1]);
								$value[2]=trim($value[2]);
								$value[3]=trim($value[3]);
								$value[4]=trim($value[4]);
								$value[5]=trim($value[5]);
								$value[6]=trim($value[6]);
								$value[7]=trim($value[7]);
								if($value[1] && $value[2] && $value[3]){
									$arr=array();
									$arr['type']=$id;
									$arr['type2']=$type;
									$arr['nd']=$value[0];
									$arr['yd']=$value[1];
									$arr['odm']=$value[2];
									$arr['gy1']=$value[3];
									$arr['gy2']=$value[4];
									$arr['chc']=$value[5];
									$arr['ch']=$value[6];
									$arr['xe']=$value[7];
									$arr['aid']=$this->hdadmin['id'];
									$arr['addtime']=time();
									$is_exsit=M('shuju_tvs')->where("type='".$arr['type']."' AND type2='".$arr['type2']."' AND odm='".$arr['odm']."' AND nd='".$arr['nd']."' AND yd='".$arr['yd']."' AND gy1='".$arr['gy1']."' AND gy2='".$arr['gy2']."' AND chc='".$arr['chc']."'")->find();
									if($is_exsit){
										//$arr['ch']=$is_exsit['ch']+$arr['ch'];
										M('shuju_tvs')->where("id='".$is_exsit['id']."'")->save($arr);
									}else{
										M('shuju_tvs')->add($arr);
									}
								}
							}
						}
						$this->success("保存成功！");
					}else{
						$this->error("请按照模板上传文件！");
					}
				}
			}
		}else{
			$this->error("请上传EXCEL文件！");
		}
	}
	public function downmb(){
		$id=I('id','',intval);
		$type=I('type','',intval);
		foreach($this->arr_datascate2 as $dk=>$dv){
			foreach($dv as $k=>$v){
				if($id==$k){
					if($type){
						$file['name']=$v.'-'.$this->arr_datascate3[$k][$type].'模板.xlsx';
						$file['url']='./Public/sjmb'.$id.'-'.$type.'.xlsx';
					}else{
						$file['name']=$v.'模板.xlsx';
						$file['url']='./Public/sjmb'.$id.'.xlsx';
					}
				}
			}
		}
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$file['name']);
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file['url']));
		ob_clean();
		flush();
		readfile($file['url']);
		exit;
	}
}
class ExcelToArrary {

    public function __construct() {
        Vendor("PHPExcel.PHPExcel"); //引入phpexcel类(注意你自己的路径)
        Vendor("PHPExcel.PHPExcel.IOFactory");

    }

    public function read($filename, $encode, $file_type) {
        if (strtolower($file_type) == 'xls') {//判断excel表类型为2003还是2007
            Vendor("Excel.PHPExcel.Reader.Excel5");
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        } elseif (strtolower($file_type) == 'xlsx') {
            Vendor("Excel.PHPExcel.Reader.Excel2007");
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        }
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filename);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] = (string) $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }
        return $excelData;
    }
	public function read2($filename, $encode, $file_type) {
        if (strtolower($file_type) == 'xls') {//判断excel表类型为2003还是2007  
            Vendor("Excel.PHPExcel.Reader.Excel5");
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        } elseif (strtolower($file_type) == 'xlsx') {
            Vendor("Excel.PHPExcel.Reader.Excel2007");
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        }
        $objReader->setReadDataOnly(true);
		$objReader->setLoadAllSheets();// 加载所有的工作表
        $objPHPExcel = $objReader->load($filename);
		$excelData = array();
		$excelData['sheetnum'] = $objPHPExcel->getSheetCount();// 获取工作表的个数  
    	$excelData['sheetnames'] = $objPHPExcel->getSheetNames(); // 获取所有工作表的名字数组
		foreach($excelData['sheetnames'] as $k=>$v){
			//$objWorksheet = $objPHPExcel->getActiveSheet();
			$objWorksheet = $objPHPExcel->getSheet($k);
			$highestRow = $objWorksheet->getHighestRow();
			$highestColumn = $objWorksheet->getHighestColumn();
			$highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
			
			for ($row = 1; $row <= $highestRow; $row++) {
				for ($col = 0; $col < $highestColumnIndex; $col++) {
					$excelData['sheet'.$k][$row][] = (string) $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
				}
			}
		}
        
        return $excelData;
    }
}