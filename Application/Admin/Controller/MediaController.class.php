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

                    if( (!$data = S('allData')) ||  (1 == S('resetAllData')) ){
                        $data = $this->all();
                        //存入缓存
                        S('allData',$data,3000);

                        //同时要将更新缓存的flag设置为0
                        S('resetAllData',0);

                    }
                    $this->assign('allStatus','active');

                    if($data !== ''){
                        $this->setLiked($data);
                    }
                    $this->assign('data',$data);
                    $this->assign('auto',$this->auto);
                    $this->display('allMedia');

                    break;
                case 'collect':
                    $this->collect();
                    break;
                case 'getStatus':

                    $data = $this->getStatus();

                    if($data !== ''){
                        $this->setLiked($data);
                    }

                    $this->assign('data',$data);
                    $this->assign('auto',$this->auto);
                    $this->display('allMedia');

                    break;
                //新增媒体
                case 'add':
                    $this->add();
                    break;
                case 'upload':
                    echo $this->upload();
                    exit;
                    break;

                //提交表单后新增文章
                case 'update':
                    $this->update();
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

    
    private function update(){
        if( !isset( $_POST['send']) || '' == I('post.send')){
            ToolModel::goBack('参数不能为空');
        }

        $data['id'] = I('post.id',0);
        $data['title'] = I('post.title','');
        $data['content'] = I('post.content','');
        $data['time'] = date('Y/m/d H:i:s',time());

        if(!$this->obj->updateMedia($data)){
            ToolModel::goBack('更新失败');
        }

        //更新成功后，需要更新缓存flag
        $this->resetCacheStatus();

        //根据传入的状态返回对应的页面
        $this->returnUrl('更新成功');
    }
    
    /**
     * 点击收藏资源时触发
     */
    private function collect(){

        //判断id
        $id = I('get.id');
        if( !isset($id) || 0 == intval($id)){
            ToolModel::goBack('参数不能为空');
        }

        //执行收藏操作
        if(!$this->obj->doCollect($id)){
            ToolModel::goBack('收藏失败');
        }else{

            //更新成功后，需要更新缓存flag
            $this->resetCacheStatus();

            //根据传入的状态返回对应的页面
            $this->returnUrl('收藏成功');


        }
    }

    /**
     * 根据传入的状态返回对应的页面
     * @param $msg
     */
    private function returnUrl($msg){
        //获取传上来的当前页面状态是媒体文件,文档文件,我的,还是收藏的
        $status = I('get.status');

        //定义状态一览
        $statusArr = array('media','file','me','like');

        //如果接收到的状态是状态一览表中的内容,则返回对应的页面
        if(in_array($status,$statusArr)){
            ToolModel::goToUrl($msg,'doAction/action/getStatus/status/'.$status);
            //否则则返回总页面
        }else{
            ToolModel::goToUrl($msg,'doAction/action/all');
        }
    }

    /**
     * 根据传入的查询条件查找
     * @return mixed
     */
    private function getStatus(){

        $status = I('get.status');
        if( !isset($status) || ('' == $status)){

            ToolModel::goBack('参数不能为空');
        }

        switch ($status){
            case 'media':
                $where['type'] = array('in',C('MEDIA_TYPE_ARRAY'));
                $this->assign('mediaStatus','active');                  //设置页面显示那个按钮是激活状态
                $this->assign('status','media');                        //用于给点击收藏操作时返回对应页面用

                //设置缓存
                if( (!$data = S('mediaStatus')) || (1 == S('resetMediaStatus')) ){
                    $data = $this->obj->getMediaBtStatus($where);
                    //存入缓存
                    S('mediaStatus',$data,3000);

                    //同时要将更新缓存的flag设置为0
                    S('resetMediaStatus',0);
                }
                break;
            case 'file':
                $where['type'] = array('in',C('FILE_TYPE_ARRAY'));
                $this->assign('fileStatus','active');
                $this->assign('status','file');

                //设置缓存
                if( (!$data = S('fileStatus')) || (1 == S('resetFileStatus')) ){
                    $data = $this->obj->getMediaBtStatus($where);
                    //存入缓存
                    S('fileStatus',$data,3000);

                    //同时要将更新缓存的flag设置为0
                    S('resetFileStatus',0);
                }
                break;
            case 'me':
                $where['author'] = $_SESSION['uid'];
                $this->assign('meStatus','active');
                $this->assign('status','me');

                //设置缓存
                if( (!$data = S('meStatus')) || (1 == S('resetMeStatus')) ){
                    $data = $this->obj->getMediaBtStatus($where);
                    //存入缓存
                    S('meStatus',$data,3000);

                    //同时要将更新缓存的flag设置为0
                    S('resetMeStatus',0);
                }
                break;
            case 'like':
                $where['label'] = array('like',"%{$_SESSION['uid']}%");
                $this->assign('likeStatus','active');
                $this->assign('status','like');

                //设置缓存
                if( (!$data = S('likeStatus')) || (1 == S('resetLikeStatus')) ){
                    $data = $this->obj->getMediaBtStatus($where);
                    //存入缓存
                    S('likeStatus',$data,3000);

                    //同时要将更新缓存的flag设置为0
                    S('resetLikeStatus',0);
                }
                break;
            default:
                ToolModel::goBack('参数错误');
                break;
        }
        return $data;
    }

    /**
     * 取得所有资源
     * @return mixed
     */
    private function all(){

        return $this->obj->getAllMedia();
    }

    /**
     * 删除资源(不只是图片)
     */
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
                    //删除图片后，需要更新缓存flag,并重新取得资源
                    $this->resetCacheStatus();
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

        //设置删除图片的相关配置项
        $config = $this->setImgConfig();

        //上传文件
        $retArr = ToolModel::uploadImg($config);

        if($retArr['success']){
            $arr = $this->setJsonData($retArr);
            $data = $this->setImgData($arr);
            //插入新资源
            $id = $this->obj->insertMedia($data);
            if( !$id ){
                $arr['success'] = 0;
                $arr['msg'] = '图片存入失败';
             }else{
                //更新成功后，需要更新缓存flag
                $this->resetCacheStatus();

                $arr['success'] = 1;
                $arr['id'] = $id;
            }
        }else{
            $arr['success'] = 0;
            $arr['msg'] = '上传失败! (原因: '.$retArr['msg'].')';
//            $arr['msg'] = $retArr['msg'];
        }

        echo json_encode($arr);
        exit;
    }

    private function setImgConfig(){
        $day =  date('Ymd',time());

        //图片上传设置
        $config = array(
            'maxSize'    =>    C('FILE_SIZE'),
            'rootPath'	 =>    'Public',
            'savePath'   =>    '/Uploads/Media/'.$day.'/',
            'saveName'   =>    array('uniqid',$_SESSION['uid'].'_'),
            'exts'       =>    C('POST_UPLOAD_TYPE_ARRAY'),
            'autoSub'    =>    false,
            'subName'    =>    array('date','Ymd'),
        );
        return $config;
    }

    /**
     * 组装数据给前端显示用
     * @param $retArr
     * @return mixed
     */
    private function setJsonData(&$retArr){
        $arr['defaultName'] = '未命名,请编辑';

        $arr['msg'] = $retArr['msg'];

        $imgdataArr = explode('/',$arr['msg']);     //将名称用'/'分割

        $arr['day'] = $imgdataArr[3];               //日期是分割后第三个下标的值

        $last = array_pop($imgdataArr);             //取得名称加最后
        $nameExt = explode('.',$last);              //继续用'.'分割，用于取得后缀和名称
        $arr['name'] = $nameExt[0];                 //取得名称
        $arr['ext'] = array_pop($nameExt);          //取得后缀

        $arr['size'] = ceil($retArr['size']/1024);

        return $arr;


    }

    /**
     * 做成用于新增数据表的数据
     * @param $arr
     * @return mixed
     */
    private function setImgData($arr){
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
        $data['time']   = date('Y/m/d H:i:s',time());
        return $data;
    }

    /**
     * 显示新增媒体页面
     */
    private function add(){
//        $this->assign('add',true);
//        $this->display('media');
        $this->display('addMedia');
    }

    /**
     * 根据收藏的label内容来判断当前用户是否是已经收藏过该资源了,追加状态没给前端页面判断用
     * @param $data
     */
    private function setLiked(&$data){
        for ($i= 0;$i<count($data);$i++){

            if($data[$i]['label'] == ''){
                $data[$i]['liked'] = 0;
            }else{

                $labelArr = explode(',',$data[$i]['label']);

                if(in_array($_SESSION['uid'],$labelArr) ){
                    $data[$i]['liked'] = 1;
                }else{
                    $data[$i]['liked'] = 0;
                }
            }

        }
    }

    /**
     * 重置需要更新缓存的flag(在新增，更新，收藏)
     */
    private function resetCacheStatus(){
        //更新成功后，需要更新缓存flag
        S('resetAllData',1);
        S('resetMediaStatus',1);
        S('resetFileStatus',1);
        S('resetMeStatus',1);
        S('resetLikeStatus',1);
    }

}
