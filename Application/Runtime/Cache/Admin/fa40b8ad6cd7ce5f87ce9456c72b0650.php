<?php if (!defined('THINK_PATH')) exit();?><html lang="zh-CN" xmlns="http://www.w3.org/1999/xhtml"><!--<![endif]-->
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="noindex,follow" name="robots">

    <title>稿库系统 ‹ 注册表单</title>

    <link href="/edit/Public/css/Admin/login.css" type="text/css" rel="stylesheet"><meta content="noindex,follow" name="robots">
</head>
<body class="login login-action-register wp-core-ui  locale-zh-cn">
<div id="login">
    <h1>
        <a tabindex="-1" title="ZanBlog" href="http://localhost/NewWordpress">稿库系统</a>
    </h1>
    <p class="message register">在这个站点注册</p>
    <form novalidate="novalidate" method="post" action="/edit/index.php/Admin/Login/Register/reg" id="registerform" name="registerform">
        <p>
            <label for="user_login">用户名<br>
                <input type="text" size="20" value="" class="input" id="user_login" name="user_login" datatype="*6-15" errormsg ="密码范围在6~15位之间" /></label>
        </p>
        <p>
            <label for="user_email">电子邮件<br>
                <input type="email" size="25" value="" class="input" id="user_email" name="user_email"></label>
        </p>
        <p id="reg_passmail">注册确认信将会被寄给您。</p>
        <br class="clear">
        <input type="hidden" value="" name="redirect_to">
        <p class="submit"><input type="submit" value="注册" class="button button-primary button-large" id="wp-submit" name="wp-submit"></p>
    </form>

    <p id="nav">
        <button onclick="wp_shake_js();">aaa</button>
        <a href="login.html">登录</a> |
        <a href="lostpassword.html">忘记密码？</a>
    </p>

    <p id="backtoblog"><a href="http://localhost/NewWordpress/">← 回到稿库系统</a></p>

</div>

<script type="text/javascript" src="http://validform.rjboy.cn/wp-content/themes/validform/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="http://validform.rjboy.cn/Validform/v5.1/Validform_v5.1_min.js"></script>
<script type="text/javascript">

    //$('#registerform').Validform();

    function wp_shake_js (){
        addLoadEvent = function( func ){
            if(typeof jQuery != "undefined")
                jQuery(document).ready( func );
            else if( typeof wpOnload != 'function' ){
                wpOnload = func;
            }else{
                var oldonload=wpOnload;
                wpOnload = function(){
                    oldonload();
                    func();
                }
            }
        };

        function s(id,pos){
            g(id).left=pos+'px';
        }

        function g(id){
            return document.getElementById(id).style;
        }

        function shake(id,a,d){
            c = a.shift();
            s( id, c );
            if( a.length > 0 ){
                setTimeout( function(){
                    shake(id,a,d);
                },d);
            }else{
                try{g(id).position='static';
                    wp_attempt_focus();
                }catch(e){

                }
            }
        }

        addLoadEvent(function(){
            var p = new Array(15,30,15,0,-15,-30,-15,0);
            p = p.concat(p.concat(p));
            var i=document.forms[0].id;g(i).position='relative';
            shake(i,p,20);
        });
    }




    try{document.getElementById('user_login').focus();}catch(e){}
    if(typeof wpOnload=='function')wpOnload();
</script>

<div class="clear"></div>

</body>
</html>