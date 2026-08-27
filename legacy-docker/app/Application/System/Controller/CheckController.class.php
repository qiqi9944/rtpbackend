<?php
namespace System\Controller;
use Think\Controller;
class CheckController extends Controller {
    public function _initialize(){
		$adminid = session('session_admin');
		$hdadmin = M('user')->where("id='".$adminid."'")->find();
		if(empty($adminid) || empty($hdadmin['id'])){
			session('session_admin',NULL);
			$this->redirect('Login/index');
		}else{
			if($hdadmin['type']!=1){
				session('session_admin',NULL);
				$this->error("您无权进入该系统！",U('Login/index'));
			}
			if($hdadmin['status']!=1){
				session('session_admin',NULL);
				$this->error("您的账号已被禁用，不能登录系统",U('Login/index'));
			}
			if($hdadmin['lanmu']){
			   $hdadmin['lanmu']= explode(',',$hdadmin['lanmu']);
			}
			$this->hdadmin=$hdadmin;
		}
		$this->arr_datascate1=C('Lt_datascate1');
		$this->arr_datascate2=C('Lt_datascate2');
		$this->arr_datascate3=C('Lt_datascate3');
		$this->arr_lm=C('Lt_lm');
		$this->arr_usertype=C('Lt_usertype');
		$this->arr_newstype=C('Lt_newstype');
		$this->arr_newstag=C('Lt_newstag');
		$this->arr_bannerwz=C('Lt_bannerwz');
	}
}