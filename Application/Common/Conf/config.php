<?php

header("Content-type: text/html;charset=utf-8");

define(MY_SITE, $_SERVER['SERVER_NAME']);   //发送邮件附带地址的时候需要  本地环境
define(PAGE_SHOW_COUNT,6);

define(DOCUMENT_ROOT,$_SERVER['DOCUMENT_ROOT']);

//头像文件夹地址
define(PROFILE_PATH,$_SERVER['DOCUMENT_ROOT'].'/edit/Public/Uploads/profile');
//文章图片文件夹路径
define(POST_PATH,$_SERVER['DOCUMENT_ROOT'].'/edit/Public/Uploads/post');
//资源库文件夹路径
define(MEDIA_PATH,$_SERVER['DOCUMENT_ROOT'].'/edit/Public/Uploads/Media');

//角色设定
define(BAOLIAOZHE,1);       //爆料者
define(XIAOBIAN,2);         //小编
define(ZONGBIAN,3);         //总编
define(DEPT_ADMIN,4);       //部门管理员
define(ADMIN,'88');         //管理员
define(SUPPER_ADMIN,'99');  //超级管理员

//文章状态
define(POST_SAVE,'save');           //保存
define(POST_PENDING,'pending');     //初审
define(POST_PENDING2,'pending2');   //最终审核
define(POST_PENDED,'pended');       //审核通过
define(POST_DISMISS,'dismiss');     //审核不通过



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
    'DB_CHARSET' => 'utf8',
	/********************数据库配置**************************/

	/********************邮件设置(管理员邮箱)**************************/
    'MAIL_ADDRESS'=>'wu_jy1984@126.com', // 邮箱地址
    'MAIL_LOGINNAME'=>'wu_jy1984@126.com', // 邮箱登录帐号
    'MAIL_SMTP'=>'smtp.126.com', // 邮箱SMTP服务器
    'MAIL_PASSWORD'=>'84112326Wujiayu', // 邮箱密码
	/********************邮件设置(管理员邮箱)**************************/


    'DEFAULT_CHARSET'       => 'utf-8', // 默认输出编码


	'DEFAULT_MODULE' => 'Admin',		// 追加默认模块设置为Admin

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
        1	=>'爆料者',
        2	=>'小编',
        3	=>'总编',
        4	=>'部门管理员',
        88	=>'管理员',
        99	=>'超级管理员'
    ),

    //激活状态数组
    'STATUS_ARRAY' => array(
        0=>'未激活',
        1=>'已激活'
    ),

	//文章状态
    'POST_STATUS'  => array(
        'save'     => '保存',
        'pending'  => '待审核',
        'pending2' => '待最终审核',
        'dismiss'  => '审核未通过',
        'pended'   => '已审核通过',
        'close'    => '所有部门审核完毕'
    ),

	//定义资源库可上传的文件后缀
	'POST_UPLOAD_TYPE_ARRAY' => array(
		'jpg','png','jpeg','gif',
		'txt','xls','pdf','doc',
		'xlsx','docx','pptx','pptx'
	),


	'MEDIA_TYPE_ARRAY'=> array(
		'jpg','png','jpeg','gif'
	),


	'FILE_TYPE_ARRAY'=> array(
		'txt','xls','pdf','doc',
		'xlsx','docx','pptx','pptx'
	),



);