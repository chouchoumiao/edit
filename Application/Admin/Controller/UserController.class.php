<?php
namespace Admin\Controller;
use Admin\Model\ToolModel;
use Think\Controller;

header("Content-type: text/html;charset=utf-8");

class UserController extends CommonController {

    private $dept;
    private $auto;

    public function doAction(){

        $action = $_GET['action'];
        if( isset($action) && '' != $action ){
            switch($action){

                //取得所有用户(分页)
                case 'all':
                    $this->assign('all',true);


                    $userObj = D('User');

                    //取得所有用户信息总条数，用于分页
                    $count = $userObj->getAllUserCount();

                    //分页
                    import('ORG.Util.Page');// 导入分页类
                    $Page = new \Org\Util\Page($count,PAGE_SHOW_COUNT);// 实例化分页类 传入总记录数
                    $limit = $Page->firstRow.','.$Page->listRows;

                    //取得指定条数的信息
                    

                    $user = $userObj->showUserList($limit);

                    $show = $Page->show();// 分页显示输出


                    $this->assign('allUser',$user); //用户信息注入模板
                    $this->assign('page',$show);    //赋值分页输出

                    $this->display('user');
                    break;

                //取得当前用户
                case 'the':

                    //如果有传值过来用查询传值的用户
                    if(isset($_GET['id']) && '' != $_GET['id']){
                        $userId = I('get.id');
                    }else{
                        $userId = $_SESSION['uid'];
                    }

                    $userInfo = D('User')->getTheUserInfo($userId);

                    //如果是管理员则显示可以选择变换角色和部门
                    if(D('User')->isAdmin()){

                        $this->assign('admin',true);

                        $this->dept = $userInfo['udi_dep_id'];
                        $this->auto = $userInfo['udi_auto_id'];

                        // dump($userInfo);exit;
                        //追加部门设置
                        $this->assign('theDept',$this->theDept());
                        //追加角色设置
                        $this->assign('theAuto',$this->theAuto());
                    }

                    $this->assign('the',true);
                    $this->assign('userInfo',$userInfo);
                    $this->display('user');
                    break;

                //删除用户
                case 'del':

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
                case 'city':
                    echo D('City')->get4thCity();
                    break;
                case 'update':
                    D('updateUser')->updateUser();
                    //dump($_POST);
                    break;

            }
        }

    }


    /**
     * 取得对应用户的部门信息并进行判断输出
     * @return string
     */
    private function theDept(){

        //取得数据库中的deptjson格式后，转化为数组格式
        $deptArr = json_decode($this->dept);

        //取得数据库中的部门表
        $deptDefineArr = D('Dept')->getAllDept();

        //拼接成html
        $html = '';

        //显示所有的部门信息，如果该用户选过的则显示打勾，不然则不打勾
        for($i=1;$i<=count($deptDefineArr);$i++){

            $html .= '<div class="checkbox inline-block">';
            $html .= '<div class="custom-checkbox">';

            //用于判断没有选择的次数（如果没有选择的次数等于总部门数，则表示没有选中）
            $x = 0;

            //循环判断数据库中部门表在该用户的数组中是否存在，存在则表示选中状态
            for($j=0;$j<count($deptArr);$j++){
                //如果该用户的部门id在数据表中存在，则改部门为选中状态
                if($deptArr[$j] == $i){
                    $html .= '<input type="checkbox" id="dept'.$i.'" value="'.$i.'" name="dept'.$i.'" class="checkbox-purple" checked>';
                }else{
                    //不存在数据表，数值加一
                    $x++;

                }
            }
            //都不存在，则表示该用户没有选中该部门
            if($x == count($deptArr)){
                $html .= '<input type="checkbox" id="dept'.$i.'" value="'.$i.'" name="dept'.$i.'" class="checkbox-purple">';
            }
            $html .= '<label for="dept'.$i.'"></label>';
            $html .= '</div>';
            $html .= '<div class="inline-block vertical-top">'.$deptDefineArr[$i]['name'];
            $html .= '</div> &nbsp &nbsp';
            $html .= '</div>';
        }

        return $html;

    }

    private function theAuto(){

        $obj = D('Auto')->getAllAuto();
        $html = '';

        //count($obj) - 2 最后两个是管理员和超级管理员，不予显示
        for($i=0;$i<(count($obj) - 2);$i++){

            $html .= '<div class="radio inline-block">';
            $html .= '<div class="custom-radio m-right-xs">';

            if( $this->auto == $obj[$i]['id']){
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

    /**
     * 拼接角色列表显示
     * @return string
     */
    private function auto(){

        $obj = D('Auto')->getAllAuto();
        $html = '';
        //(count($obj) - 1) 超级管理员不予显示
        for($i=0;$i<(count($obj) - 1);$i++){

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


    /**
     * 新增用户是上传头像后操作
     * @param $config
     * @return string
     */
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
