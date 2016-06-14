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

                    $this->assign('add',true);

                    $autopass = make_password();
                    $this->assign('autopass',$autopass);

                    //追加部门设置
                    $this->assign('dept',$this->dept());

                    //追加角色设置
                    $this->assign('auto',$this->auto());

                    $this->display('user');
                    break;

                //追加用户
                case 'addNew':

//                    $deptList = array();
//                    //$deptCount = D('Dept')->getDeptCount();
//                    for($i = 1;$i<=4;$i++){
//                        $name = 'dept'.$i;
//                        if($_POST[$name]){
//                            $deptList[] = I("post.a");
//                        }
//                    }
//                    $dept = json_encode($deptList);
//                    echo empty(array_filter(json_decode(dept)));exit;
//
                    //D('User')->checkAddNewUser();
                    //dump($_POST);exit;

                    D('User')->addNewUser();
                    break;


            }
        }

    }

    /**
     * 拼接部门列表显示
     * @return string
     */
    private function dept(){

        $obj = D('Dept')->getAllDept();
        $html = '';
        for($i=0;$i<count($obj);$i++){

            $html .= '<div class="checkbox inline-block">';
            $html .= '<div class="custom-checkbox">';
            $html .= '<input type="checkbox" id="dept'.$obj[$i]['id'].'" value="'.$obj[$i]['id'].'" name="dept'.$obj[$i]['id'].'" class="checkbox-purple" checked>';
            $html .= '<label for="dept'.$obj[$i]['id'].'"></label>';
            $html .= '</div>';
            $html .= '<div class="inline-block vertical-top">'.$obj[$i]['name'];
            $html .= '</div> &nbsp &nbsp';
            $html .= '</div>';
        }

        return $html;

    }

    private function auto(){

        $obj = D('Auto')->getAllAuto();
        $html = '';
        for($i=0;$i<count($obj);$i++){

            $html .= '<div class="radio inline-block">';
            $html .= '<div class="custom-radio m-right-xs">';

            if( 1 == $obj[$i]['id']){
                $html .= '<input type="radio" id="auto'.$obj[$i]['id'].'" value="'.$obj[$i]['id'].'" checked name="auto">';
            }else{
                $html .= '<input type="radio" id="auto'.$obj[$i]['id'].'" value="'.$obj[$i]['id'].'" name="auto">';
            }
            $html .= '<label for="auto'.$obj[$i]['id'].'"></label>';
            $html .= '</div>';
            $html .= '<div class="inline-block vertical-top">'.$obj[$i]['name'];

            $html .= '</div> &nbsp &nbsp';
            $html .= '</div>';

        }

        return $html;
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
