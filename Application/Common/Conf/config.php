<?php

define(MY_SITE, $_SERVER['SERVER_NAME']);				//发送邮件附带地址的时候需要  本地环境
//define(MY_SITE, 'www.chouchoumiao.com');  //发送邮件附带地址的时候需要  正式环境使用


return array(

	'SHOW_PAGE_TRACE'=>true,        //开启追踪调试

	/********************数据库配置**************************/
	'DB_TYPE' => 'mysql',
	'DB_HOST' => 'localhost',
	'DB_NAME' => 'editSystem',
	'DB_USER' => 'root',
	'DB_PWD' => '84112326Wu',
	'DB_PORT' => 3306,
	'DB_PREFIX' => 'ccm_',
	/********************数据库配置**************************/

	/********************邮件设置(管理员邮箱)**************************/
    'MAIL_ADDRESS'=>'wu_jy1984@126.com', // 邮箱地址
    'MAIL_LOGINNAME'=>'wu_jy1984@126.com', // 邮箱登录帐号
    'MAIL_SMTP'=>'smtp.126.com', // 邮箱SMTP服务器
    'MAIL_PASSWORD'=>'84112326Wujiayu', // 邮箱密码
	/********************邮件设置(管理员邮箱)**************************/



);