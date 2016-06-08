function delUser(id){

    if (!confirm('确定要删除吗？')){
        return;
    }

    $.ajax({
        url:"./Admin/User/doAction/action/del"//改为你的动态页
        ,type:"POST"
        ,data:{
            'id':id
        }//调用json.js类库将json对象转换为对应的JSON结构字符串
        ,dataType: "json"
        ,success:function(json){

            //alert(json.msg);return;
            if(json.success == "NG"){
                alert('删除失败')
                return false;
            }else{
                //登录成功跳转到后台首页
                location = "./Admin/User/doAction/action/all";

            }
        }
        ,error:function(xhr){alert('PHP页面有错误！'+url+xhr.responseText);}
    });

}