<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 空控制器
 * Class EmptyController
 * @package Admin\Controller
 */
class EmptyController extends Controller {

    /**
     * 空模块是判断是否有session，有就进入index画面，没有进入登录页面
     */
    public function index(){
        if( (!isset($_SESSION['username'])) || ('' == $_SESSION['username']) ){
            //无session则进入后台主页面
            $this->redirect('Login/login');
        }else{
            //有session则进入后台主页面
            $this->redirect('Index/index');
        }
    }
}
