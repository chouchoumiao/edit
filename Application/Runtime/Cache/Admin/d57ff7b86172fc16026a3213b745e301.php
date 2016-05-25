<?php if (!defined('THINK_PATH')) exit();?><html lang="zh-CN" xmlns="http://www.w3.org/1999/xhtml"><!--<![endif]-->
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="noindex,follow" name="robots">

    <title>稿库系统 ‹ 注册表单</title>

    <link href="/edit/Public/css/Admin/login.css" type="text/css" rel="stylesheet">
    <meta content="noindex,follow" name="robots">
</head>
<body class="login login-action-register wp-core-ui  locale-zh-cn">
<div id="login">
    <h1>
        <a tabindex="-1" title="ZanBlog" href="http://localhost/NewWordpress">稿库系统</a>
    </h1>
    <p class="message register">在这个站点注册</p>
    <form novalidate="novalidate" method="post" action="/edit/index.php/Admin/Login/index" id="registerform" name="registerform">
        <p>
            <label for="user_login">用户名<br>
                <input type="text" size="20" value="" class="input inputxt Validform_error" id="user_login" name="user_login" />
            </label>
        </p>
        <p>
            <label for="user_email">电子邮件<br>
                <input type="email" size="25" value="" class="input" id="user_email" name="user_email" >
            </label>
        </p>
        <p id="reg_passmail">注册确认信将会被寄给您。</p>
        <br class="clear">
        <input type="hidden" value="" name="redirect_to">
        <p class="submit">
            <input type="submit" value="注册" class="button button-primary button-large" id="wp-submit" name="wp-submit">
        </p>
    </form>

    <p id="nav">
        <a href="login.html">登录</a> |
        <a href="lostpassword.html">忘记密码？</a>
    </p>
</div>

<script type="text/javascript" src="https://cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
<script type="text/javascript" src="/edit/Public/js/common.js?v=20160101"></script>
<script type="text/javascript" src="/edit/Public/js/Admin/login.js?v=20160120"></script>

<div class="clear"></div>

</body>
</html>