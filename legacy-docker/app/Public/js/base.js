function surelogout(){
	if(confirm('确认退出账号？')){
		return true;
	}else{
		return false;
	}
}
function upload_del(obj){
	if(confirm('确认删除？')){
		$(obj).parent().remove();
		return true;
	}else{
		return false;
	}
}
function hd_cyfl(val,index){
	$.ajax({
		type:"POST", //AJAX提交方式为GET提交
		url:"/System/Ajax/docyfl", //处理页的URL地址
		data:{"cyfl":val},//要传递的参数
		datatype:'json',
		success:function(data){ //成功后执行的方法
			var html='<option value="0">---请选择---</option>';
			if(data){
				data = eval(data);
				for(i=0;i<data.length;i++){
					if(index==data[i].id){
						html +='<option value="'+data[i].id+'" selected="selected">'+data[i].name+'</option>';
					}else{
						html +='<option value="'+data[i].id+'">'+data[i].name+'</option>';
					}
				}
				
			}
			$('#cyfl2').html(html);
		},
	});
	
}
