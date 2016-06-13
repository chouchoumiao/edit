$(function(){

    $('#upload').change(function(){

        //alert($('#upload').val());
        //$('#upload-img').src = '/edit/Public/img/Admin/profile/profile2.jpg';
        $("#upload-img").attr("src",$('#upload').val());
    });

});

function checkForm(){

    var userval = $('#user_login').val();
    if( isNull( userval ) ){
        return '用户名不能为空！';
    }

    if( userval.length < 6){
        return '用户名必须大于等于6位！';
    }

    var emailval = $('#user_email').val();
    if( (isNull( emailval )) || ('' == cTrim(emailval,0)) ){
        return '邮箱地址不能为空！';
    }
    if( !isEmail( emailval ) ){
        return '请输入正确的邮箱地址！';
    }

    var passval = $('#user_pass').val();

    if( (isNull( passval )) || ('' == cTrim(passval,0)) ){
        return '密码不能为空！';
    }

    return '';
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
            }else{
                //登录成功跳转到后台首页
                location = "./Admin/User/doAction/action/all";

            }
        }
        ,error:function(xhr){alert('PHP页面有错误！'+url+xhr.responseText);}
    });
}

