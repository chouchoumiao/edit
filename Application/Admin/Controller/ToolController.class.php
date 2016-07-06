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
            case ADMIN:
            case SUPPER_ADMIN:
                //$this->assign('postControl',true);
                $this->assign('mediaControl',true);
                $this->assign('userControl',true);
                $this->assign('scoreControl',true);
                $this->assign('noticeControl',true);
                break;
            case ZONGBIAN:
                $this->assign('mediaControl',true);
                break;
            case XIAOBIAN:
                $this->assign('mediaControl',true);
                break;
            case BAOLIAOZHE:
                $this->assign('postControl',true);
                $this->assign('mediaControl',true);
                break;
            default:
                break;
        }
    }

}