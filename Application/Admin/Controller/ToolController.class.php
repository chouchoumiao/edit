<?php
/**
 * Created by wujiayu.
 * User: Administrator
 * Date: 2016/5/19
 * Time: 13:43
 */

namespace Admin\Controller;

use Think\Controller;

class ToolController extends Controller{

    /**
     * 根湖传入的权限id；来判断，并生成对应的功能列表
     * @param $auto
     */
    public function doAuto($auto){

        switch ($auto){
            case SUPPER_ADMIN:
                //$this->assign('postControl',true);
                $this->assign('postMainControl',true);
                $this->assign('mediaControl',true);
                $this->assign('userControl',true);
                $this->assign('scoreControl',true);
                $this->assign('noticeControl',true);
                break;
            case ADMIN:
                $this->assign('postMainControl',true);
                $this->assign('mediaControl',true);
                $this->assign('userControl',true);
                $this->assign('scoreControl',true);
                $this->assign('noticeControl',true);
                break;
            case DEPT_ADMIN:
                $this->assign('postMainControl',true);
                $this->assign('mediaControl',true);
                $this->assign('userControl',true);
                $this->assign('scoreControl',true);
                $this->assign('noticeControl',true);
                break;
            case ZONGBIAN:
                $this->assign('postMainControl',true);
                $this->assign('postPending2Control',true);   //总编追加未最终审核一览
                $this->assign('mediaControl',true);
                break;
            case XIAOBIAN:
                $this->assign('postMainControl',true);
                $this->assign('postPendingControl',true);   //小编追加未审核一览
                $this->assign('mediaControl',true);
                break;
            case BAOLIAOZHE:

                $status = D('User')->getNowUserStatus();

                if($status != -1){
                    $this->assign('postMainControl',true);
                    $this->assign('postControl',true);
                    $this->assign('mediaControl',true);
                    $this->assign('baoliaozheScoreControl',true);
                }else{
                    $this->assign('noIndex',true);
                }

                break;
            default:
                break;
        }
    }

}