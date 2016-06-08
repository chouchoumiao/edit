<?php
namespace Admin\Controller;
use Admin\Model\ValidateModel;
use Think\Controller;

class UserController extends CommonController {
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

                    $this->assign('all',true);
                    $this->assign('allUser',D('User')->getAllUserInfo());
                    $this->display('user');
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
                    //
                    //if($userInfo['udi_sex'] == 1){
                    //    $userInfo['udi_sex'] = '男';
                    //}elseif($userInfo['udi_sex'] == 0){
                    //    $userInfo['udi_sex'] = '女';
                    //}else{
                    //    $userInfo['udi_sex'] = '未知';
                    //}
                    $this->assign('the',true);
                    $this->assign('userInfo',$userInfo);
                    $this->display('user');
                    break;

                //删除用户
                case 'del':

                    //$arr['success'] = 'OK';
                    //$arr['msg'] = 'DDAA';
                    //echo json_encode($arr);exit;

                    //如果有传值过来用查询传值的用户
                    if(isset($_POST['id']) && '' != $_POST['id']){

                        if(D('User')->delTheUserInfo(I('post.id'))){
                            $arr['success'] = 'OK';
                        }else{
                            $arr['success'] = 'NG';
                        }

                        //echo D('User')->getLastSql();exit;
                        echo json_encode($arr);

                    }else{
                        $this->error('无法取得要删除的用户id');
                    }
                    break;
                //追加用户
                case 'add':
                    $autopass = make_password();
                    $this->assign('autopass',$autopass);
                    $this->assign('add',true);
                    $this->display('user');
                    break;

                //追加用户
                case 'addNew':

                    //检查提交的文本框格式
                    $msg = ValidateModel::CheckAddUser();
                    if('' != $msg){
                        $this->error($msg);
                    }

                    if(D('User')->addToUser(I('post.pass'))){

                        $this->redirect('Admin/User/doAction/action/all');
                    }else{
                        $this->error('新用户追加失败');
                    }

                    break;


            }
        }

    }

    //public function doAction(){
    //
    //    $action = $_GET['action'];
    //    if( isset($action) && '' != $action ){
    //        switch($action){
    //
    //            //取得所有用户
    //            case 'all':
    //                $this->assign('allUser',D('User')->getAllUserInfo());
    //                $this->display('all');
    //                break;
    //
    //            //取得当前用户
    //            case 'the':
    //                //如果有传值过来用查询传值的用户
    //                if(isset($_GET['id']) && '' != $_GET['id']){
    //                    $userId = I('get.id');
    //                }else{  //没有传值的话就查询本登录的用户信息
    //                    $userId = $_SESSION['uid'];
    //                }
    //
    //                $userInfo = D('User')->getTheUserInfo($userId);
    //                //
    //                //if($userInfo['udi_sex'] == 1){
    //                //    $userInfo['udi_sex'] = '男';
    //                //}elseif($userInfo['udi_sex'] == 0){
    //                //    $userInfo['udi_sex'] = '女';
    //                //}else{
    //                //    $userInfo['udi_sex'] = '未知';
    //                //}
    //
    //                $this->assign('userInfo',$userInfo);
    //                $this->display('profile');
    //                break;
    //
    //            //删除用户
    //            case 'del':
    //
    //                //如果有传值过来用查询传值的用户
    //                if(isset($_GET['id']) && '' != $_GET['id']){
    //
    //                    if(D('User')->delTheUserInfo(I('get.id'))){
    //                        $arr['success'] = 1;
    //                    }else{
    //                        $arr['success'] = 0;
    //                    }
    //
    //                    echo $arr['success'];exit;
    //                    echo json_encode($arr);
    //                    break;
    //                }else{
    //                    $this->error('无法取得要删除的用户id');
    //                }
    //            //追加用户
    //            case 'add':
    //
    //                $this->display('add');
    //                break;
    //
    //
    //        }
    //    }
    //
    //}


}
