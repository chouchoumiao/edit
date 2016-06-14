<?php
/**
 * Created by wujiayu.
 * User: Administrator
 * Date: 2016/5/19
 * Time: 13:59
 */

namespace Admin\Controller;

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
        if(D('login')->checklogin()){
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
                $arr['msg'] = '恭喜您，注册成功！<br/>请登录到您的邮箱及时激活您的帐号！！';
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