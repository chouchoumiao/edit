<?php
namespace Admin\Controller;
use Think\Controller;

class IndexController extends CommonController {
    private $username;

    /**
     * 显示主页面
     */
    public function index(){

        //将用户信息写入session（已经在CommonController中判定没有session跳转登录也操作了）
        $this->username = isset($_SESSION['$username'])?$_SESSION['$username']:array();

        //显示后台主页面
        $this->assign('data',D('Login')->showMain($this->auth['username']));
        $this->display();
    }


}