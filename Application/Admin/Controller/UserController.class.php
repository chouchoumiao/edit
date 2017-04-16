<?php
namespace Admin\Controller;
use Admin\Model\ToolModel;
use Think\Controller;

header("Content-type: text/html;charset=utf-8");

class UserController extends CommonController {

    private $uploadImgName;
    private $dept;
    private $auto;
    private $uid;
    private $obj;

    public function doAction(){

        $action = $_GET['action'];
        if( isset($action) && '' != $action ){

            //用于根据用户权限来显示对应功能
            $this->obj = D('User');

            $user = $this->obj->getNowUserDetailInfo();

            $this->dept = $user['udi_dep_id'];
            $this->auto = intval($user['udi_auto_id']);
            $this->uid = intval($user['uid']);

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
                        'maxSize'    =>    C('FILE_SIZE'),
                        'rootPath'	 =>    'Public',
                        'savePath'   =>    '/Uploads/profile/',
                        'saveName'   =>    array('uniqid',''),
                        'exts'       =>    C('MEDIA_TYPE_ARRAY'),
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
                    $this->updateUser();
                    break;
                case 'editimg':
                    //图片上传设置
                    $config = array(
                        'maxSize'    =>    C('FILE_SIZE'),
                        'rootPath'	 =>    'Public',
                        'savePath'   =>    '/Uploads/profile/',
                        'saveName'   =>    array('uniqid',$_SESSION['uid'].'_'),
                        'exts'       =>    C('MEDIA_TYPE_ARRAY'),
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
     * 更新用户
     *
     */
    private function updateUser(){
        if($this->obj->updateUser()){
            //如果修改的是当前用户的信息,则重置session
            ToolModel::setNowUserBaseSession();
            if ($this->auto == ADMIN || $this->auto == DEPT_ADMIN || $this->auto == SUPPER_ADMIN){

                //如果是审核爆料者的情况下下，激活成功后需要向改爆料者发送通知邮件（只有超级管理员和管理员可以）
                if ($this->auto == ADMIN || $this->auto == SUPPER_ADMIN){
                    if(isset($_POST['auditSend']) && '' != I('post.auditSend')){
                        if($this->obj->afterAuditToUser(I('post.oldEmail'))){
                            ToolModel::goToUrl('审核完毕，并已成功发送通知邮件给用户了','all');
                        }else{
                            ToolModel::goToUrl('审核完毕，但通知用户邮件发送出错，请联系管理员','all');
                        }
                    }
                }
                ToolModel::goToUrl('修改用户信息成功','all');
            }else{
                ToolModel::goToUrl('修改用户信息成功',U('Index/index'));
            }
        }

        ToolModel::goBack('修改用户信息出错');
    }

    /**
     * 删除指定id的用户信息
     */
    private function del(){
        //如果有传值过来用查询传值的用户
        if(isset($_POST['id']) && '' != $_POST['id']){

            if(!$this->obj->idIsExist(I('post.id'))){
                ToolModel::goBack('警告，传值错误');
            }

            //删除前先取得该用户的img，如果用户删除成功，则将原先上传的头像也删除，避免垃圾数据
            $img = $this->obj->getOldImg(I('post.id'));
            $imgName = $img['img'];

            //删除用户
            if($this->obj->delTheUserInfo(I('post.id'))){

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

        $this->obj->addNewUser();
    }

    /**
     * 显示追加新用户画面
     */
    private function add(){

        $this->assign('add',true);

        $autopass = make_password();
        $this->assign('autopass',$autopass);

        //部门管理员的情况下，部门不需要显示
        if($this->auto == DEPT_ADMIN){
            //追加部门设置
            $this->assign('dept',ToolModel::DEPT_ADMINShowAllDept($this->dept));

            //追加角色设置
            $this->assign('auto',ToolModel::DEPT_ADMINShowAllAuto());
        }else{

            //显示dept
            $this->assign('showDept',true);

            //追加部门设置
            $this->assign('dept',ToolModel::showAllDept());

            //追加角色设置
            $this->assign('auto',ToolModel::showAllAuto());
        }


        $this->display('addUser');

    }

    /**
     * 根据传值过来的id取得用户信息
     */
    private function the(){
        //如果有传值过来用查询传值的用户
        if(isset($_GET['id']) && '' != $_GET['id']){

            if(!$this->obj->idIsExist(I('get.id'))){
                ToolModel::goBack('警告，传值错误');
            }

            $userId = I('get.id');
        }else{
            $userId = $_SESSION['uid'];
        }

        $userInfo = $this->obj->getTheUserInfo($userId);

        if($this->auto == DEPT_ADMIN){
            //追加角色设置
            $this->assign('theAuto',ToolModel::DEPT_ADMINTheAuto($userInfo['udi_auto_id']));
            //追加部门设置
            $this->assign('theDept',ToolModel::DEPT_ADMINTheDept($userInfo['udi_dep_id']));
        }else{

            //显示dept
            $this->assign('showTheDept',true);

            //页面显示用(CheckBox)
            $this->assign('theAuto',ToolModel::theAuto($userInfo['udi_auto_id']));
            //页面显示用(radio)
            $this->assign('theDept',ToolModel::theDept($userInfo['udi_dep_id']));
        }

        //追加左边介绍处使用(文字)
        $this->assign('thisAuto',ToolModel::autoCodeToName($userInfo['udi_auto_id'],$userInfo['udi_dep_id']));
        //追加左边介绍处使用(文字)
        $this->assign('thisDept',ToolModel::deptCodeToName($userInfo['udi_dep_id']));

        //如果是管理员,并且当前不是管理员则显示可以选择变换角色和部门（管理员默认对所有部门有效，所以不必显示）
        if($this->obj->isAdmin() && ($userId != $_SESSION['uid'])){
            $this->assign('admin',1);
            //追加字段,方面在js段判断是否需要验证部门都没有选择
            $this->assign('noShowDeptAndAuto',true);
        }else{
            $this->assign('admin',0);
        }

        if( (isset($_GET['audit'])) && intval(I('get.audit')) == 1 ){
            $this->assign('audit',true);
        }

        $this->assign('the',true);
        $this->assign('theUserInfo',$userInfo);
        $this->display('theUser');
    }


    /**
     * 显示所有用户信息
     */
    private function all(){


        //取得所有用户信息总条数，用于分页
        if($this->auto == DEPT_ADMIN){                                          //部门管理员
            $count = $this->obj->getDeptAdminAllUserCount($this->dept);
        }else if($this->auto == ADMIN){                                                                  //管理员，超级管理员
            $count = $this->obj->getAdminAllUserCount();
        }else if($this->auto == SUPPER_ADMIN){          //超级管理员未用
            $count = $this->obj->getAllUserCount();
        }else{
            ToolModel::goBack('您没有该权限');
        }


        //分页
        import('ORG.Util.Page');// 导入分页类
        $Page = new \Org\Util\Page($count,PAGE_SHOW_COUNT_10);                //实例化分页类 传入总记录数
        $limit = $Page->firstRow.','.$Page->listRows;

        //取得指定条数的信息

        //取得所有用户信息总条数，用于分页
        if($this->auto == DEPT_ADMIN){                                          //部门管理员（显示编辑和总编）
            $user = $this->obj->showDeptAdminUserList($limit,$this->dept);
        }else if($this->auto == ADMIN){                                         //管理员，超级管理员（显示除了编辑和总编）
            $user = $this->obj->showAdminUserList($limit);
        }else if($this->auto == SUPPER_ADMIN){                                  //超级管理员未用(显示所有)
            $user = $this->obj->showUserList($limit);
        }else{
            ToolModel::goBack('您没有该权限');
        }




        $show = $Page->show();// 分页显示输出

        for ($i=0;$i<count($user);$i++){

            //将只有一个部门的爆料者名称修改为通讯员
            $deptArr = explode('，',$user[$i]['udi_dep_id']);
            if ( ($user[$i]['udi_auto_id'] == BAOLIAOZHE_NAME) && (count($deptArr) == 1) ){         //需要修改来正确显示通讯员 20170416 wujiayu
                $user[$i]['udi_auto_id'] = TONGXUNYUAN_NAME;
            }

            //如果姓名过长则截取
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

        $this->display('allUser');
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
