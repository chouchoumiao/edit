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
                }
            ,success:function(json){
                if(json.success == "NG"){
                    showError( json.msg );
                    wp_shake_js();
                    $("#wp-submit").val('登录');

                    return false;
                }else{
                    //登录成功跳转到后台首页
                    location = '../Index/index';

                }
            }
            ,error:function(xhr){alert('PHP页面有错误！'+xhr.responseText);}
        });
    }

});