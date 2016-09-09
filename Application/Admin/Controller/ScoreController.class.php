<?php
namespace Admin\Controller;
use Admin\Model\ToolModel;
use Think\Controller;

header("Content-type: text/html;charset=utf-8");

class ScoreController extends CommonController {

    private $obj;
    private $dept;
    private $auto;

    public function doAction(){

        $action = $_GET['action'];
        if( isset($action) && '' != $action ){

            $user = D('User')->getNowUserDetailInfo();

            $this->obj = D('Score');
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

                //该用户的总分
                case 'showSum':
                    $this->showSum();
                    break;
                default:
                    ToolModel::goBack('警告,非法操作');
                    break;
            }
        }

    }

    /**
     * 获取该用户的总积分
     */
    private function showSum(){
        if( (!isset($_GET['uid'])) || (0 == intval(I('get.uid'))) ){
            ToolModel::goBack('参数错误');
        }

        $uid = intval(I('get.uid'));
        if($this->auto == DEPT_ADMIN){      //部门管理员查询时，只取本部门的合计值
            $sumScore = $this->obj->getDeptAdminSumScoreByUid($uid,$this->dept);
        }else{

            $sumScore = $this->obj->getSumScoreByUid($uid);
        }
        ToolModel::goToUrl('当前总积分为:'.$sumScore,'doAction/action/all');

    }

    /**
     * 显示文章列表信息
     */
    private function all(){

        $this->assign('all',true);

        if($this->auto == DEPT_ADMIN){
            //取得所有用户信息总条数，用于分页
            $count = $this->obj->getDeptAdminAllScoreCount($this->dept);

        }elseif ($this->auto == BAOLIAOZHE){        //当前用户为爆料者，可以查询属于自己的评分记录
            $count = $this->obj->getBaoliaozheScoreCount();
        }else{
            //取得所有用户信息总条数，用于分页
            $count = $this->obj->getAllScoreCount();
        }
        //取得所有用户信息总条数，用于分页
        //$count = $this->obj->getAllScoreCount();

        //分页
        import('ORG.Util.Page');// 导入分页类
        $Page = new \Org\Util\Page($count,PAGE_SHOW_COUNT_6);// 实例化分页类 传入总记录数
        $limit = $Page->firstRow.','.$Page->listRows;

        //取得指定条数的信息
        $show = $Page->show();// 分页显示输出

        if($this->auto == DEPT_ADMIN){
            $data = $this->obj->getDeptAdminAllScore($limit,$this->dept);
        }
        elseif ($this->auto == BAOLIAOZHE){
            $data = $this->obj->getBaoliaozheScore($limit);
        }else{

            $data = $this->obj->getAllScore($limit);
        }

        if(!$data){
            $data = '';
        }else{
            $scoreAuthor = array();
            for ($i=0;$i<count($data);$i++){
                $scoreAuthor[] = $this->obj->getScoreAuthorName($data[$i]['score_author']);
            }


            $this->assign('scoreAuthorArr',$scoreAuthor);

            $this->assign('page',$show);    //赋值分页输出
        }

        $this->assign('data',$data);


        $this->display('score');

    }

}
