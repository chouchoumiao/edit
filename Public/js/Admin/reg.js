$(function(){

    //光标停在用户名input上
    try{document.getElementById('user_login').focus();}catch(e){}
    if(typeof wpOnload=='function')wpOnload();

    $("#wp-submit").click(function(){

        var msg = checkForm( 'login' );

        if('' != msg){
            showError( msg );
            wp_shake_js();

            return false;
        }

        if(!ajaxMsg('reg')){
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
                    'user_email':cTrim($('#user_email').val(),0)
                }//调用json.js类库将json对象转换为对应的JSON结构字符串
            ,dataType: "json"
            ,beforeSend:function(XMLHttpRequest){
                    $("#wp-submit").val('正在提交...');
                    $("#wp-submit").attr('disabled',true);      //防止多次提交
                }
            ,success:function(json){

                $("#wp-submit").val('注册');
                $("#wp-submit").attr('disabled',false);         //防止多次提交恢复按钮

                if(json.success == "NG"){
                    showError( json.msg );
                    wp_shake_js();
                    return false;
                }else{
                    var msg = json.msg+'<a href="login.html">点我登录</a>'
                    showMSg(msg);
                }
            }
            ,error:function(xhr){alert('PHP页面有错误！'+xhr.responseText);}
        });
    }

});