<?php
namespace Admin\Controller;
use Think\Controller;

class IndexController extends CommonController {
    //private $username;

    /**
     * 显示主页面
     */
    public function index(){

        //$this->display('upload');

        //dump($_SESSION);exit;
        //取得当前用户的信息
        $userInfo = D('User')->getTheUserInfo($_SESSION['uid']);

        //根据用户的权限来分别显示对应功能
        if($userInfo){
            $this->assign('userInfo',$userInfo);
            $this->display();
        }else{
            $this->error('取得当前用户信息失败,请重新登录');
        }
    }
//
//    public function doAction(){
//
//        $action = $_GET['action'];
//        if( isset($action) && '' != $action ){
//            switch($action){
//
//                //
//                case 'the':
//                    $userInfo = D('User')->the();
//                    $this->assign('userInfo',$userInfo);
//                    $this->display('profile');
//                    break;
//            }
//        }
//
//    }


}
