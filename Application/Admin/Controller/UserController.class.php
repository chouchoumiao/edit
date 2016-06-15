<?php
namespace Admin\Controller;
use Think\Controller;

class UserController extends CommonController {

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

                    D('User')->addNewUser();
                    break;

                //选择上传图片
                case 'upload':

                    //图片上传设置
                    $config = array(
                        'maxSize'    =>    3145728,
                        'rootPath'	 =>    'Public',
                        'savePath'   =>    '/Uploads/profile/',
                        'saveName'   =>    array('uniqid',''),
                        'exts'       =>    array('jpg','png','jpeg'),
                        'autoSub'    =>    false,
                        'subName'    =>    array('date','Ymd'),
                    );

                    echo $this->upload($config);
                    exit;
                    break;
                default:
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

    private function upload($config){
        if (!empty($_FILES)) {

            $upload = new \Think\Upload($config);// 实例化上传类
            $images = $upload->upload();
            //判断是否有图
            if($images){

                $name = $images['Filedata']['savename'];
                $_SESSION['newImg'] = $name;    //如果传上传图成功则将图片路径写入Session，方便写入数据库

                return $name;
            }
            else{
                $_SESSION['newImg'] = '';       //上传失败则清空session中的新图片信息
                return $upload->getError();
            }
        }
    }




}
