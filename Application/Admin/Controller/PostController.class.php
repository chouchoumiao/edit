<?php
namespace Admin\Controller;
use Admin\Model\ToolModel;
use Think\Controller;
use Think\Log;

header("Content-type: text/html;charset=utf-8");

class PostController extends CommonController {

    private $postObj;
    private $dept;
    private $auto;

    public function doAction(){

        $action = $_GET['action'];
        if( isset($action) && '' != $action ){

            $user = D('User')->getNowUserDetailInfo();

            $this->postObj = D('Post');
            $this->dept = $user['udi_dep_id'];
            $this->auto = intval($user['udi_auto_id']);

            //用于根据用户权限来显示对应功能
            $autoCon = new ToolController();
            $autoCon->doAuto($this->auto);

            switch($action){

                //取得所有用户(分页)
                case 'all':
                    $this->all();
                    break;

                case 'unlockPost':
                    $this->unlockPost();
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
                case 'update':
                    $this->update();
                    break;

                case 'upload':
                    $this->upload();
                    break;

                case 'uploadAttachment':
                    $this->uploadAttachment();
                    break;

                //提交表单后新增文章
                case 'addNew':
                    $this->addNew();
                    break;

                case 'deleteImg':
                    $this->deleteImg();
                    break;

                case 'delAttachment':
                    $this->delAttachment();
                    break;

                default:
                    ToolModel::goBack('警告,非法操作');
                    break;
            }
        }

    }

    /**
     * 清除该文章的缓存
     */
    private function unlockPost(){
        //将锁定文章的缓存去除
        if(S('lockPostId'.intval(I('post.postid')))){
            S('lockPostId'.intval(I('post.postid')),null);
            S('lockUser'.intval(I('post.postid')),null);
            Log::write('fuction:unlockPost() && clearcache && POSTID: '.I('post.postid'),'LOCKIMG');
        }else{
            Log::write('fuction:unlockPost() && No cache && POSTID: '.I('post.postid'),'LOCKIMG');
        }


    }

    /**
     * 文章中删除图片后原先上传的图片也要删除
     */
    private function deleteImg(){

        $imgPath = I('post.imgPath');

        $pathArr = explode('/',$imgPath);
        $count = count($pathArr);
        $newPath = POST_PATH.'/'.$pathArr[$count-2].'/'.$pathArr[$count-1];

        $del = ToolModel::delImg($newPath);
        if($del == 1){
            $arr['success'] = 1;
            $arr['msg'] = '删除成功';
        }else{
            $arr['success'] = o;
            $arr['msg'] = $del;
        }
        echo json_encode($arr);
        exit;


    }

    /**
     * 文章中删除图片后原先上传的图片也要删除
     */
    private function delAttachment(){

        $imgPath = I('post.path');

        $pathArr = explode('/',$imgPath);
        $count = count($pathArr);
        $newPath = POST_ATTACHMENT_PATH.'/'.$pathArr[$count-2].'/'.$pathArr[$count-1];

        $del = ToolModel::delImg($newPath);
        if($del == 1){
            $arr['success'] = 1;
            $arr['msg'] = '删除成功';
        }else{
            $arr['success'] = o;
            $arr['msg'] = $del;
        }
        echo json_encode($arr);
        exit;


    }

