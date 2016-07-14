<?php
namespace Admin\Controller;
use Admin\Model\ToolModel;
use Think\Controller;

header("Content-type: text/html;charset=utf-8");

class MediaController extends CommonController {

    private $auto;
    private $obj;
    private $dept;

    public function doAction(){

        $action = $_GET['action'];
        if( isset($action) && '' != $action ){

            $user = D('User')->getNowUserDetailInfo();

            $this->obj = D('Media');
            $this->dept = $user['udi_dep_id'];
            $this->auto = intval($user['udi_auto_id']);
            //用于根据用户权限来显示对应功能
            $autoCon = new ToolController();
            $autoCon->doAuto($this->auto);

            switch($action){

                //取得所有用户(分页)
                case 'all':
                    $data = $this->all();


                    $this->assign('all',true);
                    $this->assign('allStatus','active');
                    $this->assign('data',$data);
                    $this->assign('auto',$this->auto);
                    $this->display('media');

                    break;
                case 'getStatus':
                    $data = $this->getStatus();

                    $this->assign('all',true);
                    $this->assign('data',$data);
                    $this->assign('auto',$this->auto);
                    $this->display('media');

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

    //根据传入的查询条件查找
    private function getStatus(){

        $status = I('get.status');
        if( !isset($status) || ('' == $status)){

            ToolModel::goBack('参数不能为空');
        }

        switch ($status){
            case 'media':
                $where['type'] = array('in',C('MEDIA_TYPE_ARRAY'));
                $this->assign('mediaStatus','active');
                break;
            case 'file':
                $where['type'] = array('in',C('FILE_TYPE_ARRAY'));
                $this->assign('fileStatus','active');
                break;
            case 'me':
                $where['author'] = $_SESSION['uid'];
                $this->assign('meStatus','active');
                break;
            case 'like':
                $where['label'] = array('like',"%{$_SESSION['uid']}%");
                $this->assign('likeStatus','active');
                break;
            default:
                ToolModel::goBack('参数错误');
                break;
        }
        return $this->obj->getMediaBtStatus($where);

    }


    /**
     * 取得所有资源
     * @return mixed
     */
    private function all(){

        return $this->obj->getAllMedia();
    }

    private function delImg(){
        $id = I('post.id',0);
        $img = I('post.img','');

        if($img){
            
            $imgPath = MEDIA_PATH.'/'.$img;
            $del = ToolModel::delImg($imgPath);

            if($del == 1){

                //删除数据库中对应的资源
                if(!$this->obj->deleteMedia($id)){
                    $arr['success'] = 0;
                    $arr['msg'] = '数据库中资源删除失败'.M('media')->getLastSql();
                }else{
                    $arr['success'] = 1;
                }


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

            $arr['msg'] = $retArr['msg'];

            $imgdataArr = explode('/',$arr['msg']);     //将名称用'/'分割

            $arr['day'] = $imgdataArr[3];               //日期是分割后第三个下标的值

            $last = array_pop($imgdataArr);             //取得名称加最后
            $nameExt = explode('.',$last);              //继续用'.'分割，用于取得后缀和名称
            $arr['name'] = $nameExt[0];                 //取得名称
            $arr['ext'] = array_pop($nameExt);          //取得后缀

            $arr['size'] = ceil($retArr['size']/1024);

            //追加数据库数据做成
            $data['title']  = '';
            $data['content']  = '';
            $data['author']  = $_SESSION['uid'];
            $data['path']   = $arr['msg'];
            $data['day']    = $arr['day'];
            $data['name']   = $arr['name'];
            $data['type']   = $arr['ext'] ;  //将后缀名存入
            $data['label']   = '' ;          //将标签存入

            $data['size']   = $arr['size'];
            $data['status'] = 1;
            $data['time']   = date('Y/m/d H:m:s');

            $id = $this->obj->insertMedia($data);
            if( !$id ){
                $arr['success'] = 0;
                $arr['msg'] = '图片存入失败';
             }else{
                $arr['success'] = 1;
                $arr['id'] = $id;
            }

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
    private function add(){        $this->assign('add',true);
        $this->display('media');
    }

}
