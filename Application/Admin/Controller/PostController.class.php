<?php
namespace Admin\Controller;
use Admin\Model\ToolModel;
use Think\Controller;

header("Content-type: text/html;charset=utf-8");

class PostController extends CommonController {

    private $dept;

    public function doAction(){

        $action = $_GET['action'];
        if( isset($action) && '' != $action ){
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

        //取得所有用户信息总条数，用于分页
        $count = $obj->getCount();

        //分页
        import('ORG.Util.Page');// 导入分页类
        $Page = new \Org\Util\Page($count,PAGE_SHOW_COUNT);// 实例化分页类 传入总记录数
        $limit = $Page->firstRow.','.$Page->listRows;

        //取得指定条数的信息


        $user = $obj->showPostList($limit);

        $show = $Page->show();// 分页显示输出


        $this->assign('allPost',$user); //用户信息注入模板
        $this->assign('page',$show);    //赋值分页输出

        $this->display('post');
    }

    

}