    /**
     * 更新文章内容
     */
    private function update(){

        //检查ajax上传的数据,并赋值
        $this->postObj->checkAndSetUpdateData();

        //更新文章
        if( $this->postObj->updatePost() ){

            if(intval(I('post.flag')) == 5){

                $this->postObj->insertScore($this->dept);
            }

            $arr['success'] = 1;
            $arr['msg'] = '更新成功！';
        }else{
            $arr['success'] = 0;
            $arr['msg'] = '更新失败，请重试！';
        }
        //将锁定文章的缓存去除
        if(S('lockPostId'.intval(I('post.postid')))){
            S('lockPostId'.intval(I('post.postid')),null);
            S('lockUser'.intval(I('post.postid')),null);
            Log::write('fuction:update() && clearcache && POSTID: '.I('post.postid'),'LOCKIMG');
        }


        //获取上传的attachment数值
        if( '' !=I('post.attachment')){

            //更新附件的内容,如果有删除的话(先取出该文章对于附件的个数，如果现在个数小于原先个数则说明有删除，需要更新，否则不更新)
            $oldAttachment = $this->postObj->getAttachmentData(intval(I('post.postid')));
            if($oldAttachment){
                $newAttachmentData['post_id'] = intval(I('post.postid'));
                $newAttachmentData['post_attachment'] = htmlspecialchars_decode(I('post.attachment'));
                $newAttachmentData['post_save_name'] = htmlspecialchars_decode(I('post.saveName'));
                $newAttachmentData['post_file_name'] = htmlspecialchars_decode(I('post.fileName'));

                $this->postObj->updatetAttachmentData($newAttachmentData);
            }
        }


        echo json_encode($arr);
        exit;
        
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

                //查找该文章是否有单独上传附件,有则删除
                $this->postObj->delAttachmentData(I('post.id'));

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

        $day =  date('Ymd',time());

        //图片上传设置
        $config = array(
            'maxSize'    =>    3145728,
            'rootPath'	 =>    'Public',
            'savePath'   =>    '/Uploads/post/'.$day.'/',
            'saveName'   =>    array('uniqid',$_SESSION['uid'].'_'),
            'exts'       =>    array('jpg','png','jpeg'),
            'autoSub'    =>    false,
            'subName'    =>    array('date','Ymd'),
        );

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
     * 直接上传附件
     */
    private function uploadAttachment(){

        //设置删除图片的相关配置项
        $config = $this->setImgConfig();

        //上传文件
        $retArr = ToolModel::uploadImg($config);
        $arr['path'] = $retArr['msg'];
        $arr['fileName'] = $retArr['fileName'];

        //取得修改后的文件名,并去除后缀
        $nameArr = explode('.',$retArr['saveName']);
        $arr['saveName'] = $nameArr[0];

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

    private function setImgConfig(){
        $day =  date('Ymd',time());

        //图片上传设置
        $config = array(
            'maxSize'    =>    3145728,
            'rootPath'	 =>    'Public',
            'savePath'   =>    '/Uploads/postAttachment/'.$day.'/',
            'saveName'   =>    array('uniqid',$_SESSION['uid'].'_'),
            'exts'       =>    C('POST_UPLOAD_Attachment_TYPE_ARRAY'),
            'autoSub'    =>    false,
            'subName'    =>    array('date','Ymd'),
        );
        return $config;
    }


    /**
     * 只有爆料者可以进行
     * 显示新增文章页面
     */
    private function add(){

        if($this->auto != BAOLIAOZHE){
            ToolModel::goBack('您没有发表文章的功能');
        }

        //只显示该爆料者可以提交的部门
        $this->assign('dept',ToolModel::onlyShowTheDept($this->dept));

        $html= '';
        $isSave = 'save';
        $html .= '<div class="col-sm-2  col-sm-offset-3">';
        $html .= '<input type="button" class="btn btn-info btn-block" 
                    onclick="return addFormSubmit('.$isSave.');" value="保存不审核">';
        $html .='</div>';
        $html .= '<div class="col-sm-2">';
        $html .= '<input type="button" class="btn btn-info btn-block" onclick="return addFormSubmit();" value="提交审核">';
        $html .='</div>';

        $this->assign('btn',$html);

        $this->assign('add',true);
        $this->display('post');
    }

    /**
     * 取得当前文章信息
     */
    private function the(){

        //如果对文章点击预览后执行预览画面显示
        if(isset($_GET['preview']) && intval(I('get.preview')) == 1){

            $post = $this->postObj->getThePostAndUser(I('get.id'));

//            $attachmentData = $this->postObj->getAttachmentData(I('get.id'));
//
//            if($attachmentData){
//                $this->assign('attachmentList',$attachmentData['post_attachment']);
//            }

            if($post){
                $post['post_dept'] = ToolModel::deptCodeToNameArr($post['post_dept']);
                $this->assign('post',$post);
                $this->display('preview');
            }

            exit;
        }

        if(!isset($_GET['id'])) ToolModel::goBack('警告,session出错请重新登录');

        //判断cache中是否存在该文章的缓存,有则表示该文章处理正在编辑中
        if( S('lockPostId'.intval(I('get.id'))) == intval(I('get.id')) ){
            if(S('lockUser'.intval(I('get.id'))) != $_SESSION['username']){
                Log::write('fuction:the() && hasCache Can not edit && 
                            lockUser:'.S('lockUser'.intval(I('get.id'))).' && 
                            ThisAuto:'.$_SESSION['username'].' && 
                            POSTID: '.I('get.id'),'LOCKIMG');
                ToolModel::goBack("本文文章由【".S('lockUser'.intval(I('get.id')))."】正在编辑中,请过会再编辑");
                exit;
            }
        }

        $data = $this->postObj->getThePost(I('get.id'));

        $thePostId = intval(I('get.id'));

        if($data){
            //$lockID = I('get.id');
            //如果是小编的情况下点击了审核,需要新增同样的文章
            if( intval($this->auto) == XIAOBIAN ){

                //继续判定点击的文章作者是不是当前小编,如果是则不作拷贝也不作判断
                if($data['post_author'] != $_SESSION['uid']){

                    //先判定是否已经拷贝过了，如果拷贝过了，则取得原先拷贝的文章，没有则新增
                    $isCopiedObj = $this->postObj->isCopiedByXIAOBIAN(I('get.id'));
                    if($isCopiedObj){
                        ToolModel::goBack('您已经有了该文章的拷贝了,请操作备份文件');
                        exit;
                    }else{
                        $newID = $this->postObj->copyPostByXIAOBIAN($data,$this->dept);
                        if( false ===  $newID){
                            ToolModel::goBack('审核时生成拷贝文件失败,请重试');
                        }else{  //被拷贝原文章的的继承字段要更新,从来可以来判断该文章是否被拷贝过

                            if( false === $this->postObj->updatePostChild(I('get.id'),$newID)){
                                ToolModel::goBack('拷贝文章时候原文章状态更新失败!');
                            }

                            $thePostId = $newID;    //小编拷贝了文章后，文章ID需要更新
                            //拷贝时候需要拷贝单独上传的附件信息(如果存在的情况下)
                            $oldAttachment = $this->postObj->getAttachmentData(I('get.id'));
                            if ( false != $oldAttachment){
                                $newAttachmentData['post_id'] = $newID;
                                $newAttachmentData['post_attachment'] = $oldAttachment['post_attachment'];
                                $newAttachmentData['post_save_name'] = $oldAttachment['post_save_name'];
                                $newAttachmentData['post_file_name'] = $oldAttachment['post_file_name'];
                                $newAttachmentData['time'] = date('Y-m-d H:i:s', time());

                                $newAttachment = $this->postObj->insertAttachment($newAttachmentData);
                                if(false == $newAttachment){
                                    ToolModel::goBack('拷贝原文章的附件时出错！');
                                }
                            }
                        }
                        $data = $this->postObj->getThePost( $newID );
                        if(!$data){
                            ToolModel::goBack('取得文章失败');
                        }
                    }
                }

            }


            $attachmentData = $this->postObj->getAttachmentData($thePostId);

            if($attachmentData){

                //$attachmentStr = json_decode($attachmentData['post_attachment']);

                $this->assign('attachmentList',$attachmentData['post_attachment']);
                $this->assign('saveNameList',$attachmentData['post_save_name']);
                $this->assign('fileNameList',$attachmentData['post_file_name']);
            }

            $lockID = $data['id'];
            $this->assign('postid',$data['id']);
            $this->assign('content',$data['post_content']);
            $this->assign('title',$data['post_title']);
            $this->assign('theDept',ToolModel::onlyShowTheDept($data['post_dept']));
//            $this->assign('theDept',ToolModel::showTheDeptForPost($data['post_dept'],$data['post_author']));
        }else{
            ToolModel::goBack('无该文章,请确认');
        }

        //根据角色来显示不同的按钮
        $html = '';
        $htmlSmall = '';
        $save     = 1;        //保存flag
        $pending  = 2;        //提交审核
        $pending2 = 3;        //继续提交审核
        $dismiss  = 4;        //审核不通过flag
        $pended  = 5;        //审核通过flag

        switch (intval($this->auto)){
            case ADMIN:
            case DEPT_ADMIN:
            case SUPPER_ADMIN:
                break;
            case ZONGBIAN:

                $html .= '<div class="col-sm-2 col-sm-offset-2">';
                $html .= '<input type="button" class="btn btn-info btn-block" 
                            onclick="return UpdateFormSubmit('.$pended.');" value="审核通过" name="send">';
                $html .= '</div>';
                $html .= '<div class="col-sm-2 col-sm-offset-1">';
                $html .= '<input type="button" class="btn btn-danger btn-block" 
                            onclick="return UpdateFormSubmit('.$dismiss.');" value="审核不通过" name="send">';
                $html .= '</div>';
                $html .= '<div class="col-sm-2 col-sm-offset-1">';
                $html .= '<input type="button" class="btn  btn-default m-left-xs btn-block" onclick="return resetAddForm();" value="清空内容" id="res">';
                $html .= '</div>';

                $htmlSmall .= '<div class="col-xs-2">';
                $htmlSmall .= '<input type="button" class="btn btn-info  btn-xs" 
                            onclick="return UpdateFormSubmit('.$pended.');" value="审核通过" name="send">';
                $htmlSmall .= '</div>';
                $htmlSmall .= '<div class="col-xs-2 col-xs-offset-2">';
                $htmlSmall .= '<input type="button" class="btn btn-danger  btn-xs" 
                            onclick="return UpdateFormSubmit('.$dismiss.');" value="审核不通过" name="send">';
                $htmlSmall .= '</div>';
                $htmlSmall .= '<div class="col-xs-2 col-xs-offset-2">';
                $htmlSmall .= '<input type="button" class="btn  btn-default btn-xs" onclick="return resetAddForm();" value="清空内容" id="res">';
                $htmlSmall .= '</div>';

                $showDeptCheckBox = false;

                break;
            case XIAOBIAN:
                $html .= '<div class="col-sm-2 col-sm-offset-2">';
                $html .= '<input type="button" class="btn btn-info btn-block" 
                            onclick="return UpdateFormSubmit('.$pending2.');" value="提交审核" name="send">';
                $html .= '</div>';
                $html .= '<div class="col-sm-2 col-sm-offset-1">';
                $html .= '<input type="button" class="btn btn-danger btn-block" 
                            onclick="return UpdateFormSubmit('.$dismiss.');" value="审核不通过" name="send">';
                $html .= '</div>';
                $html .= '<div class="col-sm-2 col-sm-offset-1">';
                $html .= '<input type="button" class="btn btn-warning btn-block" 
                            onclick="return UpdateFormSubmit('.$save.');" value="保存" name="send">';
                $html .= '</div>';
//                $html .= '<div class="col-sm-2 col-sm-offset-1">';
//                $html .= '<input type="button" class="btn  btn-default m-left-xs btn-block" onclick="return resetAddForm();" value="清空内容" id="res">';
//                $html .= '</div>';


                $htmlSmall .= '<div class="col-xs-2">';
                $htmlSmall .= '<input type="button" class="btn btn-xs btn-info" 
                            onclick="return UpdateFormSubmit('.$pending2.');" value="继续审核" name="send">';
                $htmlSmall .= '</div>';
                $htmlSmall .= '<div class="col-xs-2 col-xs-offset-2">';
                $htmlSmall .= '<input type="button" class="btn btn-danger btn-xs" 
                            onclick="return UpdateFormSubmit('.$dismiss.');" value="审核不通过" name="send">';
                $htmlSmall .= '</div>';
                $htmlSmall .= '<div class="col-xs-2 col-xs-offset-2">';
                $htmlSmall .= '<input type="button" class="btn btn-xs btn-warning" 
                            onclick="return UpdateFormSubmit('.$save.');" value="修改并保存" name="send">';
                $htmlSmall .= '</div>';
//                $htmlSmall .= '<div class="col-xs-2 col-xs-offset-2">';
//                $htmlSmall .= '<input type="button" class="btn  btn-default btn-xs" onclick="return resetAddForm();" value="清空内容" id="res">';
//                $htmlSmall .= '</div>';

                $showDeptCheckBox = false;

                break;
            case BAOLIAOZHE:
                $html .= '<div class="col-sm-2 col-sm-offset-2">';
                $html .= '<input type="button" class="btn btn-info btn-block" 
                            onclick="return UpdateFormSubmit('.$pending.');" value="提交审核" name="send">';
                $html .= '</div>';
                $html .= '<div class="col-sm-2 col-sm-offset-1">';
                $html .= '<input type="button" class="btn btn-warning btn-block" 
                            onclick="return UpdateFormSubmit('.$save.');" value="修改并保存" name="send">';
                $html .= '</div>';
                $html .= '<div class="col-sm-2 col-sm-offset-1">';
                $html .= '<input type="button" class="btn  btn-default m-left-xs btn-block" onclick="return resetAddForm();" value="清空内容" id="res">';
                $html .= '</div>';


                $htmlSmall .= '<div class="col-xs-2">';
                $htmlSmall .= '<input type="button" class="btn btn-xs btn-info " 
                            onclick="return UpdateFormSubmit('.$pending.');" value="提交审核" name="send">';
                $htmlSmall .= '</div>';
                $htmlSmall .= '<div class="col-xs-2 col-xs-offset-2">';
                $htmlSmall .= '<input type="button" class="btn btn-xs btn-warning" 
                            onclick="return UpdateFormSubmit('.$save.');" value="修改并保存" name="send">';
                $htmlSmall .= '</div>';
                $htmlSmall .= '<div class="col-xs-2 col-xs-offset-2">';
                $htmlSmall .= '<input type="button" class="btn btn-xs btn-default" onclick="return resetAddForm();" value="清空内容" id="res">';
                $htmlSmall .= '</div>';

                $showDeptCheckBox = true;

                break;

        }
        $this->assign('btnSmall',$htmlSmall);    //响应式手机用小按钮组
        $this->assign('showDeptCheckBox',$showDeptCheckBox);    //如果是小编或者总编部门固定,所以不显示部门可选
        $this->assign('btn',$html);
        $this->assign('the',true);
        $this->assign('theAuto',$this->auto);

        //echo $lockID;exit;
        //设置缓存，时间为100分钟
        if(!S('lockPostId'.intval($lockID))){

            S('lockPostId'.intval($lockID),intval($lockID),6000);
            S('lockUser'.intval($lockID),$_SESSION['username'],6000);
            Log::write('fuction:the() && Add Cache && POSTID: '.$lockID,'LOCKIMG');
        }

        $this->display('post');
    }

    /**
     * 显示文章列表信息
     */
    private function all(){

        $this->assign('all',true);
        $this->assign('auto',$this->auto);

        if( (isset($_GET['userSearch'])) && ( '' != I('get.userSearch')) ){
            if( intval($this->auto) != BAOLIAOZHE) {
                $this->getPostWithAutosAndSearch('userSearch');
            }
        }

        //管理员和超级管理的情况下,文章一览表中 的部门可以点击,点击相对应的部门显示该部门的一览
        if( (isset($_GET['deptSearch'])) && ( '' != I('get.deptSearch')) ){
            if( (intval($this->auto) != XIAOBIAN) && ( intval($this->auto) != ZONGBIAN) ) {
                $this->getPostWithAutosAndSearch('deptSearch');
            }
        }

        //根据不同的角色来来显示文章列表
        $this->getPostWithAutosAndSearch('noSearch');
    }

    /**
     * 根据传入的flag来判断是按照部门,用户查询,还是直接显示,并执行相应查询
     * @param $flag
     */
    private function getPostWithAutosAndSearch($flag){

        if($this->auto == XIAOBIAN || $this->auto == ZONGBIAN || $this->auto == DEPT_ADMIN){
            //取得属于小编或者总编部门文章总条数，用于分页
            $arr = json_decode($this->dept);
            $this->dept = $arr[0];
        }else{
            $this->dept = '';
        }

        $count = $this->postObj->getCountWithAutoAndSearch($flag,$this->auto,$this->dept);

        //分页
        import('ORG.Util.Page');// 导入分页类
        $Page = new \Org\Util\Page($count,PAGE_SHOW_COUNT);// 实例化分页类 传入总记录数
        $limit = $Page->firstRow.','.$Page->listRows;

        //取得指定条数的信息
        $post = $this->postObj->showPostListWithAutoAndSearch($flag,$this->auto,$limit,$this->dept);

        $show = $Page->show();// 分页显示输出
        //文章的标题的长度超过10个则街区10个(默认是10)
        $this->setPostTitleLength($post);
        //用户姓名超过10个则街区10个(默认是10)
        $this->setPostNameLength($post);

        //追加注入斑模板的不同文章状态的文章个数
        $this->getShowPostCountWithStatus();

        //总编的情况下,文章一览显示是审核者,而不是作者
        if($this->auto == ZONGBIAN){
            $this->assign('authorName','审批者');
        }else{
            $this->assign('authorName','作者');
        }

        $this->assign('allPost',$post); //用户信息注入模板
        $this->assign('page',$show);    //赋值分页输出

        $this->display('post');
        exit;

    }

    /**
     * 设定取得的文章中Title的长度
     * @param $post
     * @param int $len
     */
    private function setPostTitleLength(&$post,$len=6){
        for ($i=0;$i<count($post);$i++){

            //自适应小屏幕手机时只截取3个
            $post[$i]['post_title_small'] = ToolModel::getSubString($post[$i]['post_title'],3);

            //如果文章标题过长则截取
            if(  ToolModel::getStrLen($post[$i]['post_title']) > $len){
                $post[$i]['post_title'] = ToolModel::getSubString($post[$i]['post_title'],$len);
            }
        }
    }

    /**
     * 设定取得的用户名的长度
     * @param $post
     * @param int $len
     */
    private function setPostNameLength(&$post,$len=10){
        for ($i=0;$i<count($post);$i++){

            //自适应小屏幕手机时只截取3个
            $post[$i]['username_small'] = ToolModel::getSubString($post[$i]['username'],3);

            //如果姓名过长则截取
            if(  ToolModel::getStrLen($post[$i]['username']) > $len){

                $post[$i]['username'] = ToolModel::getSubString($post[$i]['username'],$len);
            }
        }
    }

    /**
     * 最后将需要在画面输入的内容注入模板
     */
    private function getShowPostCountWithStatus(){

//        if( ($this->auto != XIAOBIAN) && ($this->auto != ZONGBIAN) ){
        if( $this->auto != ZONGBIAN ){
            //取得保存文章个数
            $saveCount = $this->postObj->getStatusCountByFlag($this->auto,'save');
            //取得待审核文章个数

            //因为小编和总编的情况下不显示保存的个数,所有该html文需要判定
            $html = '';
            $html .= '<a href='.__ROOT__.'/Admin/Post/doAction/action/all/status/save>
                            保存  <span class="badge badge-warning bounceIn 
                            animation-delay3 active">'.$saveCount.'</span></a>';
            $this->assign('showSave',$html);
        }

        //取得所有文章个数
        $this->assign('allCount',$this->postObj->getStatusCountByFlag($this->auto,'all',$this->dept));
        //取得待审核文章个数
        $this->assign('peningCount',$this->postObj->getStatusCountByFlag($this->auto,'pending',$this->dept));
        //取得待最终审核文章个数
        $this->assign('pening2Count',$this->postObj->getStatusCountByFlag($this->auto,'pending2',$this->dept));
        //取得已审核文章个数
        $this->assign('pendedCount',$this->postObj->getStatusCountByFlag($this->auto,'pended',$this->dept));
        //取得审核不通过文章个数
        $this->assign('dismissCount',$this->postObj->getStatusCountByFlag($this->auto,'dismiss',$this->dept));

    }
}
