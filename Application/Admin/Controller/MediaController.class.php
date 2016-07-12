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
                    echo $this->upload();
                    exit;
                    break;

                //提交表单后新增文章
                case 'addNew':
                    $this->addNew();
                    break;
                case 'delImg':
                    $this->delImg();
                    break;

                default:
                    ToolModel::goBack('警告,非法操作');
                    break;
            }
        }

    }


    private function delImg(){
        $img = I('post.img','');

        if($img){
            
            $imgPath = MEDIA_PATH.'/'.$img;
            $del = ToolModel::delImg($imgPath);

            if($del == 1){
                $arr['success'] = 1;
            }else{
                $arr['success'] = 0;
                $arr['msg'] = $del;
            }

            echo json_encode($arr);
            exit;

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
            'savePath'   =>    '/Uploads/Media/'.$day.'/',
            'saveName'   =>    array('uniqid',$_SESSION['uid'].'_'),
            'exts'       =>    C('POST_UPLOAD_TYPE_ARRAY'),
            'autoSub'    =>    false,
            'subName'    =>    array('date','Ymd'),
        );

        $retArr = ToolModel::uploadImg($config);
        if($retArr['success']){
            $arr['defaultName'] = '未命名,请编辑';
            $arr['day'] = substr($retArr['msg'],-29,-21);
            $arr['name'] = substr($retArr['msg'],-20,-4);
            $arr['ext'] = substr($retArr['msg'],-4);
            $arr['msg'] = $retArr['msg'];
            $arr['size'] = ceil($retArr['size']/1024);

            //追加如数据库


            $arr['success'] = 1;
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
