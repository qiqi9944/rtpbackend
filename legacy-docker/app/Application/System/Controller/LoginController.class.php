<?php
namespace System\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function index(){
        $this->display();
    }
	public function dologin(){
		$username = I('username','',dhtmlspecialchars);
		$password = I('password','',dhtmlspecialchars);
		$verify = I('verify','',dhtmlspecialchars);
		if(empty($username)){
			$this->error('用户名不能为空！',U('Login/index'));
		}
		if(empty($password)){
			$this->error('密码不能为空！',U('Login/index'));
		}
		if(empty($verify)){
			$this->error('验证码不能为空！',U('Login/index'));
		}

		$scode = session('scode');
		if($verify==$scode){
			$password = md5($password);
			$userdata = M('user')->where("username='$username' AND password='$password' AND type=1")->field('id,status')->find();
			if(!empty($userdata)){
				if($userdata['status']==1){
					session('session_admin',$userdata['id']);
					$harr=array();
					$harr['type']=0;//登录记录
					$harr['aid']=$userdata['id'];
					$harr['addtime']=time();
					$harr['ip']=$_SERVER["REMOTE_ADDR"];
					M('history')->add($harr);
					$this->success('登录成功！', U('Index/index'));
				}else{
					$this->error('您的账号未审批或已被禁用，不能登录系统！',U('Login/index'));
				}
			}else{
				$this->error('用户名或密码错误！',U('Login/index'));
			}
		}else{
			$this->error('验证码错误！',U('Login/index'));
		}
	}
	public function logout(){
		session('session_admin',NULL);
		$this->success('退出成功！', U('Login/index'));
	}
}