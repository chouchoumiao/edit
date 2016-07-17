<?php
/**
 * Created by wujiayu.
 * User: Administrator
 * Date: 2016/5/19
 * Time: 13:59
 */

namespace Admin\Controller;

use Admin\Model\ToolModel;
use Think\Controller;

class LoginController extends Controller{

    private $actionName;

    public function index(){

        if((isset($_POST['action'])) && ('' != $_POST['action'])){
            $this->actionName = I('post.action');

            switch ($this->actionName){
                case 'login':
                    $this->login();
                    break;
                case 'reg':
                    $this->reg();
                    break;
                case 'lostpass':
                    $this->lostPass();
                    break;
                default:
                    ToolModel::goToUrl('非常操作','Login/login');
                    break;

            }
        }else{
            if( (!isset($_SESSION['username'])) || ('' == $_SESSION['username']) ){
                //无session则进入后台主页面
                $this->redirect('Login/login');
            }else{
                //有session则进入后台主页面
                $this->redirect('Index/index');
            }
        }

    }


    private function login(){

        //检查表单
        $msg = checkForm( $this->actionName );

        if( '' != $msg ){

            $arr['success'] = 'NG';
            $arr['msg'] = $msg;
            echo json_encode($arr);
            exit;
        }

        //检查登录用户名密码是否正确
        $userInfo = D('User')->checklogin();

        if( $userInfo ){
            
            //继续判断是否已经被激活过
            if( $userInfo['status'] == 0){
                $arr['success'] = 'NG';
                $arr['emailErr'] = true;
                $arr['msg'] = '您的邮箱还未激活,请先激活。如果注册邮箱【'.$userInfo['email'].'】错误,请联系管理员邮箱:wu_jy1984@126.com';
                echo json_encode($arr);
                exit;
            }
            
            
            
            //将用户信息中用户名 id 头像路径放入session中
            $_SESSION['uid']      = $userInfo['id'];
            $_SESSION['username'] = $userInfo['username'];
            $_SESSION['img']      = $userInfo['img'];

            //新通知条数也存入session
            /* 通知相关 因为每个在header显示所以需要加入session中*/
            $notice = D('Notice')->getActivedNotice();
            $_SESSION['activeNotice'] = $notice;
            $_SESSION['activeNoticeCount'] = count($notice);


            $arr['success'] = 'OK';
            echo json_encode($arr);
            exit;
        }else{
            $arr['success'] = 'NG';
            $arr['msg'] = '用户名或密码错误，请重新输入！';
            echo json_encode($arr);
            exit;
        }

    }

    /**
     * 退出登录
     *
     */
    public function logout(){
        unset($_SESSION['username']);
        unset($_SESSION['uid']);
        unset($_SESSION['img']);
        $this->redirect('Login/login');
    }

    /***********************************************用户注册***********************************************/
    /**
     * 注册
     */
    private function reg()
    {
        $msg = checkForm( $this->actionName );
        if( '' != $msg ){

            $arr['success'] = 'NG';
            $arr['msg'] = $msg;
            echo json_encode($arr);
            exit;
        }

        //判断新注册的用户名和邮箱地址是否已经存在
        switch (D('User')->doReg()) {
            case 1:
                $arr['success'] = 'NG';
                $arr['msg'] = '用户名或者邮箱地址已存在，请换个其他的用户名或者邮箱地址';
                break;
            case 2:
                $arr['success'] = 'NG';
                $arr['msg'] = '新用户追加到用户主表失败'.M('m_user')->getLastSql();
                break;
            case 3:
                $arr['success'] = 'NG';
                $arr['msg'] = '新用户追加到用户明细表失败';
                break;
            case 4:
                $arr['success'] = 'NG';
                $arr['msg'] = '注册失败，请联系wu_jy1984@126.com';
                break;
            case 100:
                $arr['success'] = 'OK';
                $arr['msg'] = '注册完成，请检查电子邮件。 ';
                break;
            default;
                $arr['success'] = 'NG';
                $arr['msg'] = '未知错误';
                break;
        }

        //M('m_user')->getLastSql();exit;
        echo json_encode($arr);
        exit;
    }

    /**
     * 激活注册邮件
     */
    public function activeEamil(){

        echo D('User')->activeUserEamil();
        exit;

    }

    /***********************************************用户注册***********************************************/

    /***********************************************忘记密码***********************************************/
    /**
     * 忘记密码时候执行操作
     */
    private function lostPass(){
        echo json_encode(D('Lostpass')->lostPassDo());
        exit;
    }
    /***********************************************忘记密码***********************************************/
}