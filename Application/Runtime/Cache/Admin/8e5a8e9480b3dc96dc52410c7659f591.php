<?php if (!defined('THINK_PATH')) exit();?><html lang="zh-CN" xmlns="http://www.w3.org/1999/xhtml"><!--<![endif]-->

<head>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="noindex,follow" name="robots">
	
    <title>稿库系统 ‹ 忘记密码</title>
    
    <link href="/edit/Public/css/Admin/login.css" type="text/css" rel="stylesheet">
    <meta content="noindex,follow" name="robots">
</head>
<body class="login login-action-lostpassword wp-core-ui  locale-zh-cn">
	<div id="login">
        <h1>
            <a tabindex="-1" title="ZanBlog" href="http://localhost/NewWordpress">稿库系统</a>
        </h1>
        <p class="message">请输入您的用户名或电子邮箱地址。您会收到一封包含创建新密码链接的电子邮件。</p>

        <form method="post" action="/edit/index.php/Admin/Login/index" id="lostpasswordform" name="lostpasswordform">
            <p>
                <label for="user_login">用户名或电子邮件地址<br>
                <input type="text" size="20" value="" class="input" id="user_login" name="user_login"></label>
            </p>
                <input type="hidden" value="" name="redirect_to">
            <p class="submit">
                <input type="submit" value="获取新密码" class="button button-primary button-large" id="wp-submit" name="wp-submit">
            </p>
        </form>

        <p id="nav">
            <a href="login.html">登录</a> | 
            <a href="./register.html">注册</a></p>

        <p id="backtoblog">
        <a href="http://localhost/NewWordpress/">← 回到稿库系统</a></p>
    </div>

    <script type="text/javascript" src="https://cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
    <script type="text/javascript" src="/edit/Public/js/common.js?v=20160101"></script>
    <script type="text/javascript" src="/edit/Public/js/Admin/lostpass.js?v=20160102"></script>

    <div class="clear"></div>
</body>
</html>