<?php
namespace Admin\Controller;
use Admin\Model\ToolModel;
use Think\Controller;

header("Content-type: text/html;charset=utf-8");

class UserController extends CommonController {

    private $uploadImgName;

    public function doAction(){

        $action = $_GET['action'];
        if( isset($action) && '' != $action ){

            //用于根据用户权限来显示对应功能
            $user = D('User')->getNowUserDetailInfo();

            $autoCon = new ToolController();
            $autoCon->doAuto($user['udi_auto_id']);

            switch($action){

                //取得所有用户(分页)
                case 'all':
                    $this->all();
                    break;

                //取得当前用户
                case 'the':
                    $this->the();
                    break;

                //删除用户
                case 'del':

                    $this->del();
                    break;
                //追加用户
                case 'add':

                    $this->add();
                    break;

                //追加用户
                case 'addNew':
                    $this->addNew();

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
                    //如果传上传图成功则将图片路径写入Session，方便写入数据库
                    $_SESSION['newImg'] = $this->uploadImgName;
                    exit;
                    break;
                case 'city':
                    echo D('City')->get4thCity();
                    break;
                case 'update':

                    D('User')->updateUser();
                    break;
                case 'editimg':
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
                    $_SESSION['editImg'] = $this->uploadImgName;
                    exit;
                    break;
                default:
                    ToolModel::goBack('警告,非法操作!');
                    break;

            }
        }

    }

    /**
     * 删除指定id的用户信息
     */
    private function del(){
        //如果有传值过来用查询传值的用户
        if(isset($_POST['id']) && '' != $_POST['id']){

            if(!D('User')->idIsExist(I('post.id'))){
                ToolModel::goBack('警告，传值错误');
            }

            //删除前先取得该用户的img，如果用户删除成功，则将原先上传的头像也删除，避免垃圾数据
            $img = D('User')->getOldImg(I('post.id'));
            $imgName = $img['img'];

            //删除用户
            if(D('User')->delTheUserInfo(I('post.id'))){

                //如果是默认图片则不删除，否则则删除
                if( 'default.jpg' != $imgName ){
                    ToolModel::delImg(PROFILE_PATH.'/'.$imgName);
                }

                $arr['success'] = 'OK';
            }else{
                $arr['success'] = 'NG';
            }

            echo json_encode($arr);

        }else{
            $this->error('无法取得要删除的用户id');
        }
    }
    /**
     * 新用户信息提交后检查并存入数据库
     */
    private function addNew(){
        //判断是否是表单发送过来
        if( (!isset($_POST['send'])) || ('' ==  $_POST['send']) ){
            ToolModel::goBack('非法操作');
        }

        D('User')->addNewUser();
    }

    /**
     * 显示追加新用户画面
     */
    private function add(){

        $this->assign('add',true);

        $autopass = make_password();
        $this->assign('autopass',$autopass);

        //追加部门设置
        $this->assign('dept',ToolModel::showAllDept());

        //追加角色设置
        $this->assign('auto',ToolModel::showAllAuto());

        $this->display('user');

    }

    /**
     * 根据传值过来的id取得用户信息
     */
    private function the(){
        //如果有传值过来用查询传值的用户
        if(isset($_GET['id']) && '' != $_GET['id']){

            if(!D('User')->idIsExist(I('get.id'))){
                ToolModel::goBack('警告，传值错误');
            }

            $userId = I('get.id');
        }else{
            $userId = $_SESSION['uid'];
        }

        $userInfo = D('User')->getTheUserInfo($userId);

        //追加角色设置
        $this->assign('theAuto',ToolModel::autoCodeToName($userInfo['udi_auto_id']));
        //追加部门设置
        $this->assign('theDept',ToolModel::deptCodeToName($userInfo['udi_dep_id']));

        //如果是管理员,并且当前不是管理员则显示可以选择变换角色和部门（管理员默认对所有部门有效，所以不必显示）
        if(D('User')->isAdmin() && ($userId != $_SESSION['uid'])){
            $this->assign('admin',1);
            //追加字段,方面在js段判断是否需要验证部门都没有选择
            $this->assign('noShowDeptAndAuto',true);
        }else{
            $this->assign('admin',0);
        }
        
        $this->assign('the',true);
        $this->assign('userInfo',$userInfo);
        $this->display('user');
    }


    /**
     * 显示所有用户信息
     */
    private function all(){
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

        for ($i=0;$i<count($user);$i++){
            //如果昵称过长则截取
            if(  ToolModel::getStrLen($user[$i]['username']) > 20){
                $user[$i]['username'] = ToolModel::getSubString($user[$i]['username'],20);
            }
            //如果邮箱地址过长则截取
            if(  ToolModel::getStrLen($user[$i]['email']) > 30){
                $user[$i]['email'] = ToolModel::getSubString($user[$i]['email'],30);
            }
        }

        $this->assign('allUser',$user); //用户信息注入模板
        $this->assign('page',$show);    //赋值分页输出

        $this->display('user');
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
                $this->uploadImgName = $name;

                return $name;
            }
            else{
                $_SESSION['newImg'] = '';       //上传失败则清空session中的新图片信息
                return $upload->getError();
            }
        }
    }




}
