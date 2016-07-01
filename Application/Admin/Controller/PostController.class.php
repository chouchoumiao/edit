<?php
namespace Admin\Controller;
use Admin\Model\ToolModel;
use Think\Controller;

header("Content-type: text/html;charset=utf-8");

class PostController extends CommonController {

    private $dept;
    private $auto;

    public function doAction(){

        $action = $_GET['action'];
        if( isset($action) && '' != $action ){


            $user = D('User')->getNowUserDetailInfo();

            $this->dept = $user['udi_dep_id'];
            $this->auto = $user['udi_auto_id'];

            switch($action){

                //取得所有用户(分页)
                case 'all':
                    $this->all();
                    break;

                //取得当前用户
                case 'the':
                    $this->the();
                    break;

                //删除文章
                case 'del':
                    $this->del();
                    break;

                //追加用户
                case 'add':
                    $this->add();
                    break;

                case 'upload':
                    $this->upload();
                    break;

                //提交表单后新增文章
                case 'addNew':
                    $this->addNew();
                    break;

                default:
                    ToolModel::goBack('警告,非法操作');
                    break;
            }
        }

    }

    /**
     * 根据id删除对应的文章，并且同时删除该文章中的图片
     */
    private function del(){
        //如果有传值过来用查询传值的用户
        if(isset($_POST['id']) && '' != $_POST['id']){

            $obj = D('Post');
            if(!$obj->idIsExist(I('post.id'))){
                ToolModel::goBack('警告，不存在改文章');
            }

            //删除该文章中存在的图片
            $data = $obj->getTheContent(I('post.id'));

            //数据库取出的数据需要转义
            $content = htmlspecialchars_decode($data['post_content']);

            //取得其中图片的信息(从文章内容code中查找出img)
            $imgPathArr = ToolModel::getImgPath($content);

            //删除用户
            if($obj->delThePost(I('post.id'))){

                //删除原先的图片
                $count = count($imgPathArr);
                if($count > 0){
                    for ($i = 0;$i<$count;$i++){
                        //删除时需要网站绝对路径
                        ToolModel::delImg(DOCUMENT_ROOT.$imgPathArr[$i]);
                    }
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
     * 追加新文章
     */
    private function addNew(){

        $obj = D('Post');

        //检查ajax上传的数据,并赋值
        $obj->checkAndSetNewData();

        //追加新文章
        if( $obj->addNewPost() ){
            $arr['success'] = 1;
            $arr['msg'] = '新增成功！';
        }else{
            $arr['success'] = 0;
            $arr['msg'] = '新增失败，请重试！';
        }
        echo json_encode($arr);
        exit;
    }

    /**
     * 上传图片
     */
    private function upload(){
        //图片上传设置
        $config = array(
            'maxSize'    =>    3145728,
            'rootPath'	 =>    'Public',
            'savePath'   =>    '/Uploads/post/',
            'saveName'   =>    array('uniqid',''),
            'exts'       =>    array('jpg','png','jpeg'),
            'autoSub'    =>    false,
            'subName'    =>    array('date','Ymd'),
        );

//        $retArr = $this->uploadImg($config);
        $retArr = ToolModel::uploadImg($config);


        if($retArr['success']){
            $arr['success'] = 1;
            $arr['msg'] = $retArr['msg'];
        }else{
            $arr['success'] = 0;
            $arr['msg'] = $retArr['msg'];
        }

        echo json_encode($arr);
        exit;
    }


    /**
     * 显示新增文章页面
     */
    private function add(){
        //追加部门设置
        $this->assign('dept',ToolModel::showAllDept());


        //根据权限来显示按钮，爆料者和小编是提交审核，总编是审核按钮
        if( ($this->auto == BAOLIAOZHE) || ($this->auto == XIAOBIAN) ){
            $this->assign('btnValue','提交审核');
        }else{
            $this->assign('btnValue','审核');
        }
        
        $this->assign('add',true);
        $this->display('post');
    }

    /**
     * 取得当前文章信息
     */
    private function the(){

        if(!isset($_GET['id'])) ToolModel::goBack('警告,session出错请重新登录');

        $data = D('Post')->getThePost();

        if($data){
            $this->assign('content',$data['post_content']);
            $this->assign('title',$data['post_title']);
            $this->assign('theDept',ToolModel::theDept($data['post_dept']));
        }

        $this->assign('the',true);
        $this->display('post');
    }

    /**
     * 显示文章列表信息
     */
    private function all(){

        $obj = D('Post');
        $this->assign('all',true);

        if( (intval($this->auto) == ADMIN) || ( intval($this->auto) == SUPPER_ADMIN)){
            //取得所有用户信息总条数，用于分页
            $count = $obj->getCount();

            //分页
            import('ORG.Util.Page');// 导入分页类
            $Page = new \Org\Util\Page($count,PAGE_SHOW_COUNT);// 实例化分页类 传入总记录数
            $limit = $Page->firstRow.','.$Page->listRows;

            //取得指定条数的信息


            $post = $obj->showPostList($limit);

            $show = $Page->show();// 分页显示输出

            for ($i=0;$i<count($post);$i++){

                //如果文章标题过长则截取
                if(  ToolModel::getStrLen($post[$i]['post_title']) > 10){
                    $post[$i]['post_title'] = ToolModel::getSubString($post[$i]['post_title'],10);
                }
                //如果昵称过长则截取
                if(  ToolModel::getStrLen($post[$i]['username']) > 20){
                    $post[$i]['username'] = ToolModel::getSubString($post[$i]['username'],20);
                }
            }

            $this->assign('editShow','查看');

        //如果是爆料者,则显示所有该爆料者提交的文章
        }else if( intval($this->auto) == BAOLIAOZHE ){

            $count = $obj->getBaoliaozheCount();

            if($count <= 0){
                $post = '';
            }else{
                //分页
                import('ORG.Util.Page');// 导入分页类
                $Page = new \Org\Util\Page($count,PAGE_SHOW_COUNT);// 实例化分页类 传入总记录数
                $limit = $Page->firstRow.','.$Page->listRows;

                //取得指定条数的信息


                $post = $obj->showBaoliaozhePostList($limit);

                $show = $Page->show();// 分页显示输出

                for ($i=0;$i<count($post);$i++){

                    //如果文章标题过长则截取
                    if(  ToolModel::getStrLen($post[$i]['post_title']) > 10){
                        $post[$i]['post_title'] = ToolModel::getSubString($post[$i]['post_title'],10);
                    }
                    //如果昵称过长则截取
                    if(  ToolModel::getStrLen($post[$i]['username']) > 20){
                        $post[$i]['username'] = ToolModel::getSubString($post[$i]['username'],20);
                    }
                }
                $this->assign('editShow','编辑');
            }
        }else{
            //取得属于小编或者总编部门文章总条数，用于分页
            $arr = json_decode($this->dept);


            $this->dept = $arr[0];

            $count = $obj->getDeptCount($this->dept);

            //分页
            import('ORG.Util.Page');// 导入分页类
            $Page = new \Org\Util\Page($count,PAGE_SHOW_COUNT);// 实例化分页类 传入总记录数
            $limit = $Page->firstRow.','.$Page->listRows;

            //取得指定条数的信息


            $post = $obj->showDeptPostList($this->dept,$limit);

            $show = $Page->show();// 分页显示输出

            for ($i=0;$i<count($post);$i++){

                //如果文章标题过长则截取
                if(  ToolModel::getStrLen($post[$i]['post_title']) > 10){
                    $post[$i]['post_title'] = ToolModel::getSubString($post[$i]['post_title'],10);
                }
                //如果昵称过长则截取
                if(  ToolModel::getStrLen($post[$i]['username']) > 20){
                    $post[$i]['username'] = ToolModel::getSubString($post[$i]['username'],20);
                }
            }
            $this->assign('editShow','审核');
        }


//        echo M('posts')->getLastSql();exit;

        $this->assign('allPost',$post); //用户信息注入模板
        $this->assign('page',$show);    //赋值分页输出

        $this->display('post');
    }

    

}
