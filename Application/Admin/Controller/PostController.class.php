<?php
namespace Admin\Controller;
use Admin\Model\ToolModel;
use Think\Controller;

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
     * 更新文章内容
     */
    private function update(){

        //检查ajax上传的数据,并赋值
        $this->postObj->checkAndSetUpdateData();

        //追加新文章
        if( $this->postObj->updatePost() ){
            $arr['success'] = 1;
            $arr['msg'] = '更新成功！';
        }else{
            $arr['success'] = 0;
            $arr['msg'] = '更新失败，请重试！';
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

        if( $this->auto == BAOLIAOZHE){
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
        }else{
            ToolModel::goBack('您没有发表文章的功能');
        }
        
        $this->assign('add',true);
        $this->display('post');
    }

    /**
     * 取得当前文章信息
     */
    private function the(){

        //如果对文章点击预览后执行预览画面显示
        if(isset($_GET['preview']) && I('get.preview') == 1){

            $post = $this->postObj->getThePostAndUser(I('get.id'));

            if($post){
                $post['post_dept'] = ToolModel::deptCodeToNameArr($post['post_dept']);
                $this->assign('post',$post);
                $this->display('preview');
            }

            exit;
        }

        if(!isset($_GET['id'])) ToolModel::goBack('警告,session出错请重新登录');

        $data = $this->postObj->getThePost(I('get.id'));

        if($data){
            //如果是小编的情况下点击了审核,需要新增同样的文章
            if( intval($this->auto) == XIAOBIAN ){

                //先判定是否已经拷贝过了，如果拷贝过了，则取得原先拷贝的文章，没有则新增
                $isCopiedObj = $this->postObj->isCopiedByXIAOBIAN(I('get.id'));

                if($isCopiedObj){
                    ToolModel::goBack('您已经有了该文章的拷贝了,请操作备份文件');
                    exit;
                }else{
                    $newID = $this->postObj->copyPostByXIAOBIAN($data,$this->dept);
                    if( false ===  $newID){
                        ToolModel::goBack('审核时生成拷贝文件失败,请重试');
                    }else{          //拷贝后原来的爆料者提交的文章状态设置为审核中,并小编不可编辑该文章,只能编辑拷贝后的文章


                    }
                }
                $data = $this->postObj->getThePost( $newID );

                if(!$data){
                    ToolModel::goBack('取得文章失败');
                }
            }

            $this->assign('postid',$data['id']);
            $this->assign('content',$data['post_content']);
            $this->assign('title',$data['post_title']);
            $this->assign('theDept',ToolModel::theDept($data['post_dept']));
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
                            onclick="return UpdateFormSubmit('.$pending2.');" value="继续提交审核" name="send">';
                $html .= '</div>';
                $html .= '<div class="col-sm-2 col-sm-offset-1">';
                $html .= '<input type="button" class="btn btn-danger btn-block" 
                            onclick="return UpdateFormSubmit('.$dismiss.');" value="审核不通过" name="send">';
                $html .= '</div>';
                $html .= '<div class="col-sm-2 col-sm-offset-1">';
                $html .= '<input type="button" class="btn  btn-default m-left-xs btn-block" onclick="return resetAddForm();" value="清空内容" id="res">';
                $html .= '</div>';


                $htmlSmall .= '<div class="col-xs-2">';
                $htmlSmall .= '<input type="button" class="btn btn-xs btn-info" 
                            onclick="return UpdateFormSubmit('.$pending2.');" value="继续审核" name="send">';
                $htmlSmall .= '</div>';
                $htmlSmall .= '<div class="col-xs-2 col-xs-offset-2">';
                $htmlSmall .= '<input type="button" class="btn btn-danger btn-xs" 
                            onclick="return UpdateFormSubmit('.$dismiss.');" value="审核不通过" name="send">';
                $htmlSmall .= '</div>';
                $htmlSmall .= '<div class="col-xs-2 col-xs-offset-2">';
                $htmlSmall .= '<input type="button" class="btn  btn-default btn-xs" onclick="return resetAddForm();" value="清空内容" id="res">';
                $htmlSmall .= '</div>';

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

        if($this->auto == XIAOBIAN || $this->auto == ZONGBIAN ){
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
        //用户昵称超过10个则街区10个(默认是10)
        $this->setPostNameLength($post);

        //追加注入斑模板的不同文章状态的文章个数
        $this->getShowPostCountWithStatus();

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

            //如果昵称过长则截取
            if(  ToolModel::getStrLen($post[$i]['username']) > $len){

                $post[$i]['username'] = ToolModel::getSubString($post[$i]['username'],$len);
            }
        }
    }

    /**
     * 最后将需要在画面输入的内容注入模板
     */
    private function getShowPostCountWithStatus(){

        if( ($this->auto != XIAOBIAN) && ($this->auto != ZONGBIAN) ){
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
