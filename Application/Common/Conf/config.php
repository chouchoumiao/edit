<?php

header("Content-type: text/html;charset=utf-8");

define(MY_SITE, $_SERVER['SERVER_NAME']);   //发送邮件附带地址的时候需要  本地环境

define(DOCUMENT_ROOT,$_SERVER['DOCUMENT_ROOT']);

define(PUBLIC_PATH,$_SERVER['DOCUMENT_ROOT'].'/edit/Public');

//头像文件夹地址
define(PROFILE_PATH,$_SERVER['DOCUMENT_ROOT'].'/edit/Public/Uploads/profile');
//文章图片文件夹路径
define(POST_PATH,$_SERVER['DOCUMENT_ROOT'].'/edit/Public/Uploads/post');
//文章附件文件夹路径
define(POST_ATTACHMENT_PATH,$_SERVER['DOCUMENT_ROOT'].'/edit/Public/Uploads/postAttachment');
//资源库文件夹路径
define(MEDIA_PATH,$_SERVER['DOCUMENT_ROOT'].'/edit/Public/Uploads/Media');

define(CURRENT_URL,'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

define(PAGE_SHOW_COUNT_6,6);	//每页显示6条记录
define(PAGE_SHOW_COUNT_10,10);	//每页显示10条记录

//角色设定
define(BAOLIAOZHE,1);       //爆料者
define(XIAOBIAN,2);         //小编
define(ZONGBIAN,3);         //总编
define(DEPT_ADMIN,4);       //部门管理员
define(ADMIN,88);           //管理员
define(SUPPER_ADMIN,99);    //超级管理员

//角色名称设定
define(TONGXUNYUAN_NAME,'通讯员');       //通讯员名称
define(BAOLIAOZHE_NAME,'爆料者');       //爆料者名称


//文章状态
define(POST_SAVE,'save');           //保存
define(POST_PENDING,'pending');     //初审
define(POST_PENDING2,'pending2');   //最终审核
define(POST_PENDED,'pended');       //审核通过
define(POST_DISMISS,'dismiss');     //审核不通过
define(POST_RETURN,'return');       //文章被打回给小编

return array(

	'SHOW_ERROR_MSG'        =>  true,

    //'DB_DEBUG' => true,

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
    'MAIL_ADDRESS'=>'linx2046@163.com', // 邮箱地址
    'MAIL_LOGINNAME'=>'linx2046@163.com', // 邮箱登录帐号
    'MAIL_SMTP'=>'smtp.163.com', // 邮箱SMTP服务器
    'MAIL_PASSWORD'=>'mon4184860', // 邮箱密码
	/********************邮件设置(管理员邮箱)**************************/


    'DEFAULT_CHARSET' => 'utf-8', // 默认输出编码


	'DEFAULT_MODULE' => 'Admin',  // 追加默认模块设置为Admin

    //性别数组
    'SEX_ARRAY' => array(
        0=>'女',
        1=>'男'
    ),

	//部门数组
	'DEPT_ARRAY' => array(
		1=>'路桥发布',
		2=>'今日路桥',
		3=>'路桥新闻网',
		4=>'视听路桥'
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
		-1=>'未审核',
        0=>'未激活',
        1=>'已激活'
    ),

	//文章状态
    'POST_STATUS'  => array(
        'save'     => '保存',
        'pending'  => '待审核',
        'pending2' => '待最终审核',
        'dismiss'  => '审核未通过',
        'return'   => '文章打回',
        'pended'   => '已审核通过',
        'close'    => '所有部门审核完毕'
    ),

	//文章信息的状态
	'POST_ALLOW_STATUS'  => array(
		'save',
		'pending',
		'pending2',
		'dismiss',
		'return',
		'pended',
		'close'
	),


    /**********************上传时一般设定**********************/

    //文件上传默认大小:5M
    'FILE_SIZE' => 5242880,
    
    //定义资源库可上传的文件后缀
	'POST_UPLOAD_TYPE_ARRAY' => array(
		'jpg','png','jpeg','gif',
		'txt','xls','pdf','doc',
		'xlsx','docx','pptx','pptx'
	),

    //定义资源库可上传的文件后缀
    'POST_UPLOAD_Attachment_TYPE_ARRAY' => array(
        'zip','7z','rar',
        'jpg','png','jpeg','gif',
        'txt','xls','pdf','doc',
        'xlsx','docx','pptx','pptx'
    ),

    //图片资源后缀
	'MEDIA_TYPE_ARRAY'=> array(
		'jpg','png','jpeg','gif'
	),

    //文档资源后缀
	'FILE_TYPE_ARRAY'=> array(
		'txt','xls','pdf','doc',
		'xlsx','docx','pptx','pptx'
	),
    /**********************上传时一般设定**********************/


);