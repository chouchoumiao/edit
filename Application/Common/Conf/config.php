<?php

header("Content-type: text/html;charset=utf-8");

define(MY_SITE, $_SERVER['SERVER_NAME']);   //发送邮件附带地址的时候需要  本地环境
define(PAGE_SHOW_COUNT,6);

define(DOCUMENT_ROOT,$_SERVER['DOCUMENT_ROOT']);
define(PROFILE_PATH,$_SERVER['DOCUMENT_ROOT'].'/edit/Public/Uploads/profile');

//角色设定
define(BAOLIAOZHE,1);       //爆料者
define(XIAOBIAN,2);         //小编
define(ZONGBIAN,3);         //总编
define(ADMIN,'88');         //管理员
define(SUPPER_ADMIN,'99');  //超级管理员


return array(

    'DB_DEBUG' => true,


//	'SHOW_PAGE_TRACE'=>true,        //开启追踪调试

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


    'DEFAULT_CHARSET'       => 'utf-8', // 默认输出编码

    //性别数组
    'SEX_ARRAY' => array(
        0=>'女',
        1=>'男'
    ),

    //部门数组
    'DEPT_ARRAY' => array(
        1=>'路桥发布',
        2=>'路桥新闻',
        3=>'台州新闻',
        4=>'五水共治'
    ),

    //角色数组
    'AUTO_ARRAY' => array(
        1=>'爆料者',
        2=>'小编',
        3=>'总编',
        4=>'管理员',
        5=>'超级管理员',
    ),

    //激活状态数组
    'STATUS_ARRAY' => array(
        0=>'未激活',
        1=>'已激活'
    ),

    'POST_STATUS' => array(
        'save'    => '保存',
        'pending' => '待审核',
        'pended'  => '已审核'
    ),




);