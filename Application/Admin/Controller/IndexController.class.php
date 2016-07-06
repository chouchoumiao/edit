<?php
namespace Admin\Controller;
use Think\Controller;

class IndexController extends CommonController {

    private $auto;
    private $postObj;
    private $dept;   //仅适用于小编总编一个部门的情况

    /**
     * 显示主页面
     */
    public function index(){

        //取得当前用户的信息
        $userInfo = D('User')->getTheUserInfo($_SESSION['uid']);

        //根据用户的权限来分别显示对应功能
        if($userInfo){

            $this->auto = $userInfo['udi_auto_id'];
            
            $autoCon = new ToolController();

            $autoCon->doAuto($this->auto);

            $this->dept = $userInfo['udi_dep_id'];
            $this->postObj = D('Post');

            //获取相关数据
            $this->getData();
            
            $this->assign('userInfo',$userInfo);
            $this->display();
        }else{
            $this->error('取得当前用户信息失败,请重新登录');
        }
    }

    private function getData(){
        //管理员和超级管理员时显示文章
        if( (intval($this->auto) == ADMIN) || ( intval($this->auto) == SUPPER_ADMIN)){

            //所有文章个数
            $allCount = $this->postObj->getAllStatusCount();
            //待审核文章个数
            $peningCount = $this->postObj->getStatusCount('pending');
            //待最终审核文章个数
            $pending2Count = $this->postObj->getStatusCount('pending2');
            //已审核文章个数
            $pendedCount = $this->postObj->getStatusCount('pended');
            //未通过审核文章个数
            $dismissCount = $this->postObj->getStatusCount('dismiss');

        //如果是爆料者,则显示所有该爆料者提交的文章
        }else if( intval($this->auto) == BAOLIAOZHE ){

            //取得待审核文章个数
            $peningCount = $this->postObj->getBaoliaozheStatusCount('pending');
            $pending2Count = $this->postObj->getBaoliaozheStatusCount('pending2');
            //取得已审核文章个数
            $pendedCount = $this->postObj->getBaoliaozheStatusCount('pended');

            $dismissCount = $this->postObj->getBaoliaozheStatusCount('dismiss');

            //重新取得所有状态的文章个数
            $allCount = $this->postObj->getAllBaoliaozheCount();

        }else{
            //取得属于小编或者总编部门文章总条数，用于分页
            $arr = json_decode($this->dept);

            $this->dept = $arr[0];


            //不文章状态取得所有文章个数
            $allCount = $this->postObj->getAllDeptCount($this->dept);
            //取得待审核文章个数
            $peningCount = $this->postObj->getDeptStatusCount($this->dept,'pending');
            $pending2Count = $this->postObj->getDeptStatusCount($this->dept,'pending2');
            //取得已审核文章个数
            $pendedCount = $this->postObj->getDeptStatusCount($this->dept,'pended');

            $dismissCount = $this->postObj->getDeptStatusCount($this->dept,'dismiss');

        }


        $this->assign('allCount',$allCount);
        $this->assign('peningCount',$peningCount);
        $this->assign('pening2Count',$pending2Count);
        $this->assign('pendedCount',$pendedCount);
        $this->assign('dismissCount',$dismissCount);

    }
}
