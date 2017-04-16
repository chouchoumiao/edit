<?php
namespace Admin\Controller;
use Admin\Model\ToolModel;
use Think\Controller;

header("Content-type: text/html;charset=utf-8");

class NoticeController extends CommonController {

    private $obj;
    private $dept;
    private $auto;
    private $uid;

    public function doAction(){

        $action = $_GET['action'];
        if( isset($action) && '' != $action ){

            $user = D('User')->getNowUserDetailInfo();

            $this->obj = D('Notice');
            $this->dept = $user['udi_dep_id'];
            $this->auto = intval($user['udi_auto_id']);
            $this->uid = intval($user['uid']);

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

        if( (!isset($_POST['send'])) || ('' == $_POST['send']) ){
            ToolModel::goBack('非法提交表单');
        }

        if( (!isset($_POST['id'])) || ( 0 == intval(I('post.id'))) ) ToolModel::goBack('参数错误');

        $id = intval(I('post.id'));

        $data['title'] = I('post.title','');
        $data['author'] = $_SESSION['uid'];
        $data['link'] = I('post.link','');
        $data['content'] = I('post.content','');
        $data['from_date'] = I('post.from_date','');
        $data['to_date'] = I('post.to_date','');
        $data['dept'] = ToolModel::deptFormToDbData();
        $data['auto'] = ToolModel::autoFormToDbData();
        $data['time'] = date('Y/m/d H:i:s',time());

        $this->obj->checkData($data);


        if(!$this->obj->updateData($id)){
            ToolModel::goBack('修改失败');
        }

        ToolModel::goToUrl('修改成功','all');
        exit;

        
    }

    /**
     * 根据id删除对应的文章，并且同时删除该文章中的图片
     */
    private function del(){

        if( (!isset($_GET['id'])) || (0 == intval(I('get.id'))) ){
            ToolModel::goBack('参数错误1');
        }

        $id = intval(I('get.id'));

        if( !$this->obj->delById($id)) {
            ToolModel::goBack('删除失败');
        }

        ToolModel::goToUrl('删除成功','doAction/action/all');
        exit;

    }

    /**
     * 追加新文章
     */
    private function addNew(){

        if(!isset($_POST['send'])){
            ToolModel::goBack('非法提交表单');
        }


        $data['title'] = I('post.title','');
        $data['author'] = $_SESSION['uid'];
        $data['link'] = I('post.link','');
        $data['content'] = I('post.content','');
        $data['from_date'] = I('post.from_date','');
        $data['to_date'] = I('post.to_date','');
        $data['dept'] = ToolModel::deptFormToDbData();
        $data['auto'] = ToolModel::autoFormToDbData();
        $data['time'] = date('Y/m/d H:i:s',time());

        $this->obj->checkData($data);

        if(!$this->obj->addNewData()){
            ToolModel::goBack('新增失败');
        }

        ToolModel::goToUrl('新增成功','all');
    }

    /**
     * 显示新增文章页面
     */
    private function add(){

        //部门管理员的情况下，部门不需要显示
        if($this->auto == DEPT_ADMIN){

            //追加部门设置
            $this->assign('dept',ToolModel::DEPT_ADMINShowAllDept($this->dept));

            //追加角色设置
            $this->assign('auto',ToolModel::DEPT_ADMINShowAllAutoCheckbox());

        }else{
            //显示dept
            $this->assign('showDept',true);

            //追加部门设置
            $this->assign('dept',ToolModel::showAllDept());

            //追加角色设置
            $this->assign('auto',ToolModel::showAllAutoCheckbox());
        }


        $this->display('addNotice');
    }

    /**
     * 取得当前通知信息
     */
    private function the(){

        if( (!isset($_GET['id'])) || ( 0 == intval($_GET['id'])) ) ToolModel::goBack('参数错误');

        $id = intval($_GET['id']);

        $data = $this->obj->getTheNoticeById($id);

        if(!$data){
            $data = '';
        }else{
            //部门管理员的情况下，部门不需要显示
            if($this->auto == DEPT_ADMIN){
                //追加角色设置
                $this->assign('theAuto',ToolModel::DEPT_ADMINTheAutoCheckbox($data['auto']));
                //追加部门设置
                $this->assign('theDept',ToolModel::DEPT_ADMINTheDept($data['dept']));
            }else{
                //显示dept
                $this->assign('showTheDept',true);

                //追加角色设置
                $this->assign('theAuto',ToolModel::theAutoCheckbox($data['auto']));
                //追加部门设置
                $this->assign('theDept',ToolModel::theDept($data['dept']));
            }

        }

        $this->assign('theData',$data);

        $this->display('theNotice');

    }

    /**
     * 显示文章列表信息
     */
    private function all(){


        //取得所有用户信息总条数，用于分页
        $count = $this->obj->getAllNoticeCount();

        //分页
        import('ORG.Util.Page');// 导入分页类
        $Page = new \Org\Util\Page($count,PAGE_SHOW_COUNT_10);// 实例化分页类 传入总记录数
        $limit = $Page->firstRow.','.$Page->listRows;

        //取得指定条数的信息
        $show = $Page->show();// 分页显示输出
        if($this->auto == DEPT_ADMIN){
            $data = $this->obj->getDeptAdminAllNotice($limit,$this->uid);
        }else{

            $data = $this->obj->getAllNotice($limit);
        }

        if(!$data){
            $data = '';
        }

        $this->assign('data',$data);
        $this->assign('page',$show);    //赋值分页输出

        $this->display('allNotice');

    }

}
