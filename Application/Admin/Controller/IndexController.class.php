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

            $this->auto = intval($userInfo['udi_auto_id']);
            
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

    /**
     *  取得不同文章状态的文章个数用于显示在主页
     */
    private function getData(){

        if($this->auto == XIAOBIAN || $this->auto == ZONGBIAN ){
            //取得属于小编或者总编部门文章总条数，用于分页
            $arr = json_decode($this->dept);
            $this->dept = $arr[0];
        }else{
            $this->dept = '';
        }

        /* 文章相关*/
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

        /* 文章相关*/
        //取得资源个数
        $this->assign('mediaCount',D('Media')->getMediaCount());


        $this->assign('activeNoticeCount',count(D('Notice')->getActivedNotice()));
        $this->assign('activeNotice',D('Notice')->getActivedNotice());



    }
}
