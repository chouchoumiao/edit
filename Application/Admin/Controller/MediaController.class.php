<?php
namespace Admin\Controller;
use Admin\Model\ToolModel;
use Think\Controller;

header("Content-type: text/html;charset=utf-8");

class MediaController extends CommonController {

    private $auto;
    private $postObj;
    private $dept;

    public function doAction(){

        $action = $_GET['action'];
        if( isset($action) && '' != $action ){

            $user = D('User')->getNowUserDetailInfo();

            $this->postObj = D('Media');
            $this->dept = $user['udi_dep_id'];
            $this->auto = intval($user['udi_auto_id']);
            //用于根据用户权限来显示对应功能
            $autoCon = new ToolController();
            $autoCon->doAuto($this->auto);

            switch($action){

                //取得所有用户(分页)
                case 'all':
                    //$this->all();

                    $this->assign('all',true);
                    $this->assign('auto',$this->auto);
                    $this->display('media');

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
                    exit;
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
     * 上传图片
     */
    private function upload(){

        $day =  date('Ymd',time());

        //图片上传设置
        $config = array(
            'maxSize'    =>    3145728,
            'rootPath'	 =>    'Public',
            'savePath'   =>    '/Uploads/media/'.$day.'/',
            'saveName'   =>    array('uniqid',$_SESSION['uid'].'_'),
            'exts'       =>    array('jpg','png','jpeg'),
            'autoSub'    =>    false,
            'subName'    =>    array('date','Ymd'),
        );

        return ToolModel::uploadImg($config);
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
        $this->assign('add',true);
        $this->display('media');
    }

}
