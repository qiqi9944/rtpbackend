<?php
namespace System\Controller;
use Think\Controller;
class AjaxController extends Controller {
	//上传图片
    public function uploadimg(){
		$w=I('w');
		$h=I('h');
		if(!empty($_FILES['file']['name'])){
			$upload = new \Think\Upload();// 实例化上传类
			$upload->maxSize   =     8388608 ;// 设置附件上传大小
			$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
			$upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
			$upload->savePath  ='/images/'; // 设置附件上传根目录
			// 上传文件
			$info   =   $upload->upload();
			if($info){
				if($w && $h){
					$pic="/Uploads".$info['file']['savepath'].$info['file']['savename'];
					$thumb = new \Think\Image();
					$thumb->open('.' .$pic);
					$arr_src = explode('.',$pic);
					$arr_src[0]=$arr_src[0].'_thumb';
					$thumbname = implode('.',$arr_src);
					$thumb->thumb($w, $h,\Think\Image::IMAGE_THUMB_CENTER)->save('.' . $thumbname);
					$res['src']=$thumbname;
				}else{
					$res['src']="/Uploads".$info['file']['savepath'].$info['file']['savename'];
				}
				$res['code'] = 0;
			}else{
				$res['code']= 1;
				$res['msg'] = $upload->getError();
			}
		}else{
			$res['code'] = 1;
			$res['msg'] = '上传失败';
		}
		echo json_encode($res);
    }
	//上传文件
	public function uploadexcel(){
		if(!empty($_FILES['file']['name'])){
			$upload = new \Think\Upload();// 实例化上传类
			$upload->maxSize   =     10485760 ;// 设置附件上传大小
			$upload->exts      =     array('xls', 'xlsx');// 设置附件上传类型
			$upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
			$upload->savePath  ='/excel/'; // 设置附件上传根目录
			// 上传文件
			$info   =   $upload->upload();
			if($info){
				$res['src']="/Uploads".$info['file']['savepath'].$info['file']['savename'];
				$res['name']=$info['file']['name'];
				$res['code'] = 0;
			}else{
				$res['code']= 1;
				$res['msg'] = $upload->getError();
			}
		}else{
			$res['code'] = 1;
			$res['msg'] = '上传失败';
		}
		echo json_encode($res);
    }
	
}