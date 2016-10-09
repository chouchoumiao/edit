<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>登录</title>
        <!--<title>稿库系统 ‹ 登录</title>-->

        <link href="/edit/Public/css/Admin/load-style.css" type="text/css" rel="stylesheet">
        <link href="/edit/Public/css/Admin/admin.css" type="text/css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        
    </head>
    <body class="login login-action-login wp-core-ui  locale-zh-cn">
        <div id="login">
            <h1>
                <a tabindex="-1" title="ZanBlog" href="#">稿库系统</a>
            </h1>

            

    <form method="post" action="/edit/index.php/Admin/Login/login" id="loginform" name="loginform">
        <p>
            <label for="user_login">用户名或电子邮件地址<br>
            <input type="text" size="20" value="" class="input" id="user_login" name="log"></label>
        </p>
        <p>
            <label for="user_pass">密码<br>
            <input type="password" size="20" value="" class="input" id="user_pass" name="pwd"></label>
        </p>
            <p class="forgetmenot">
                <label for="rememberme">
                    <input type="checkbox" value="forever" id="rememberme" name="rememberme"> 记住我的登录信息
                </label>
            </p>
        <p class="submit">
            <input type="submit" value="登录" class="button button-primary button-large" id="wp-submit" name="wp-submit">
            <input type="hidden" value="http://localhost/NewWordpress/wp-admin/" name="redirect_to">
            <input type="hidden" value="1" name="testcookie">
        </p>
    </form>

    <p id="nav">
        <a href="./register.html">注册</a> |
        <a href="lostpassword.html">忘记密码？</a>
    </p>



        </div>

        <div class="clear"></div>

        <script type="text/javascript" src="https://cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
        <script type="text/javascript" src="/edit/Public/js/common.js?v=20160101"></script>

        
    <script type="text/javascript">
        function wp_attempt_focus(){
            setTimeout( function(){ try{
                d = document.getElementById('user_login');
                d.focus();
                d.select();
            } catch(e){}
            }, 200);
        }

        wp_attempt_focus();
        if(typeof wpOnload=='function')wpOnload();
    </script>
    <script type="text/javascript" src="/edit/Public/js/Admin/login.js?v=20160104"></script>

    </body>
</html>