$(function(){

    //光标停在用户名input上
    try{document.getElementById('user_login').focus();}catch(e){}
    if(typeof wpOnload=='function')wpOnload();

    /**
     * 光标离开用户输入input时自动做检查
     */
    // $('#user_login').blur(function () {


    //     var user  = cTrim($('#user_login').val(),0);
    //     var msg = '';

    //     if(isNull(user)){
    //         msg = '用户名不能为空！';
    //         showError( msg );
    //         wp_shake_js();
    //         return false;
    //     }
    //     if(user.length < 6){
    //         msg = '用户名必须大于等于6位！';
    //         showError( msg );
    //         wp_shake_js();
    //         return false;
    //     }
    // });

    // *
    //  * 光标离开邮箱输入input时自动做检查
     
    // $('#user_email').blur(function () {
    //     var email  = cTrim($('#user_email').val(),0);
    //     var msg = '';

    //     if(isNull(email)){
    //         msg = '邮箱地址不能为空！';
    //         showError( msg );
    //         wp_shake_js();
    //         return false;
    //     }
    //     if(!isEmail(email)){
    //         msg = '请输入正确的邮箱地址！';
    //         showError( msg );
    //         wp_shake_js();
    //         return false;
    //     }
    // });

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

        if(!ajaxMsg('reg')){
            return false;
        }

    });

    // /**
    //  * 点击获取密码时做检查
    //  */
    $("#lost-submit").click(function(){

        alert(99);
        var msg = checkForm();

        if('' != msg){
            showError( msg );
            wp_shake_js();

            return false;
        }

        if(!ajaxMsg('lostpass')){
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
                }
            ,success:function(json){
                if(json.success == "NG"){
                    showError( json.msg );
                    wp_shake_js();
                    $("#wp-submit").val('注册');

                    return false;
                }else{
                    $("html").html(json.msg);

                }
            }
            ,error:function(xhr){alert('PHP页面有错误！'+xhr.responseText);}
        });
    }

});