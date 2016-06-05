<?php
namespace Admin\Controller;
use Think\Controller;

class UserController extends CommonController {
    //private $username;

    /**
     * 显示主页面
     */
    public function index(){

    }

    public function doAction(){

        $action = $_GET['action'];
        if( isset($action) && '' != $action ){
            switch($action){

                //取得所有用户
                case 'all':
                    $this->assign('allUser',D('User')->getAllUserInfo());
                    $this->display('all');
                    break;

                //取得当前用户
                case 'the':
                    //如果有传值过来用查询传值的用户
                    if(isset($_GET['id']) && '' != $_GET['id']){
                        $userId = I('get.id');
                    }else{  //没有传值的话就查询本登录的用户信息
                        $userId = $_SESSION['uid'];
                    }

                    $userInfo = D('User')->getTheUserInfo($userId);
                    $this->assign('userInfo',$userInfo);
                    $this->display('profile');
                    break;

                //删除用户
                case 'del':

                    //如果有传值过来用查询传值的用户
                    if(isset($_GET['id']) && '' != $_GET['id']){

                        if(D('User')->delTheUserInfo(I('get.id'))){
                            $arr['success'] = 1;
                        }else{
                            $arr['success'] = 0;
                        }

                        echo $arr['success'];exit;
                        echo json_encode($arr);
                        break;
                    }else{
                        $this->error('无法取得要删除的用户id');
                    }
                //追加用户
                case 'add':

                    $this->display('add');
                    break;


            }
        }

    }


}
