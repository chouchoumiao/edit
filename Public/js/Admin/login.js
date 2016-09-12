$(function(){

    //光标停在用户名input上
    try{document.getElementById('user_login').focus();}catch(e){}
    if(typeof wpOnload=='function')wpOnload();

    /**
     * 点击注册时做检查
     */
    $("#wp-submit").click(function(){

        var msg = checkForm( 'login' );

        if('' != msg){
            showError( msg );
            wp_shake_js();

            return false;
        }

        if(!ajaxMsg('login')){
            return false;
        }

    });

    /**
     * 使用ajax进行检查
     */
    function ajaxMsg(action){
        $.ajax({
            url:"../Login/index"//改为你的动态页
            ,type:"POST"
            ,data:{
                    'action':action,
                    'user_login':cTrim($('#user_login').val(),0),
                    'user_pass':cTrim($('#user_pass').val(),0)
                }//调用json.js类库将json对象转换为对应的JSON结构字符串
            ,dataType: "json"
            ,beforeSend:function(XMLHttpRequest){
                    $("#wp-submit").val('正在登录...');
                    $("#wp-submit").attr('disabled',true);  //防止多次提交
                }
            ,success:function(json){
                if(json.success == "NG"){

                    if(json.emailErr){
                        var msg = json.msg;
                    }else {
                        var msg = json.msg+"<a href='lostpassword.html'>忘记密码？</a>";
                    }


                    showError( msg );
                    wp_shake_js();
                    $("#wp-submit").val('登录');
                    $("#wp-submit").attr('disabled',false); //恢复按钮

                    return false;
                }else{

                    //登录后假如有session地址，则登录后跳转到改地址，否则则跳入主页
                    if(json.currentUrl == ""){
                        location.href = '../Index/index';
                    }else{
                        location.href = json.currentUrl;
                    }


                }
            }
            ,error:function(xhr){alert('PHP页面有错误！'+xhr.responseText);}
        });
    }

});