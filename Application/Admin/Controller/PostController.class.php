<?php
namespace Admin\Controller;
use Admin\Model\ToolModel;
use Think\Controller;

header("Content-type: text/html;charset=utf-8");

class PostController extends CommonController {

    public function doAction(){

        $action = $_GET['action'];
        if( isset($action) && '' != $action ){
            switch($action){

                //取得所有用户(分页)
                case 'all':
                    $this->assign('all',true);
                    $this->display('post');
                    break;

                //取得当前用户
                case 'the':

                    $this->assign('the',true);
                    $this->display('post');

                    break;

                //删除用户
                case 'del':

                    break;
                //追加用户
                case 'add':
                    $this->assign('add',true);
                    $this->display('post');



                    break;

                //追加用户
                case 'addNew':


                    break;

                default:
                    break;
            }
        }

    }

}
