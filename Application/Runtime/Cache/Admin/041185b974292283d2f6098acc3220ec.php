<?php if (!defined('THINK_PATH')) exit();?><html lang="zh-CN" xmlns="http://www.w3.org/1999/xhtml"><!--<![endif]-->
<head>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="noindex,follow" name="robots">
    
	<title>稿库系统 ‹ 登录</title>
    
    <link href="/edit/Public/css/Admin/login.css?v=20160103" type="text/css" rel="stylesheet">
    
</head>
<body class="login login-action-login wp-core-ui  locale-zh-cn">
<div id="login">
    <h1>
        <a tabindex="-1" title="ZanBlog" href="http://localhost/NewWordpress">稿库系统</a>
    </h1>
	
    <form method="post" action="http://localhost/NewWordpress/wp-login.php" id="loginform" name="loginform">
        <p>
            <label for="user_login">用户名或电子邮件地址<br>
            <input type="text" size="20" value="" class="input" id="user_login" name="log"></label>
        </p>
        <p>
            <label for="user_pass">密码<br>
            <input type="password" size="20" value="" class="input" id="user_pass" name="pwd"></label>
        </p>
            <p class="forgetmenot"><label for="rememberme"><input type="checkbox" value="forever" id="rememberme" name="rememberme"> 记住我的登录信息</label></p>
        <p class="submit">
            <input type="submit" value="登录" class="button button-primary button-large" id="wp-submit" name="wp-submit">
            <input type="hidden" value="http://localhost/NewWordpress/wp-admin/" name="redirect_to">
            <input type="hidden" value="1" name="testcookie">
        </p>
    </form>

    <p id="nav">
        <a href="./register.html">注册</a> |
        <a href="./lostpassword.html">忘记密码？</a>
    </p>

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

    <p id="backtoblog"><a href="http://localhost/NewWordpress/">← 回到稿库系统</a></p>
	
	</div>
    
    <div class="clear"></div>
	
</body>
</html>