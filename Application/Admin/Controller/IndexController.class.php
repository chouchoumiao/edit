<?php
namespace Admin\Controller;
use Think\Controller;

class IndexController extends CommonController {
    //private $username;

    /**
     * 显示主页面
     */
    public function index(){
        //取得当前用户的信息
        $userInfo = D('User')->getTheUserInfo();
        //根据用户的权限来分别显示对应功能
        if($userInfo){

            //将需要显示的img信息给放入session中，方便前台调用
            $_SESSION['img'] = $userInfo['udi_img'];

            $this->assign('userInfo',$userInfo);
            $this->display();
        }else{
            $this->error('取得当前用户信息失败,请重新登录','Login/login');
        }
    }

    public function doAction(){

        $action = $_GET['action'];
        if( isset($action) && '' != $action ){
            switch($action){
                case 'editUserInfo':
                    $userInfo = D('User')->getTheUserInfo();
                    $this->assign('userInfo',$userInfo);
                    $this->display('profile');
                    break;
            }
        }

    }


}
