$(function(){

    $('#upload').change(function(){

        //alert($('#upload').val());
        //$('#upload-img').src = '/edit/Public/img/Admin/profile/profile2.jpg';
        $("#upload-img").attr("src",$('#upload').val());
    });

});

function checkNewUser(){

    var userval = $('#user_login').val();

    if( isNull( userval ) ){
        alert('用户名不能为空！');
        return false
    }

    if( userval.length < 6){
        alert('用户名必须大于等于6位！');
        return false;
    }

    var emailval = $('#user_email').val();
    if( (isNull( emailval )) || ('' == cTrim(emailval,0)) ){
        alert('邮箱地址不能为空！');
        return false;
    }
    if( !isEmail( emailval ) ){
        alert('请输入正确的邮箱地址！');
        return false;
    }

    var passval = $('#user_pass').val();

    if( (isNull( passval )) || ('' == cTrim(passval,0)) ){
        alert('密码不能为空！');
        return false;
    }

    //判断部门复选框是否都没有选中
    if( 0 == ($("input[class='checkbox-purple']:checked").length)){
        alert('至少选中一个部门');
        return false;
    }


}

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
            }else if(json.success == "OK"){
                alert(json.msg);
                //登录成功跳转到后台首页
                location = "./Admin/User/doAction/action/all";

            }
        }
        ,error:function(xhr){alert('PHP页面有错误！'+url+xhr.responseText);}
    });
}

